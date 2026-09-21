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


# GUÍA DEL CONSTRUCTOR DE TIENDAS CON IA — 🛠️ «EL MAESTRO» (`/crear-tienda`)

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** cuando haya que tocar, revisar o explicar el asistente que **arma una
> tienda conversando** (la página `/crear-tienda`), su motor, sus tablas, sus costos o su pestaña del
> Súper Admin.
> **Qué resuelve:** que cualquiera —con cuenta o sin cuenta— cree su tienda sin llenar ningún
> formulario: el asistente le pregunta una cosa a la vez, **mira sus fotos** y al final la tienda
> queda publicada; y después le ofrece **su primer producto**.
> **Estado:** ✅ **EN PRODUCCIÓN** (desplegado y probado el **2026-09-14**; **rediseñado el 2026-09-15**
> —usabilidad, fotos múltiples y arranque—, **vuelto a rediseñar esa misma tarde** con las 15 órdenes
> del §1quater —rubros múltiples, IA que piensa, publicación automática y cierre con enlace— y
> **rediseñado otra vez esa noche** con las órdenes del §1quinquies).
>
> 🎵🆕 **MÓDULO NUEVO DEL 2026-09-17 — LA CANCIÓN DE CADA TIENDA (el jingle de 40 segundos):** al crear
> una tienda, el robot le pide a **Treblo (Sonauto)** una canción comercial con **su nombre y su rubro**,
> la deja en **40,000 s exactos** con ffmpeg y la guarda en el hosting; suena en la ficha con un
> reproductor. Motor: **`includes/cancion.php`** · panel: **Súper Admin → 🎵 Canciones (Treblo)**.
> Todo el detalle en el **§16**. ⚠️ **Y un dato que manda: 1 canción = 100 créditos y las 4 cuentas
> están en 0** (§16.6): sin recargar, las tiendas nuevas se quedan sin canción.
>
> 🎯🆕 **VERSIÓN 7 (2026-09-15, noche, 3.ª parte) — «DOS PUERTAS Y EL FLUJO DEL TIPO DE NEGOCIO»
> (la de HOY)**: **solo quedan dos formas de crear una tienda** — **El caminante** (la captura en campo,
> que se queda **igual que está**) y **El maestro** (este módulo); las otras puertas
> (`registrar_negocio.php`, `crear_negocio.php`, `guardar_asistente.php`) **se borraron del hosting** y
> todos los enlaces del sitio apuntan a `/crear-tienda`. El flujo nuevo es:
> **8 fotos sí o sí** (con 4 el asistente dice *«todavía no lo podemos hacer, trabajamos a partir de 8
> fotos»*) → **el tipo de negocio** (🏪 tienda o local → su ubicación · 🛵 vendedor ambulante → su
> horario · 🌐 vende por internet → **los distritos donde entrega**, en **tarjetas 2×2 con su miniatura**
> y el botón **azul «🗺️ Todos los distritos»**) → lo que la IA leyó → **la tienda SE PUBLICA AL INSTANTE
> con sus 2 productos escritos por la IA** → el menú de **edición en grilla de 2 columnas**
> (`🛍️ Editar mis productos` · `🖼️ Editar la portada` · `📝 Editar otro dato` · `✅ No editar nada`) →
> **y al final el WhatsApp** (*«sería bueno que tus clientes también puedan llamarte o escribirte por
> WhatsApp… ¿a qué número quieres que te escriban?»*), que es lo que le da dueño a la tienda. Y **la barra
> de escribir está copiada de WhatsApp** (campo «Mensaje», emojis 😊, adjuntar 📎, cámara 📷 y el
> **micrófono negro redondo** que dicta y se convierte en ➤ enviar; se corta solo a los **2 minutos**).
> Paso a paso: **§2ter**. Órdenes del jefe: **§1sexies**.
> 📷 **VERSIÓN 6 (2026-09-15, noche, 2.ª parte) — «OCHO FOTOS Y CONFIRMA ANTES DE PUBLICAR»**:
> el mínimo son **8 fotos** (no 5) y el asistente **dice qué fotos quiere** (afuera, dentro, productos,
> máquinas, personal, tarjeta y folleto); **antes de publicar se confirman el nombre Y el número**, y si
> ese número **ya es de otra tienda** el asistente **se detiene y pregunta** («ese número ya tiene la
> tienda X, ¿es tuya?») **sin tocar nada** de la tienda que ya existe (orden del jefe: *«se puede dañar
> una tienda que ya exista… preguntaría si es el número correcto»*); y se completan **los campos que
> pide `registrar_negocio.php`** que faltaban: **la dirección** y **las redes (Facebook, Instagram,
> TikTok)**, más los **5 tipos de ubicación** de esa página. Detalle: **§1quinquies** (órdenes),
> **§2bis** (paso a paso) y **§5octies** (la lectura y la seguridad del teléfono).
> 📷🆕 **VERSIÓN 5 (2026-09-15, noche) — «CREA TU TIENDA CON NOSOTROS»: LAS FOTOS PRIMERO**
> (detalle en el **§1quinquies** y el paso a paso en el **§2bis**): el asistente saluda con
> **«Crea tu tienda con nosotros»**, el botón **abre la galería del celular** (en ese paso **no hay
> botón de cámara**), el dueño manda sus fotos y **una sola llamada de visión** saca de ahí
> **el nombre del letrero, el rubro, el teléfono del aviso, la dirección y lo que vende**
> (**§5octies**). Con eso se le confirma por botones y —en cuanto da su WhatsApp— **la tienda YA está
> publicada**, aunque él siga creyendo que la está creando: lo que falta (cómo vende, la dirección, la
> zona, el horario y las redes) se pregunta después y **cada respuesta actualiza la tienda en vivo, sin
> decirle que edita**; el enlace, su usuario y su clave se le muestran **al final**, como estreno.
> 🔄 **VERSIÓN 4 (2026-09-15, tarde) — «EL RUBRO PRINCIPAL + LA PUBLICACIÓN ES AUTOMÁTICA»** (detalle en
> el §1quater): **una tienda puede tener hasta 4 rubros o categorías**, la IA **piensa** si el negocio
> tiene local o trabaja llevando, «Todas las anteriores» en la zona, el **copy de la descripción lo
> escribe la IA**, la tienda **se publica sola** (ya no hay botón de publicar ni paso de aprobación),
> el dueño **edita después** sobre la tienda en línea, y el flujo **cierra con la felicitación y el
> enlace** (sin botón de eliminar y sin WhatsApp del jefe).
> 🔄 **VERSIÓN 3 (2026-09-15, mañana)** — el rediseño de usabilidad: **página sola de pantalla completa**,
> **galería con varias fotos** (1 a 8) y el **arranque** que distingue logueado de no logueado (§1ter).
> **VERSIÓN 2 (2026-09-14, tarde)**: dos botones de foto, la tienda primero y los productos después, la
> cuenta con su WhatsApp y 2 productos con el asistente (§1bis).
> 🔴 **VERSIÓN 8 (2026-09-20) — LA DE HOY: «LA UBICACIÓN PRIMERO» + «EL COMPOSITOR DE LA GUÍA»** (el
> detalle completo está en **§2octies**): **lo PRIMERO que ve el dueño es el BOTÓN MORADO de la
> UBICACIÓN** (antes de las 8 fotos y sin ningún paso previo; sin ubicación no sigue, y si no quiere dar
> el GPS puede **escribir su distrito**), y la barra de escribir **ya no es la de WhatsApp**: es la de la
> **guía** — **📷 (con su menú «Abrir cámara» / «Abrir galería») · 🎤 (el micrófono del buscador, con
> envío automático al segundo) · cuadro · ➤**. Se fueron el 😊, el 📎, el 🎤 verde y la ventana modal de
> la foto. ⚠️ **El Supremo sigue con el compositor de WhatsApp**: comparte sus clases (ver la trampa).
> **Archivos hermanos:** `GUIA_CHATBOT_DEEPSEEK.md` (el chat de ayuda 🧭, la otra puerta de IA),
> `GUIA_CAMINANTE_WEB.md` (la captura en campo), `GUIA_PANEL_DUENO_Y_CAPTURA_RAPIDA.md` (cámara y
> dictado), `GUIA_IMAGENES_Y_OPTIMIZACION.md` (WebP y versiones).

---

## §1. QUÉ ES (y de dónde salió)

Pedido del jefe (2026-09-14), textual y desordenado como él habla, resumido en sus 7 ideas:

1. *«una página privada así como es el caminante, una página solo para usuarios registrados»* que
   permita **usar nuestra API de Inteligencia artificial para crear una tienda**.
2. Lo primero que pregunte sea **cómo se llama la tienda**, dejando claro que **no son los productos**.
3. El asistente **se presenta correctamente** (tiene nombre y carácter propio).
4. Después de la descripción, **cuántos productos** (1, 3 o 5) explicando bien qué significa.
5. **Fotos de la tienda**: la IA las mira, comenta lo que ve y pide otro ángulo (*«veo sillas, veo
   mesas… mándame una de tu cocina o de tus compañeros»*), hasta 4 o 5.
6. **Un producto con sus fotos**, con opciones tomadas del contexto (*«¿te parece el ceviche solo, el
   combinado o los caldos?»*), y **la IA comenta la foto del plato** y pide otra más de cerca.
7. **Vendedor ambulante 🛵 vs venta por internet/delivery 🏠** (y local fijo 🏪), explicando la
   diferencia real: el ambulante camina por las calles de Chimbote todo el día; el de internet lleva
   el producto exclusivamente a la casa del cliente.

Y tres condiciones que puso encima de todo:

* **«Por fuerza tiene que usarse la API de IA, para que de esa manera se pueda medir el consumo.»**
* **«Voy a darte otra API, una API diferente para que use solamente esta área.»** → el constructor
  tiene **su propia clave y su propia cuenta** de DeepSeek (ver §6).
* **«Confío en tu criterio… que sea muy muy amigable.»**

Y al final: **asociarlo al Ninja** 🥷 con una opción que diga *crear tu primera tienda* o *ya tengo
una tienda, crear mi primer producto* (§8).

### 🆕 LO QUE PIDIÓ DESPUÉS (2026-09-14, tarde) — la versión 2

Textual (dictado), resumido en sus 6 órdenes:

1. **Dos botones cada vez que hay que subir una foto**: *«cada vez que haya que abrir la cámara que
   aparezca el botón de "clic aquí para abrir la cámara" y que aparezca también un segundo botón de
   "prefiero usar una imagen de mi galería"… no te compliques con los títulos, títulos cortos»*.
   → `📷 Cámara` y `🖼️ Galería`, **siempre juntos**, los dos en cada paso de foto.
2. **Fuera la pregunta de cuántos productos**: *«creo que fue innecesario tocar ese tema porque siempre
   el usuario creará su tienda; seríamos claros diciéndole "todavía no subas productos, estás creando
   tu tienda… puedes poner el letrero de afuera, un lugar referencial para llegar, fotos de tus sillas,
   de tu oficina"… el usuario debe entender que DENTRO de su tienda crea productos.»*
3. **El orden nuevo**: publicar la tienda → *«perfecto, ya tienes tu tienda y puedes mirarla aquí»* +
   el enlace → *«¿te parece si creamos tu primer producto?»* → se crea → **felicitación** →
   *«¿deseas eliminarlo o crear otro producto?»* (se puede **eliminar con un clic**).
4. **Al segundo producto**: se le felicita otra vez y, para agregar **más**, se le manda a un
   **WhatsApp del jefe (908 785 164)** —el número del administrador desde el **2026-09-19**; el `955 041 690` es hoy su número **personal** y quedó en `ADMIN_WHATSAPP_VIEJOS`— con el mensaje **ya escrito**: *«hola, mi tienda es tal y
   quisiera agregar más productos; ya tengo agregado dos productos: pollo frito y pollo sancochado»*.
5. **Su WhatsApp y su contraseña**: *«es muy importante pedirle al usuario su WhatsApp para que sepa
   luego cómo loguearse, y su contraseña… le pones un botón diciendo "¿te gustaría guardar estos datos
   en tu WhatsApp para que nunca te olvides tu contraseña?"»*.
6. **La contraseña, corta**: *«pueden ser tres letras y un número… no vamos a usar el cero para no
   confundir con la letra O; no usamos ni el cero ni la O: todas las letras de la A a la Z menos la O»*.

### 🆕 LO QUE PIDIÓ DESPUÉS (2026-09-15, tarde) — la versión 4

Dictado del jefe, en su orden, y qué se hizo con cada cosa:

| # | Lo que pidió (textual, resumido) | Cómo quedó |
|---|---|---|
| 1 | *«Una tienda, un negocio puede pertenecer a varios rubros… dile primero "elige el rubro principal"; si deseas puedes agregar también otros rubros hasta un máximo de cuatro rubros o categorías; **siempre usa la palabra rubros barra categorías**»* | Nuevo paso **`rubro_mas`**: tras elegir el principal se le ofrecen los otros (los que propuso la IA + los candidatos). Los extras se guardan en **`directorio_negocio_rubros`** (la tabla del editor de tiendas): la tienda sale en **todos** sus rubros. Máximo **4** (`TIENDA_IA_RUBROS_MAX`). |
| 2 | *«Hay un botón que dice "subir más" y **debe decir "subir más fotos"**»* | Los chips de fotos dicen **`🖼️ Subir más fotos`**. |
| 3 | *«La IA debe ser capaz de pensar… me dice "veo tus camiones azules… mándame la foto del frente de tu local", y **ahí no le estamos dando al usuario ninguna opción**: podría decir "sí tengo local" o "**no tengo local**"; cuando diga "no tengo local", le ofrecemos **entregas a domicilio**»* | La IA ahora contesta **TRES líneas**: capturas · comentario · **`LOCAL` / `DOMICILIO` / `IGUAL`**. Si dice **DOMICILIO**, al dueño le sale el chip **`🚚 No tengo local: lo llevo a su casa`**, que pone «lo llevo a tu casa» y **se salta** la pregunta de cómo vende. También se le ordenó **no pedir «la foto de tu local»** cuando lo que ve son camiones, montones de arena o reparto. |
| 4 | *«En los distritos… **no aparece el botón "todas las anteriores"**, falta ese botón»* | Chip **`🗺️ Todas las anteriores (atiendo en toda la zona)`**: la tienda queda con **Chimbote** de base y en su descripción se añade *«Atendemos en toda la provincia del Santa: Chimbote, Nuevo Chimbote, Coishco y Santa.»* (`TIENDA_IA_NOTA_TODA_LA_ZONA`). |
| 5 | *«El área donde se debe escribir está muy pequeña y no se nota; **debe ser un cuadro blanco**»* | El campo es un **cuadro blanco** con borde, 50 px de alto mínimo y foco marcado. |
| 6 | *«El botón de dictar está **repetido dos veces**… no es necesario que diga "dictar"; pon un **ícono**, como WhatsApp, que la gente ya lo tiene asociado»* | El campo ya no dice «o toca el micrófono» y el botón es **solo el ícono del micrófono** (SVG) con `data-dictado-icono="1"`; su nombre va en `aria-label`/`title`. |
| 7 | *«Aquí ya debe la IA aplicar el **copyright** (copywriting), sobre todo en la descripción de "de qué trata"… el copyright debe ser **en vivo, ahí creado**»* | `tienda_ia_ia_descripcion()`: al publicar, la IA escribe la **descripción** de la ficha con lo que contó el dueño, lo que **vio** en sus fotos, sus rubros, su zona y su horario. Si la IA falla, se guarda lo que escribió él. |
| 8 | *«**No debe preguntar**… ya debe estar publicado en ese momento, ya debe aparecer el link y decir "ya está tu tienda publicada, ¿deseas editar algo?"… **no pedimos aprobación para publicar, es automático**»* | **Se retiró el paso `resumen`** con el botón `🚀 PUBLICAR MI TIENDA`. Al terminar el horario la tienda **se publica sola** (`TIENDA_IA_PUBLICAR_AUTO`) y lo que se le muestra es la tarjeta **«Así ha quedado tu tienda»** + `✏️ Editar algo` (que **actualiza la tienda en línea**). |
| 9 | *«Cuando dice "crear mi primer producto" **debes decirle: recuerda que tu tienda está vacía, todavía no tiene productos**, vamos a agregar uno; **también te ha asignado un usuario y una clave**»* | El mensaje de la publicación lo dice tal cual, y los datos de entrada van en su tarjeta con el botón rojo. |
| 10 | *«El botón que dice **guardar en mi WhatsApp**… debe ser **llamativo, color rojo**; es muy importante que guarde su usuario y su contraseña; dile que **nunca más lo podrá volver a ver**»* | Botón **`💾 Guardar mis datos en mi WhatsApp`** en **rojo** (`tia-btn--rojo`, con un latido suave) y la nota *«Guárdalos AHORA: la contraseña se muestra una sola vez»*. |
| 11 | *«Cuando se está creando el primer producto… **"dale otra" no se entiende**; ese botón debería ser **"abrir galería" o "abrir cámara"**»* | Los chips de ese paso son **`📷 Abrir cámara`** y **`🖼️ Abrir galería`** (abren la cámara o el carrete sin ir al servidor). |
| 12 | *«Como ya tenemos las fotos cargadas, **muéstrale un slide con las fotos ya subidas** para que en base a eso también cree producto»* | En el paso de las fotos del producto se pinta una **tira con sus fotos** (`pintarSlideFotos`); al tocar una, esa foto se le pone al producto (el motor la **copia** a un archivo nuevo para que borrar una no rompa la otra). |
| 13 | *«**No me sirve el botón de eliminar**… prefiero una publicación mal hecha que una publicación que no existe»* | Fuera el botón `🗑️ Eliminar` de la tarjeta del producto (la función sigue existiendo, y si él lo pide **escribiendo** se le atiende). |
| 14 | *«Has puesto un botón de "quiero más productos" y **no es necesario**: ahí ya debe ir de frente "felicidades, tu tienda ya se encuentra con dos productos, por favor revísalo" y **le pones el link**… ya no es necesario que le pongas mi WhatsApp ahí»* | Al **segundo producto** el asistente **felicita, muestra el enlace y termina** (`fin`). El paso `mas_productos` quedó **retirado** (solo se atiende a las conversaciones viejas que se quedaron ahí, y se las lleva al cierre nuevo). |
| 15 | *«En el mensaje que me llega al WhatsApp… **siempre con contexto con un enlace**, enlace a la tienda que acaba de crear… eso me obliga a entrar al sitio, buscar y enterarme; si me dieras el link daría clic y ya estaría dentro»* | 🔗 **Regla de oro aplicada**: `tienda_ia_whatsapp_mas_url()` ahora incluye **`Mi tienda: <URL>`** en el mensaje (aunque ese botón ya no se le ofrezca al dueño, la regla queda cumplida si algún día se usa). |

Y lo que se le preguntó (2026-09-15, tarde):

| Pregunta | Lo que eligió |
|---|---|
| **¿Qué debe hacer «Todas las anteriores»?** | **Que la tienda atienda en TODA la zona** (base Chimbote + la nota en la descripción), y no el trabajo grande de que una tienda viva en varios distritos. |
| **¿Qué pasa al crear el segundo producto?** | **Termina ahí**: felicitación + enlace. Para más productos después: el ninja 🥷 o el botón del panel (`/crear-tienda?modo=producto`). |

### 🆕 LO QUE PIDIÓ DESPUÉS (2026-09-15) — la versión 3: el rediseño de usabilidad

Textual (dictado, y muy enojado): *«no me gusta nada de nada de nada desde el aspecto de la
usabilidad… muestra por ejemplo **el footer de la página dentro del área de chat** cuando le das clic
al botón galería; se supone que tienes libertad para elegir muchas galerías y los mensajes no son los
ideales; quiero que cambies **totalmente el diseño** en cuanto a la usabilidad, la experiencia del
usuario… **si tenemos una API de IA conectada a este módulo entonces debe ser capaz de diferenciar
cuando un usuario está logueado y cuando no**; si no está logueado se le invita a crear su primera
tienda, luego que crea su primera tienda se le invita a crear su primer producto; el usuario tiene
**libertad de subir una o cinco fotos o hasta ocho** si es necesario… le vamos a hacer preguntas como
el **número de teléfono en el cual quiere que sus clientes le escriban, que ese es el número de la
tienda**… y también le vamos a preguntar si desea tales **productos que le podamos crear en base a
sus fotos**… **hazlo como página, una página normal**»*.

Sus 7 órdenes, y qué se hizo con cada una:

| # | Lo que pidió | Cómo quedó |
|---|---|---|
| 1 | **«El footer de la página dentro del área de chat»** — y al preguntársele eligió **«página sola, pantalla completa»** | `crear_tienda_ia.php` **ya no incluye `includes/header.php` ni `includes/footer.php`**: es una página entera suya (§4bis). La barra de escribir **ya no va `position: fixed`**: es la última pieza de una columna `100dvh`, así que **debajo de ella no hay nada** (medido: 0 elementos debajo). |
| 2 | **«Libertad para elegir muchas galerías»** | El botón **🖼️ Elegir varias** abre el carrete con `multiple`: se pueden marcar **varias fotos de golpe** y se suben **en un solo envío** (`foto[]`). El tope son **8** y **con 1 ya se puede publicar** (`TIENDA_IA_FOTOS_TIENDA_MIN` 3 → **1**). |
| 3 | **«Los mensajes no son los ideales»** | Se reescribió **todo el guion** otra vez (§2): cada mensaje dice qué se espera de él, sin manuales, y las opciones se llaman por lo que hacen («✅ Listo», «🖼️ Subir más», «💰 Poner el precio»…). |
| 4 | **«Debe diferenciar cuando un usuario está logueado y cuando no»** | Nuevo **primer paso `arranque`** (§3bis): sin cuenta invita a **crear su primera tienda**; con cuenta y sin tiendas, lo mismo con su nombre; con tiendas, ofrece **🛍️ Agregar un producto** o **🛠️ Crear otra tienda**. Y al publicar **siempre** se le ofrece **su primer producto**. |
| 5 | **«El número de teléfono en el cual quiere que sus clientes le escriban: ese es el número de la tienda»** | El paso se pregunta así, textualmente: *«📱 ¿A qué número quieres que te escriban tus clientes? Ese será el número de tu tienda (y con él entras tú cuando quieras 😉)»*. |
| 6 | **«Productos en base a sus fotos»** | Lo que la IA **ve** en las fotos de la tienda se guarda en `datos['visto']` y entra en el pedido con que se proponen los productos: las opciones del primer producto salen de **lo que el dueño mostró**, no solo de lo que escribió (§5ter). Se le pregunta con *«¿Creamos tu primer producto? Con lo que vi en tus fotos ya tengo ideas 🛍️»*. |
| 7 | **¿Cuántos productos?** (se le preguntó) | **2, como estaba**: el asistente crea 2 y para más se le manda el **WhatsApp del jefe** con el mensaje ya escrito (la opción «hasta 5» se descartó). |

### 🆕 LO QUE PIDIÓ DESPUÉS (2026-09-15, noche) — la versión 5: LAS FOTOS PRIMERO

El jefe lo dictó como pregunta y eligió cada pieza cuando se le preguntó:

> *«¿Y qué pasaría si le dijéramos que pida al usuario mínimo cinco fotos y luego de las fotos que ha
> recibido analizarlas y ver qué datos puede obtener: si se puede adivinar el rubro, si se puede
> adivinar el título del negocio, tal vez el teléfono…? Por eso saludará diciendo **"Crea tu tienda con
> nosotros"**, cargar fotos y **abrir automáticamente la galería** y **no habría cámara**; el usuario
> podrá escoger una cierta cantidad de fotos y estas fotos serán analizadas por la inteligencia
> artificial y en base a eso **armará un prototipo de la tienda**, y los datos que le falten los irá
> pidiendo.»*

| # | Lo que pidió | Cómo quedó |
|---|---|---|
| 1 | **Saludar con «Crea tu tienda con nosotros»** | El paso `arranque` dice: *«¡Hola! 👋 Soy 🛠️ El maestro. **Crea tu tienda con nosotros** 🚀 Mándame **5 fotos** (el letrero, tu frente, lo que vendes) y yo la armo solito.»* |
| 2 | **«Cargar fotos y abrir automáticamente la galería»**, sin botón de cámara | El botón grande del saludo **es el carrete**: manda el valor `galeria`, el navegador abre el selector de fotos **en ese mismo toque** y el paso `fotos` va con `solo_galeria` (el botón 📷 Cámara queda **oculto** ahí; en las fotos del producto siguen los dos, como él los pidió antes). ⚠️ El navegador **no permite** abrir un selector de archivos sin un toque de la persona (bloquea el `.click()` automático): por eso el carrete se abre con el **primer toque**, no solo al cargar la página. Y no se pierde nada: el selector del propio celular ya ofrece «Tomar foto». |
| 3 | **Mínimo cinco fotos** (se le preguntó: ¿5 estricto, o 5 con salida a las 3?) | Eligió **MÍNIMO 5 ESTRICTO**: con menos **no avanza** (`TIENDA_IA_FOTOS_ARRANQUE_MIN`). El mensaje dice **exacto** cuántas faltan («Llevas 3 de 5 📸 Mándame 2 más») y se pueden **tomar en ese momento** con el mismo carrete. Tope: **8** por envío. |
| 4 | **Analizar las fotos y sacar de ahí los datos** (rubro, título, teléfono) | **`tienda_ia_ia_arranque()`**: UNA llamada de visión con el lote **a 1600 px** (para poder LEER el letrero) devuelve una **ficha de 8 líneas** —`CAPTURAS · LETRERO · RUBRO · TELEFONO · DIRECCION · LOCAL · PRODUCTOS · COMENTARIO`— y de ahí salen los botones para confirmar. Detalle y costos: **§5octies**. |
| 5 | **El teléfono del letrero: ¿se puede adivinar?** (se le preguntó qué hacer con él) | Eligió **«lo propone y él confirma»**: el número que se lee sale en un paso propio —*«📱 En tus fotos leí este número: **943112233**. ¿Es tu WhatsApp? (a veces es el de la imprenta que hizo tu letrero 😅)»*— y **jamás** se usa para crear la cuenta ni para actualizar una tienda sin que lo confirme (es el único dato que puede hacer daño: ver §5octies). |
| 6 | **Armar un prototipo y pedir lo que falte** | Con las 5 fotos el asistente **arma la tienda**: confirma el nombre en un toque, elige el rubro entre los que dedujo la IA y —en cuanto da el WhatsApp— **la tienda queda publicada**. Después pregunta **solo lo que falta** (cómo vende, la zona, el horario). |
| 7 | **«Publicar y vas preguntando si quiere editar algo, pero no le digas que está editando: hazlo creer que recién está creando su sitio, pero en sí el sitio ya está creado»** (textual) | **Publicación SILENCIOSA** (`tienda_ia_publicar_silencioso()`): la tienda se publica al dar el WhatsApp **sin decirle nada** y la conversación sigue `en_curso`; cada respuesta de después la actualiza **en vivo** (`tienda_ia_actualizar_silenciosa()`) y el asistente **nunca dice «edité»**. El enlace, su usuario y su clave se le muestran **una sola vez, al final** (`tienda_ia_revelar()`), como estreno. Los botones dejaron de decir «✏️ Editar algo»: ahora dicen **`📝 Cambiar algún dato`**. |

### 🆕 Y LO QUE PIDIÓ DESPUÉS (2026-09-15, noche) — la versión 6: las 8 fotos y la confirmación antes de publicar

Textual (dictado):

> *«Vamos a confiar un poco en la inteligencia artificial, pero sí tienes razón: **se puede dañar una
> tienda que ya exista**. En todo caso estaría creando una **subtienda temporal** o **preguntaría si es
> el número correcto**… **antes de hacer la publicación debe preguntar el nombre correcto**, o sea
> **confirmar si el nombre y el número son los correctos**. Y **pedir mínimo mínimo ocho fotos**: fotos
> de la parte de afuera de tu tienda, dentro de tu tienda, de tus productos, de las máquinas que usas,
> de tu personal; si tienes una **tarjeta** tómale fotos y si tienes un **folleto** también tómale foto
> — así tienes que decirle al usuario todo eso — y ya con los datos que saques de ahí son suficientes;
> y **si no sacas datos, aquí de esta página copia los datos que pide** `registrar_negocio.php`.»*

| # | Lo que pidió | Cómo quedó |
|---|---|---|
| 1 | **«Mínimo mínimo ocho fotos»** | `TIENDA_IA_FOTOS_ARRANQUE_MIN` = **8** (estricto: con 7 no avanza). El mensaje dice exacto cuántas faltan. |
| 2 | **Decirle QUÉ fotos** (afuera, dentro, productos, máquinas, personal, tarjeta, folleto) | El paso `fotos` lo dice tal cual (`TIENDA_IA_TEXTO_FOTOS_ARRANQUE`): *«Afuera de tu tienda 🏪 · dentro 🛋️ · tus productos 📦 · tus máquinas o herramientas 🧰 · tu personal 👥 · tu tarjeta 💳 · tu folleto 📄»*. Y a la IA se le dice que ésas son las fotos que se le pidieron (así entiende una **tarjeta** o un **folleto**: de ahí también se leen nombre, teléfono y dirección). |
| 3 | **«Se puede dañar una tienda que ya exista»** | 🔴 **Nada se toca si el número ya es de alguien**: al dar un WhatsApp que **ya tiene cuenta**, el asistente **NO publica ni actualiza nada** y pregunta —paso `tel_ocupado`— *«📱 Ese número ya tiene la tienda **«X»**. ¿Es tuya?»* → `✅ Sí, es mi número` (sigue el camino de siempre) · `🆕 No, me equivoqué de número` (se le borra y se le vuelve a preguntar). Mientras no conteste, **no existe nada suyo en la cuenta de otro**. Probado con un **cebo** real: `__tia_prueba12.php` (§11). |
| 4 | **«Confirmar si el nombre y el número son los correctos»** | El **nombre** se confirma con un toque (`nombre_ok`: *«En tu letrero leo: «X», ¿así se llama tu tienda?»*) y el **número** tiene su paso propio (`tel_leido`, con el aviso de la imprenta) o se pregunta (`whatsapp`) — **las dos cosas ANTES de publicar**. |
| 5 | **«Si no sacas datos, copia los datos que pide `registrar_negocio.php`»** | Se añadieron los campos de esa página que el asistente **no** preguntaba: **📍 la dirección** (paso `direccion`, y si la IA la leyó en las fotos **se la propone para confirmar**) y **📘 las redes** (paso `redes`: Facebook, Instagram y TikTok en **una sola pregunta**, opcional). Y **`vendedor` pasó de 3 a los 5 tipos** de esa página (`fisica · ambulante · domicilio · nacional · mayorista`). Todo se guarda en las mismas columnas (`direccion`, `facebook`, `instagram`, `tiktok`, `ubicacion_tipo`). |

### 🆕 Y LO QUE PIDIÓ DESPUÉS (2026-09-15, noche, 3.ª parte) — la versión 7: DOS PUERTAS Y EL FLUJO DEL TIPO DE NEGOCIO

Textual, y fue una orden sobre otra:

> *«Solo deben existir **dos formas de crear una tienda**: **uno, El caminante, que se va a quedar tal cual
> como está; y dos, el modo con inteligencia artificial**.»*
>
> *«**Ocho fotos sí o sí**… si te dice que solo tiene cuatro, dile que **todavía no lo podemos hacer,
> solamente trabajamos a partir de 8 fotos**. Luego le vas a preguntar **qué tipo de negocio es**: una
> tienda o un local, un vendedor ambulante, o una persona que vende por internet… y le muestras **los dos
> productos** que él tiene para que le dé clic y los pueda editar… El WhatsApp **es lo último**: sería bueno
> que tus clientes también puedan llamarte o enviarte mensajes de WhatsApp de tus pedidos, **¿a qué número
> deseas que te escriban?**»*
>
> *«Escribe con miniaturas Santa, Chimbote, Nuevo Chumbote y Coishco, procurando que quepan todas en una
> **grilla de 2 por 2**, y abajo, con **color azul**, «todos los distritos».»*
>
> *«Ahora quiero que **copies exactamente el menú de WhatsApp** de la parte de abajo, donde se dedica a
> enviar mensajes: **cópialos igual, muy igual**.»*

| # | Lo que pidió | Cómo quedó |
|---|---|---|
| 1 | **Solo dos formas de crear una tienda** | **El caminante** (`/caminante/`, intacto) y **El maestro** (`/crear-tienda`). Las tres puertas viejas se **borraron del hosting** (dan **404**, verificado por HTTP) y **todos los enlaces del sitio** (portada, menú, pie, panel, buscador, rubro, reclamos, registro, sitemap, chat 🥷) apuntan ahora a `/crear-tienda`. Ver **§8**. |
| 2 | **8 fotos «sí o sí»**, y con menos, decir que no se puede | `TIENDA_IA_FOTOS_ARRANQUE_MIN = 8` (estricto). El mensaje exacto: *«Todavía **no podemos empezar** 📸 Trabajamos **a partir de 8 fotos**. Llevas 4 y te faltan 4…»* (probado: con 4 **no avanza** y **no se pierde ninguna**). |
| 3 | **Preguntar el tipo de negocio** con sus tres ramas | Paso nuevo **`tipo`**: `🏪 Una tienda o local` → **la ubicación** · `🛵 Vendedor ambulante` → **el horario** (4 opciones + «después») · `🌐 Vendo por internet` → **los distritos donde entrega**. Las tres ramas terminan en la confirmación de lo que la IA leyó (nombre y rubro). |
| 4 | **Los distritos en grilla 2×2 con miniatura y el botón azul abajo** | Paso nuevo **`entregas`**: los 4 distritos visibles salen como **tarjetas** (`tarjeta: true`) en **grilla de 2 columnas**, cada una con su **chincheta y su color** (`color`, hecho por nosotros: no hay fotos de distritos), y debajo el botón **azul** `🗺️ Todos los distritos` (`azul: true`). Se pueden **marcar varios** (se pinta `✅` y aparece `✅ Ya está, seguir`). |
| 5 | **Crear la tienda y mostrarle sus 2 productos para editarlos** | **La tienda se publica AL INSTANTE** al confirmar el nombre y el rubro (sin pedir permiso, sin paso de aprobación) y la IA le deja **sus 2 productos** con **la foto donde los vio** y su descripción. Inmediatamente sale el menú de **edición** (paso `editar`) con **4 botones en grilla de 2 columnas**: `🛍️ Editar mis productos` · `🖼️ Editar la portada` · `📝 Editar otro dato` · `✅ No editar nada`. |
| 6 | **Editar un producto: la foto y la descripción** | Pasos nuevos `producto_elegir` → `producto_editar` → (`producto_editar_foto` \| `producto_editar_texto`). La foto puede ser **una nueva** o **una de las 8** (`usar_foto:<n>`), y en la descripción **él escribe y la IA le da el formato** (`tienda_ia_ia_descripcion_producto(..., $texto_dueno)`: *«respeta TODO lo que él escribió y cuéntalo mejor»*). |
| 7 | **Editar la portada** (mejor con una foto nueva) | Paso nuevo `portada`: se le pide una foto nueva (o una de las que ya mandó) y queda como **la primera de la galería** (`orden 0`), que es la que se ve en la ficha. |
| 8 | **El WhatsApp ES LO ÚLTIMO** | La tienda nace **sin teléfono** (`tienda_ia_publicar(..., $sin_telefono=true)`) y **sin dueño**; el número llega al final, en el paso `whatsapp`, con el texto que él dictó: *«📱 Una última cosa: **sería bueno que tus clientes también puedan llamarte o escribirte por WhatsApp** para sus pedidos. **¿A qué número quieres que te escriban?**»*. Con ese número se le **crea la cuenta** (usuario = su WhatsApp + clave), se le **asigna la tienda** y se le muestran sus datos **una sola vez** (paso `fin`). |
| 9 | **Copiar el compositor de WhatsApp «igual, muy igual»** | La barra de escribir es la de WhatsApp (§2quater): campo que dice **«Mensaje»**, **😊 emojis**, **📎 adjuntar** (la galería), **📷 cámara** y el **micrófono negro redondo** a la derecha, que **dicta en vivo** y **se convierte en ➤ enviar** en cuanto hay texto. Se corta solo a los **2 minutos** (tope que también pide la carta de requisitos). |

### Lo que decidió el jefe cuando se le preguntó (2026-09-14)

| Pregunta | Lo que eligió |
|---|---|
| **¿Qué significa 1 · 3 · 5 productos?** | **Es el arranque y SIEMPRE puede agregar más después.** Nada de límites: el número es lo que se hace hoy en esta conversación. |
| **¿Cómo se llama el asistente?** | **🛠️ «El maestro»** (el maestro albañil: el que levanta la tienda contigo). |
| **¿Y el mensaje de voz grabado?** | **Dictado en vivo y gratis** (Web Speech API, lo que ya usa el sitio). El audio que se transcribe con IA queda **pendiente**: cuesta por minuto y hace falta **otra clave** (la de DeepSeek **no** transcribe audio). |
| **¿Cómo seguimos?** | **«Constrúyelo ya, completo, y me lo muestras funcionando.»** |

---

## §2. LA CONVERSACIÓN, PASO POR PASO (el guion exacto)

> 🔴 **EL FLUJO DE HOY ES EL DEL §2ter** (versión 7: las 8 fotos, el tipo de negocio, la publicación al
> instante y el WhatsApp al final). La tabla de abajo es el flujo de la **versión 4** y **sigue vivo**
> para: (1) las conversaciones que quedaron a medias en un paso viejo, (2) el **modo «ya tengo una
> tienda»** (`?modo=producto`, que empieza en `producto_nombre`), (3) los pasos de **producto**
> (`producto_nombre` → `producto_fotos` → `producto_precio` → `producto_listo`, que en el flujo nuevo
> **no se usan al principio**: los 2 productos los pone la IA) y (4) los pasos que el flujo nuevo
> reutiliza tal cual (`rubro_mas`, `whatsapp`, `distrito`, `horario`, `nombre_ok`, `rubro_ok`, `fin`) y
> los de **`✏️`/`📝` cambiar un dato** (`edit:*`). Todo eso está en el **mismo motor** y no se toca.

> ### ⚡ REGLA DE ESTILO (orden del jefe, 2026-09-14 noche)
> *«Solamente con leer la entrada ya me aburriste… no se ve práctico, no se ve rápido. Tienes que buscar
> ser **minimalista, rápido, sin tanto detalle**: cambia los textos, hazlo más **dinámico, más directo**.»*
>
> **Cómo se escribe todo lo que dice el asistente** (y lo que dice la IA, que es la otra mitad):
> · **1 o 2 líneas por mensaje.** Nunca un párrafo, nunca una lista de más de 3 puntos.
> · **La IA: UNA frase de máximo 15 palabras** (está en el `system`: «como en un chat de WhatsApp»).
> · **Nada de explicar lo que ya se entiende** (ni decir «aquí no hay formularios», ni «nada se publica
>   hasta el final»: el botón de publicar ya lo dice).
> · **Ejemplo del antes y el después:**
>
> ❌ ANTES (aburría): *«¡Hola! 🛠️ Soy El maestro y te voy a ayudar a armar tu tienda. Aquí no hay
> formularios: te pregunto una cosa a la vez, tú me contestas escribiendo o hablando (toca el 🎙️), y al
> final tu tienda queda publicada con tus fotos. Son 5 minutitos 😉 Y tranquilo: nada se publica hasta
> que tú toques el botón al final.»*
>
> ✅ AHORA: *«¡Hola! 🛠️ Soy **El maestro**. Te armo la tienda en 5 minutos 🚀 Te pregunto, me contestas
> (o me hablas 🎙️) y ya.»* → y de una: *«**¿Cómo se llama tu tienda?** (el nombre, no lo que vendes 😎)»*


El que manda los pasos es **el servidor**, no la IA (§4). Cada paso tiene su **texto local** (el
guion, ya escrito y aprobado por criterio) y la IA solo pone **las palabras del momento**: aprueba el
nombre, resume lo que entendió, propone productos, comenta las fotos. Si la IA falla, responde vacío
o se acaba el saldo, **el guion local sigue solo** y la conversación no se rompe nunca.

| # | Paso | Qué dice (resumen) | Quién aporta |
|---|---|---|---|
| 0 | `arranque` | 🚪 **Lo primero que ve** (y cambia según quién llega, §3bis): *«¡Hola! 👋 Soy 🛠️ El maestro y veo que todavía no tienes tienda en DeChimbote.com. Te la armo en 5 minutos: tú me cuentas, yo la publico con tu WhatsApp. ¿Vamos?»* → `🚀 Crear mi primera tienda` · `🔑 Ya tengo cuenta` | local (sabe quién eres) |
| 1 | `nombre` | *«**¿Cómo se llama tu tienda?** 😎 El nombre del negocio, no lo que vendes (así sale en DeChimbote.com).»* | local + **IA valida** |
| 2 | `trato` | *«**Cuéntame de «X»**: qué vendes, a quién y dónde atiendes. Si prefieres, toca el 🎙️ y me lo dices hablando.»* | local + **IA resume** |
| 3 | `rubro` | *«🏷️ **Elige el rubro principal** de tu tienda 👇 Si deseas, puedes agregar también otros rubros o categorías: **hasta 4** en total.»* (1-3 rubros REALES) | claves + **IA elige** |
| 4 | `rubro_mas` | *«Ya tienes el principal: **X** 🏷️ ¿Le sumas otros rubros o categorías? Puedes tener hasta 4 en total.»* → los otros rubros + `✅ Listo, seguimos` | local |
| 5 | `fotos_tienda` | ***«Ahora las fotos de tu tienda** 📷 … **de 1 a 8**, tú eliges»* → **📷 Abrir cámara** y **🖼️ Abrir galería**; la IA dice qué ve en el lote y **piensa** si es LOCAL o DOMICILIO (§5quinquies) | local + **IA mira y piensa** |
| 6 | `vendedor` | *«🛵 Ambulante · 🏠 Lo llevo a tu casa · 🏪 Local fijo»* — **se salta** si tocó `🚚 No tengo local` | local |
| 7 | `distrito` | *«📍 ¿En qué zona estás?»* + `📍 Usar mi ubicación` · **`🗺️ Todas las anteriores (atiendo en toda la zona)`** · los distritos | local (GPS) |
| 8 | `whatsapp` | *«📱 **¿A qué número quieres que te escriban tus clientes?** Ese será el número de tu tienda (y con él entras tú cuando quieras 😉).»* | local |
| 9 | `horario` | *«🕐 ¿En qué horario atiendes?»* → **y AQUÍ LA TIENDA SE PUBLICA SOLA** (antes, la IA escribe el copy de la descripción) | local + **IA escribe** |
| — | `resumen` | **PASO RETIRADO**: era la aprobación con `🚀 PUBLICAR MI TIENDA`. Solo se atiende a conversaciones viejas. | — |
| 10 | `publicado` | 🎉 *«¡Listo! Tu tienda «X» ya está publicada! \<enlace\>»* + el aviso de las fotos comprimidas y **la hora exacta** + *«Ojo: tu tienda todavía no tiene productos. Vamos a agregar el primero.»* + 🔑 usuario y clave con el **botón rojo** → `🛍️ Crear mi primer producto` · `✏️ Editar algo` · `🏪 Ver mi tienda`. Debajo va la tarjeta **«Así ha quedado tu tienda»** | local |
| 11 | `producto_nombre` | *«**¿Cuál va a ser tu primer producto?** Toca uno o escríbeme el tuyo 👇»* (opciones sacadas de su relato **y de lo que la IA vio en sus fotos**) | **IA propone** |
| 12 | `producto_fotos` | *«📷 Mándame la foto de X…»* + **la tira con las fotos que ya subió** (toca una y se la pone al producto) + `💰 Poner el precio` · `📷 Abrir cámara` · `🖼️ Abrir galería` | local + **IA mira** |
| 13 | `producto_precio` | *«💰 ¿Cuánto cuesta X? Escribe solo el número 👇»* → **el producto se crea EN LÍNEA** | local |
| 14 | `producto_listo` | 🎉 *«¡«X» ya está en línea! ¿Creamos otro producto?»* → `➕ Crear otro producto` · `🏪 Ya está, gracias` (**sin botón de eliminar**) | local |
| 15 | `fin` | 🎉 *«¡Felicidades! Tu tienda «X» ya tiene 2 productos (A, B). Mírala aquí 👇 \<enlace\>…»* + `🏪 Ver mi tienda` · `✏️ Editar algo` | local |
| — | `mas_productos` | **PASO RETIRADO**: mandaba al WhatsApp del jefe. Al segundo producto se cierra con el enlace. | — |

**El tope de 2 productos con el asistente** (`TIENDA_IA_PRODUCTOS_CON_IA`): al segundo, en vez de
«Crear otro» se le ofrece **💬 Quiero más productos**, que abre el WhatsApp del jefe
(`TIENDA_IA_WHATSAPP_MAS`, = `ADMIN_WHATSAPP` = **908785164** desde el **2026-09-19**; el `955041690` es hoy el número **personal** del jefe, en `ADMIN_WHATSAPP_VIEJOS`) con este mensaje ya escrito:

> *Hola 👋 Mi tienda es «Combinado Doña Lucha» y quisiera agregar más productos a mi tienda en
> dechimbote.com. Ya tengo 2 productos: Caldo de gallina y Ceviche mixto. Así quisiera agregar más
> productos. ¿Me puedes ayudar? 🙏*

**Modo «ya tengo una tienda»** (`/crear-tienda?modo=producto`): el mismo motor, con los pasos
`tienda` (elegir cuál de sus tiendas) → `producto_nombre` → `producto_fotos` → `producto_precio` →
publicar (crea **solo el producto** en la tienda elegida).

**Detalles que hacen que funcione de verdad:**

* El nombre se acepta a la **segunda**: si el dueño escribe lo que vende, la IA contesta
  *«Eso es lo que vendes, no el nombre. ¿Cómo se llama tu tienda? Por ejemplo: «Caldos Doña Rosa» o
  «El Buen Sabor».»* y **no avanza** hasta que dé un nombre.
* El relato (paso 2) pide **mínimo 6 palabras**: si contesta «caldos», se le dice *«cuéntame un
  poquito más 👀»* — igual que hace el chat 🥷 con los 10 consejos.
* **Candado de las fotos (2026-09-15):** con **1** foto ya se puede publicar
  (`TIENDA_IA_FOTOS_TIENDA_MIN`) y el tope son **8** (`…_MAX`); desde la **3.ª**
  (`TIENDA_IA_FOTOS_TIENDA_IDEAL`) el propio asistente recomienda subir más, pero **nunca lo obliga**
  (orden del jefe: *«el usuario tiene libertad de subir una o cinco fotos o hasta ocho»*). Al llegar a
  8 el asistente **pasa solo** al paso siguiente. El producto necesita **1** foto como mínimo (hasta 3).
* **Varias fotos de un solo golpe (§5ter):** el dueño puede marcar 5 u 8 fotos en el carrete y se
  mandan **en un solo envío**; el motor las guarda todas y la IA las mira **en UNA sola llamada**.
**Cuando el dueño se equivoca, se corrige EN VIVO**: en la tarjeta de la tienda publicada (y en la del
cierre) hay **`✏️ Editar algo`**, que abre los chips (nombre · de qué trata · rubro principal · **otros
rubros o categorías** · fotos · cómo vendo · zona · WhatsApp · horario). Al contestar, el motor
**actualiza la tienda que ya está en línea** (`tienda_ia_actualizar_publicada()`) y le dice *«✅ Listo, ya
lo cambié en tu tienda (…).»* — ya no hay paso de aprobación que volver a llenar. Si edita **de qué
trata**, la descripción **se vuelve a escribir** (copy nuevo de la IA); si edita **las fotos**, las nuevas
**se suman** a su galería (no se le borran las que ya tenía publicadas).

---

## §2bis. 📷 EL FLUJO DE LA VERSIÓN 5/6, PASO POR PASO (histórico: las fotos primero y la publicación silenciosa)

> ⚠️ **Esta tabla ya NO es el flujo de hoy: es la versión 5/6** (2026-09-15, noche). El flujo vivo está
> en el **§2ter**. Lo que sigue valiendo de aquí: **cómo se lee la ficha de las fotos** (`nombre_ok`,
> `rubro_ok`, `tel_leido`), **la seguridad del número** (`tel_ocupado`) y los pasos de producto
> (`producto_*`), que el flujo nuevo **no usa al principio** (los 2 productos los pone la IA) pero que
> **siguen vivos** para el modo `?modo=producto`.

| # | Paso | Qué dice (resumido) | Quién aporta |
|---|---|---|---|
| 0 | `arranque` | 🚪 *«¡Hola! 👋 Soy 🛠️ El maestro. **Crea tu tienda con nosotros** 🚀 Mándame **8 fotos** de tu negocio y yo la armo solito.»* → **`📷 Cargar las fotos de mi negocio`** (el botón **abre el carrete**) · `🔑 Ya tengo cuenta` | local |
| 1 | `fotos` | 📷 *«**Mándame 8 fotos de tu negocio.** Afuera de tu tienda 🏪 · dentro 🛋️ · tus productos 📦 · tus máquinas o herramientas 🧰 · tu personal 👥 · tu tarjeta 💳 · tu folleto 📄»* → y mientras llegan: *«Llevas **3 de 8** fotos 📸 Mándame **5** más…»*. **Sin botón de cámara** (solo el carrete). La IA **comenta lo que ve** en cada tanda y **lee la ficha** (§5octies). Con 8 → pasa solo | local + **IA mira y lee** |
| 2 | `nombre_ok` | ✅ *«📷 En tu letrero leo: **«FERRETERIA EL MARTILLO DE ORO»** ¿Así se llama tu tienda?»* → `✅ Sí, así se llama` · `✏️ Es otro nombre` (que lleva al paso `nombre` de siempre) | local + **IA leyó** |
| 3 | `rubro_ok` | 🏷️ *«Por tus fotos diría que tu tienda es de **ferretería**. ¿Le atino?»* → **rubros REALES** del directorio + `✏️ Ninguno de estos` | local + **IA leyó** |
| 4 | `rubro_mas` | 🏷️ *«Ya tienes el principal: **Ferreterías**. ¿Le sumas otros rubros o categorías? Hasta 4 en total.»* | local |
| 5 | `tel_leido` | 📱 **solo si se leyó un número**: *«En tus fotos leí este número: **943112233**. ¿Es tu WhatsApp? (a veces es el de la imprenta que hizo tu letrero 😅)»* → `✅ Sí, es mi WhatsApp` · `✍️ No, es otro` | local |
| 6 | `whatsapp` | 📱 *«¿A qué número quieres que te escriban tus clientes? Ese será el número de tu tienda…»* → **🚀🤫 Y AQUÍ LA TIENDA SE PUBLICA, SIN DECIRLE NADA** | local |
| — | `tel_ocupado` | 🔴 **solo si ese número ya es de alguien**: *«📱 Ese número ya tiene la tienda **«X»**. ¿Es tuya?»* → `✅ Sí, es mi número` (publica y sigue) · `🆕 No, me equivoqué de número` (vuelve al paso 6). **Aquí NO se ha publicado nada** | local |
| 7 | `vendedor` | *«¿Cómo le llega a tu cliente?»* → `🏪 Local fijo` · `🛵 Ambulante` · `🏠 Lo llevo a tu casa` · `🚚 Vendo a todo el país` · `📦 Vendo al por mayor` (los **5 tipos de `registrar_negocio.php`**), o el chip `🚚 No tengo local` si la IA lo vio → **en vivo** | local |
| 8 | `direccion` | 📍 Si la IA leyó una dirección: *«En tus fotos leí esta dirección: **Av. Jose Pardo 123**. ¿Es la tuya?»* → `✅ Sí, es esa` · `✏️ Es otra` · `⏭️ Después`. Si no, la pregunta tal cual (calle y número, o una referencia) → **en vivo** | local + **IA leyó** |
| 9 | `distrito` | 📍 *«¿En qué zona estás?»* + `📍 Usar mi ubicación` · **la que se leyó en el letrero** · `🗺️ Todas las anteriores` · los distritos → **en vivo** | local (GPS) |
| 10 | `horario` | 🕐 *«¿En qué horario atiendes?»* → **en vivo** | local |
| 11 | `redes` | 📘 *«¿Tienes Facebook, Instagram o TikTok? Pásame los que uses (o toca «Después»)»* → **🎉 Y AQUÍ EL ESTRENO**: *«¡Listo! Tu tienda «X» ya está publicada! \<enlace\>»* + las fotos comprimidas + **la hora** + *«tu tienda todavía no tiene productos»* + 🔑 **usuario y clave** con el botón rojo → `🛍️ Crear mi primer producto` · `📝 Cambiar algún dato` · `🏪 Ver mi tienda` | local + **IA escribe el copy** |
| 12 | `publicado` → `producto_*` → `fin` | Igual que siempre: el primer producto, el segundo y el cierre con la felicitación y el enlace. ⚠️ Al publicar **la conversación se cierra** (`estado='publicada'`), así que el producto se atiende **en una conversación nueva** que entra por el `arranque` con `valor='producto'` | igual que la v4 |

**Las tres cosas que hay que tener claras para no romperlo:**

1. **Lo primero que llega puede ser un LOTE DE FOTOS, sin ningún paso antes.** El botón del saludo abre
   la galería, así que el navegador hace un POST `multipart` con `foto[]` y la conversación **todavía
   está en `arranque`**. Por eso el caso `arranque` de `tienda_ia_recibir()` atiende las fotos
   (`tienda_ia_paso_fotos()`): si no, caerían en el saludo y **se perderían** (el dueño tendría que
   volver a elegirlas). Lo mismo vale para un arrastre de fotos al chat.
2. **La publicación silenciosa NO cierra la conversación.** `tienda_ia_publicar(..., $silencioso=true)`
   deja `estado='en_curso'` y **no toca `paso`** (si lo cerrara, el siguiente POST abriría **otra**
   conversación en el `arranque` y el flujo se cortaría a mitad: ver trampa 28).
3. **Todo lo que contesta después de publicar se guarda con `tienda_ia_actualizar_silenciosa()`**
   (`tienda_ia_actualizar_publicada(..., $con_desc=false)`: sin reescribir el copy en cada respuesta,
   que sería una llamada de la IA por pregunta). El copy definitivo se escribe **una sola vez**, en el
   estreno, con todo lo que ya sabe.

---

## §2ter. 🎯 EL FLUJO DE HOY, PASO POR PASO (versión 7, 2026-09-15 noche)

> 🔴 **Este es el flujo que ve hoy cualquiera que entre a `/crear-tienda`.** Lo manda el servidor
> (`tienda_ia_recibir()`), la IA solo mira, lee y escribe: **cada paso tiene guion local de respaldo**, así
> que la conversación **nunca se traba** aunque la IA falle. Los textos están en `tienda_ia_guion()`.
>
> 🔴 **CAMBIO DEL 2026-09-20 (versión 8, la de HOY): ANTES DE LAS FOTOS VA LA UBICACIÓN.** La tabla de
> abajo (versión 7) queda como historia **en dos puntos**: en el paso **0** (`arranque`) el botón ya **no**
> es «📷 Cargar las 8 fotos» sino **`📍 Ubicación` (MORADO)** —lo primero que ve el dueño— y en el paso
> **3a** ya **no se vuelve a preguntar la zona** (se pregunta al principio). Todo el detalle, con el
> porqué y la prueba: **§2octies**.

| # | Paso | Qué dice (resumido) | Quién aporta |
|---|---|---|---|
| 0 | `arranque` | 🚪 *«¡Hola! 👋 Soy 🛠️ El maestro. **Vamos a crear tu primera tienda** 🚀 Necesito **8 fotos** de tu negocio: la parte de afuera 🏪, tus productos 📦, tus servicios… **Dame las 8 fotos y yo me encargo del resto.**»* → **`📷 Cargar las 8 fotos de mi negocio`** (el botón **abre el carrete**) · `🔑 Ya tengo cuenta`. Con cuenta y con tiendas, el saludo cambia a *«Ya tienes N tiendas… ¿Qué hacemos hoy?»* | local |
| 1 | `fotos` | 📷 *«**Mándame 8 fotos de tu negocio**: afuera 🏪 · dentro 🛋️ · tus productos 📦 · tus máquinas 🧰 · tu personal 👥 · tu tarjeta 💳 · tu folleto 📄»*. **Sin botón de cámara** (solo el carrete). 🔴 **Con menos de 8 NO avanza**: *«Todavía **no podemos empezar** 📸 Trabajamos **a partir de 8 fotos**. Llevas 4 y te faltan 4…»*. Con las 8: *«¡Ya tengo tus 8 fotos! 📸 Con esto te armo la tienda.»* → pasa solo | local + **IA mira y lee la ficha** (§5octies) |
| 2 | `tipo` | 🏪 *«**¿Qué tipo de negocio es?** Toca el tuyo 👇»* → `🏪 Una tienda o local` · `🛵 Vendedor ambulante` · `🌐 Vendo por internet` | local |
| 3a | `direccion` → `distrito` | 🏪 **Tienda o local**: *«📍 ¿Cuál es tu dirección? (la calle y el número, o una referencia)»* (si la IA la leyó en las fotos, **se la propone para confirmar**) → y después **la zona** (*«📍 ¿En qué zona estás?»* + `📍 Usar mi ubicación` + los distritos) | local + **IA leyó** |
| 3b | `horario` → `distrito` | 🛵 **Vendedor ambulante**: *«🛵 ¡Vendedor ambulante! Entonces dime tu horario»* → `🌅 Mañanas` · `🌇 Tardes` · `🌙 Noches` · `🕐 Todo el día` · `⏭️ Después` → y después **la zona** | local |
| 3c | `entregas` | 🌐 **Vende por internet**: *«**¿En qué distritos entregas tus productos?** Toca todos los que repartes 👇»* → **las 4 tarjetas en grilla 2×2** (Chimbote · Coishco · Nuevo Chimbote · Santa, cada una con su chincheta y su color; se marcan varias y se pintan `✅`) + **abajo el botón azul `🗺️ Todos los distritos`** + `✅ Ya está, seguir` cuando ya marcó alguna | local |
| 4 | `nombre_ok` | ✅ *«📷 En tu letrero leo: **«X»** ¿Así se llama tu tienda?»* → `✅ Sí, así se llama` · `✏️ Es otro nombre` (lleva al paso `nombre` de siempre) | local + **IA leyó** |
| 5 | `rubro_ok` | 🏷️ *«Por tus fotos diría que tu tienda es de **cevichería**. ¿Le atino?»* → **rubros REALES** del directorio + `✏️ Ninguno de estos` | local + **IA leyó** |
| 6 | `rubro_mas` | 🏷️ *«Ya tienes el principal: **Cevicherías**. ¿Le sumas **otros rubros o categorías**? Puedes tener **hasta 4** en total.»* Los que ofrece salen primero del **mapa de afinidades** del rubro elegido (a una cevichería: pescaderías, restaurantes, bodegas) y después de lo que la IA **vio** en las fotos | local |
| 7 | **🚀 LA PUBLICACIÓN** | **Al tocar «Seguir» aquí la tienda SE PUBLICA AL INSTANTE** (`tienda_ia_crear_tienda_final()`): se crea **sin teléfono y sin dueño**, la IA **escribe la descripción** y le deja **sus 2 productos** (los que vio en las fotos, con **su foto** y su descripción). Mensajes: *«🛍️ Te dejé **2 productos** armados con tus fotos.»* + *«🎉 **¡Ya está en internet!** Mírala aquí 👇 \<enlace\>»* | local + **IA escribe el copy** |
| 8 | `editar` | 🎉 *«**¡Ya está tu tienda!** Le puse 2 productos: «X» y «Y». **¿Quieres cambiar algo?**»* → **4 botones en grilla de 2 columnas**: `🛍️ Editar mis productos` · `🖼️ Editar la portada` · `📝 Editar otro dato` · `✅ No editar nada`. **No hay botón de eliminar** y **no se le dice «editar»** en ningún momento. También llega aquí la **tarjeta resumen** (`tienda_ia_resumen()`) | local |
| 9a | `producto_elegir` → `producto_editar` | 🛍️ *«¿Cuál de tus productos quieres cambiar?»* (solo los que ya tiene + `⬅️ Volver`) → *«✏️ **«X»** ¿Qué le cambiamos?»* → `📷 Cambiar la foto` · `📝 Cambiar la descripción` · `✅ Ya está` · `⬅️ Volver` | local |
| 9b | `producto_editar_foto` | 📷 *«**Mándame la foto nueva de «X»** o toca una de las que ya subiste 👇»* → la nueva se guarda como **la primera de su galería y en `imagen`** (la que muestra la ficha) | local + **IA** (guarda WebP) |
| 9c | `producto_editar_texto` | ✍️ *«**¿Cómo es «X»?** Escríbelo o dicta 🎙️ (una o dos líneas) y yo lo dejo bonito.»* (o el chip `✨ Que lo escriba la IA`) → **lo que él escribió pasa por el copywriting** y se le muestra: *«✅ Así quedó la descripción…»* | **IA escribe** |
| 9d | `portada` | 🖼️ *«**¿Qué foto quieres de portada?** Es la que se ve primero en tu tienda. Súbela aquí o toca una de las que ya mandaste 👇»* → la elegida pasa al **principio de la galería** (`orden 0`) | local |
| 10 | `whatsapp` | 📱 *«Una última cosa: **sería bueno que tus clientes también puedan llamarte o escribirte por WhatsApp** para sus pedidos. **¿A qué número quieres que te escriban?**»* → el número leído en las fotos (si lo hay) · `⏭️ Por ahora no` | local |
| — | `tel_ocupado` | 🔴 **solo si ese número ya es de alguien**: *«📱 Ese número ya tiene la tienda **«X»**. ¿Es tuya?»* → `✅ Sí, es mi número` (cierra) · `🆕 No, me equivoqué de número` (se le borra y se le vuelve a preguntar). **La tienda ya está publicada**, así que aquí solo se cierra con su número | local |
| 11 | `fin` | 🎉 *«**¡Felicidades! Tu tienda «X» ya tiene 2 productos** (…). Mírala aquí 👇 \<enlace\>…»* + *«🛍️ **¿Quieres más productos?** Toca **Agregar otro producto** y te lo dejo listo»* + *«📦 ¿Son **muchos**? Cárgalos tú con la cámara y la voz en tu inventario: \<enlace a `productos.php?n=ID#crear`\>»* + *«🏪 **¿Tienes otro negocio?**…»* + 🔑 **usuario y clave** la primera vez (con el aviso de que **la clave no se vuelve a mostrar**) + `🏪 Ver mi tienda` · **`🛍️ Agregar otro producto`** · **`📷 Mandar fotos de mis productos`** · **`🆕 Crear otra tienda`** · `📝 Cambiar algún dato` | local |
| 12 | `productos_lote` | 📷🆕 *«**Mándame las fotos de tus productos** —varias de una vez— y yo te los clasifico y los publico solos»*. El dueño manda una **tanda de fotos** (hasta 8) y la IA **las clasifica en UNA sola llamada**: nombre, una frase de descripción y **el precio solo si está escrito en la foto**. Se publican **todos de un tirón**, se le muestra la lista (*«🎉 Clasifiqué 3 productos… Ya están en línea»*) y **se reescribe el copy** con lo nuevo. Repite las veces que quiera (`✅ Ya está, gracias` para cerrar). Detalle: **§2sexies** | local + **IA clasifica** |

**Las cuatro cosas que hay que tener claras para no romperlo:**

1. **La tienda se publica ANTES de tener dueño y ANTES de tener teléfono.** `tienda_ia_publicar(...,
   false, true, true)` (simulacro = no, **silencioso = sí**, **sin teléfono = sí**) inserta la fila con
   **`dueno_id = NULL`** y `whatsapp/telefono = NULL`. ⚠️ **`dueno_id` tiene una llave foránea a
   `directorio_usuarios` (`fk_negocio_dueno`): con `0` el INSERT revienta** (trampa 33). Hasta que llegue
   el WhatsApp, la tienda se maneja por **`datos['publicado']['negocio_id']`**
   (`tienda_ia_negocio_del_dueño()` ya sabe hacerlo sin cuenta), y al dar el número
   `tienda_ia_cerrar_con_telefono()` le pone el WhatsApp, le **crea la cuenta** y le **asigna la tienda**
   (`dueno_id`). Esa última pregunta es **obligatoria**: sin ella la tienda quedaría sin dueño.
2. **Publicar NO cierra la conversación.** Se publica en modo silencioso (`estado='en_curso'` y sin tocar
   `paso`); el paso lo pone `tienda_ia_crear_tienda_final()` en **`editar`** y **se guarda antes de
   guardar** (trampa 34). Si la publicación cerrara la conversación, el siguiente POST abriría **otra**
   conversación en el `arranque` y el flujo se cortaría justo después de crear la tienda.
3. **Las banderas de dibujo viajan al navegador.** El guion marca las opciones con
   `tarjeta` + `color`, `azul` y `rejilla`, pero quien las manda al navegador es **`tia_opciones()`** (la
   puerta): si no las deja pasar, **todo sale como chips sueltos** (trampa 35) y las tarjetas de los
   distritos, el botón azul y la grilla de 2×2 no se pintan.
4. **El orden de los pasos lo manda el servidor, y cada rama del `tipo` vuelve al mismo sitio**
   (`nombre_ok`). Las tres ramas dejan el `vendedor` puesto (`fisica` · `ambulante` · `domicilio`) y la
   zona (`distrito`/`entregas`), y de ahí sale **todo** el copy de la descripción.

---

## §2quater. 💬 EL COMPOSITOR COPIADO DE WHATSAPP (la barra de escribir)

> 🗄️ **SUPERADO PARA EL MAESTRO EL 2026-09-20** (orden posterior del jefe: *«quiero llevar este mismo
> estilo de los botones —el botón de cámara, el botón de grabador, el input y el botón de enviar— a la
> página del maestro, tal cual como lo tenemos ahorita en la guía, tal cual»*): **El maestro ya usa el
> compositor de la guía** (📷 · 🎤 · cuadro · ➤, sin 😊 ni 📎), y todo lo de este apartado quedó como
> **historia**. Estado de hoy y el porqué: **§2octies**.
> ⚠️ **PERO NO SE BORRA NADA DE AQUÍ: 👑 EL SUPREMO (`/supremo`) SIGUE USANDO ESTE MISMO COMPOSITOR** —
> reusa sus clases (`.tia-wa`, `.tia-wa__campo`, `.tia-wa__ico`, `.tia-wa__mic`, `.tia-emoji`) con sus
> propios ids (`#supEmoji`, `#supClip`, `#supCam`, `#supEnviar`, `#supEmojiPanel`) y **el CSS vive
> compartido en `assets/css/tienda_ia.css`**: si alguien lo borra al limpiar El maestro, **El Supremo se
> queda sin estilos** (trampa 51 de §12).

Orden del jefe (2026-09-15, noche, textual): *«Ahora quiero que **copies exactamente el menú de WhatsApp**
de la parte de abajo, donde se dedica a enviar mensajes: **cópialos igual, muy igual**.»*
Y la segunda vuelta (2026-09-16, con la captura de su celular en la mano): *«en modo mobile el bloque de
cámara/galería debe ser visible **solo cuando se necesite**, no estar siempre visible… y **hazlos idénticos
al WhatsApp, hasta en color**, sobre todo en modo mobile, **IDÉNTICO**»*.

> 🎨 **CÓMO ES EL DE VERDAD** (WhatsApp Android, el de su captura), de izquierda a derecha:
> **`[😊]  [ Mensaje………… ]  [📎]  [📷]  [🎤 verde]`**
> · la franja de abajo es del **mismo color del fondo del chat** (sin caja ni borde propio);
> · encima flota **una pastilla blanca** de esquinas muy redondeadas;
> · los íconos son **GRISES (`#54656f`)**, sin fondo;
> · el botón de la derecha es el **CÍRCULO VERDE de WhatsApp (`#00a884`)** con el ícono **BLANCO**:
>   micrófono sin texto, flecha de enviar en cuanto lo hay.

| Pieza | Cómo es | Dónde vive |
|---|---|---|
| **😊 Emojis** | Va **a la IZQUIERDA del campo** (como WhatsApp), gris y sin fondo. Abre un **cajoncito de 48 emojis** en grilla (tope `34vh`); cada uno se inserta en el campo | `#tiaEmoji` + `#tiaEmojiPanel` |
| **El campo** | Pastilla blanca con el texto de ayuda **«Mensaje»** (`#8696a0`), texto `#111b21`, **16 px** (el iPhone no hace zoom) y **crece solo** hasta 110 px | `#tiaTexto` (`crear_tienda_ia.php`) |
| **📎 Adjuntar** | Gris `#54656f`: abre **la galería**. 🆕 **Solo se ve en los pasos que piden una foto** | `#tiaClip` → `#tiaArchivoGaleria` |
| **📷 Cámara** | Gris `#54656f`: abre la cámara del celular (input con `capture`). 🆕 **Solo en los pasos de foto** | `#tiaCam` → `#tiaArchivo` |
| **🎤➤ El botón redondo** | **El VERDE de WhatsApp (`#00a884`)** con el ícono **BLANCO**: **sin texto dicta** (dictado EN VIVO del navegador, en español de Perú) y **con texto se convierte en ➤ enviar**. Mientras dicta se pone **rojo con un latido** (`.is-dictando`). ⏱️ **Se corta solo a los 2 minutos** (120 000 ms) y avisa: *«Corté el dictado a los 2 minutos ⏱️»* | `#tiaEnviar` + `pintarBotonWa()` / `dictarEmpezar()` / `dictarParar()` |
| **El alto** | **88 px** en un celular (**95 px** con la línea de ayuda) — antes de la orden del 2026-09-16 medía **199 px**: más de un cuarto de la pantalla. El chat se queda con ~**597 px** | medido con `__tia_ver.py` (§11) |

⚠️ El **micrófono negro** que se había pedido el 2026-09-15 **quedó retirado**: manda la última orden
(*«hasta en color… IDÉNTICO»*).

⚠️ **Aquí NO se usa el componente de dictado del sitio** (`assets/js/dictado_voz.js`): ese pone su propio
botón con el texto «dictar» debajo del campo, y el jefe pidió que **el micrófono sea solo el ícono de
WhatsApp**. Por eso el compositor lo maneja `tienda_ia.js` (y el dictado del sitio **no** se engancha:
el `textarea` no lleva `data-dictado`).

⚠️ **Los dos botones grandes de foto** (`📷 Abrir cámara` / `🖼️ Abrir galería`) siguen existiendo, pero
**solo salen en los pasos que piden una foto** —menos en el arranque, donde el carrete se abre desde el
botón del saludo y no hay cámara—. **En los demás pasos no hay ni un ícono de foto en la pantalla**
(orden del 2026-09-16; el porqué de que antes salieran siempre: **trampa 37**).

> 🆕 **2026-09-16 — EL 📷 ABRE UNA VENTANA, NO LA CÁMARA** (orden del jefe: *«al abrir la cámara puede
> ser un modal que carga las 2 opciones»*): el ícono **📷 del compositor** y el botón grande
> **`📷 Abrir cámara`** ya no disparan la cámara de frente — abren la **ventana emergente `#tiaSheetFoto`**
> con **`📷 Tomar una foto ahora`** y **`🖼️ Elegir de mi galería`** (y `Cancelar`). El **📎** y el botón
> **`🖼️ Abrir galería`** siguen abriendo el carrete directo, porque su texto ya dice lo que hacen.
> Detalle y las otras dos ventanas: **§2quinquies**.

---

## §2quinquies. 🆕 UN DUEÑO, VARIAS TIENDAS (y las ventanas emergentes) — 2026-09-16

> 🔴 **Lo que dijo el jefe (textual):** *«Cuando acabo de crear una tienda no hay forma de crear otra
> tienda… **un usuario puede tener varias tiendas, no olvidar: un mismo usuario puede tener varios
> negocios**. Implementa la opción de poder crear una nueva tienda al acabar de crear una y **pon
> submenús en popup escondidos**: ejemplo al abrir la cámara puede ser un modal que carga las 2
> opciones, y asimismo mete otros modales donde lo creas conveniente.»*

**El problema de verdad (lo que estaba roto):** la conversación terminaba en el paso `publicado`/`fin`
pero **seguía `en_curso`**, así que `tienda_ia_actual()` la devolvía otra vez y el dueño **volvía a ver
la misma tarjeta final** — sin ningún botón para empezar su segunda tienda. (Y en el flujo viejo del
paso `resumen`, `tienda_ia_publicar_flujo()` **reabría** una conversación que el publicador ya había
cerrado: le faltaba poner el `estado` antes de guardar.)

| Pieza | Cómo quedó | Dónde vive |
|---|---|---|
| **`🆕 Crear otra tienda`** | Botón en la tarjeta final (`fin`), en `publicado` y en el `mas_productos` viejo. **Cierra** la conversación que estaba abierta y abre **una limpia en el paso `fotos`** (sin volver a preguntar «¿qué hacemos hoy?»: el dueño ya dijo lo que quería) | `tienda_ia_guion()` + `case 'otra_tienda'` de la puerta |
| **`tienda_ia_cerrar_conversacion()`** | Pone `abandonada` las conversaciones **en curso** de ESA persona (por `usuario_id` o por su hash de visitante) y **solo** del `modo = 'nueva'`. Nunca toca la de otro ni una ya publicada (la fila se queda: el gasto y el embudo del Súper Admin no se pierden) | `includes/tienda_ia.php` |
| **`tienda_ia_nueva_tienda()`** | Cierra la anterior, crea la nueva (`paso = 'fotos'`, `flujo = 'fotos'`, datos en blanco), le pone su saludo *«🆕 ¡Vamos con tu nueva tienda! Lo que ya publicaste queda como está: esta es otra tienda, de otro negocio»* y devuelve la conversación | `includes/tienda_ia.php` |
| **La tienda anterior NO se toca** | El publicador sigue igual: si el nombre es **de una tienda que ya es suya**, la **actualiza**; si es otro nombre, crea **un negocio nuevo a su nombre** (§3bis) | `tienda_ia_publicar()` |
| **`nueva_tienda: true`** | Lo que le dice al navegador que **borre la conversación de la pantalla** y pinte la nueva | `api/tienda_ia.php` → `tienda_ia.js` |
| **`mis_tiendas`** | Todas sus tiendas (nombre, enlace, nº de productos) viajan en **cada** respuesta: es lo que come la ventana «🏪 Mis tiendas». ⚠️ **No** van en `opciones` (una opción con `url` el navegador la pinta como enlace y rompería los chips del paso «elige tu tienda») | `tienda_ia_mis_tiendas()` |
| **🛡️ El único caso que se frena** | Si la tienda de esa conversación **ya se publicó y todavía no tiene dueño** (falta el WhatsApp, que es la última pregunta), **no se le deja** empezar otra: quedaría **huérfana** en el directorio. Se le dice: *«Antes de armar otra tienda terminemos esta 📱 Escríbeme tu número de WhatsApp…»* | `case 'otra_tienda'` de la puerta |
| **El tope del día** | `TIENDA_IA_TIENDAS_POR_USUARIO_DIA` pasó de **2 a 5**: con 2, el dueño de verdad (bodega + puesto + taller) se topaba con «Hoy ya creaste tus tiendas del día» | `includes/config_tienda_ia.php` |

### 🪟 Las tres ventanas emergentes (modales)

Nacen **escondidas** en la página (`hidden`) y las abre `tienda_ia.js`; se cierran con
**Cancelar/Cerrar**, tocando **el fondo** (el velo) o con **Escape**:

| Ventana | Qué carga | Se abre desde |
|---|---|---|
| 📷 `#tiaSheetFoto` | **Las 2 opciones de la foto**: `📷 Tomar una foto ahora` (input con `capture`) y `🖼️ Elegir de mi galería` (varias de una vez) + `Cancelar` | El **📷 del compositor**, el botón grande `📷 Abrir cámara` y el chip `📷 Abrir cámara` del paso del producto |
| 🏪 `#tiaSheetTiendas` | **Todos sus negocios**, cada uno con su enlace y sus productos, y el botón naranja `🆕 Crear otra tienda` | Menú **⋯ → 🏪 Mis tiendas** |
| 🆕 `#tiaSheetOtra` | La **confirmación** de armar otra tienda, con el aviso que corresponde: *«Tu tienda «X» ya está publicada y **no se toca**…»* o, si aún no publicó, *«lo que llevas (tus N fotos) se queda a medias»* | El botón `🆕 Crear otra tienda` (de la tarjeta final y del modal de tiendas) y el menú **⋯** |

⚠️ **Si se toca un título, un texto o un botón de estas ventanas, la sonda 15 lo caza**
(`__tia_prueba15.php`: comprueba que las tres existan, que **nazcan escondidas**, que el botón de otra
tienda esté en los tres pasos finales y que el de agregar producto no deje al dueño sin salida).

### 🛍️ «¿Cómo sigue creando MÁS productos?» (el callejón sin salida, 2026-09-16)

> 🔴 **Lo que preguntó el jefe:** *«¿Cómo puede el usuario seguir creando más productos?»* — y tenía
> razón: al llegar a los **2 productos** el asistente contestaba *«Ya tienes tus **2 productos** 👌 Si
> quieres más, entra con tu número y tu clave a tu tienda»*: **ni botón, ni enlace, ni camino**. Y en el
> paso `publicado` lo mismo, pero **sin decir nada** (mandaba al cierre y ya).

| Pieza | Cómo quedó |
|---|---|
| **`TIENDA_IA_PRODUCTOS_CON_IA` (2) es el tope de la RONDA, no de la vida** | `productos_ia` cuenta la ronda y **se pone a 0** cada vez que el dueño pide más; `productos_total` cuenta **todos** los que lleva hechos en la conversación y es el que **numera** (*«Producto 3:»*, *«Producto 4:»*…, antes volvía a decir *«tu primer producto»*) |
| **`tienda_ia_ronda_productos()`** | Abre otra ronda: avisa *«¡Vamos con otro! 🛍️ Ya llevas **2 productos en «X»**»*, pone la ronda a 0 y le propone productos nuevos (`tienda_ia_ia_producto()`) |
| **El botón en la tarjeta final** | **`🛍️ Agregar otro producto`** (`valor = 'producto'`) en el paso `fin`. En `publicado` el «Crear mi primer producto» ya no salta al cierre: **también abre ronda** |
| **📦 La puerta del inventario** | El mensaje final lleva el enlace a **`productos.php?n=ID#crear`**: la pantalla del panel para **cargar muchos productos con la cámara y la voz**, sin tope y sin pasar por el asistente. La misma puerta está en la ventana **🏪 Mis tiendas**, con un botón **`➕ Producto`** por cada negocio |
| **El freno de verdad** | Los de siempre, en la puerta: **80 llamadas por conversación** y **300 por persona y día**; al pasarse, el asistente avisa con cariño y **no gasta**. No hay tope de productos (una tienda puede tener 100) |

⚠️ **Ojo al tocar esto:** si se vuelve a poner un tope duro de productos, el dueño se queda otra vez sin
camino. Lo que hay que cuidar es que **siempre** haya un botón para seguir y un enlace al inventario.

---

## §2sexies. 📝 EL COPY CON BOTONES, 💬 LAS OPINIONES Y 📷 LAS FOTOS DE PRODUCTOS (2026-09-16)

> 🔴 **Lo que dijo el jefe, con una tienda suya delante** (`https://dechimbote.com/neg/novedades-gaela-chimbote`):
> *«quedó muy floja su descripción **y sin opiniones**; eso debe ser automático al crear la tienda»* ·
> *«lo ideal sería pedirle al usuario que siga subiendo fotos de sus productos e ir clasificando la
> inteligencia artificial… la IA **siempre pedirá fotos y dará opciones**»* · *«debe ir mejorando el
> copywriting (texto del negocio) según se vayan agregando más productos **y repetir los botones de
> llamada a la acción en el copywriting**»*.

### A. 📝 El copy de la tienda (ya no es un párrafo plano)

`tienda_ia_ia_descripcion()` ([`includes/tienda_ia.php`](deploy/includes/tienda_ia.php)) está **reescrita**: antes
pedía *«2 a 4 frases, sin emojis y sin listas»* y salía un texto muerto; ahora la IA escribe el **copy del
sitio**, con el **kit de colores** (`cz-tit`, `cz-sub`, `cz-caja`, `cz-lista`, `cz-precio`, `cz-cta`,
`cz-cta-final`) y **los botones de llamada a la acción repetidos** (`cz-btn cz-wa` de WhatsApp —con su
`data-msg` y el marcador `{URL}`— y `cz-btn cz-tel` para llamar). El motor de la ficha
(`descripcion_negocio_html()`, guía `publicando a los amigos de jimmy.md` §4) los convierte en **botones
verdes de verdad**, con el número del propio negocio.

| Pieza | Qué hace |
|---|---|
| `tienda_ia_ia_descripcion()` | Escribe el copy con una **estructura fija** (título, gancho, caja de atención, botón, «lo que encuentras» en lista, precios si los hay, dónde y cuándo, banda `cz-cta`, botón de consulta, botón de llamada y cierre). Prohibido inventar (precios, años, marcas, direcciones, teléfonos) y prohibido escribir teléfonos a mano |
| `tienda_ia_copy_limpiar()` | Solo deja las etiquetas permitidas, arregla los `&` sueltos y quita las envolturas del modelo (```html, «HTML:») |
| `tienda_ia_copy_ok()` | El **listón** (el mismo del publicador de los amigos): ≥300 caracteres, ≥3 `<h3>`, la banda `cz-cta-final`, ≥2 botones de WhatsApp y ningún teléfono escrito. Si no pasa, **no se guarda** y queda el texto de respaldo (nunca una ficha rota) |
| `tienda_ia_productos_publicados()` | La lista de productos **de verdad** (con su precio) que se le pasa al copy: es lo que hace que **el texto mejore con cada producto** |
| `tienda_ia_mejorar_descripcion()` | 🔁 Vuelve a escribir y **guardar** la descripción con el catálogo al día. Se llama **después de cada producto** y al terminar cada tanda de fotos; el dueño ve *«📝 Le mejoré la descripción a tu tienda con lo que llevas cargado 👌»* |

### B. 💬 Las opiniones, automáticas al crear la tienda

Una ficha recién creada salía con *«Todavía no hay opiniones… ¡Sé el primero!»* (lo primero que ve el
cliente: parece un local vacío). Ahora, al publicar, se siembran **3 opiniones con contexto**
(`tienda_ia_sembrar_opiniones()`, con los productos y el rubro reales, fechas repartidas, 5⭐/5⭐/4⭐ y
`fuente = 'maestro'`):

* Es **idempotente**: si la tienda ya tiene opiniones no toca nada (una tienda que se **actualiza** no
  recibe otras tres) y recalcula la nota al final.
* **No avisa al jefe por Telegram** (son de semilla, no de un cliente real): por eso se insertan directo,
  como las 297 de `includes/opiniones_semilla.php`, y **no** con `opinion_crear()` (que sí avisaría).
* Si la IA falla, entra el **respaldo local** (`tienda_ia_opiniones_respaldo()`), armado con el rubro, la
  zona y los productos: la tienda **nunca** se queda sin opiniones.
* Se apaga con `TIENDA_IA_OPINIONES_AUTO = false` (§13).

### C. 📷 «Mándame las fotos de tus productos» (la IA los clasifica)

Paso nuevo **`productos_lote`** (guion + `tienda_ia_paso_productos_lote()` + `tienda_ia_ia_productos_lote()`),
que sale en la tarjeta final y en la de publicada con el botón **`📷 Mandar fotos de mis productos`**:

1. El dueño manda **una tanda de fotos** (hasta 8 de una vez, `TIENDA_IA_FOTO_LOTE_IA`).
2. **UNA sola llamada** las clasifica: al modelo se le pide **una línea por producto**
   (`FOTO <n> | nombre | descripción | precio`) — nunca JSON (trampa 5) — y las recoge el lector tolerante
   `tienda_ia_leer_productos_lote()`.
3. **El precio solo se copia si está escrito en la foto** (etiqueta, pizarra, cartel); si no, el producto
   sale **«a consultar»**. Nada inventado: ni marcas, ni medidas, ni modelos.
4. Se **publican todos** de un tirón (con su foto y la descripción que ya trajo la clasificación: **no se
   gasta una llamada por producto**) y **se reescribe el copy** de la tienda.
5. Se le muestra la lista (*«🎉 Clasifiqué 3 productos de tus fotos: … Ya están en línea ✅»*) y se queda
   en el mismo paso para **mandar otra tanda** (`✅ Ya está, gracias` para cerrar).

⚠️ **Lo que hay que cuidar:** la respuesta del paso es `tipo = 'foto'`, así que los botones de cámara y
galería tienen que seguir encendiéndose (§2quater) y el aviso de la barra lo cambia `tienda_ia.js`
(`r.paso === 'productos_lote'` dice que son fotos **de productos**, no de la tienda).

---

## §2septies. 🛒 «AGREGAR MI TIENDA» — EL BOTÓN NEGRO/ROSADO DE LOS RESULTADOS (2026-09-16)

> **Cuándo leer esto:** cuando haya que tocar, revisar o explicar el botón **«Agregar mi tienda»** que
> sale **en los resultados de búsqueda** (`buscar.php`), o el parámetro **`prod`** del maestro.
> **Archivos:** `deploy/buscar.php` (el botón y su CSS) · `deploy/crear_tienda_ia.php` · `deploy/api/tienda_ia.php`
> · `deploy/includes/tienda_ia.php` (`tienda_ia_producto_pedido()`) · `deploy/assets/js/tienda_ia.js`.
> **Probado:** `__tia_prueba17.php` (**57 ✅ · 0 ❌**, §11).

**Lo que pidió el jefe (textual):** *«cuando muestre los resultados, abajo del botón "ver cumpleaños cerca
de mí" pon un botón negro con texto rosado que diga "agregar mi tienda". Lo que hará este botón es: si no
está logueado, loguearlo; y si ya está logueado, abrir el maestro crear tienda y le dirá "dame las
imágenes para el producto" —en este caso cumpleaños— y lo agregará el producto a su tienda».*

### A. El botón (en `buscar.php`)

* Vive **dentro del bloque de ubicación** (`.cerca-bloque`), **al final**: en el flujo normal queda
  **debajo** del botón «📍 Ver … cerca de mí» (y en el modo «cerca de mí», debajo de «🔎 Ampliar búsqueda»).
  Sale **siempre** que se vean resultados (también cuando no hay ninguno: el que busca su rubro suele ser
  el que lo vende).
* Es **negro con letra rosada** (clase `.agrega-tienda`, en el `<style>` de la propia página):
  `background:#111` · `color:#ff5ea8` (hover: `#000` / `#ff86bd`). Texto: **«Agregar mi tienda»**.
  Ancho completo y **16 px** (móvil primero: menos de 16 px hace que el celular haga zoom).
* **A dónde lleva** (lo decide el servidor con `usuario_actual()`, que es lo que pidió el jefe):
  * **Con sesión** → `url('/crear-tienda?modo=producto&prod=<el término buscado>')`.
  * **Sin sesión** → `url('/login.php?redirect=' . rawurlencode('crear-tienda?modo=producto&prod=<término>'))`:
    entra y **vuelve solo** al maestro con el producto ya dicho.
* El término que viaja es el **limpio** (`$termino`, el mismo que se buscó: `comprar cumpleaños` →
  `cumpleaños`), recortado a **40** caracteres. **Sin término** (navegar por rubro) el botón sigue, pero
  **sin `prod`**: el asistente preguntará el nombre del producto, como siempre.
* ⚠️ **El `redirect` lleva la ruta SIN codificar** y la codifica `rawurlencode` entera: si se le pasara
  el `&prod=` ya codificado, el `%` viajaría como `%25` (doble codificación; funciona, pero ensucia la URL).

### B. El producto ya dicho: `prod` (el corazón del pedido)

`/crear-tienda?modo=producto&prod=cumpleaños` llega al motor y `tienda_ia_producto_pedido()` hace el atajo:

1. Solo actúa en **modo `producto`** y en los pasos donde **todavía no hay producto** (`tienda` = eligiendo
   tienda · `producto_nombre` = le estaban preguntando el nombre). Si ya hay un producto en curso (o la
   conversación ya terminó) **no toca nada**: manda la conversación.
2. **Con UNA sola tienda** no se le pregunta cuál —igual que el flujo de siempre—: se elige sola.
   **Con varias**, primero elige cuál (el atajo se completa en cuanto toca la suya).
3. Nace el producto **con el nombre que trajo el botón** y el paso salta derecho a **`producto_fotos`**:
   *«📷 **Mándame la foto de cumpleaños** (de cerquita, que se antoje)»*. **No pregunta cómo se llama el
   producto** (y de paso **no gasta** la llamada de IA que proponía nombres).
4. Después todo es el flujo de siempre: la foto → **«💰 ¿Cuánto cuesta cumpleaños?»** → el producto queda
   **publicado en su tienda** (`producto_crear` + el copy de la tienda se reescribe).

**Cómo viaja `prod`** (los 4 sitios, si falta uno se rompe):

| Pieza | Qué hace |
|---|---|
| `crear_tienda_ia.php` | Lee `$_GET['prod']` (limpiado con `tienda_ia_limpiar($…, 60)`) y lo pone en `window.TIA_CFG.producto`; también lo conserva en los enlaces de la puerta (`login`/`registro`). |
| `assets/js/tienda_ia.js` (**`?v=12`**) | Manda `prod` en **cada** petición: el `GET` del arranque, el **JSON** de las respuestas y el **`FormData`** de las fotos. |
| `api/tienda_ia.php` | Lo lee del `GET` y del `POST` (⚠️ **también en la rama multipart**: ver la trampa 49) y lo pasa al motor. |
| `includes/tienda_ia.php` | `$s['datos']['producto_pedido']` guarda el nombre y `tienda_ia_producto_pedido()` aplica el atajo (en el `GET` de la puerta y en el `POST`, antes de leer el paso). |
| `login.php` | 🆕 **El `redirect` del login se arregló aquí** (a raíz de este botón): el formulario **no llevaba el `redirect` en el POST**, así que `login.php?redirect=…` **siempre** terminaba en el panel. Ahora viaja en un campo escondido (como en `registro.php`), el enlace de «Regístrate» también lo lleva, y con eso el visitante sin sesión **entra y cae otra vez en el maestro con el producto ya dicho**. ⚠️ **El único camino que sigue cayendo en el panel es el botón de Google** (`google_login.php`/`google_callback.php` no llevan el dato): pendiente honesto. |

⚠️ **El navegador manda `prod` en CADA envío a propósito** (es lo que hace que funcione aunque la
conversación se cree en el primer POST): por eso el atajo **tiene que ser idempotente** — y lo es, porque
en cuanto hay un producto en `datos['productos']` se retira (verificado en la prueba 17: la segunda
visita **no duplica** el producto ni pierde la foto).

---

## §2octies. 🔴 LA UBICACIÓN PRIMERO Y EL COMPOSITOR DE LA GUÍA (2026-09-20 — la versión 8, la de HOY)

**Pedido textual del jefe:** *«En una nueva sesión quiero llevar este mismo estilo de los botones —el botón
de cámara (el que abre el modal de "Abrir cámara" o "Abrir galería"), el botón de grabar, el input y el
botón de enviar— a la página del maestro, tal cual como lo tenemos ahorita en la guía, tal cual. Y una
corrección más: cuando carga, lo primero que va a aparecer es un botón pidiendo ubicación, eso es lo
primero; luego aparece el texto ya con la ubicación recibida (ya tenemos la calle, ya tenemos el distrito)
y luego recién preguntamos que suban las fotos: pedimos 8 fotos siempre. Pero es obligatorio: la ubicación
como primer dato es vital. Que aparezca dentro de la conversación el botón de ubicación y que diga, así, en
color morado, "ubicación". Si no comparte su ubicación, pues no continúa, así de simple»*.

### a) 📍 LA UBICACIÓN ES LO PRIMERO (y es obligatoria)

| Qué | Cómo quedó |
|---|---|
| **La primera pantalla** | El paso `arranque` (el saludo) **ya no pide tocar «crear mi tienda»**: el PRIMER botón que ve el dueño es **`📍 Ubicación`**, **MORADO** (`morado: true` → `.tia-chip--morado`), y su valor es **`gps`**. El saludo dice qué va a pasar: *«Son dos cosas: **tu ubicación** 📍 y **8 fotos** de tu negocio… **Empecemos por tu ubicación** 👇»* |
| **Quién ve eso** | El que **va a crear su tienda** (sin tiendas todavía) o **sin sesión**. El dueño que **ya tiene tiendas** sigue viendo su menú de siempre (`🚀 Crear otra tienda` · `🛍️ Agregar un producto`); al tocar «crear otra tienda» **también se le pide la ubicación** (paso `ubicacion`) |
| **Sin ubicación NO continúa** | Si escribe cualquier cosa que no sea un distrito, el bot **insiste** con el mismo botón morado: *«Sin tu ubicación no puedo seguir 🙏 Toca el botón morado **o escríbeme tu distrito** (Chimbote, Nuevo Chimbote, Coishco o Santa)»* |
| **La salida que eligió el jefe** | Si no quiere dar el GPS, **escribir el distrito vale**: `tienda_ia_distrito_leido()` lo reconoce contra los **distritos REALES** del sitio (gana el nombre más largo: «Nuevo Chimbote» antes que «Chimbote») y sigue. ⚠️ Esto lo eligió el jefe el 2026-09-20 (*«puede escribir su distrito y sigue»*) para que nadie quede atrapado sin poder crear su tienda |
| **Con el GPS** | `tienda_ia_distrito_por_gps($lat, $lng)` — **la misma receta del Caminante**: los **30 negocios activos más cercanos votan** pesando por cercanía; **no llama a ningún servicio de mapas**. Se guardan `lat`/`lng` + `distrito_id` + `distrito_nombre`, y el bot contesta *«📍 ¡Ubicación recibida! Estás en **Chimbote** 🎯»* y **pasa derecho a las fotos** |
| **Si la zona sale dudosa** | Cuando el GPS está lejos de todo negocio conocido (`dudoso`), el bot lo dice sin drama: *«Si me equivoqué de zona, al final puedes cambiarla en «Editar algo»»* (y ese menú tiene **«Mi zona»**, que sigue existiendo) |
| **⚠️ La calle** | El GPS da **coordenadas**, no el nombre de la calle (eso pediría un geocodificador de pago). Decisión del jefe: **el bot confirma la ZONA y pide las fotos, y la calle sigue saliendo de las fotos** (la IA lee el letrero y la dirección, como hasta hoy) |
| **Ya no se pregunta dos veces** | Al ser local, el paso de la **dirección** ya no re-pregunta la zona: guarda la calle y **salta derecho** a la confirmación del nombre (`si distrito_id > 0` → `nombre_ok`). El paso de la zona (`distrito`) queda **solo para el menú de edición** («Mi zona») |

**Los pasos renumerados** (`tienda_ia_pasos()`, es lo que mueve la barrita): `arranque 0` ·
**`ubicacion 1`** · `fotos 2` · `tipo 3` · `direccion/horario/entregas 4` · `distrito 5` · `nombre_ok 6` ·
`rubro_ok 7` · `rubro_mas 8` · `editar 9` · `producto_* 10` · `whatsapp/tel_* 11` · `publicado/producto_nombre 12`
· … (`progreso = n / 12`).

### b) 🎯 LA BARRA DE ESCRIBIR, IGUAL A LA DE LA GUÍA

`[📷 cámara] [🎤 micrófono] [ Escribe tu respuesta… ] [ ➤ enviar]` — y nada más.

| Pieza | Cómo es | Dónde vive |
|---|---|---|
| **📷 Cámara** | Círculo gris claro de **36 px** que abre un **MENÚ CHIQUITO encima del botón** (como el ☰ del sitio, con icono de cámara): **📷 Abrir cámara** (input con `capture`) y **🖼️ Abrir galería** (input `multiple`, para marcar varias fotos). Se cierra tocándolo otra vez, tocando cualquier otra parte o con `Escape` | `#tiaCam` + `#tiaCamMenu` / `#tiaCamFoto` / `#tiaCamGaleria` |
| **🎤 Micrófono** | **El MISMO del buscador** (`buscador_voz.js` copiado): círculo **naranja** de **42 px**, el **SVG** del micrófono, y **latido rojo** (`#c1121f`) mientras escucha. Reconocedor **NUEVO por dictado**, el candado `instancia !== vozActual`, **`es-PE` → `es-ES` → `es-MX`** y sus **avisos** en la misma caja blanca. Lo dictado cae en el cuadro **sin limpiar** (una frase va entera) | `#tiaVoz` + `#tiaVozAviso` |
| **🚀 Envío automático** | Si el micrófono se apaga **porque escuchó silencio**, el mensaje **se manda solo al segundo** (`AUTO_ENVIO_MS = 1000`), igual que en la guía. Se cancela si él escribe, toca el micrófono, abre la cámara o le da al ➤; y si el que apaga el micrófono es **su dedo**, **no se manda** | `autoEnviarVoz()` / `autoEnvioCancelar()` |
| **El cuadro** | **Píldora delgada** (34 px, fondo `#f1f2f4`, sin borde a la vista) que **crece sola** hasta 110 px y se pone blanca con el anillo naranja al enfocarla. Sigue siendo el `textarea` `#tiaTexto` (el dueño puede escribir párrafos) | `#tiaTexto` (`class="tia-campo"`) |
| **➤ Enviar** | Círculo **naranja** de 36 px: **solo envía** (se apaga si el cuadro está vacío) | `#tiaEnviar` + `pintarBotonEnviar()` |
| 🗄️ **Lo que se retiró** | El **😊** de emojis (48), el **📎** de adjuntar, el **🎤 verde de WhatsApp** con su corte a los 2 minutos, los **dos botones grandes de foto** (`📷 Abrir cámara` / `🖼️ Abrir galería`) y la **ventana modal `#tiaSheetFoto`** | — |

⚠️ **El ⛔ que NO se toca:** el compositor de WhatsApp (`§2quater`) **sigue vivo en 👑 El Supremo** y su CSS
se queda compartido en `assets/css/tienda_ia.css` (**trampa 42**).

### b.2) 🔴 LA SEGUNDA VUELTA DEL MISMO DÍA (2026-09-20, noche): LAS BARRAS, ARREGLADAS

**Lo que dijo el jefe (con dos capturas en la mano, la de El maestro y la de El guía):** *«corrige
inmediatamente las barras de uso de la sección del maestro, está horrible, está muy mal hecho; tienes que
dejarlo tal cual como está en la guía»*.

**Lo que estaba mal (medido en un Chrome aparte con `__maestro_barra.py`: la página viva, 390 px, sin tocar
el navegador del jefe). Cuatro bichos, todos de la misma familia — *una clase que se usa para dos cosas*:**

| # | Bicho | Cómo se veía | Por qué pasaba |
|---|---|---|---|
| 1 | 🔴 **La fila del compositor se llamaba `.tia-barra`** | La fila medía **12 px** y era **una píldora blanca**: los botones (36, 42 y 34 px) se **salían por arriba y por abajo** y pisaban el botón «📍 Ubicación» de al lado | **`.tia-barra` es la barrita del progreso de la cabecera** (`height: 4px`). Las dos reglas se fusionaron: la fila se quedó con el `height: 4px` de la barrita y la barrita con el `background: #fff` de la fila |
| 2 | 🔴 **La barrita del progreso se volvió una píldora blanca** | En la cabecera, debajo de «El maestro», salía un **bloque blanco redondeado** en vez de la línea fina | El mismo choque, al revés (la regla de la fila pisaba a la de la barrita) |
| 3 | 🔴 **El ➤ salía como un ÓVALO de 36 × 46 px** | El botón de enviar era **más alto que ancho** y el icono quedaba apretado | Quedaba viva una regla del compositor viejo: **`.tia-enviar { min-height: 46px; padding: 10px 18px }`** (y el cuadro viejo `.tia-texto`, con su `min-height: 50px`) |
| 4 | 🔴 **El 📷 no abría NADA (el menú salía fuera de la pantalla)** | El dueño tocaba la cámara y no pasaba nada | El menú (`#tiaCamMenu`) y el aviso del micrófono son `absolute` con `bottom: calc(100% + 4px)`, y **`‹.tia-composer›` no era `position: relative`**: se anclaban a la página entera y el menú aparecía en **y = −84 px** (medido) |

**Cómo quedó (lo que se hizo, archivo por archivo):**

| Archivo | Cambio |
|---|---|
| `crear_tienda_ia.php` | La fila del compositor pasó de `class="tia-barra"` a **`class="tia-composer__linea"`** (una clase que es suya y de nadie más). Se dejó el aviso escrito encima, en el HTML |
| `assets/css/tienda_ia.css` | (a) La fila es **`.tia-composer__linea`**: `display: flex; align-items: center; gap: 6px;` **sin** fondo, radio ni sombra. (b) `.tia-composer` es **la franja blanca de la guía** (`.cbot-pie`): `background: #fff`, `border-top: 1px`, `padding: 6px 7px`, y **`position: relative`** (⚠️ de aquí cuelgan el menú de la cámara y el aviso del micrófono: **no quitar**). (c) Se **borraron** las reglas del compositor viejo que pisaban al nuevo: `.tia-texto` (y su `min-height: 50px`) y **`.tia-enviar { min-height: 46px; padding: 10px 18px }`** — ese era el óvalo. (d) El cuadro `.tia-campo` quedó con los valores exactos del `.cbot-input` de la guía: **34 px**, fondo `#f1f2f4`, radio `999px`, `padding: 6px 13px`, y crece hasta 110 px. (e) El ➤ usa el **naranja del sitio** (`base.css`: `--marca-naranja: #ea6a12`): esta página es una **página sola** y no carga `base.css`, así que sin el valor de respaldo salía de **otro** naranja (`#e07a1f`) |
| `assets/css/supremo.css` | 👑 El Supremo **sigue con su franja transparente** de WhatsApp (`.sup-body .tia-composer`), porque el CSS es compartido y ahora la franja blanca es la de El maestro |
| `crear_tienda_ia.php` · `supremo.php` | **`?v=12`** para `tienda_ia.css` (`supremo.css` pasó a `?v=8`). ⚠️ **Al re-subir el CSS hay que subir el `?v=`**: el hosting cachea los estáticos **por URL** y, sin cambiarlo, el jefe sigue viendo el archivo viejo (pasó: con el `?v=11` repetido siguió sirviendo el ➤ viejo hasta que se subió a **12**) |

**Las dos barras quedaron IDÉNTICAS a las de la guía** (medidas las dos, una al lado de la otra, con
`__maestro_barra.py` y `__maestro_barra.py guia`): 📷 **36 px** círculo `#f0f2f5` · 🎤 **42 px** círculo
naranja `#ea6a12` · cuadro **34 px** `#f1f2f4` radio `999px` · ➤ **36 px** círculo `#ea6a12`, con la
**franja blanca y su línea arriba**. Lo único distinto (a propósito): el cuadro de El maestro lleva **letra
de 16 px** (regla del sitio: por debajo de 16 px el iPhone hace zoom solo) y es un `textarea` que **crece**
hasta 110 px.

**Cómo se comprueba (la receta, para la próxima):**
`python __maestro_barra.py` (la página viva, la mide y saca la foto `__maestro_barra_movil.png`) ·
`python __maestro_barra.py guia` (el compositor de El guía, para comparar pieza por pieza) ·
`python __maestro_barra.py local` (el CSS local **antes** de subirlo) ·
`python __maestro_barra_check.py` (por HTTP: que la clase nueva esté, que la vieja no, y que no haya
mojibake). ⚠️ **Ninguna de estas abre nada en el navegador del jefe**: es un **Chrome sin cabeza** con
perfil temporal.

### c) Cómo se probó (lo que cazó el camino)

**Prueba 18 = `__tia_prueba18.php`** (sonda temporal, borrada del hosting): **50 ✅ · 0 ❌**. Recorre el
camino real —página por HTTP → cuenta de prueba → login → cookie → CSRF → JSON → **foto de verdad**— y
comprueba: el compositor nuevo y **la ausencia del viejo**, el CSS (🎤 naranja con latido, botón morado,
menú chiquito), el JS (los tres idiomas, el candado, el envío automático, y **que no queda `pintarBotonWa`
ni el corte de 2 minutos**), **que el PRIMER botón es el morado**, que sin ubicación **insiste**, que con el
GPS **saca el distrito real de Chimbote** y **pasa a las fotos**, que **la foto entra** después de la
ubicación, y que **escribir el distrito a mano** también funciona. Limpia todo al final (2 cuentas, sus
conversaciones y sus fotos).

La prueba **encontró 3 cosas** que se arreglaron (y quedan como lección):
1. 🔴 **La primera pantalla seguía siendo «crear mi tienda»**: el paso `arranque` saludaba y pedía un toque
   antes de la ubicación. **Se cambió**: ahora el primer botón ES el morado de la ubicación.
2. 🔴 **`api/tienda_ia.php` se comía la bandera `morado`**: la puerta **reconstruye cada opción** con una
   lista blanca de claves (`tarjeta`, `azul`, `rejilla`, `color`, `icono`…) y **`morado` no estaba en la
   lista** → el motor la mandaba y el navegador recibía un chip normal. **Se añadió** (y es la trampa 50).
3. ⚠️ El **CSRF es por sesión**: la prueba usaba el de la cuenta A para la cuenta B y el servidor contestó
   *«La página estuvo abierta mucho rato»*. Es de la prueba, no del sitio… pero es el mismo aviso que ve un
   dueño que deja la pestaña abierta: **si pasa, recargar**.

**Versiones:** `assets/js/tienda_ia.js?v=13` · `assets/css/tienda_ia.css?v=12` (v. 12 = la vuelta del
§2octies b.2, la de las barras arregladas).

---


## §3. QUIÉN PUEDE ENTRAR (y la cuenta que recibe)

* 🔑 **El asistente crea la cuenta.** Como es él quien le da al dueño «su usuario y su contraseña»,
  la cuenta la crea el módulo al publicar la tienda: **usuario = su WhatsApp** (se guarda en
  `directorio_usuarios.telefono` y el correo interno es `<numero>@dechimbote.com`) y **contraseña
  generada** = **3 letras + 1 número**, sin la **O** ni el **0**, mezclados al azar.
  Se la muestra **una sola vez**, en la tarjeta final, con el botón
  **📲 Guardar en mi WhatsApp** (`wa.me/?text=` → se la manda a sí mismo).
  El jefe también la recibe en el aviso del Telegram: así puede ayudarlo si la pierde.
* ✅ **Lo que NO cambia**: **toda tienda queda con su dueño** (nunca huérfana). Por eso el asistente
  pide el WhatsApp **antes** de publicar. 🆕 (2026-09-16) ⚠️ **Ojo con el flujo de hoy (§2ter, regla 1):
  ahí la tienda nace publicada SIN dueño** y el dueño de verdad entra con su WhatsApp **en la última
  pregunta**; hasta que ese número llega, la tienda es un caso **huérfano permitido** — y por eso el
  botón **`🆕 Crear otra tienda` NO se le ofrece a un visitante en ese momento** (§2quinquies): primero
  se cierra esta, después la siguiente.
* 🚪 **La página, por tanto, ya no exige sesión para empezar** (decisión del 2026-09-14, tarde, a
  partir de la orden anterior). Si el que llega **ya tiene sesión**, se usa su cuenta y **no se le
  muestra ninguna contraseña**: solo se le guarda el número en su ficha.
* 🔒 **Si el jefe quiere volver a cerrar la puerta**: `TIENDA_IA_SOLO_REGISTRADOS = true` en
  `includes/config_tienda_ia.php` → vuelve la pantalla de «crea tu cuenta gratis / entra» (esa
  pantalla sigue existiendo, solo se salta). La comprobación de verdad está en `api/tienda_ia.php`
  (401), no en el navegador.
* 🆔 **Sin cuenta, la conversación se identifica por un hash de su sesión de PHP** (`visitante`), así
  puede recargar, dejar el celular y volver sin perder nada, y nadie más la ve.
* 🛡️ **Abrir la página NO escribe nada en la base**: el `GET` de la puerta trae el saludo sin crear la
  fila (`tienda_ia_actual(..., false)`); la conversación se crea cuando la persona **contesta de
  verdad** (el primer POST). Así un robot que pase por ahí no deja basura.
### 🔄 SI EL WHATSAPP YA EXISTE: SE ACTUALIZA, NUNCA SE DICE «NO SE PUEDE»
> Orden del jefe (2026-09-14, noche): *«si el usuario da un número de WhatsApp que ya existe se le dice
> que vas a actualizar su tienda con los nuevos datos y le vas a agregar su nuevo producto; nunca se
> dice que no se puede, okay.»*

Lo que pasa, sin preguntar nada y sin negarle nada:

| Caso | Qué hace el asistente | Qué dice |
|---|---|---|
| El número **es nuevo** | Le crea la cuenta (usuario = su WhatsApp + clave) | *«🔑 **Usuario:** 943… · **Clave:** RG2Z»* |
| El número **ya existe** y el nombre que escribió **es el de una tienda suya** | **ACTUALIZA esa tienda**: rubro, zona, cómo vende, WhatsApp, horario y descripción nuevos; **las fotos nuevas van primero** (portada nueva) y las viejas se conservan; el nombre y el enlace **NO se tocan** | *«✅ Ese número ya tiene **Combinado Doña Lucha**: la **actualizo** con estos datos nuevos 👌»* → y al publicar: *«✅ **Actualicé tu tienda «Combinado Doña Lucha» con los datos nuevos** … ¿Le agregamos **un producto nuevo**?»* |
| El número **ya existe** pero el nombre **no es de ninguna tienda suya** | Le crea **un negocio nuevo a su nombre** (puede tener dos: una bodega y un puesto) | *«✅ Ese número ya es tu cuenta: te dejo esta tienda a tu nombre 👌»* |
| Ya tenía cuenta (en los dos casos de arriba) | **No** se le inventa clave nueva | *«🔑 Entra con tu número y tu clave de siempre 👇»* |

**Cómo se decide si es «la misma tienda»** (`tienda_ia_tienda_parecida()`): se comparan los nombres
**sin tildes ni mayúsculas**; solo cuenta como la misma si son **iguales** o si **uno contiene al otro**.
Un nombre «parecido a medias» **no** actualiza nada: se le crea la tienda nueva. (Adivinar con parecidos
era peligroso: «Puesto Doña Lucha» no es «Combinado Doña Lucha» y le habría pisado los datos.)

**Y el nombre bueno no se pierde:** al actualizar se conserva el nombre guardado (con su tilde) y el
asistente lo usa de ahí en adelante, aunque el dueño lo vuelva a escribir sin la ñ.

* 📱 **Ahora se puede entrar con el número de teléfono**: `login()` acepta el número (busca por
  `telefono` o por `<numero>@dechimbote.com`) y la pantalla de entrar dice **«Correo o número de
  teléfono»** (el campo dejó de ser `type="email"`, que no dejaba escribir un número).
* La página manda `X-Robots-Tag: noindex, nofollow`.

## §3ter. 🔑 SI PIERDE SU CONTRASEÑA (módulo nuevo, 2026-09-16)

> Pregunta del jefe, textual: *«¿actualmente cómo un usuario que se creó su tienda con El maestro
> puede recuperar su contraseña?»* → **La verdad era que no podía**: en `/login` **no había ninguna
> puerta**, el sitio **nunca manda correos** (no hay una sola llamada a `mail()` en `deploy/`) y esas
> cuentas **ni tienen correo real** (su correo interno es `<su número>@dechimbote.com`). La clave se
> muestra **una sola vez** al crearla y en la base solo queda el **hash bcrypt**: no se puede leer.
> El jefe eligió **las dos puertas** y así quedaron.

**Lo que NO se puede hacer (y por qué):** la contraseña **no se descifra**. La única recuperación
posible es **darle una nueva**. A una cuenta de **administrador** no se le cambia la clave desde aquí:
una sesión robada no puede dejar al jefe fuera de su propio sitio (`clave_restablecer()` lo bloquea).

### 🔑 Puerta 1 — el dueño la pide (`/recuperar`)

* Página nueva **`recuperar.php`** (URL amable **`/recuperar`**, regla en el `.htaccess`) y enlace
  **«¿Olvidaste tu contraseña?»** debajo del campo de la contraseña en `/login`.
* Pide **su número de WhatsApp o su correo** (igual que `login()`, que también acepta el número).
* 🔒 **Nunca dice si esa cuenta existe**: la respuesta es siempre la misma («✅ Pedido enviado») y
  siempre queda a la vista el **WhatsApp del administrador con el mensaje ya escrito**, para el que
  no quiera esperar. Sin esa precaución, la página serviría para averiguar quién está registrado.
* Deja un **PEDIDO** en la tabla nueva **`directorio_claves_pedidas`** (se crea sola) y le manda al
  jefe un **aviso de Telegram** del tipo nuevo **`clave_olvidada`** («🔑 OLVIDÓ SU CONTRASEÑA») con
  el nombre, el usuario, su tienda, la IP y el enlace al panel.
* 🛡️ **Contra el ruido:** el mismo dato **no se repite en 30 minutos** (`CLAVES_ESPERA_MIN`) y una
  misma IP no pasa de **5 pedidos por hora** (`CLAVES_TOPE_IP_HORA`); el aviso, además, lleva
  `dedupe_min = 60`. Y **el pedido nunca tumba la página**: si la tabla falla, se avisa y ya.

### 🔑 Puerta 2 — el jefe le da la clave nueva (Súper Admin → 👥 Usuarios)

* Arriba de la lista sale la tarjeta **«🔑 Olvidaron su contraseña (N)»** con cada pedido: quién es,
  con qué entra, su tienda, la IP y sus dos botones: **`🔑 Darle clave nueva`** y **`🙈 Descartar`**.
  El **globito naranja** del menú **👥 Usuarios** avisa cuántos hay esperando.
* En **cada cuenta** de la tabla hay el botón **`🔑 Restablecer contraseña`** (no aparece en los
  administradores). Genera una clave con el **mismo formato que El maestro** (3 letras + 1 número,
  sin O ni 0, mezclada), la guarda con hash, **cierra las sesiones abiertas de esa cuenta** y marca
  sus pedidos como **atendidos**.
* La clave en claro sale **UNA sola vez**, en una tarjeta verde con la clave en grande, el **mensaje
  ya escrito** (en un `textarea` que se copia tocándolo) y el **botón verde de WhatsApp** al número
  del dueño (`wa.me/51…` con el mensaje puesto). Si la cuenta se registró con correo (sin teléfono),
  el botón no sale y el jefe copia el texto.
* El mensaje dice las tres cosas que hacen falta: **su usuario** (su número), **su clave nueva** y
  **el enlace para entrar**, con el recordatorio de siempre: *«Guárdala AHORA en este chat: no se
  vuelve a mostrar»*.

### 🧪 Cómo se prueba (sin molestar a nadie)

| Comando | Qué prueba |
|---|---|
| `python __diag_run.py __sonda_clave.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS` | El motor: la tabla se crea sola, el pedido se guarda (y **no se duplica**), la clave nueva **abre** la cuenta y la **vieja ya no**, se cierran las sesiones, el pedido queda atendido y **a un admin no se le toca**. Deja el `JSON` y un registro en `__diag_log.txt`. |
| `python __recuperar_http.py` | La página del dueño por HTTP: `/recuperar.php` y `/recuperar` (200 + formulario), el enlace en `/login.php`, el **envío real** del formulario (con cookie y token CSRF) y el campo vacío. ⚠️ **Antes hay que apagar el aviso** para que la prueba no suene en el Telegram del jefe (`__sonda_aviso_off.php` con `&modo=off`) y **después limpiar y volver a encenderlo** (`__sonda_limpiar_clave.php`, que además borra las filas `999000*`). |
| `python __diag_run.py __sonda_admin_clave.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS` | La pestaña **👥 Usuarios** sin navegador: simula la sesión del admin, abre la sección dentro de la sonda y comprueba el HTML (lista de pedidos, los botones, la tarjeta de la clave y el botón de WhatsApp). ⚠️ **No se puede incluir `superadmin.php` dos veces** en la misma petición: la segunda revienta con `Cannot redeclare reclamo_por_id()`. |
| `python __diag_run.py __sonda_lista_pedidos.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS` | La **consulta de la lista** con sus columnas (`cuenta_tipo` incluida: es la que decide si se ofrece el botón 🔑 o la nota de «cuenta de administrador»). Inserta la fila **a mano** (no llama a `claves_pedir()`), así que **no avisa a nadie**. |
| `python __regresion_http.py` | Repaso de que `/login.php`, `/recuperar`, `/`, `/buscar` y `/superadmin.php` siguen respondiendo. |
| `python __ftp_revisar.py` | Mira qué archivos `__*` quedaron en la raíz del hosting (los de esta sesión: **ninguno**). `… borrar` los borra, pero **solo con el OK del jefe**. |

⚠️ **Para volver a probar el envío sin ruido**: `avisos_config_guardar('clave_olvidada', 0)` **al
principio de la petición** (la configuración se cachea en memoria: apagarla *después* de haberla
leído no apaga nada en esa misma petición — pasó el 2026-09-16 y el jefe recibió un aviso de PRUEBA).

## §3bis. 🚪 EL ARRANQUE: EL ASISTENTE SABE SI ESTÁS LOGUEADO O NO (2026-09-15)

> 🔴 Orden del jefe, textual: *«si tenemos una API de IA conectada a este módulo entonces debe ser
> capaz de diferenciar cuando un usuario está logueado y cuando no está logueado: si no está logueado
> se le invita a crear su primera tienda; luego que crea su primera tienda se le invita a crear su
> primer producto.»*

El **primer paso de toda conversación nueva** es **`arranque`**, y su mensaje lo arma el **servidor**
(`tienda_ia_arranque_extra()`, que mira `usuario_actual()` y sus tiendas). Tres caras:

| Quién llega | Qué le dice | Qué le ofrece |
|---|---|---|
| **Sin cuenta** | *«¡Hola! 👋 Soy 🛠️ El maestro y veo que todavía **no tienes tienda** en DeChimbote.com. Te la armo en 5 minutos: tú me cuentas, yo la publico con tu WhatsApp. ¿Vamos?»* | `🚀 Crear mi primera tienda` · `🔑 Ya tengo cuenta` (entra en la misma pestaña) |
| **Con cuenta y sin tiendas** | *«¡Hola, Jimmy! 👋 Todavía **no tienes ninguna tienda** publicada 📭 ¿Armamos la tuya?»* | `🚀 Crear mi primera tienda` |
| **Con cuenta y con tiendas** | *«¡Hola, Jimmy! 👋 Ya tienes **2 tiendas**: «Combinado Doña Lucha», «Bodega El Sol». ¿Qué hacemos hoy?»* | `🛍️ Agregar un producto` · `🛠️ Crear otra tienda` |

* **Con UNA sola tienda no se le pregunta cuál**: al tocar «Agregar un producto» el asistente dice
  *«Perfecto, va en «Combinado Doña Lucha» 👌»* y pasa directo al nombre del producto. Con dos o más,
  le muestra la lista (paso `tienda`).
* **El saludo en el `arranque` no se duplica**: ese mismo mensaje saluda, invita y pregunta. Para las
  demás conversaciones (modo «ya tengo una tienda») el saludo corto lo pone `tienda_ia_saludo()`.
* ⚠️ **El nombre con que se le saluda** sale de `tienda_ia_como_llamarlo()`: si el `nombre` del usuario
  **es el de una de sus tiendas** (es lo que pasa con las cuentas que crea El maestro), **no se saluda
  por nombre** — si no salía *«¡Hola, Combinado!»* (§12, trampa 16).
* **Y la puerta del primer producto nunca se cierra**: se le ofrece al publicar (`publicado`) y, si
  dice *«Ahora no»*, otra vez en la tarjeta final (`fin`).

## §4. CÓMO ESTÁ HECHO (arquitectura)

```
assets/js/tienda_ia.js  ──POST/GET──▶  api/tienda_ia.php  ──▶  includes/tienda_ia.php  ──▶  api.deepseek.com
   (la conversación)                    (la puerta JSON)        (el motor: pasos, guion, IA,   (modelo con visión)
   cámara · dictado · chips                                     publicación y medición)              │
                                                                        │                             │
                                          includes/config_tienda_ia.php ◀── (clave PROPIA, topes, precios)
                                                                        ▼
                                     directorio_negocios · directorio_fotos · directorio_servicios · directorio_producto_fotos
                                                                        ▼
                                          directorio_ia_tiendas + directorio_ia_tiendas_log  (el avance y el consumo)
```

**El paso lo decide el servidor, la IA solo escribe.** El estado vive en la tabla
(`paso` + `datos` JSON) y el motor (`tienda_ia_recibir()`) es un `switch` por paso. Esa es la
diferencia con «un chat con un prompt largo»: aquí la IA **no puede** pedir un dato que ya tiene, ni
saltarse el candado de las fotos, ni publicar sin que el dueño toque el botón.

### Archivos del módulo

| Archivo | Qué es |
|---|---|
| `deploy/includes/config_tienda_ia.php` | 🔑 **La clave propia**, el modelo, el razonamiento apagado, los topes de tokens, los límites, los paquetes 1/3/5, el estado con que nace la tienda y los precios de DeepSeek. |
| `deploy/includes/tienda_ia.php` | **El motor**: tablas, gasto, llamada a la API, sesión, guion, prompts por paso, publicación y estadísticas. |
| `deploy/api/tienda_ia.php` | **La puerta** que usa el navegador (GET la conversación · POST la respuesta o la foto). Aquí se comprueba la sesión y el CSRF. |
| `deploy/crear_tienda_ia.php` | **La página** (URL amigable `/crear-tienda`). Es una **página sola de pantalla completa** (§4bis): no incluye la cabecera ni el pie del sitio. |
| `deploy/assets/js/tienda_ia.js` | La conversación: burbujas con avatar, chips, **cámara (1 foto) y galería (varias fotos)**, dictado, GPS, resumen y las tarjetas de «¡publicada!». |
| `deploy/assets/css/tienda_ia.css` | Los estilos (móvil primero, 16 px) **y los del dictado** (`.cz-dict*`, que en el sitio vivían dentro de `productos.php`). |
| `deploy/includes/vista_tienda_ia_admin.php` | La pestaña **🛠️ El maestro (tiendas IA)** del Súper Admin: consumo y embudo. |
| `deploy/assets/js/dictado_voz.js` | 🎙️ (ya existía) El botón de dictar se pinta solo sobre el `[data-dictado]` del campo. |
| `deploy/assets/js/imagen_optimizar.js` · `includes/imagenes.php` | (ya existían) La compresión en el navegador y el motor WebP + versiones 300/800/1600 del sitio. |
| `deploy/includes/clave_recuperar.php` | 🔑 **EL MOTOR DE «OLVIDÉ MI CONTRASEÑA»** (§3ter): la tabla de pedidos, la búsqueda de la cuenta por número o correo, el aviso al jefe, la **clave nueva** (mismo formato que El maestro) y el mensaje + enlace de WhatsApp. |
| `deploy/recuperar.php` | 🔑 **La página del dueño** (URL amable `/recuperar`, enlazada desde `/login`): pide su número o su correo y deja el pedido. Nunca dice si la cuenta existe. |

### Tablas nuevas (se crean solas)

```sql
directorio_ia_tiendas       -- una conversación: usuario, modo, negocio_id, paso, estado,
                            -- datos (JSON con todo lo contestado), chat (lo que se ve),
                            -- llamadas, tokens_in/out, cache_hit, costo_usd, fechas
directorio_ia_tiendas_log   -- CADA llamada a la IA: paso, modelo, con_imagen, ok/error,
                            -- tokens, caché, ms y costo_usd  ← el medidor del consumo
directorio_claves_pedidas   -- 🔑 cada pedido de «olvidé mi contraseña» (§3ter): usuario_id,
                            -- entrada + entrada_norm (para no duplicar), teléfono, nombre, ip,
                            -- estado (pendiente/atendido/descartado), quién y cuándo lo atendió
```

Se instalan **solas** la primera vez que alguien abre la página (`tienda_ia_tablas_ok()` →
`tienda_ia_instalar()`), porque en este hosting los `migrar_*.php` están bloqueados por el
antivirus. La pestaña del Súper Admin avisa si faltan.

## §4bis. 🖥️ LA PÁGINA ES UNA PÁGINA SOLA (2026-09-15)

> 🔴 El jefe lo dijo así: *«se pudo ver en el área de llenado, por así decirlo, del módulo el footer
> de la página, cosa que se supone que es una página; hazlo como página, una página normal»*. Y
> cuando se le preguntó, eligió **«página sola, pantalla completa»**.

`crear_tienda_ia.php` **ya no incluye `includes/header.php` ni `includes/footer.php`**. Es dueña de su
documento entero y su estructura es una **columna de `100dvh`**:

```
.tia-app  (height: 100vh; height: 100dvh; display:flex; flex-direction:column; overflow:hidden)
   ├── .tia-top        cabecera propia: ← volver · 🛠️ El maestro · el paso · ⋯ (menú) · la barrita
   ├── .tia-chat       LA conversación — `flex:1 1 auto; min-height:0; overflow-y:auto` (lo único que scrollea)
   ├── .tia-opciones   los chips del paso (`max-height:40vh`, con su propio scroll si hay muchos)
   └── .tia-composer   la barra de escribir — `flex:0 0 auto` y **nada debajo de ella**
```

* **Por qué se acabó el problema:** la barra de escribir **ya no es `position: fixed`** (antes lo era
  y tapaba el pie del sitio, que se veía «dentro del área de chat»). Ahora es la última pieza de la
  columna: **debajo no existe nada**, ni el pie del sitio, ni el botón del ninja, ni un aviso flotante.
* **📏 MEDIDO** el 2026-09-15 con Chrome sin cabeza sobre la página real (marcado y CSS de
  producción): `página que scrollea = NO` · `composer position = static` · `composer pegado al borde
  inferior = sí` · `elementos debajo del composer = 0` · `pie del sitio en la página = 0` ·
  `fuente del campo = 16 px` · `galería múltiple = sí` · `rejilla de fotos visible = 5/5`.
* **Lo que se pierde:** el menú hamburguesa del sitio. En su lugar hay un **⋯** con *Guardar y seguir
  después*, *Empezar de nuevo*, *Ir a mi panel* / *Entrar con mi cuenta* y *Volver al inicio*, más la
  **←** de la cabecera. Es lo que corresponde a una página de trabajo a pantalla completa.
* **Sí se conserva el conteo de visitas**: la página llama a `stats_registrar_visita()` en un
  `try/catch` (lo único de `header.php` que hacía falta aquí).
* ⚠️ Como no se incluye la cabecera, **el CSS del sitio no carga**: todo lo que esta página necesita
  (colores, tipografía, el dictado `.cz-dict*`) vive en `assets/css/tienda_ia.css`. Si algún día se
  quiere reutilizar un componente del sitio aquí, hay que traer **sus estilos**, no dar por hecho
  que vienen de `base.css`.

---

## §5. LA IA: QUÉ SE LE PIDE Y CÓMO SE LE HABLA

* **Modelo:** `deepseek-v4-flash-vision-exp` (**el mismo del chat 🥷**, cuesta lo mismo que el normal
  y una imagen ocupa como mucho ~384 tokens). Comprobado el 2026-09-14 con la clave nueva: lee texto
  en español de una foto y describe un local real.
* **🧠 El razonamiento va APAGADO** (`reasoning_effort: none`) y esto es **lo más importante de todo
  el módulo**. Medido con la clave del constructor, no es teoría:

  | | Sin apagar | Con `none` |
  |---|---|---|
  | Saludar y aprobar un nombre | 2,03 s · **254 tokens** (229 de pensamiento) | **1,09 s · 21 tokens** |
  | Elegir rubro entre 8 candidatos | 4,7 s · **respuesta VACÍA** (gastó 800 tokens pensando) | **0,81 s · «17, 12, 3»** |
  | Mirar la foto de una tienda | 5,3 s y texto más flojo | **1,8 s y descripción más honesta** |

  Un modelo que se pone a razonar en una conversación guiada **tarda el doble, gasta 12 veces más y
  a veces se queda mudo** (devuelve texto vacío). Apagado, es más rápido, más barato y **más fiable**.
* **El guion de fondo (system) es idéntico en todas las llamadas** a propósito: DeepSeek acierta la
  caché del prompt y sale más barato. **Nada de la tienda va en el system**: eso va en el turno.
* **Formato de las respuestas: líneas con etiqueta, nunca JSON.** Se probó pedir «responde SOLO un
  JSON»: el modelo se pone a razonar, se queda sin tokens y **devuelve vacío**. Las tareas piden
  cosas como *«LÍNEA 1: SI o NO · LÍNEA 2: una frase corta»* o *«solo los números»*.
* **Cada paso tiene su respaldo local.** Si la IA devuelve `ok:false` (clave, red, saldo, vacío), el
  motor sigue con el guion y **acepta lo que dijo el dueño** (él manda).
* **Los rubros se le ofrecen en lista corta:** el motor saca los 8 candidatos **sin IA** (nombre +
  las palabras clave de `directorio_categoria_claves`, como hace el Caminante) y la IA elige 1-3.
  Así el prompt no lleva los 122 rubros y el resultado son rubros **que existen de verdad**.
* **Las fotos que ve la IA son una copia de viaje:** se guarda el WebP del sitio (≤1600 px + 800 +
  300) y se le manda un JPEG reducido a 1024 px (`tienda_ia_foto_data_url()`); **esa copia no se
  archiva**. Si el servidor no tuviera GD, se manda el WebP tal cual (comprobado que lo entiende).
* **Las fotos SÍ se guardan** (son las de la tienda y del producto) en
  `assets/uploads/<usuario>/ia_<fecha>_<hex>.webp`, con el prefijo `ia_` para poder distinguir lo que
  dejó este módulo de las fotos buenas del sitio.

---

## §5bis. 🖼️ LAS FOTOS SE COMPRIMEN (WebP) Y EL AVISO DE LOS 10 MINUTOS

### Cómo quedan las fotos
El constructor usa **la misma función del sitio**: `img_guardar_subida()` (`includes/imagenes.php`) →
**WebP** calidad 82, lado máximo **1600 px**, más las versiones de **800** y **300 px**, que son las que
sirve la ficha con `img_tag()` (el `srcset`).

**📏 MEDIDO el 2026-09-14** con una foto «de celular» realista de **2400×1600**:

| | Peso |
|---|---|
| La foto tal como sale del celular | **583 KB** |
| Guardada por el sitio (WebP 1600 px) | **66 KB** → **89 % menos** |
| Versión de 800 px (la ficha en escritorio) | 18,6 KB |
| **Versión de 300 px** (la rejilla en el celular) | **6,1 KB** ← la que de verdad baja el visitante |

⚠️ **Honestidad de la medición:** con una imagen de **ruido puro** (peor caso posible, nada que ver con
una foto) el WebP puede pesar **más** que el JPEG. El ahorro real sale de las fotos de verdad y de
servir la versión del tamaño justo. Por eso la prueba 6 mide con una foto realista (y la primera vez,
midiendo con ruido, salió al revés: quedó anotado en §12).

### Lo que se le dice al dueño al terminar (orden del jefe, 2026-09-14 noche)
*«No olvides decirle a la persona que las fotos están muy nítidas y son muy claras, vas a comprimirlas
para que sean más rápidas de descargar y las puedan ver más pronto los visitantes… que dentro de 10
minutos ya puede entrar a ver todo listo con fotos. Pide siempre ese tiempo… a partir de ahora calculas
la hora y le dices, por ejemplo, si son las 8: a partir de las 8:10 tu sitio web ya estará listo.»*

Al publicar, el asistente dice (texto real):

> 🎉 **¡Ya tienes tu tienda!** Mírala aquí: \<enlace\>
>
> 📸 Tus fotos están **muy nítidas y claras**. Las voy a **comprimir** para que carguen rapidísimo en tu
> tienda: así tus visitantes las ven al toque y tu página abre volando en cualquier celular.
>
> ⏱️ Tu celular está terminando de subirlas, así que dale ese tiempito: **a partir de las 21:10 tu sitio
> ya estará listo con todas tus fotos**. A esa hora entras y lo ves todo.
>
> ¿Te parece si ahora creamos **tu primer producto**?

* **La hora se calcula de verdad**: `tienda_ia_hora_aviso()` = hora de **Lima** de ahora +
  `TIENDA_IA_MINUTOS_ESPERA` (10). Si son las 8:02, dice «8:12» (y nunca «08:12»).
* Cada producto terminado lleva el mismo recordatorio en corto: *«Tu foto está nítida y clara y la estoy
  comprimiendo para que cargue rapidísimo… si no la ves, dale unos minutitos al celular y recarga.»*
* Y la tarjeta final repite: *«si todavía no las ves todas, entra a partir de las \<hora\>»*.
* ⛔ **Nunca se le habla de «megabytes»** (orden del jefe): se le dice que **carga rápido** y que sus
  visitantes **la ven al toque**.

## §5ter. 📷 LA GALERÍA MÚLTIPLE: VARIAS FOTOS EN UNA SOLA LLAMADA (2026-09-15)

> 🔴 Orden del jefe: *«se supone que tienes libertad para elegir muchas galerías»* + *«el usuario
> tiene libertad de subir una o cinco fotos o las que sean, u **hasta ocho** si es necesario»*.

Cómo funciona, de punta a punta:

1. **El botón 🖼️ Elegir varias** abre el carrete con `multiple`: el dueño marca 5 u 8 fotos de una vez.
2. El navegador las **comprime una por una** (`CZImg.optimizar`, 1600 px) mostrando *«Preparando tus
   fotos… 3 de 5»*, y pinta **una burbuja con las miniaturas** (`URL.createObjectURL`, se liberan al
   cargar) con la nota *«Subiendo a tu tienda…»*.
3. Se mandan **en UN solo envío** (`FormData` con `foto[]`). El servidor las normaliza con
   `tia_archivos()` (`api/tienda_ia.php`), **nunca más de 8 por envío**, y las guarda todas con
   `tienda_ia_guardar_foto()` (WebP 1600 + 800 + 300).
4. **La IA las mira en UNA sola llamada** (`tienda_ia_ia_fotos()` → `tienda_ia_llamar(['imagenes'=>…])`).
   Pedido: *«LÍNEA 1: los números de las fotos que sean CAPTURA, o NINGUNA · LÍNEA 2: tu respuesta»*.
5. **Respeto del tope:** si ya tiene 5 y manda 5, se guardan las que caben y se le dice *«Te guardo
   las primeras 3: el tope son 8 fotos de tienda 😉»*. Al llegar a 8 el asistente **pasa solo** a
   «cómo atiendes».
6. **La regla de las capturas sigue valiendo para TODO el lote:** si la IA marca la foto 2 y la 4, se
   **borran del hosting** y se le avisa; si vuelve a mandarlas, manda él (`tienda_ia_captura_decision`).

**📏 MEDIDO el 2026-09-15 con la clave del constructor** (no es teoría):

| | Antes (una llamada por foto) | Ahora (el lote) |
|---|---|---|
| 3 fotos de golpe | 3 llamadas · ~6 s | **1 llamada · 2,2 s** |
| 8 fotos | 8 llamadas · ~12 s | **1 llamada · 2,2 s** |
| Costo de entrada | 384 tokens por foto, una por una | **2 imágenes ≈ 672 tokens · 3 imágenes ≈ 951 tokens** (≈ 300 por foto) |

* **La respuesta trae el candado y el comentario en el mismo mensaje**, así que el lote **no gasta el
  doble** por mirar y comentar: es una sola llamada.
* ⚠️ **`NINGUNA` es parte del protocolo, no del comentario** (§12, trampa 14): el motor lo quita antes
  de mostrarle nada al dueño.
* **Lo que la IA ve se guarda** (`datos['visto']`, hasta 5 frases) y es lo que después hace que los
  productos propuestos salgan **de sus fotos** y no solo de su relato (§5quater).
* El tope de fotos por llamada es `TIENDA_IA_FOTO_LOTE_IA` (8).

## §5quater. 🛍️ LOS PRODUCTOS SE PROPONEN CON LO QUE LA IA VIO (2026-09-15)

> 🔴 Orden del jefe: *«también le vamos a preguntar si desea tales productos… unos cuatro o cinco
> productos que le podamos crear **en base a sus fotos**»* (y al preguntársele eligió que el
> asistente cree **2**, como estaba).

* Cada comentario de la IA sobre las fotos de la tienda se apila en `datos['visto']` (máximo 5).
* `tienda_ia_ia_producto()` mete ese «visto» en el pedido: por eso las opciones del primer producto
  pueden ser cosas que **se ven** en el local (su mostrador, su cocina, sus ollas) y no solo lo que el
  dueño escribió.
* **Si no hay relato** (el caso del dueño que ya tiene tienda y vuelve a agregar un producto: su
  conversación nueva no trae `trato`), el contexto se saca de la **descripción que él ya le puso a su
  tienda** en `directorio_negocios` — y si tampoco hay nada, **no se le pregunta a la IA** (§12,
  trampa 15).

## §5quinquies. 🧠 LA IA QUE PIENSA: ¿LOCAL O DOMICILIO? (2026-09-15)

> 🔴 Orden del jefe, textual: *«la guía debe ser capaz de pensar, la Inteligencia artificial debe ser
> capaz de pensar: acabo de subir fotos de unos camiones de arena, de unos camiones de chancapiedra, y me
> dice "veo tus camiones azules con el letrero Willy Jara, montones de arena y piedra y hasta
> minicargadoras; mándame la foto del frente de tu local o dónde atiendes"… **ahí no le estamos dando al
> usuario ninguna opción**: podría decir "sí tengo local" y busca la foto, **o podría decir "no tengo
> local"; cuando diga "no tengo local", entonces le ofrecemos entregas a domicilio**»*.

Cómo quedó (el servidor sigue mandando el paso; la IA solo aporta el **criterio**):

1. El pedido de las fotos ahora pide **TRES líneas**: `LÍNEA 1` las capturas · `LÍNEA 2` el comentario
   para el dueño · **`LÍNEA 3`: `LOCAL`, `DOMICILIO` o `IGUAL`**.
2. `DOMICILIO` = *servicio que SE LLEVA* (camiones, volquetes, montones de arena, piedra o ripio,
   reparto, minicargadora). Se le pide **explícitamente** que si ve eso **no pida «la foto de tu local»**.
3. El motor guarda ese pensamiento en `datos['sugerir_domicilio']` y, si es `DOMICILIO`, añade el chip
   **`🚚 No tengo local: lo llevo a su casa`** (y se calla cuando él ya decidió).
4. Si lo toca: `vendedor = domicilio`, mensaje *«Listo: como no tienes local, en tu tienda va a decir que
   **lo llevas a su casa** 🚚»* y **se salta** el paso `vendedor` (va directo a la zona).
5. La LÍNEA 3 **no se le muestra al dueño**: se recorta antes de pintar el comentario (igual que el
   `NINGUNA` del candado de capturas).

**Medido el 2026-09-15 (prueba 8)**: con fotos de volquetes y montones, la IA contestó `DOMICILIO` y el
botón salió ✅.

## §5sexies. 📝 EL COPY DE LA DESCRIPCIÓN LO ESCRIBE LA IA (2026-09-15)

> 🔴 Orden del jefe: *«aquí ya debe la IA aplicar el **copyright**, sobre todo en la descripción de "de
> qué trata"… el copyright debe ser **en vivo, ahí creado**»*.

* `tienda_ia_ia_descripcion()` escribe **2 a 4 frases** con: lo que contó el dueño, **lo que se vio en sus
  fotos** (`datos['visto']`), sus rubros, la zona, cómo vende y el horario. Se le prohíbe inventar
  (precios, años, direcciones, promesas), poner títulos, listas o emojis.
* Se llama **al publicar** (y también cada vez que se actualiza la tienda), dentro de
  `tienda_ia_descripcion_final()`: si la IA no puede, se guarda **tal cual lo que escribió el dueño**
  (el módulo nunca depende de la IA).
* Si eligió **«Todas las anteriores»**, a esa descripción se le añade la línea de la zona
  (`TIENDA_IA_NOTA_TODA_LA_ZONA`).
* Se apaga con **`TIENDA_IA_DESCRIPCION_IA = false`**.
* **Medido (prueba 8, 2026-09-15):** *«En Willy Jara vendemos arena gruesa, arena fina, ripio, piedra
  chancada y hormigón para tu obra. Llevamos todo en volquetes hasta la puerta de tu casa, en Chimbote y
  en toda la provincia. También alquilamos minicargador…»* — sale de sus fotos + su relato, sin inventar.

### 📝 Y el copy de cada PRODUCTO (2026-09-15, tarde)

* Orden del jefe al ver su tienda de prueba: *«si te fijas tiene muy mal copyright, tiene el mismo que el
  usuario escribió… **mejóralo porque ya está publicado**»*. Dos cosas:
* **En las tiendas nuevas**: `tienda_ia_ia_descripcion_producto()` escribe **1 o 2 frases** de cada
  producto **mirando su foto** (color, cómo se entrega, para qué sirve) + lo que vende la tienda. Antes
  el producto se creaba **sin descripción** (iba `null`). Si la IA falla, se guarda vacío como antes:
  el producto se crea igual.
* **En las tiendas YA publicadas** (como la suya, que nació con la versión 3): para eso está la sonda
  **`__tia_copy_tienda.php`** + el corredor **`__tia_run_copy.py`** (§11): le pide a la IA el copy
  **mirando las fotos de la tienda** (hasta 4) y el de sus productos, enseña el antes y el después, y
  solo escribe si se le dice `go`. **Su tienda #1741 «Willy Jara» quedó mejorada así el 2026-09-15.**
* **Medido con su tienda (antes / después):**
  * Antes: *«Tengo camiones y vendo arena granulada arena gruesa arena fina piedra chancada piedra grande
    recojo desmonte cuento con tres camiones y trabajo con diferentes ferreterías ofreciéndoles servicios
    de construcción materiales de construcción»*.
  * Ahora: *«Willy Jara te lleva arena gruesa, fina, piedra chancada y desmonte hasta tu obra en Chimbote.
    Sus camiones azules se ven trabajando en la calle, listos para atender tu pedido. Atiende todo el día
    y también abastece a ferreterías de la zona.»* ← **los camiones azules salen de MIRAR sus fotos**.
  * Y sus 2 productos pasaron de **no tener descripción** a: *«Arena gruesa de buen grano, lista para tu
    obra y entregada con nuestros camiones.»* / *«Recojo tu desmonte con camión volquete como el de la
    foto, cargado y listo para llevar…»*.

## §5septies. 🏷️ LOS RUBROS MÚLTIPLES: PRINCIPAL + HASTA 3 MÁS (2026-09-15)

* **El principal** va en `directorio_negocios.categoria_id`; **los extras** en
  **`directorio_negocio_rubros`** (mismo esquema que usa el editor de tiendas:
  `negocio_id, categoria_id, orden, creado_en`, PK compuesta).
* `tienda_ia_rubros_para_sumar()` arma los chips: **primero los que la IA ya había propuesto** para el
  principal (así el dueño ve «Materiales de construcción» y «Ferreterías» juntos) y después los candidatos
  por palabras clave. Nunca repite los que ya tiene.
* `tienda_ia_guardar_rubros_extra()` escribe los extras: se llama **al publicar** y **al actualizar** la
  tienda (borra y reescribe las filas de esa tienda), y avisa al caché del buscador
  (`fuzzy_olvidar_cache()`). Si la tabla no existe (`rubros_multi_ok()`), la tienda queda con su principal
  y **no se rompe nada**.
* Tope: **`TIENDA_IA_RUBROS_MAX` = 4** (principal + 3). Al llegar al tope el asistente sigue solo.
* **Medido (prueba 8, 2026-09-15):** «Construcción / Ingeniería» + **Ferreterías** quedaron guardados, y
  `directorio_negocio_rubros` devolvió `[{"categoria_id":2,"nombre":"Ferreterías"}]` ✅.

## §5octies. 👁️📋 LA LECTURA DE LAS FOTOS: LA FICHA DEL ARRANQUE (2026-09-15, noche)

> 🔴 Orden del jefe: *«que pida al usuario mínimo cinco fotos y luego de las fotos que ha recibido
> analizarlas y ver qué datos puede obtener: si se puede adivinar el rubro, si se puede adivinar el
> título del negocio, tal vez el teléfono.»*

**Es UNA sola llamada para todo el lote** (`tienda_ia_ia_arranque()`, en `includes/tienda_ia.php`), y
la respuesta se lee con **`tienda_ia_leer_ficha()`**. La IA no llena un formulario: **contesta 8 líneas
etiquetadas** (al modelo no se le pide JSON, trampa 5).

| Línea | Qué se le pide | Qué se hace con eso |
|---|---|---|
| `CAPTURAS` | los números de las fotos que sean **captura de pantalla**, o `NINGUNA` | el candado inviolable del sitio: la captura **se borra** y se le avisa una vez (`tienda_ia_captura_decision`) |
| `LETRERO` | el nombre del negocio **tal como se lee pintado o impreso** (no el rubro, no una marca de gaseosa, no la imprenta) | paso **`nombre_ok`**: se le propone **para que lo confirme con un toque** (`tienda_ia_nombre_de_letrero()` descarta las palabras genéricas: una pared que solo dice «BODEGA» no es un nombre y se le pregunta) |
| `RUBRO` | de qué es el negocio, en 1 a 3 palabras | paso **`rubro_ok`**: se cruza con los **122 rubros reales** del directorio (`tienda_ia_chips_prototipo()` → `tienda_ia_rubros_candidatos()`) y se le ofrecen como botones |
| `TELEFONO` | el número que se lea, **solo dígitos** | paso **`tel_leido`**: **se le pregunta siempre** (ver abajo, es el dato peligroso) |
| `DIRECCION` | calle, número, distrito, «frente a…» | el paso `distrito` le pone **primero** el distrito que se lea ahí (`tienda_ia_distrito_leido()`; ⚠️ «Nuevo Chimbote» gana a «Chimbote» porque se busca el nombre más largo) |
| `LOCAL` | `LOCAL` · `DOMICILIO` · `IGUAL` | `datos['sugerir_domicilio']`: si es `DOMICILIO`, en el paso `vendedor` sale el chip **`🚚 No tengo local: lo llevo a su casa`** |
| `PRODUCTOS` | hasta 4 cosas concretas que se vean y se puedan vender | `datos['productos_vistos']`: entra en el contexto del copy y de las propuestas de producto |
| `COMENTARIO` | 1 o 2 frases para él, contando qué vio de verdad | es lo que se le muestra en el chat (y se apila en `datos['visto']`) |

**Lo que hace que se pueda LEER y no solo mirar** (`config_tienda_ia.php`):

* `TIENDA_IA_FOTO_LADO_LETRERO` (**1600 px**) y `TIENDA_IA_FOTO_CALIDAD_LETRERO` (**85**) frente a los
  1024 px / 78 del resto de llamadas: **la letra chica del aviso no se lee a 1024 px** (los dígitos se
  confunden: 6/8, 3/9, 1/7). `tienda_ia_foto_data_url($rel, $lado, $calidad)` acepta la medida y
  `tienda_ia_llamar(['lado'=>…, 'calidad'=>…])` la aplica solo a esa llamada.
* `TIENDA_IA_MAX_TOKENS_LETRERO` (**700**): son 8 líneas y 450 tokens se quedaban cortos.

**📏 Medido el 2026-09-15 (pruebas 10 y 12, clave real, fotos de verdad):**

| | Valor |
|---|---|
| Fotos reales de una tienda del sitio, leídas en UNA llamada | **3,2 s · ≈ 3 600 tokens de entrada** |
| Lo que devolvió (fotos de un puesto real) | `LETRERO: JENNY'S` · `TELEFONO: 914211676` · `LOCAL` · `PRODUCTOS: keratina, bleach, crema Dr. Ives` · comentario honesto (*«las otras fotos son de otros sitios, así que no las tomo en cuenta»*) |
| 8 fotos fabricadas con letrero legible (incluida una **tarjeta**) | `LETRERO: FERRETERIA EL MARTILLO DE ORO` · `RUBRO: ferretería` · `TELEFONO: 943112233` · `DIRECCION: Av. Jose Pardo 123` · `PRODUCTOS: clavos, tornillos, pernos, pintura, brochas` |
| Costo de esa llamada | **≈ US$ 0,0009** (8 fotos a 1600 px ≈ 5 800 tokens de entrada) — la tienda entera sigue costando **≈ US$ 0,002** |
| El caso peligroso (número de otra tienda) | el asistente **pregunta**, **no publica** y **no toca** la tienda existente: `__tia_prueba12.php` §11 |

**📸 QUÉ FOTOS SE PIDEN** (orden del jefe, 2026-09-15 noche): *«fotos de la parte de afuera de tu
tienda, dentro de tu tienda, de tus productos, de las máquinas que usas, de tu personal; si tienes una
tarjeta tómale fotos y si tienes un folleto también»* → el paso `fotos` lo dice tal cual
(`TIENDA_IA_TEXTO_FOTOS_ARRANQUE`) y **a la IA también se le dice**, porque una **tarjeta de
presentación** o un **folleto** traen nombre, teléfono y dirección: son foto legítima del negocio (no
capturas) y de ahí también se lee la ficha.

### 🔴 EL TELÉFONO DEL LETRERO: SE PREGUNTA SIEMPRE (el único dato que puede hacer daño)

El jefe eligió **«lo propone y él confirma»**, y después lo hizo más fuerte (2026-09-15, noche):
*«se puede dañar una tienda que ya exista… en todo caso estaría creando una subtienda temporal o
preguntaría si es el número correcto; antes de hacer la publicación debe preguntar el nombre correcto,
o sea confirmar si el nombre y el número son los correctos»*. Así que hay **dos candados**:

1. **El número leído no entra nunca solo**: va al paso `tel_leido`, con su aviso («a veces es el de la
   imprenta que hizo tu letrero») y **solo se usa si él toca `✅ Sí, es mi WhatsApp`**
   (`datos['telefono_ok'] = 1`). Si no lo leyó, se le pregunta (`whatsapp`).
2. 🔴 **Si el número ya es de alguien, NO se publica ni se toca nada** (paso `tel_ocupado`): el motor
   comprueba el número **antes** de publicar (`tienda_ia_usuario_por_telefono()`), y si ya tiene cuenta
   **se detiene**, le dice **de quién es** (*«Ese número ya tiene la tienda «X». ¿Es tuya?»* — se le
   nombra incluso cuando el nombre no coincide, porque ésa es la pista que delata el error) y espera:
   · `✅ Sí, es mi número` → sigue el camino de siempre (actualizar su tienda o crearle una nueva **en
   su cuenta**) y **ahí sí** se publica; · `🆕 No, me equivoqué` → se le borra el número y se le vuelve
   a preguntar. **Mientras no conteste no existe nada suyo en la cuenta de otra persona** (ni tienda
   nueva, ni datos pegados a la tienda que ya existía). Probado con un **cebo real** en
   `__tia_prueba12.php`: el cebo quedó **byte a byte idéntico** antes, durante y después (§11).

### 🧹 Cómo se lee la ficha (y por qué así)

* Las etiquetas **se buscan en cualquier parte del texto** y el valor es lo que va hasta la etiqueta
  siguiente — porque el modelo contestó **las 8 seguidas en un solo renglón** la primera vez
  (trampa 26). Se aceptan **con tilde o sin ella**, en mayúsculas o minúsculas, con viñetas o con
  `**markdown**`.
* Los `NINGUNO` / `NINGUNA` / `no se ve` **son protocolo, no datos**: `tienda_ia_ficha_valor()` los
  convierte en vacío **antes** de que nadie los vea (igual que el `NINGUNA` del candado de capturas).
* Al comentario se le quitan los restos de etiquetas antes de mostrarlo: **el protocolo jamás se le
  muestra al dueño**.
* Si la IA **no contesta** (sin saldo, sin red), la ficha queda vacía y el flujo **sigue igual**: se le
  pregunta el nombre como antes y el rubro sale de lo que haya (`tienda_ia_chips_prototipo()` cae a los
  rubros con más tiendas). **El módulo nunca depende de la IA.**

---

## §6. 🔑 LA CLAVE ES PROPIA (y la cuenta del dueño también)

* Está en **`deploy/includes/config_tienda_ia.php` → `TIENDA_IA_DEEPSEEK_KEY`**, y **jamás** se
  escribe en una guía, en un archivo de la raíz ni en JavaScript (el `.htaccess` de la raíz bloquea
  todo `/includes/` con `RedirectMatch 403`). **Verificado el 2026-09-14:** pedir
  `https://dechimbote.com/includes/config_tienda_ia.php` por web devuelve **403**.
* Es una **cuenta aparte de la del chat 🥷**: así el gasto del constructor se mide con **su** saldo y
  no se mezcla con el de la ayuda. (Saldo el 2026-09-14: **US$ 7,72**.)
* Si la clave se vacía, el asistente **sigue funcionando** con el guion local (y no gasta nada).

---

## §7. 💰 LO QUE CUESTA (medido de verdad, 2026-09-14)

Precios **oficiales de DeepSeek** (tabla de [api-docs.deepseek.com](https://api-docs.deepseek.com/quick_start/pricing),
consultada el **2026-09-14**) para **`deepseek-flash`**, que es el modelo que atiende a
`deepseek-v4-flash-vision-exp` —cuyo nombre está **retirado pero se sigue aceptando**, y se cobra a precio
de Flash—. Por **1M de tokens**:

| Concepto | Fuera de punta | En punta |
|---|---|---|
| Entrada **en caché** | $0,003 | $0,006 |
| Entrada **sin caché** | $0,15 | $0,30 |
| **Salida** (lo que escribe) | $0,60 | $1,20 |

**Punta** = 01:00–04:00 y 06:00–10:00 **UTC** de lunes a viernes (en Lima: 20:00–23:00 y 01:00–05:00).
Todo lo demás cuesta **la mitad**. Los precios del módulo viven en `config_tienda_ia.php`
(`TIENDA_IA_PRECIO_*`) y son **estos**, no los de la tabla vieja del chat 🥷 (esa dice 0,007 / 0,22 / 0,66).

| Medición real del constructor | Valor |
|---|---|
| Costo de una llamada de texto (promedio) | **≈ US$ 0,00009** |
| Costo de una llamada con foto (promedio) | **≈ US$ 0,00021** |
| Respuesta promedio de la IA | **~1 segundo** (965 ms medidos, `none`) |
| **Una tienda con 1 producto** (~15 llamadas) | **≈ US$ 0,002** (dos décimas de céntimo de dólar) |
| **Una tienda con 5 productos** (~25 llamadas) | **≈ US$ 0,004** |
| Con un saldo de US$ 5 | **≈ 2 500 tiendas** de 1 producto (≈ 1 250 de 5 productos) |

Lo que lo mantiene barato: el razonamiento apagado, el system idéntico (caché), los topes de tokens,
**una sola llamada por respuesta del dueño** y que los pasos de chips (paquete, vendedor, horario,
distrito) **no llaman a la IA**.

**Dónde se mira:** Súper Admin → **🛠️ El maestro (tiendas IA)** (§9).

---

## §8. LAS PUERTAS DE ENTRADA (cómo llega la gente)

> 🔴 **ORDEN DEL JEFE (2026-09-15, noche): SOLO DOS FORMAS DE CREAR UNA TIENDA.** Textual: *«Uno, **El
> caminante**, que se va a quedar tal cual como está; y dos, el **modo con inteligencia artificial**.»*
>
> | Forma | Dónde | Estado |
> |---|---|---|
> | **🚶 El caminante** | `/caminante/` (la captura en campo, el agente que va con el celular) | **Intacto: no se le tocó nada** (verificado por HTTP: sigue respondiendo **200**) |
> | **🛠️ El maestro (IA)** | `/crear-tienda` (+ `?modo=producto` para agregar un producto a una tienda ya hecha) | El flujo de la **versión 7** (§2ter) |
>
> **🚫 Y las otras puertas SE BORRARON DEL HOSTING** (`registrar_negocio.php`, `crear_negocio.php` y
> `guardar_asistente.php`): los archivos están guardados en
> **`_ARCHIVO_RETIRADO_PUERTAS_VIEJAS_2026-09-15\`** (jamás se despliega desde ahí) y en el sitio **dan
> 404** (verificado con `curl`: las tres). Se borraron con **`python __borrar_remoto.py <ruta>`**.

**Los caminos que llevan a El maestro (todos revisados y desplegados):**

1. **El Ninja 🥷** (`includes/chatbot.php` → `chatbot_sugerencias()`): dos opciones que **abren la página
   directamente** (acción `url`, no pregunta):
   * `🛠️ Crear mi tienda` → `/crear-tienda`
   * `➕ Agregar un producto` → `/crear-tienda?modo=producto`
   El guion del chat (`includes/chatbot_kb.php`) y su etiqueta de ruta (`assets/js/chatbot.js`) **también
   nombran al maestro**.
2. **La portada y el menú del sitio**: el botón grande del hero (*Crear tienda*) y la opción del menú
   hamburguesa (*Crear mi tienda*) apuntan a `/crear-tienda`; el **pie** y el bloque de venta del **panel**
   también.
3. **El panel del dueño** (`panel.php`): el botón naranja **🛠️ Agregar un producto hablando con El
   maestro** (o **Armar mi tienda con El maestro** si todavía no tiene ninguna) y el acceso **Mis tiendas**.
4. **Las páginas que antes mandaban a las puertas viejas** y ahora mandan aquí: `index.php`, `buscar.php`,
   `categoria.php`, `reclamar.php`, `pedidos_sin_vendedor.php`, `registro.php` (cuenta de dueño),
   `sitemap.php`, `includes/footer.php`, `includes/header.php`, `includes/panel_reco.php`.
5. 🛒🆕 **El botón «Agregar mi tienda» de los resultados de búsqueda** (`buscar.php`, negro con letra
   rosada, debajo del botón «Ver … cerca de mí», 2026-09-16): lleva al maestro **con el producto ya dicho**
   (`/crear-tienda?modo=producto&prod=<lo que se buscó>`) y, si el visitante no tiene sesión, **primero al
   login y de ahí solo** al maestro. Detalle: **§2septies**.
6. **El enlace directo**: `dechimbote.com/crear-tienda` (regla del `.htaccess`; también funciona
   `/crear_tienda_ia.php`).

---

## §9. LA PESTAÑA DEL SÚPER ADMIN: EL CONSUMO Y EL EMBUDO

`superadmin.php?seccion=maestro` → `includes/vista_tienda_ia_admin.php` (usa las clases del panel:
`.sa-grid`, `.sa-stat`, `.sa-card`, `.sa-table`; no trae CSS propio, igual que los récords).

* **6 tarjetas:** conversaciones (y cuántas a medias/dejadas) · tiendas publicadas (y el % de
  conversaciones que terminan en tienda) · **gasto de la IA** (llamadas y promedio) · **costo por
  tienda** · **saldo de la cuenta** (y para cuántas tiendas alcanza) · fotos que miró la IA (y
  llamadas fallidas y ms de promedio).
* **🚦 El embudo:** en qué paso se quedaron las conversaciones que no terminaron, **con la
  explicación de qué significa cada uno** (p. ej. «fotos_tienda: no llegó a las 3 fotos de la tienda
  — aquí se cae la mayoría»). Es lo que dice **qué pregunta hay que mejorar**.
* **💸 Qué cuesta cada paso** (llamadas, tokens, dólares y milisegundos) y el total de hoy.
* **🕐 Las últimas conversaciones**, con la persona, el paso, el estado y el gasto.
* **🧹 Borrar fotos de tiendas a medias:** limpia los archivos `ia_*` de más de **48 h** que **no
  estén en ninguna ficha** (`tienda_ia_limpiar_huerfanas()`). Solo toca lo que empieza con `ia_` y
  pasa por el nombre exacto `ia_AAAAMMDD_<12 hex>`: **jamás** una foto buena del sitio. (Ojo: un
  `LIKE '%ia_%'` a lo bruto **sí** daría falsos positivos —«maria_01.webp»—, por eso se usa regex.)

⚠️ **El consumo sale del registro de llamadas, no de las conversaciones.** Lo cazó la prueba del
2026-09-14: al borrar una conversación el gasto se hacía 0. Ahora las conversaciones y el embudo
salen de `directorio_ia_tiendas` y **los tokens y el gasto, del log**.

---

## §10. 🚫 LA REGLA INVIOLABLE DE LAS CAPTURAS (con modales)

Una **captura de pantalla jamás se publica** (muestra las pestañas, los marcadores y las direcciones
del dueño). Aquí la IA **mira cada foto** y avisa, pero con dos modales para no dejar a nadie trabado:

1. **La IA no confunde un letrero con una captura.** El prompt lista las señales de una captura de
   verdad (barras de hora/batería/señal, pestañas, barra de direcciones, cursor, la ventana de
   WhatsApp con sus botones) y dice **explícitamente** que la foto de un letrero, un menú, un afiche,
   un papel o una pizarra **NO es una captura**. (Se afinó el 2026-09-14: al principio marcaba como
   capturas unas imágenes de prueba con texto sobre fondo plano.)
2. **Una vez se avisa, después manda el dueño** (`tienda_ia_captura_decision()`): la primera vez la
   foto **se borra del hosting** y se le explica; si la vuelve a mandar, se acepta *«es tu tienda y tú
   decides»*. Probado el 2026-09-14: `rechazar → aceptar_avisando → aceptar → rechazar` (el aviso se
   limpia con una foto normal).

**Medición con fotos reales del directorio (2026-09-14):** 4 fotos de tiendas reales miradas por la
IA → **0 falsos positivos** (describió una barbería, un taller de motos, un módulo de comida y un
local de dos pisos, cada uno como era). El candado es seguro para fotos normales.

---

## §11. CÓMO SE PRUEBA (sin publicar nada)

Sondas temporales en la raíz de `D:\RELAX` (se suben, se corren y **se borran del hosting solas** con
`python __sonda_run.py`; lo que devuelven queda en el `__*_resultado.json` de cada una):

```powershell
cd D:\RELAX
python __sonda_run.py __tia_prueba.php  tia-2026-09-14-prueba   # arranque: nombre, trato, rubro y el candado de las 3 fotos
python __sonda_run.py __tia_prueba2.php tia-2026-09-14-prueba2  # fotos, vendedor, GPS, resumen y PUBLICAR EN SIMULACRO
python __sonda_run.py __tia_prueba3.php tia-2026-09-14-prueba3  # visión con FOTOS REALES del directorio, política de capturas y GPS
python __sonda_run.py __tia_prueba4.php tia-2026-09-14-prueba4  # la pestaña del Súper Admin, la limpieza y los avisos de PHP
python __sonda_run.py __tia_prueba5.php tia-2026-09-14-prueba5  # el flujo de la versión 2 completo, sin cuenta
python __sonda_run.py __tia_prueba6.php tia-2026-09-14-prueba6  # el cierre: la HORA exacta, el aviso de comprimir y la medición del WebP
python __sonda_run.py __tia_prueba7.php tia-2026-09-14-prueba7  # «si el WhatsApp ya existe, se ACTUALIZA» (3 veces la misma persona)
python __sonda_run.py __tia_prueba8.php tia-2026-09-15-flujo     # 🆕 EL FLUJO DE HOY: arranque, rubros múltiples, lote de fotos, IA que piensa, publicación automática, 2 productos y editar después
python __sonda_run.py __tia_prueba9.php tia-2026-09-15-navegador # EL CAMINO REAL DEL NAVEGADOR (página + API + `foto[]`) — FLUJO VIEJO (v4)
python __sonda_run.py __tia_prueba10.php tia-2026-09-15-fotos     # 🆕 EL ARRANQUE CON 8 FOTOS: lectura de fotos REALES del sitio, el mínimo, la publicación silenciosa, la dirección y las redes
python __sonda_run.py __tia_prueba11.php tia-2026-09-15-navegador2 # 🆕 EL CAMINO DEL NAVEGADOR con las fotos primero: página + CSRF + 8 fotos por `foto[]` + publicar sin decirlo + estreno
python __sonda_run.py __tia_prueba12.php tia-2026-09-15-seguridad  # 🔴 LA PRUEBA DE SEGURIDAD: el número que YA ES DE OTRA TIENDA (cebo) — se pregunta, no se publica y no se toca nada
python __sonda_run.py __tia_prueba13.php tia-2026-09-15-flujo7     # 🆕 EL FLUJO DE HOY (v7) POR EL CAMINO DEL CELULAR: página + compositor de WhatsApp + 4 fotos (no avanza) + 8 fotos + tipo + distritos + PUBLICAR + editar producto y portada + WhatsApp + limpieza
python __sonda_run.py __tia_prueba14.php tia-2026-09-15-ramas      # 🆕 LAS TRES RAMAS DEL TIPO DE NEGOCIO (local → dirección/zona · ambulante → horario/zona · internet → distritos), sin gastar IA
python __sonda_run.py __tia_prueba15.php tia-2026-09-16-otratienda  # 🆕 UN DUEÑO, VARIAS TIENDAS: los submenús en ventana emergente, el botón de «crear otra tienda», el cierre de la conversación vieja y el freno de la tienda sin dueño (0 tokens de IA)
python __sonda_run.py __tia_prueba16.php tia-2026-09-16-copy-fotos # 🆕 EL COPY CON BOTONES, LAS OPINIONES AUTOMÁTICAS Y LA TANDA DE FOTOS CLASIFICADA POR LA IA (3 llamadas a la IA; limpia todo al final)
python __sonda_run.py __tia_prueba17.php tia-2026-09-16-agregarmitienda # 🆕 «AGREGAR MI TIENDA»: el botón negro/rosado del buscador (sin sesión manda al login, con sesión al maestro), el atajo del `prod` y la foto del producto por el camino del celular (1 llamada de visión; limpia todo al final)
python __sonda_run.py __tia_cols.php tia-2026-09-16-cols            # 🔎 solo mira: las columnas de `directorio_servicios`, `directorio_opiniones` y `directorio_negocios` (así se cazó que NO existe `orden`)
python __sonda_run.py __tia_mejorar_tienda.php tia-2026-09-16-mejorar x "&id=1786"        # 📝 SIMULACRO: rehace el copy de una tienda ya publicada y enseña el antes y el después (no escribe)
python __sonda_run.py __tia_mejorar_tienda.php tia-2026-09-16-mejorar go "&id=1786&todas=1" # 📝 y EN SERIO: guarda el copy nuevo y siembra las opiniones (así se arregló «Novedades Gaela»)
python __sonda_run.py __tia_sobras.php tia-2026-09-15-sobras        # 🔎 ¿quedó algo de las pruebas? (conversaciones, tiendas sin dueño, cuentas, fotos) — solo mira
python __sonda_run.py __tia_columnas.php tia-2026-09-15-columnas    # 🔎 las columnas de `directorio_negocios` y las últimas líneas del registro de errores de PHP
python __tia_ver.py movil        # 🆕 MIRA la página como un celular (390 px) y saca FOTOS: __tia_vista_movil_*.png (Chrome aparte, no toca el navegador del jefe)
python __tia_ver.py todo         # y además el paso de las fotos, el cajón de emojis cerrado, los distritos y la vista de PC
python __tia_ver.py modales      # 🆕 LAS VENTANAS EMERGENTES con el JS de verdad: abre la cámara, «Mis tiendas» y «Otra tienda» con un toque REAL y mide que abran y que Escape las cierre
python __sonda_run.py __tia_limpiar_restos.php tia-2026-09-15-limpiar      # 🧹 restos de prueba (simulacro)
python __sonda_run.py __tia_limpiar_restos.php tia-2026-09-15-limpiar go   # 🧹 y en serio
python __sonda_run.py __tia_limpiar_conv_huerfanas.php tia-2026-09-15-huerfanas     # 🧹 conversaciones cuyo dueño ya no existe (simulacro)
python __sonda_run.py __tia_limpiar_conv_huerfanas.php tia-2026-09-15-huerfanas go  # 🧹 y en serio
C:\xampp\php\php.exe __tia_parser_test.php         # 🧪 LOCAL y gratis: el lector de la ficha contra respuestas REALES de la IA
```

**Las que mandan hoy son la 17 (el botón «Agregar mi tienda» y el producto ya dicho), la 16 (el copy con
botones, las opiniones automáticas y la tanda de fotos que clasifica la IA), la 15 (un dueño, varias
tiendas y las ventanas emergentes), la 13 (el flujo de la
versión 7 por el camino del celular), la 14 (las tres ramas del tipo de negocio), la 12 (la seguridad del
número ocupado), las de «🔎 mirar» (`__tia_sobras.php`, `__tia_columnas.php`, `__tia_cols.php`), el
rehacedor de copy de una tienda ya publicada (`__tia_mejorar_tienda.php`) y el test del parser**; las
anteriores quedan como historial (la 10 y la 11 prueban el flujo de la versión 5/6, que sigue vivo en sus
pasos de lectura y en el modo `?modo=producto`).

**🆕 La prueba 17 (`__tia_prueba17.php`) — 🛒 «AGREGAR MI TIENDA» (el botón del buscador y el `prod`).**
Es la que cubre el pedido del 2026-09-16 (**§2septies**) y va **por el camino real**: página por HTTP →
`login.php` con cookie y CSRF → `crear-tienda?modo=producto&prod=cumpleaños` → foto de verdad por `foto[]`.
Comprueba: **(1)** el botón en `buscar.php` — negro/rosado, **debajo** del botón «Ver … cerca de mí», su
enlace **sin sesión** (pasa por el login y vuelve con el producto) y **con sesión** (derecho al maestro) —;
**(2)** que `prod` llegue al navegador (`TIA_CFG.producto`) y que el JS (**`v=12`**) lo mande en el arranque,
en el JSON y en las fotos; **(3)** los **3 casos del atajo** en unidad (una tienda → solas las fotos · ya hay
producto → no toca nada · sin `prod` → flujo de siempre); **(4)** que la conversación arranque en
**«📷 Mándame la foto de cumpleaños»** y **NO** pregunte el nombre; **(5)** que la **foto entre en SU
producto** (1 llamada de visión) y que el precio que pida sea el de **ese** producto; **(6)** que la segunda
visita **NO duplique** el producto; **(7)** que **con dos negocios** pregunte cuál y el producto nazca **en
la que tocó**; y **(8)** que las tiendas queden **intactas y sin productos inventados** (`0 productos`:
la prueba **se para a propósito en la pregunta del precio**, porque el precio ya crea el producto y eso
**le manda un aviso de Telegram al jefe** —una prueba no debe hacer eso—, y ese tramo ya está probado por
la 16). Además camina el **camino completo del que NO tiene sesión** (`login.php?redirect=…` → el
formulario con su CSRF → **vuelve al maestro con el producto**, no al panel). Termina **limpiando todo**
(2 tiendas, las conversaciones, la cuenta, las fotos del hosting y las filas de registro). Resultado del
estreno: **57 ✅ · 0 ❌** y **3 fallos de verdad cazados**: la **doble codificación** del `redirect`, **la
trampa 49** (la rama multipart tiraba el `modo`) y que **el `redirect` del login se perdía en el POST**
(arreglado en `login.php`).

**🆕 La prueba 16 (`__tia_prueba16.php`) — EL COPY, LAS OPINIONES Y LAS FOTOS DE PRODUCTOS.** Levanta una
tienda de prueba con 3 productos y comprueba las tres cosas que pidió el jefe: **(A)** que la IA escriba
un **copy de verdad** —con `cz-tit`/`cz-lista`/`cz-caja`/`cz-cta`/`cz-cta-final`, **2 botones de WhatsApp
y 1 de llamada**— que pase el listón, que traiga solo las etiquetas permitidas, que no escriba teléfonos,
y que el motor de la ficha lo convierta en **botones reales** (`cz-btn--wa` con su `wa.me/51…` y
`cz-btn--tel` con su `tel:+51…`); **(B)** que se siembren **3 opiniones** al crear la tienda (4⭐ o 5⭐,
fechas repartidas, `fuente = maestro`, nota recalculada) y que **la segunda vez no duplique**; y **(C)** que
el dueño mande **3 fotos de productos** (fabricadas con GD, con el precio escrito) y que la IA **los
clasifique** —nombre, descripción y **el precio leído de la foto**—, los **publique con su foto** y
**reescriba el copy** con lo nuevo. Al final limpia todo (productos, opiniones, tienda, cuenta,
conversación y las fotos subidas).

*Última corrida (2026-09-16):* **36 ✅ · 0 ❌ · 0 restos** — el copy salió con **1743 caracteres, 3 `<h3>`,
2 botones de WhatsApp y 1 de llamada**, más el bloque `💵 Desde S/ 10.00`; las opiniones quedaron en
*«Vecina de Chimbote · 5⭐ · Fui por unas zapatillas deportivas para mi hijo y me atendieron al toque…»*
(nota **4.7**); y la tanda clasificó *«Zapatillas Nike Air Force 1 (S/ 120.00) · Mochila escolar · Polo
deportivo (S/ 35.00)»* — **leyó los precios escritos en las fotos** — y el copy nuevo pasó a
*«…zapatillas, mochilas y ropa en Chimbote»*.

**🆕 La prueba 15 (`__tia_prueba15.php`) — UN DUEÑO, VARIAS TIENDAS (0 tokens de IA).** Levanta el
estado a mano en la base (un dueño de prueba con **dos negocios publicados** y su conversación
**terminada** en `fin`, que es el estado exacto que reportó el jefe), **entra de verdad por
`login.php`** (cookie + CSRF, el camino de la web) y comprueba: la página trae **las tres ventanas
escondidas** y el menú con sus dos submenús · el **JS/CSS nuevos** responden 200 con sus piezas ·
`mis_tiendas` devuelve sus **2 negocios con su enlace** · el botón **`🆕 Crear otra tienda` está en los
tres pasos finales** (`publicado`, `fin` y el `mas_productos` viejo) · 🛍️ con **2 productos ya hechos**
(`productos_ia = 2`), el botón **`🛍️ Agregar otro producto`** **no** cae en el callejón sin salida:
**abre otra ronda** (paso `producto_nombre`, ronda a 0, total conservado y *«Producto 3:»* bien numerado)
y el mensaje final trae el enlace del **inventario** (`productos.php`) · al tocar «otra tienda» la
conversación vieja queda **`abandonada`**, nace **una nueva en el paso `fotos`** (`nueva_tienda: true`,
sin tienda encima) y **sus dos tiendas siguen intactas y activas** · y 🛡️ un visitante con la tienda
**publicada y sin dueño** **NO** puede empezar otra (se le pide el WhatsApp y su conversación no se
cierra). Todo se limpia al final.

*Última corrida (2026-09-16):* **56 ✅ · 0 ❌ · 0 restos** — con el texto que devolvió el asistente:
*«🆕 ¡Vamos con tu nueva tienda! Lo que ya publicaste (tus 2 tiendas) queda como está: esta es otra
tienda, de otro negocio.»*, el de la ronda de productos *«¡Vamos con otro! 🛍️ Ya llevas **2 productos en
«Prueba Bodega Doña Rosa»**.»* + *«**Producto 3:** ¿cuál es?»* y el freno: *«Antes de armar otra tienda
terminemos esta 📱 Escríbeme tu número de WhatsApp…»*.

**🆕 La prueba 13 (`__tia_prueba13.php`) — EL FLUJO DE HOY, POR EL CAMINO DEL CELULAR.** Es la prueba
madre de la versión 7: **pide la página por HTTP**, saca su **CSRF**, manda las fotos **de verdad** por
`foto[]` (multipart con cookie y con **fotos fabricadas con GD**: un letrero con el nombre, la dirección y
el teléfono, el local por dentro, la cocina y 4 platos) y camina **todo** el flujo:

1. **🎨 La página y su compositor:** `.tia-wa`, el campo que dice **«Mensaje»**, el **micrófono** con su
   ícono de enviar oculto, los tres íconos (emojis, adjuntar, cámara), el cajoncito de emojis, la galería
   **múltiple**, y que **NO** traiga cabecera ni pie del sitio.
   > ⚠️ **Cambió el 2026-09-20:** lo que se comprueba hoy es el compositor de la **guía** (📷 con su menú
   > «Abrir cámara» / «Abrir galería» · 🎤 del buscador · cuadro · ➤) y **la ausencia** del de WhatsApp.
   > Lo hace la **prueba 18** (§2octies c).
2. **🔴 El candado de las 8 fotos:** con **4** el asistente **no avanza** (*«Trabajamos a partir de 8
   fotos»*) y **no se pierde ninguna** (quedan guardadas); con las **8** salta al **tipo de negocio**.
3. **👁️ La lectura:** `letrero`, `rubro`, `telefono` leídos de las fotos, y los **3 tipos de negocio**.
4. **🌐 La rama de internet:** los **4 distritos como tarjetas** (con su `color`), el **botón azul** y el
   marcado múltiple → *«🗺️ ¡Perfecto! Entregas en toda la zona»*.
5. **🚀 La publicación instantánea:** al confirmar nombre y rubro **la tienda ya está en internet**
   (se comprueba por **HTTP 200** en su enlace y en la base: **activa**, **sin dueño todavía**, con la
   **descripción escrita por la IA**, con **2 productos** y **8 fotos** en la galería), y el menú de
   **edición en grilla de 2 columnas** (con **ningún** botón de eliminar) + la tarjeta de resumen.
6. **✏️ Las ediciones:** elegir un producto → **cambiar la descripción** (se le escribe el texto y la IA
   lo pule: *«Así quedó la descripción…»*) → **cambiar su foto** (subida real) → y **cambiar la portada**
   con una foto nueva.
7. **📱 El WhatsApp al final:** la pregunta textual que pidió el jefe, y con el número **la tienda queda
   CON dueño**, se le dan **usuario y clave** y **el enlace**, y termina en el paso `fin`.
8. **🧹 La limpieza total:** tienda, productos, fotos (con `tienda_ia_borrar_foto`), rubros extra, cuenta,
   conversación, su log y las `ia_*` que quedaron sueltas.

*Última corrida (2026-09-15, noche):* **72 ✅ · 1 ❌** — y la única ❌ fue **de la propia prueba** (buscaba
el enlace dentro de un `json_encode`, que escapa las barras `\/`): todo lo demás en verde. Lo que devolvió,
palabra por palabra: con 4 fotos *«Todavía no podemos empezar 📸 Trabajamos a partir de 8 fotos. Llevas 4 y
te faltan 4»* ✅ · con 8: *«¡Ya tengo tus 8 fotos!»* → **tipo de negocio** ✅ · `letrero: Cevichería Puerto
Nuevo` · `rubro: cevichería` · `telefono: 999…` ✅ · distritos **en tarjetas con color + el azul** ✅ ·
**tienda #1776 publicada y activa, sin dueño, con la descripción de la IA, 2 productos y 8 fotos**, y su
enlace **HTTP 200** ✅ · `editar` con los **4 botones en grilla** y **sin «eliminar»** ✅ · la descripción
del producto reescrita por la IA ✅ · la foto del producto y **la portada** cambiadas ✅ · la pregunta del
WhatsApp ✅ · **dueño #45 y `whatsapp` puesto** ✅ · **0 restos**.

**🆕 La prueba 14 (`__tia_prueba14.php`) — LAS TRES RAMAS DEL TIPO DE NEGOCIO.** Camina las ramas que la
13 no toca, **sin gastar casi nada** (esos pasos no llaman a la IA: abre una conversación ya parada en el
paso `tipo` y la mueve): **🏪 tienda o local** → `direccion` → `distrito` → `nombre_ok` · **🛵 ambulante**
→ `horario` (4 opciones + «después») → `distrito` → `nombre_ok` · **🌐 internet** → `entregas` (4 tarjetas
+ **un solo** botón azul, marcar y desmarcar con `✅` y la opción de seguir). Comprueba además que en el
flujo nuevo **nada se publique ni se actualice antes de tiempo** (la tienda todavía no existe).

*Última corrida (2026-09-15, noche):* **18 ✅ · 0 ❌** · las 3 conversaciones borradas al final.

**La prueba 10 (`__tia_prueba10.php`) — el motor del flujo de la versión 5/6.**
1. **👁️ La lectura con fotos REALES del sitio** (fotos de una tienda de verdad, sin escribir nada):
   enseña la ficha que devuelve la IA y los rubros reales que salen de ella.
2. **📷 El flujo completo con fotos fabricadas con GD** (un letrero legible con nombre, dirección y
   teléfono, y una **tarjeta**): lo primero que se manda es **el lote de fotos** (como el navegador, que
   abre el carrete desde el saludo) y comprueba que **no se pierdan** y que con **4 de 8 no avance**.
3. **✅ Las confirmaciones**: el nombre del letrero, el rubro deducido y el teléfono leído.
4. **🚀🤫 La publicación silenciosa**: al dar el WhatsApp, la tienda **ya está en la base** (con sus 8
   fotos) y la conversación **sigue `en_curso`** en el paso `vendedor`, **sin que se le haya dicho**.
5. **🏪📍📘 Los pasos que van actualizando en vivo**: cómo vende (los 5 tipos), **la dirección** (se le
   propone la que se leyó en las fotos) y **las redes**.
6. **🎉 El estreno**: al terminar las redes, el enlace, su usuario y su clave, y **`login()` entra**.
7. **🧹 Limpieza**: borra tienda, conversación, cuenta y fotos (0 restos).

**Última corrida (2026-09-15, noche) — todo en verde:** `LETRERO: FERRETERIA EL MARTILLO DE ORO` ·
`RUBRO: ferretería` · `TELEFONO: 943112233` · `DIRECCION: Av. Jose Pardo 123` ·
`PRODUCTOS: clavos, tornillos, pernos, pintura, brochas` ✅ · **con 4 fotos no avanzó y no se perdió
ninguna; con 8 sí** ✅ · el chip del rubro salió **solo** con `[🔧 Ferreterías]` ✅ · el paso `tel_leido`
preguntó por el número del aviso ✅ · **tienda publicada mientras la conversación seguía en `vendedor`** ✅ ·
la dirección y la zona se propusieron **solas** desde el letrero ✅ · las redes repartidas bien
(`facebook=fb.com/martillodeoro`, `instagram=@martillo.oro`) ✅ · el estreno con enlace, usuario y clave y
**`login()` ENTRA** ✅ · el copy escrito con lo que se vio en las fotos y **sin ningún teléfono** ✅ ·
**8 fotos en la galería de la tienda** ✅ · **0 restos**.

**La prueba 11 (`__tia_prueba11.php`) — el camino del navegador.** Pide la **página por HTTP**, saca su
**CSRF**, abre la conversación por la puerta JSON, **sube 8 fotos de verdad con `foto[]`** en un solo
POST (multipart, con cookie: lo único que una sonda interna no puede probar) y sigue todo el flujo hasta
el estreno, comprobando en la base que **cada respuesta se va guardando en vivo**. Comprueba además que
la página traiga el **`?v=6`** del JS, que informe el **mínimo de 8** y que **el primer botón del saludo
mande `galeria`** (si eso se rompe, el carrete deja de abrirse).
*Última corrida:* página 200 con las 6 comprobaciones ✅ · saludo con «Crea tu tienda con nosotros» y
`min_fotos_arranque: 8` ✅ · **8 fotos en un POST → la IA leyó el letrero real (`JENNY'S`) y el teléfono
del aviso (`914211676`)** ✅ · **nunca se le mostró el protocolo crudo** ✅ · al WhatsApp: **tienda
publicada y la conversación `en_curso`, sin decirle nada** ✅ · el orden `vendedor → direccion → distrito
→ horario → redes → publicado` con **cada dato guardado en la base** ✅ · estreno con enlace y `login()` ✅ ·
limpieza ✅.

**🔴 La prueba 12 (`__tia_prueba12.php`) — LA PRUEBA DE LA SEGURIDAD** (la que pidió el jefe: *«se puede
dañar una tienda que ya exista»*).
1. Crea un **cebo**: una cuenta con su tienda y datos conocidos (dirección, horario, descripción
   «CEBO: esta descripción NO se debe tocar»).
2. Otro dueño hace **todo el flujo** y da **el número del cebo**.
3. Comprueba que el asistente **se detiene y pregunta** (*«Ese número ya tiene la tienda
   «Tienda Cebo Prueba». ¿Es tuya?»*), que **no publicó nada**, que la conversación **sigue sin cerrar**
   y que **el cebo quedó byte a byte idéntico**.
4. Contesta que **no** es suyo → vuelve a pedir el número y **sigue sin publicar nada**.
5. Da su **número de verdad** → recién ahí se publica, y el cebo **sigue intacto** hasta el final.
6. 🧹 Borra todo (cebo incluido) y comprueba que no queda nada.

*Última corrida:* `¿el asistente preguntó? SÍ ✅` · `¿le dijo de quién es ese número? SÍ ✅` ·
`¿se publicó algo? NO ✅` · `¿el cebo quedó INTACTO? SÍ ✅` (las 3 veces que se le preguntó) ·
su tienda publicada con su número y sus 8 fotos ✅ · limpieza ✅.

**Una trampa de la propia prueba (por si vuelve a pasar):** la 1.ª corrida **no llegó** al momento
peligroso porque el número del cebo se lo comió el paso `tel_leido` (que estaba esperando un «sí» o un
«no»). La prueba ahora **pasa primero por ese paso diciendo que no** y recién entonces da el número
peligroso. Moraleja para cualquier prueba de este módulo: **seguir el paso en el que está la
conversación**, no el que uno cree.

**⚙️ Si una prueba deja restos:** el botón 🧹 del Súper Admin solo limpia fotos de más de **48 h**, así
que para los restos del mismo día está **`__tia_limpiar_restos.php`** (simulacro primero, y con
`go` lo hace) y —para las **conversaciones cuyo dueño ya no existe** (al publicar la fila se queda con
el `usuario_id` de la cuenta de prueba, trampa 30)— **`__tia_limpiar_conv_huerfanas.php`** (también
simulacro y `go`).

**Las dos pruebas que mandan son la 8 (el motor) y la 9 (el navegador)**; la 7 y las anteriores quedan
como historial. **La clave de la prueba 8 cambió**: ahora es `tia-2026-09-15-flujo` (si se corre con la
vieja, contesta «clave incorrecta»).

**La prueba 8 (`__tia_prueba8.php`) — el motor.** Recorre, con la clave REAL de la IA y sin cuenta:
1. **El arranque en sus tres caras** (sin cuenta · con cuenta y sin tiendas · con cuenta y con
   tiendas) llamando a `tienda_ia_guion('arranque', …, $extra)`.
2. El nombre, de qué trata y el **rubro principal** + **un rubro extra** (y comprueba que el extra queda
   **guardado en `directorio_negocio_rubros`**).
3. **Un lote de 3 fotos en un solo envío** y comprueba si la IA pensó `DOMICILIO` y si salió el chip
   **`🚚 No tengo local`** (y lo toca si salió).
4. **`🗺️ Todas las anteriores`**: verifica que la zona quede con Chimbote de base + `distrito_todas`.
5. **La publicación AUTOMÁTICA**: comprueba que al terminar el horario la tienda ya está en la base, que
   la **descripción la escribió la IA** (y que lleva la nota de toda la zona) y que **`login()` entra**
   con el usuario y la clave que se le dieron.
6. Los **2 productos**: el primero **aprovechando una foto ya subida** (`usar_foto:0`, y comprueba que
   sea una **copia** y no el mismo archivo), y el segundo hasta el **cierre con la felicitación y el
   enlace** (`fin`).
7. **`✏️ Editar algo` DESPUÉS de publicar**: cambia el horario y verifica que **cambió en la base**.
8. **Borra todo** (tienda, productos, rubros extra, cuenta, fotos y conversación) y comprueba que no
   queda nada. ⚠️ Las fotos se recogen **antes** de borrar la conversación y se borran con
   `tienda_ia_borrar_foto()`; nunca con un `glob` por fecha (eso borraría fotos de gente real).

**Última corrida (2026-09-15, tarde) — todo en verde:** arranque ✅ en las tres caras · **rubro principal
+ Ferreterías guardados en la base** ✅ · lote de 3 fotos ✅ · **la IA pensó DOMICILIO y el chip
`🚚 No tengo local` salió y funcionó** ✅ · `Todas las anteriores` → zona «Chimbote» + nota en la
descripción ✅ · **publicación automática** con el enlace ✅ · **el copy de la descripción lo escribió la
IA** (arena, ripio, piedra chancada, volquetes, toda la provincia) ✅ · cuenta `999…` + clave `XXE1` y
**entra con ella** ✅ · 1.er producto con **una foto ya subida** (copia aparte ✅) · 2.º producto →
**cierre con «¡Felicidades!… 2 productos» y el enlace** ✅ · **editar el horario después de publicar
cambia la base** ✅ · **0 restos** al final ✅.

**La prueba 9 (`__tia_prueba9.php`) — el camino del navegador.** Es la que cierra el asunto, porque
una sonda interna no puede probar ni el `multipart` ni el CSRF: pide la **página por HTTP**, saca su
CSRF, abre la conversación por la **puerta JSON**, responde por JSON y **sube 3 fotos de verdad con
`foto[]`** en un solo POST. Comprueba además que la página **no traiga cabecera ni pie del sitio**, que
la galería sea `multiple` y que la puerta informe **8 fotos de tope**. Al terminar **borra la
conversación y sus fotos** usando el hash de su propia sesión (⚠️ la cookie se llama
**`CHIMBOTE_SID`**, no `PHPSESSID`: `SESSION_NAME` en `config.php`).

**Última corrida:** HTTP 200 en la página y en la API · 7/7 comprobaciones de la página ✅ · paso
`arranque` con el mensaje de «todavía no tienes tienda» ✅ · `nueva` → `nombre` → `trato` → `rubro` →
`fotos_tienda` ✅ · **3 fotos subidas en un POST: 3 guardadas y UNA sola llamada a la IA (2,6 s)** ✅ ·
limpieza: 1 conversación y 3 fotos borradas ✅.

**📐 La medición del diseño (sin abrir el navegador del jefe):** `python __tia_medir.py todo` baja la
página real, le quita los `<script>` (para que no llame a la API), le inyecta una sonda que llena el
chat de ejemplo y **mide la pantalla con Chrome sin cabeza** en tamaño de celular y de PC. Deja el
archivo temporal en su carpeta temporal y lo borra al terminar; el navegador del jefe no se toca.

Lo que **tiene** que salir (así salió el 2026-09-15): `pagina_scrollea: false` ·
`composer_position: static` · `composer_abajo` = el borde inferior de la pantalla ·
**`elementos_DEBAJO_del_composer: []`** · `pies_del_sitio_en_la_pagina: 0` · `fuente_textarea: 16px` ·
`galeria_multiple: true` · `fotos_visibles: 5/5` · la caja de la barra de escribir ≈ **120 px** y el
composer entero ≈ **159 px** (con los dos botones de foto a la vista).

**🧹 Y si una prueba deja restos:** el botón 🧹 del Súper Admin solo limpia fotos de más de **48 h**,
así que para los restos del mismo día está **`__tia_limpiar_restos.php`** (simulacro primero, y con
`go` lo hace): borra las conversaciones de prueba y **las fotos `ia_*` que no estén en ninguna
conversación NI en ninguna tienda** (tiendas, productos y `imagen` de `directorio_servicios` cuentan como
«en uso»: es la trampa 23), con el mismo patrón del botón y con `img_borrar()` (que se lleva también las
versiones de 300 y 800 px).
**🔍 Y para mirar una tienda sin tocarla** (¿tiene todas sus fotos?, ¿le faltan archivos?, ¿quién es el
dueño?): **`__tia_tiendas_prueba.php`** (solo lee; se le pasa un nombre y lista la tienda, sus fotos con
«archivo presente / FALTA», sus productos y su dueño).

**📝 Y para MEJORAR EL COPY de una tienda que ya está publicada** (las que nacieron antes del
2026-09-15, cuando la descripción era la del dueño tal cual):

```powershell
cd D:\RELAX
$env:PYTHONIOENCODING='utf-8'
python __tia_run_copy.py 1741            # SIMULACRO: muestra el copy nuevo (mirando sus fotos) y no escribe
python __tia_run_copy.py 1741 go         # lo guarda: la tienda + la descripción de sus productos
python __tia_run_copy.py 1741 go tienda  # lo guarda, pero solo la tienda (sin tocar los productos)
```

El corredor sube `__tia_copy_tienda.php`, lo llama con `negocio_id` y lo **borra del hosting** al
terminar. Solo toca **descripciones** de esa tienda y de sus productos: **no cambia fotos, precios ni
rubros**. La clave es `tia-2026-09-15-copy`.

**Verificación por HTTP:**

| URL | Esperado | Dio |
|---|---|---|
| `/crear-tienda` | 200 + `class="tia-app"` + `multiple` + los dos botones de foto, **sin** cabecera ni pie del sitio | ✅ 200 |
| `/crear-tienda?modo=producto` | 200 | ✅ 200 |
| `/api/tienda_ia.php` (GET, sin sesión) | 200 con el **arranque** («todavía no tienes tienda») y sin escribir en la base | ✅ 200 |
| `/assets/css/tienda_ia.css?v=4` · `/assets/js/tienda_ia.js?v=4` | 200 (y **el `?v=` que pide la página**) | ✅ 200 |
| `/includes/config_tienda_ia.php` | 403 (la clave no se lee) | ✅ 403 |
| `/login.php` | 200 + «Correo o número de teléfono» | ✅ 200 |

## §12. TRAMPAS QUE YA NOS MORDIERON (no repetirlas)

1. **`*/` dentro de un comentario PHP.** Escribir la ruta en markdown (`**` + `/crear-tienda**`)
   dentro de un bloque `/* … */` **cierra el comentario** y rompe el archivo con un error de sintaxis
   en una línea que no tiene nada que ver. Pasó al escribir `crear_tienda_ia.php`.
2. **Las opciones de la IA no son texto suelto.** `tienda_ia_ia_producto()` devolvía una lista de
   cadenas mientras el guion esperaba `['texto'=>…, 'valor'=>…]`: en PHP 8 un `$o['texto']` sobre una
   cadena es **error fatal**. Lo cazó la prueba 2. Ahora se devuelven ya en formato de botón **y** la
   puerta las normaliza (`tia_opciones()`), y el JS acepta las dos formas.
3. **El gasto no puede salir de las conversaciones.** Si se borra una conversación, el consumo debe
   seguir ahí: **se mide desde el log de llamadas**.
4. **`reasoning_effort` no es un adorno.** Sin apagarlo, este modelo piensa 200-800 tokens por
   respuesta, tarda el doble y **a veces contesta vacío**. Ver §5.
5. **No se le pide JSON al modelo.** Ver §5.
6. **Un `LIKE '%ia_%'` miente** («maria_01.webp»): para la limpieza se usa el nombre exacto con regex.
7. **`registro.php` no miraba el `redirect`** y mandaba al panel: quien venía del constructor perdía
   su tienda a medias. Arreglado (y solo con rutas de casa).
8. **PIÉRDESE EL ESTADO GUARDADO SI SE PISA LA COPIA VIEJA.** `tienda_ia_publicar()` guarda en la
   base los datos buenos (con `publicado` y `cuenta`), pero quien lo llamó seguía con su copia en
   memoria y la volvía a guardar: **se perdía el enlace y la cuenta**, y todo lo de después (crear
   los productos) fallaba con «No encontré tu tienda» (lo cazó la prueba 5). Ahora, después de
   publicar, **se recargan los datos desde la base ANTES de escribir nada**.
9. **`switch` con casos duplicados = gana el VIEJO.** Al reescribir el guion quedaron los casos
   antiguos (`producto_nombre`, `producto_fotos`…) *antes* de los nuevos: PHP entra por el primero y
   los nuevos nunca se ejecutan (el asistente seguía pidiendo «cuántos productos»). Antes de dar por
   bueno un guion nuevo: **comprobar que no hay `case` repetidos**
   (`Select-String -Path includes\tienda_ia.php -Pattern "case '"`).
10. **`productos_ia` hay que contarlo.** Si no se suma al crear cada producto, el asistente cree que
   van 0: felicita con «0 productos» y nunca ofrece el WhatsApp del jefe.
11. **Abrir la página no debe escribir nada.** El `GET` de la puerta trae el saludo con
   `tienda_ia_actual(..., false)`: si creara la fila, cada visita (o cada robot) dejaría una
   conversación vacía en la base.
12. **No midas la compresión con una imagen de ruido.** La primera medición del WebP se hizo con una
   imagen de **píxeles aleatorios**: salió **más grande** que el JPEG (450 KB vs 353 KB) y a punto estuvo
   de quedar escrito que «comprimir no sirve». Con una foto **realista** (zonas suaves, como cualquier
   foto de celular) el resultado es el de verdad: **583 KB → 66 KB (89 % menos)**. Si una prueba te da un
   número raro, **mira con qué dato se midió** antes de creerlo o de escribirlo en una guía.
13. **La PREGUNTA del paso tiene que verse al abrir.** En la primera pantalla solo salía el saludo
   (el texto del paso lo pintaba el navegador solo al pasar de paso), así que el dueño **leía el hola y
   no sabía qué contestar** — y eso, con razón, se siente como un manual que no arranca. Lo cazó el jefe
   al leer la entrada. Ahora el `GET` de la puerta **añade la pregunta del paso** si el último mensaje
   no es ya esa pregunta (y la guarda para no repetirla al recargar).
14. **Las fotos de una conversación abandonada quedan en el disco** (no hay nada que las borre solo):
   para eso está el botón 🧹 de la pestaña del Súper Admin.
15. **`NINGUNA` NO es un comentario (2026-09-15).** El candado de capturas del lote contesta en la
   **LÍNEA 1** («2, 4» o «NINGUNA») y el comentario va en la **LÍNEA 2**. La primera versión solo
   reconocía líneas de números: cuando el modelo escribía `NINGUNA`, **esa palabra se le mostraba al
   dueño dentro del comentario** (*«NINGUNA — Las tres son fotos de letreros…»*, lo cazó la prueba 8).
   Ahora se reconocen las dos formas y, si el comentario viniera en la misma línea, se recorta.
16. **La IA explicando NO es una lista de productos (2026-09-15).** Cuando el dueño vuelve a agregar
   un producto y su conversación no trae relato, el pedido quedaba vacío y el modelo contestaba
   *«No puedo proponer productos porque el dueño no contó nada todavía»* → **esa frase salía como
   botón para tocar** (lo cazó la prueba 8). Se arregló por los dos lados: se le pasa la
   **descripción que la tienda ya tiene** en la base (y no se le pregunta si no hay nada que mirar),
   y **solo se aceptan títulos de 1 a 4 palabras** (más de 40 caracteres = no es un producto).
17. **El nombre del usuario puede ser el de su tienda (2026-09-15).** Las cuentas que crea El maestro
   guardan como `nombre` **el de la tienda**, así que saludar con la primera palabra daba
   *«¡Hola, Combinado!»* (tienda «Combinado Doña Lucha»). Lo resuelve `tienda_ia_como_llamarlo()`: si
   el nombre es el de una de sus tiendas, **se saluda sin nombre**.
18. **La barra de escribir NO se pone `position: fixed` (2026-09-15).** Es lo que hizo que el jefe
   viera **el pie del sitio dentro del área de chat**: al ser fija tapaba el pie y este aparecía en la
   misma zona del campo. En una columna `100dvh` la barra va al final del flujo y **nada queda debajo**;
   además así **no hay que adivinar el hueco** que la barra tapa (el `padding-bottom: 150px` de la
   versión anterior era justo eso: una apuesta).
19. **Sin cabecera del sitio, el CSS del sitio no existe (2026-09-15).** Al quitar `includes/header.php`
   se fueron `base.css`, `components.css` y compañía: **todo** lo que esta página usa tiene que estar
   en `assets/css/tienda_ia.css` (incluido el `.cz-dict*` del dictado). Si se trae un componente del
   sitio, hay que traer **sus estilos**.
20. **El candado de las capturas también hay que probarlo con el lote.** Se probó con una captura
   **fabricada con GD** (barras de hora/batería, barra de direcciones y pestañas): la prueba 8 manda
   ese lote, la IA lo marca, el motor **borra el archivo** y la foto **no queda** en la conversación.
   Es la única forma de verificar la regla inviolable sin subir una captura de verdad.
21. 🔴 **EL HOSTING CACHÉA LOS ARCHIVOS ESTÁTICOS POR URL (2026-09-15, y esto muerde a CUALQUIER
   módulo).** Se subió el `assets/css/tienda_ia.css` nuevo (16 733 bytes) **y el servidor siguió
   sirviendo el viejo** (16 266 bytes) porque la página pedía `?v=3` y **esa URL ya estaba en la
   caché**: cualquier `?v=otra_cosa` devolvía el archivo nuevo. Se descubrió midiendo el diseño: el
   CSS que llegaba al navegador **no tenía las reglas recién subidas** (la barra de escribir seguía
   con el maquetado anterior). → **Al cambiar un `.css` o un `.js`, hay que SUBIR SU VERSIÓN en el
   `?v=` de la página** (`?v=3` → `?v=4`) y volver a subir ese PHP. Sirve como diagnóstico rápido:

   ```powershell
   # ¿el servidor me está dando el archivo nuevo?
   (Invoke-WebRequest 'https://dechimbote.com/assets/css/tienda_ia.css?v=4' -UseBasicParsing).RawContentLength
   (Get-Item D:\RELAX\deploy\assets\css\tienda_ia.css).Length      # tienen que coincidir
   ```
22. **Borrar una foto con `unlink` deja las versiones de 300 y 800 px (2026-09-15).** Cada foto que
   guarda el módulo son **3 archivos** (`…webp`, `…-800.webp`, `…-300.webp`). Al limpiar los restos de
   la prueba 9 se borró solo el principal con `@unlink`: quedaron **fotos fantasma** que nadie ve pero
   ocupan sitio (se cazaron contando archivos, no conversaciones). Para borrar una foto **siempre**
   `tienda_ia_borrar_foto()` (que llama a `img_borrar()` del sitio y se lleva las tres). Y para dejar
   el módulo impecable después de una prueba: `__tia_limpiar_restos.php` (§11).
23. 🔴 **LA LIMPIEZA NUNCA PUEDE MIRAR SOLO LAS CONVERSACIONES (2026-09-15).** `__tia_limpiar_restos.php`
   daba por huérfana toda foto `ia_*` que no estuviera en una **conversación**. Una tienda publicada
   puede tener sus fotos **sin conversación viva** (el jefe siguió con su celular, la conversación se
   cerró…): limpiar así **le habría borrado las fotos a una tienda de verdad**. Ahora protege también las
   fotos de **`directorio_fotos`**, **`directorio_producto_fotos`** y **`directorio_servicios.imagen`**
   (medido: 5 084 fotos de tiendas y 928 de productos quedaron protegidas y solo se borraron los 3 restos
   de las pruebas). **Antes de tocar cualquier archivo del sitio, preguntarse siempre quién más lo usa.**
24. **El `pdo->fetchColumn()` sobre `created_at` revienta: la columna se llama `actualizado_en` (2026-09-15).**
   `directorio_negocios` no tiene `created_at` ni `creado_en`: al escribir una sonda que la consultaba, la
   SQL murió con *«Unknown column»* y no se vio nada más de la lista. Mirar el esquema antes de escribir
   la consulta (o rodearla de `try/catch` y seguir).
25. **Un paso nuevo puede ROMPER un caso del `switch` de antes (2026-09-15).** Al meter el bloque de la
   zona «Todas las anteriores», el `else` que venía después **pisó** el id recién calculado
   (`(int)'todas' = 0`) y el asistente se quedaba repitiendo *«Toca tu distrito 👇»* para siempre. La
   lección: al añadir una rama a un paso, revisar **qué hace el `else` de abajo**.
26. 🔴 **EL MODELO CONTESTA LAS 8 ETIQUETAS EN UN SOLO RENGLÓN (2026-09-15, primera corrida de la
    prueba 10).** A la IA se le piden **8 líneas** (`CAPTURAS: … LETRERO: … RUBRO: …`) y en las **dos
    primeras llamadas reales contestó todo seguido en un solo renglón**. El lector por líneas dejaba la
    ficha **vacía** (sin nombre, sin rubro, sin teléfono) y —peor— le pintaba al dueño **el protocolo
    crudo** (*«CAPTURAS: NINGUNA LETRERO: JENNY'S RUBRO: …»*), que es justo lo que prohíbe la trampa 15.
    Arreglado: `tienda_ia_leer_ficha()` busca cada etiqueta **en cualquier parte del texto** (el valor
    es lo que va hasta la etiqueta siguiente) y **al comentario se le quitan los restos de etiquetas**.
    Lección general: **si el formato importa, no se confía en el formato** — se parsea con tolerancia y
    se limpia antes de mostrar. Hay una prueba local que lo comprueba sin gastar un token:
    `__tia_parser_test.php` (§11).
27. 🔴 **`trim()` CON CARACTERES MULTIBYTE ROMPE EL UTF-8 Y PIERDE EL DATO (2026-09-15).**
    `trim($v, "«»·–—")` se lee como **bytes**: el `0xC2` que comparten «·», ««» y «»» se come la mitad
    de una comilla, el texto queda con bytes inválidos y entonces **`preg_replace` con `/u` devuelve
    `null`**, así que el dato se esfuma. Nos borró **el nombre del letrero** (««Pollería El Buen Sabor»»
    → vacío) y habría metido un nombre roto en la base. Ahora los bordes se quitan con **regex y `/u`**
    y hay un `tienda_ia_utf8_sano()` de red. Lección: **con texto de la IA, `trim()` solo con ASCII**.
28. 🔴 **LA PUBLICACIÓN SILENCIOSA NO PUEDE CERRAR LA CONVERSACIÓN (2026-09-15).** `tienda_ia_actual()`
    encuentra la conversación a medias **por `estado='en_curso'`**: si la publicación silenciosa dejaba
    la fila en `publicada` (como hace la publicación normal), el **siguiente POST abría OTRA
    conversación** en el `arranque` y el flujo se cortaba justo después del WhatsApp (se perdían la
    zona, el horario y el estreno). Por eso `tienda_ia_publicar(..., $silencioso=true)` deja
    `estado='en_curso'` y **no toca `paso`**; el `estado='publicada'` lo pone **el estreno**
    (`tienda_ia_revelar()`), que es cuando el flujo ya terminó de preguntar.
29. **En el flujo nuevo el dueño NO cuenta nada: hay que darle contexto a la IA (2026-09-15).**
    `tienda_ia_descripcion_final()` solo escribía el copy **si había relato** (`$d['trato']`), y con las
    fotos primero ese campo va **vacío**: la tienda se habría publicado **sin descripción**. Ahora el
    contexto también son `datos['visto']` y `datos['productos_vistos']` (lo que la IA **vio**), y el
    pedido del copy los lleva como *«Cosas que se venden (se vieron en sus fotos)»*. Medido: la
    descripción sale con lo que se ve en las fotos (*«En JENNYS encuentras calzado y productos de
    belleza como champú, cremas y tintes…»*). **Y se le prohíbe escribir teléfonos**: el número del
    aviso se colaba en el copy («llámanos al 943112233») y ese número puede ser el de la imprenta.
30. **La conversación queda HUÉRFANA cuando su dueño se borra (2026-09-15).** Al publicar, el motor le
    pone a la fila el `usuario_id` de la **cuenta nueva**: si después se borra la cuenta (las pruebas lo
    hacen), la conversación queda apuntando a un usuario que no existe y **ensucia el embudo del Súper
    Admin** (cuenta como conversación y como tienda publicada). Para eso está
    **`__tia_limpiar_conv_huerfanas.php`** (§11): simulacro primero, `&go=1` para borrar, y **nunca
    toca una foto que esté en uso** (trampa 23).
31. 🔴 **UN CHIP DE OTRO PASO NO ES UN DATO (2026-09-15, lo cazó la prueba 11).** En el paso de la
    **dirección** se aceptaba cualquier texto libre y, como los botones mandan **su propio texto**, al
    tocar un chip viejo («🗺️ Todas las anteriores») la dirección quedaba guardada como *«Todas las
    anteriores»*. Ahora los pasos nuevos **miran el VALOR** del chip y, si no es de ese paso, **no tocan
    nada** (y el flujo sigue preguntando). ⚠️ La regla vale para todo paso con chips: **el texto de un
    botón jamás se guarda como si fuera una respuesta escrita** (la misma lección del `nombre_ok`:
    «✏️ Es otro nombre» no puede pasar por el validador de nombres).
32. 🔴 **PARTIR LA FRASE POR PALABRAS NO ES ENTENDERLA (2026-09-15).** El paso de las redes repartía lo
    que escribía el dueño por palabras: *«mi facebook es fb.com/x y el insta @y»* dejó **`instagram =
    "insta"`** (el nombre de la red, no el enlace) y el **@handle cayó en TikTok**. Ahora el motor
    **recuerda de qué red se está hablando**: cuando el token es solo el nombre de la red, se guarda esa
    red como «la última» y **el enlace que viene después va ahí**; las palabras de relleno («mi», «es»,
    «el», «y») se ignoran y cada enlace se coloca en su columna. Medido: `facebook=fb.com/martillodeoro`
    · `instagram=@martillo.oro` · `tiktok` vacío ✅.
33. 🔴 **`dueno_id = 0` NO ES «SIN DUEÑO»: REVIENTA LA LLAVE FORÁNEA (2026-09-15, noche; lo cazó la
    prueba 13).** El flujo nuevo publica la tienda **antes** de preguntar el WhatsApp, o sea **sin
    dueño**. Al principio se insertaba `dueno_id = 0` y el INSERT moría con *«Cannot add or update a
    child row: a foreign key constraint fails (`fk_negocio_dueno`)»* — y el dueño solo veía **«No pude
    publicar tu tienda 😅 Prueba otra vez.»** (el mensaje amable esconde el motivo real). Se arregló
    insertando **`NULL`** (`$uid > 0 ? $uid : null`), que es lo que la columna admite (es `NULL` y la FK
    es `ON DELETE SET NULL`). **Dónde se ve el motivo de verdad:** el motor lo escribe con `error_log()`
    y el **registro de errores del hosting** está en **`/home/u196269909/.logs/error_log_dechimbote_com`**
    (se lee con la sonda `__tia_columnas.php`, §11) — sin eso, este fallo se busca a ciegas.
34. 🔴 **EL PASO SE GUARDA ANTES DE GUARDAR, NO DESPUÉS (2026-09-15, noche; también lo cazó la prueba
    13).** `tienda_ia_crear_tienda_final()` hacía `tienda_ia_guardar($s)` y **después**
    `$s['paso'] = 'editar'`: la fila quedaba guardada en **`rubro_mas`** y el dueño, al tocar cualquier
    cosa, volvía a ver *«¿Le sumas otros rubros o categorías?»* en vez del menú de edición (y cada toque
    **publicaba otra tienda**, porque el paso seguía siendo el de publicar). Ahora el paso se pone
    **antes de guardar**. Regla general: **`tienda_ia_guardar($s)` escribe `paso`, `estado` y `datos`:
    todo lo que deba quedar guardado tiene que estar puesto ANTES de llamarla.**
35. 🔴 **LAS BANDERAS DE DIBUJO TIENEN QUE PASAR POR LA PUERTA (2026-09-15, noche; lo cazó la prueba
    13).** El guion marca las opciones con `tarjeta`, `color`, `azul` y `rejilla`, pero quien arma la
    respuesta del navegador es **`tia_opciones()`** en `api/tienda_ia.php` y **solo copiaba
    `texto/valor/principal/nota/url/misma`**: las tarjetas de los distritos, el botón azul y la grilla de
    2 columnas **llegaban al navegador como chips sueltos** (se veían los 5 distritos uno tras otro). Al
    añadir una bandera nueva al guion hay que **añadirla también ahí** (y en el pintor de
    `tienda_ia.js`).
36. 🔴 **`tienda_ia.css` ESTABA ENVENENADO EN `?v=5` (2026-09-15, noche).** El archivo se subía bien
    (22 405 bytes) pero la página pedía `?v=5`, **una URL que el servidor ya tenía en caché con la
    versión vieja** (19 081 bytes, sin el bloque del compositor): el micrófono de WhatsApp salía **sin
    estilos**. Diagnóstico en 10 segundos:
    `(Invoke-WebRequest 'https://dechimbote.com/assets/css/tienda_ia.css?v=6' -UseBasicParsing).RawContentLength`
    → **coincidir con `(Get-Item deploy\assets\css\tienda_ia.css).Length`**. Se arregló subiendo el número
    a **`?v=6`** en `crear_tienda_ia.php` y volviendo a subir la página. Es la trampa 21 otra vez, ahora
    con el compositor: **cada vez que se toca un `.css` o un `.js`, subir su `?v=` y volver a subir el
    PHP que lo pide.** (Hoy: **CSS `?v=9`** y **JS `?v=10`**; ver también la **trampa 39**.)
37. 🔴 **`[hidden]` NO GANA CONTRA UN `display` NUESTRO — Y ESO TAPÓ LA PANTALLA DEL CELULAR
    (2026-09-16, lo cazó el jefe con una captura).** El navegador esconde lo que lleva el atributo
    `hidden`, pero con una regla **flojita** (`[hidden] { display: none }`): **cualquier `display` nuestro
    la pisa**. Como `.tia-emoji { display: grid }` y `.tia-foto { display: grid }`, en el celular se veían
    **siempre** —en todos los pasos— **el cajón de los 48 emojis** y **los dos botones de cámara/galería**,
    y la barra de escribir medía **199 px de alto** (más de un cuarto de la pantalla). El jefe: *«elimina
    inmediatamente ese bloque de íconos que tapan toda la pantalla en modo mobile… el bloque de
    cámara/galería debe ser visible solo cuando se necesite»*. Arreglado por dos lados:
    **(1)** en el CSS, una regla de una línea —**`[hidden] { display: none !important; }`**— que devuelve
    el mando al atributo (o sea que el `elX.hidden = true` de JS **ahora sí esconde**); y
    **(2)** el 📎 y el 📷 del compositor **solo se encienden en los pasos de foto** (`pideFoto`).
    **Lección general: si algo se muestra y se esconde con `.hidden`, revisar que su CSS no traiga un
    `display` que gane; y para comprobarlo, medir el alto real de la barra** (`__tia_ver.py`, §11).
    Medido después del arreglo: barra de **88 px** y **ni un ícono de foto** en los pasos que no piden foto.
    🧪 **Y se comprueba con la página de verdad, con su JavaScript puesto**: `python __tia_ver.py real`
    (§11) abre la página en un Chrome aparte a 390 px, caza los errores de JS y dice, uno por uno, si el
    cajón de emojis, los botones de foto, el clip, la cámara y el menú **están ocultos de verdad**
    (`offsetParent === null`), además del color del botón redondo. Última corrida (2026-09-16):
    `errores_de_js: []`, todos ocultos, `mic_color: rgb(0,168,132)`, `composer_alto: 95`, `chat_alto: 597` ✅.
38. **Chrome sin cabeza en Windows NO deja medir por debajo de ~484 px de ancho (2026-09-16).** Pidiendo
    `--window-size=390,844` la página se maqueta igual a **484 px** y la captura sale cortada. Para ver el
    diseño de un celular de verdad hay que **fijarle el ancho a la página a mano**
    (`html,body{width:390px!important;max-width:390px!important}`): así lo hace `__tia_ver.py` (§11).
39. 🔴 **OTRA VEZ LA CACHÉ DE CLOUDFLARE (2026-09-16 — es la trampa 36 y la 21, que ya estaban escritas).**
    Se subieron el **JS y el CSS nuevos «sin cambiar el `?v=`»** y, por HTTP, `tienda_ia.js?v=9` seguía
    devolviendo **43 691 caracteres y sin `tia-tienda__mas`** mientras que la **misma ruta con un parámetro
    de más** (`?v=9&cb=123`) devolvía **44 605 y con la marca nueva** (`Cache-Control: public,
    max-age=604800` = **7 días**; `cf-cache-status: HIT` con la URL vieja, `MISS` con la nueva). O sea:
    **el archivo del servidor estaba bien y el navegador del jefe habría seguido con el viejo.**
    **Regla (la misma de la 36): cada vez que se toca el JS o el CSS hay que SUBIR EL NÚMERO del `?v=` en
    `crear_tienda_ia.php`, subir también la página y comprobarlo por HTTP** — y para comprobar cualquier
    estático, **añadirle un parámetro cualquiera a mano**: si con el parámetro aparece y sin él no, era la
    caché, no el FTP ni el antivirus. Hoy las versiones están en **JS `?v=10`** y **CSS `?v=9`**, y la
    sonda 15 ya lo verifica (busca `tia-tienda__mas` dentro del JS servido).
40. 🔴 **`directorio_servicios` NO TIENE COLUMNA `orden` — y el `catch` callado escondió el fallo
    (2026-09-16).** La consulta que lee los productos para el copy (`tienda_ia_productos_publicados()`)
    pedía `ORDER BY orden ASC, id ASC` **copiando de `directorio_fotos`** (ahí sí existe `orden`). En
    `directorio_servicios` no existe: MySQL devuelve **error 1054**, el `try/catch` lo tragaba y la
    función devolvía **una lista vacía**… así que el copy se quedaba sin productos **y el dueño no veía
    ningún error** (la prueba 16 lo cazó: *«sus 3 productos se leen de la base → 0 productos»*). Las
    columnas de verdad están en `__tia_cols.php` (y en su resultado): `id, negocio_id, titulo,
    tipo_producto, descripcion, precio, unidad, imagen, destacado, activo, disponible_hasta, creado_en`.
    **Lección: cuando una función «no encuentra nada», sospechar del `catch` que se come el error** — se
    le puso `error_log()` para que salga en el registro de PHP.
41. 🔴 **EL COPY NECESITABA «CONTEXTO» Y LOS PRODUCTOS NO CONTABAN (2026-09-16).** `tienda_ia_descripcion_final()`
    solo escribía el copy si había `trato`, `visto` o `productos_vistos`; al **rehacer el copy de una
    tienda ya publicada** (donde esos campos no existen, solo su catálogo) salía **sin descripción y sin
    ningún error**: la sonda devolvía *«❌ la IA no devolvió copy»* cuando en realidad **la IA nunca fue
    llamada**. Ahora **los productos también son contexto** (`!empty($op['productos'])`). Le pasó igual a
    la tienda de prueba: por eso se prueba con el camino completo, no solo con la función suelta.
49. 🔴 **LA RAMA MULTIPART DEL `POST` TIRABA EL `modo` (Y CON ÉL, TODO EL MODO «AGREGAR UN PRODUCTO»)
    (2026-09-16, lo cazó la prueba 17).** `api/tienda_ia.php` armaba `$d` de dos maneras: si el cuerpo era
    **JSON** (texto, opciones) lo leía entero, pero si era **`multipart/form-data`** (LAS FOTOS, que el
    compositor manda en un `FormData`) construía **a mano** un array con `accion`, `csrf`, `texto`, `valor`
    y `tipo`… y **dejaba fuera `modo`**. Como el modo se lee de `$d['modo']`, **la foto de un producto caía
    en la conversación de «tienda nueva»**: el dueño, que venía de `/crear-tienda?modo=producto`, veía el
    paso de las **8 fotos de la tienda** (*«Todavía no podemos empezar 📸 … Llevas 1 y te faltan 7»*) y su
    producto se quedaba sin foto. **Arreglado:** la rama multipart ahora copia **`modo` y `prod`** de
    `$_POST` (el JS ya los mandaba desde siempre: era el servidor el que los tiraba). ⚠️ **Regla general:
    toda pieza que el navegador manda en los DOS caminos (JSON *y* `FormData`) hay que leerla en las DOS
    ramas.** El síntoma clásico de este tipo de bicho es *«el paso se fue a otro flujo»*, no un error.
50. 🔴 **LA PUERTA DE LA API RECONSTRUYE CADA OPCIÓN CON SU LISTA BLANCA: UNA BANDERA NUEVA SE PIERDE
    SI NO SE AÑADE AHÍ (2026-09-20, lo cazó la prueba 18).** El motor mandaba la opción del botón de la
    ubicación con **`'morado' => true`**, el guion la devolvía bien… y el navegador recibía un chip
    **normal** (el JSON traía `{"texto":"📍 Ubicación","valor":"gps","principal":true,"nota":""}`).
    **El motivo:** `tia_opciones()` (en `api/tienda_ia.php`) **rearma cada opción a mano** y solo copia las
    claves de dibujo que conoce (`url`, `misma`, `tarjeta`, `azul`, `rejilla`, `color`, `icono`) — las
    demás se caen. **Arreglado:** se añadió `morado`.
    ⚠️ **Regla: al inventar una bandera de dibujo nueva hay que tocar SIEMPRE los tres sitios —
    el guion (motor) → la lista blanca de `api/tienda_ia.php` → el pintor (`tienda_ia.js`) y su CSS.** El
    síntoma es *«la mandé y no se ve»*, sin ningún error a la vista.
51. 🔴 **EL CSS DEL COMPOSITOR ES COMPARTIDO CON 👑 EL SUPREMO: BORRARLO LO DEJA SIN ESTILOS (2026-09-20).**
    Al cambiar el compositor de El maestro (del verde de WhatsApp al de la guía) **se borraron las reglas
    `.tia-wa*` y `.tia-emoji` de `assets/css/tienda_ia.css`**… y con eso **El Supremo** (`supremo.php`)
    se quedaba con el campo y sus botones **pelados**: su página **reusa esas mismas clases**
    (`#supEmoji`, `#supClip`, `#supCam`, `#supEnviar`, `#supEmojiPanel`) y `supremo.js` busca
    `.tia-wa__mic-ico` / `.tia-wa__mic-enviar`. **Arreglado:** las reglas se restauraron (quedan
    compartidas y marcadas con un aviso).
    ⚠️ **Regla: antes de borrar CSS o HTML de un componente, `grep` de sus clases en TODO `deploy/`** — el
    mismo compositor vive en dos páginas y el dueño de las clases no siempre es el archivo que se está
    tocando. (Es la hermana de la trampa 25: un cambio de un paso puede romper otro caso.)
52. 🔴 **UNA CLASE, UN USO: LA FILA DEL COMPOSITOR SE LLAMABA `.tia-barra`, QUE ES LA BARRITA DEL
    PROGRESO (2026-09-20, la que vio el jefe).** El jefe: *«corrige inmediatamente las barras de uso de la
    sección del maestro, está horrible, está muy mal hecho»*. La fila de 📷 🎤 cuadro ➤ llevaba
    **`class="tia-barra"`**, la MISMA clase de la **barrita del progreso de la cabecera**
    (`.tia-barra { height: 4px; background: rgba(255,255,255,.18) }`, con su `#tiaBarra` dentro). Al
    fusionarse las dos reglas, **la fila quedó de 12 px y blanca** (los botones de 36, 42 y 34 px se
    salían y pisaban el botón «Ubicación») y **la barrita del progreso** se volvió una **píldora blanca**
    en la cabecera. **Arreglado:** la fila se llama **`.tia-composer__linea`**.
    ⚠️ **Regla: antes de ponerle una clase a algo, `grep` de esa clase en todo `deploy/`** — si ya existe,
    **no es tuya**. Y **una clase no puede describir dos cosas** que se pintan distinto.
53. 🔴 **EL `?v=` DE LOS ESTÁTICOS HAY QUE SUBIRLO AL RE-SUBIR EL MISMO ARCHIVO (2026-09-20).** El hosting
    cachea `assets/css/*.css` y `assets/js/*.js` **por URL**: al volver a subir el CSS **en la misma
    dirección** (`tienda_ia.css?v=11`) siguió sirviendo **el cuerpo viejo** — el ➤ seguía del naranja
    anterior y parecía que el arreglo no había entrado. Con **`?v=12`** entró al instante. Por eso:
    **cada vez que se toca un `.css` o un `.js`, subir su `?v=` y volver a subir la página** que lo llama
    (y `grep` de quién más lo llama: El Supremo también lo carga).
54. 🚫 **NUNCA REESCRIBIR UN PHP CON `Get-Content -Raw` + `Set-Content -Encoding UTF8` (2026-09-20).**
    PowerShell leyó el archivo como **Windows-1252** y lo volvió a escribir como UTF-8: el archivo entero
    quedó **mojibake** (`respuesta…` → `respuestaâ€¦`, los emoji hechos `â•`), le metió **BOM** y subió
    así al hosting (se vio en la página: *«Escribir tu respuestaâ€¦»*). **Se revierte byte a byte** con
    **`python __reparar_utf8.py go`** (devuelve el texto original exacto: 19 691 y 20 854 bytes, los
    mismos de antes del daño). ⚠️ **Regla: los archivos se editan con la herramienta `edit`/`write`**, y
    si hay que tocarlos desde PowerShell es **`Get-Content -Encoding UTF8`** y **`Set-Content -Encoding
    UTF8NoBOM`**; y **después de tocar cualquier PHP, `php -l` y mirarlo en la página viva**.

## 🎵 LAS 7 TRAMPAS DE LA CANCIÓN DE LA TIENDA (2026-09-17 — el detalle, en el §16)

42. **LA API NO PUEDE HACER UNA CANCIÓN DE 40 SEGUNDOS.** `length_range` **solo acepta múltiplos de 30**
    (`[40,40]` contesta `422 «Input should be a multiple of 30»`). Por eso se pide **`[30,60]`** y los 40
    segundos exactos los hace el motor, recortando aquí (§16).
43. **LOS `tags` SON DE UNA LISTA CERRADA, NO TEXTO LIBRE.** Un tag inventado («catchy commercial jingle»)
    **rechaza TODA la petición con 422** («Invalid tags: …») y no genera nada: la lista de los que valen
    está en `includes/config_cancion.php`. Y para probar tags **sin gastar créditos**: mandar el
    `length_range` en `[40,40]` junto con ellos — la API dice qué tag está mal y **no crea ninguna canción**.
44. **NO SE PUEDEN MANDAR `tags` + `lyrics` + `prompt` JUNTOS** («cannot provide all three tags, lyrics,
    and prompt»): dos como máximo. El motor manda **letra + prompt** (la letra es la que asegura que se
    cante el nombre de la tienda). `negative_tags` **no** cuenta para esa regla.
45. **LA API TIENE UN ESTADO QUE NO ESTÁ EN SU DOCUMENTACIÓN: `SAVING`.** Además de PENDING, GENERATING y
    SUCCESS existe **SAVING** (guardando el audio en su CDN, tarda un rato): el motor lo tomaba por un
    fracaso y la canción se perdía (le pasó a la Llantería El Doctor). Ahora **solo FAILURE/ERROR/CANCELED
    son fracaso** y cualquier otro estado se espera.
46. **EL HOSTING NO TRAE FFMPEG Y `migrar_*.php` DA 403.** Se subió un **ffmpeg estático** a
    `_cancion/ffmpeg` (76 MB, con `__cancion_subir_ffmpeg.py`) porque el recorte exacto lo necesita; y el
    instalador de las tablas se llama **`instalar_canciones.php`** porque **Hostinger responde 403 a
    cualquier archivo `migrar_*`** (comprobado con `migrar_reportes.php`, que existió de verdad).
47. **`-t 40` NO DEJA 40 SEGUNDOS: deja 39,975.** El MP3 que devuelve la API trae su propia etiqueta de
    retardo del codificador y el corte se come ~25 ms (medido decodificando el audio, no el encabezado).
    La receta que sí da **40,000000 s clavados** es **`apad,atrim=0:40,afade=t=out:st=38:d=2`** (§16).
48. **EL MP3 SE QUEDA EN LA CACHÉ DE CLOUDFLARE.** Al rehacer una canción (misma ruta, audio nuevo) el
    visitante seguía oyendo la vieja: la ficha le pone **`?v=<fecha>`** a la dirección. Y ojo con el
    **contador de fotogramas del MP3**: el archivo de ffmpeg empieza con una etiqueta **ID3v2**, y si no se
    salta, el conteo se corta en el primer fotograma (leyó 0,026 s de una canción de 40 s).

---

## §13. CÓMO SE CAMBIA ALGO (todo en un solo archivo)

En `deploy/includes/config_tienda_ia.php`:

| Quiero… | Toca |
|---|---|
| Cambiar el nombre o la cara del asistente | `TIENDA_IA_NOMBRE`, `TIENDA_IA_EMOJI`, `TIENDA_IA_TITULO` |
| Que la tienda nazca **pendiente de aprobación** | `TIENDA_IA_ESTADO` → `'pendiente'` |
| Exigir más o menos fotos | `TIENDA_IA_FOTOS_TIENDA_MIN` (**1**) / `_MAX` (**8**) / `_IDEAL` (**3**, desde ahí recomienda más), `TIENDA_IA_FOTOS_PRODUCTO_MAX` |
| 🆕 **Las fotos del arranque** (mínimo, tope) | `TIENDA_IA_FOTOS_ARRANQUE_MIN` (**8**, orden del jefe) · `TIENDA_IA_FOTOS_ARRANQUE_MAX` (**8**) |
| 🆕 **Qué fotos se le piden** (la lista del paso `fotos`) | `TIENDA_IA_TEXTO_FOTOS_ARRANQUE` (afuera · dentro · productos · máquinas · personal · tarjeta · folleto) |
| 🆕 **Que el paso de las fotos vuelva a mostrar el botón de cámara** | `$solo_galeria = true` del `case 'fotos'` en `tienda_ia_guion()` (el JS lo lee como `solo_galeria` y esconde `#tiaCamara`) |
| 🆕 **Que la IA lea mejor la letra chica del letrero** | `TIENDA_IA_FOTO_LADO_LETRERO` (**1600**) · `TIENDA_IA_FOTO_CALIDAD_LETRERO` (**85**) · `TIENDA_IA_MAX_TOKENS_LETRERO` (**700**) |
| 🆕 **Qué se le pide a la IA que lea en las fotos** | el texto de `tienda_ia_ia_arranque()` (las 8 etiquetas) y su lector tolerante `tienda_ia_leer_ficha()` |
| 🆕 **Que el saludo diga otra cosa** | `case 'arranque'` en `tienda_ia_guion()` (el botón tiene que seguir mandando `valor = 'galeria'` para que se abra el carrete) |
| Cuántos rubros o categorías puede tener una tienda | `TIENDA_IA_RUBROS_MAX` (**4** = principal + 3) |
| Que la tienda **vuelva a pedir aprobación** para publicar | `TIENDA_IA_PUBLICAR_AUTO` → `false` (vuelve el paso `resumen` con su botón) |
| Que la descripción sea la del dueño y no la de la IA | `TIENDA_IA_DESCRIPCION_IA` → `false` |
| El texto que se añade a la descripción con «Todas las anteriores» | `TIENDA_IA_NOTA_TODA_LA_ZONA` |
| Cuántas fotos mira la IA en UNA sola llamada | `TIENDA_IA_FOTO_LOTE_IA` (8) |
| Cuántos productos crea el asistente antes de mandar al WhatsApp | `TIENDA_IA_PRODUCTOS_CON_IA` (**2** — es el tope **por ronda**; el dueño puede abrir todas las rondas que quiera con `🛍️ Agregar otro producto`, ver §2quinquies) |
| El número del jefe para «quiero más productos» | `TIENDA_IA_WHATSAPP_MAS` (usa `ADMIN_WHATSAPP` = 908785164 desde el **2026-09-19**; el 955041690 es hoy su número **personal**, en `ADMIN_WHATSAPP_VIEJOS`) |
| El texto de los dos botones de foto | `TIENDA_IA_TEXTO_CAMARA` (📷 Tomar foto) · `TIENDA_IA_TEXTO_GALERIA` (🖼️ Elegir varias) |
| Que la galería deje de aceptar varias fotos de golpe | `multiple` del `#tiaArchivoGaleria` en `crear_tienda_ia.php` (y el lote en `api/tienda_ia.php`) |
| Cambiar lo que dice el arranque según quién llega | `case 'arranque'` en `tienda_ia_guion()` + `tienda_ia_arranque_extra()` |
| Las letras/números de las contraseñas | `TIENDA_IA_CLAVE_LETRAS` (sin la O) · `TIENDA_IA_CLAVE_NUMEROS` (sin el 0) |
| Volver a exigir cuenta para empezar | `TIENDA_IA_SOLO_REGISTRADOS` → `true` |
| Que razone más (más caro y más lento) | `TIENDA_IA_RAZONAMIENTO` → `'low'` o `'medium'` |
| Apagar el consumo (que solo use el guion) | Vaciar `TIENDA_IA_DEEPSEEK_KEY` |
| Cerrar la puerta a los visitantes | `TIENDA_IA_SOLO_REGISTRADOS` (la comprobación real está en `api/tienda_ia.php`) |
| Otro modelo | `TIENDA_IA_MODELO` (👉 tiene que ser uno **con visión**, si no no puede mirar las fotos) |
| Más o menos uso por persona/día | `TIENDA_IA_TIENDAS_POR_USUARIO_DIA` (**5**, era 2), `TIENDA_IA_LLAMADAS_POR_USUARIO_DIA`, `TIENDA_IA_LLAMADAS_GLOBALES_DIA` |
| 🆕 **Cuántas tiendas nuevas puede crear una persona al día** | `TIENDA_IA_TIENDAS_POR_USUARIO_DIA` (**5**) |
| 🆕 **El texto / las opciones de las ventanas emergentes** | `crear_tienda_ia.php` (las tres hojas: `#tiaSheetFoto`, `#tiaSheetTiendas`, `#tiaSheetOtra`) + sus manejadores en `assets/js/tienda_ia.js` + los estilos en `assets/css/tienda_ia.css` (`.tia-sheet*`) |
| 🆕 **Quitar o mover el botón «🆕 Crear otra tienda»** | `case 'publicado'` · `case 'fin'` · `case 'mas_productos'` de `tienda_ia_guion()` (y el `case 'otra_tienda'` de `api/tienda_ia.php`, que es quien crea la conversación nueva) |
| 🆕 **Cuántos productos ofrece seguido antes de felicitar** | `TIENDA_IA_PRODUCTOS_CON_IA` (**2**, es el tope de **la ronda**: al pedir más se abre otra con `tienda_ia_ronda_productos()`) |
| 🆕 **La puerta del inventario (cámara + voz, sin tope)** | `productos.php?n=<ID>#crear` — el enlace va en el mensaje final (`tienda_ia_guion('fin')`) y en el botón `➕ Producto` de la ventana **🏪 Mis tiendas** |
| 🆕 **El copy con colores y botones** | `TIENDA_IA_COPY_RICO` (**true**). El prompt vive en `tienda_ia_ia_descripcion()` y el listón en `tienda_ia_copy_ok()` (§2sexies A) |
| 🆕 **Que el copy se rehaga al agregar productos** | `TIENDA_IA_MEJORAR_COPY` (**true**) → `tienda_ia_mejorar_descripcion()` |
| 🆕 **Las opiniones al crear la tienda** | `TIENDA_IA_OPINIONES_AUTO` (**true**, con `false` la ficha nace sin opiniones) · `TIENDA_IA_OPINIONES_N` (**3**) |
| 🆕 **Cuántas fotos de productos se clasifican de una vez** | `TIENDA_IA_FOTOS_LOTE_MAX` (= `TIENDA_IA_FOTO_LOTE_IA`, **8**) y el paso `productos_lote` |
| 🆕 **Rehacer el copy (y sembrar opiniones) de una tienda YA publicada** | `python __sonda_run.py __tia_mejorar_tienda.php tia-2026-09-16-mejorar [x\|go] "&id=<ID>"` (§11) |

**Límites anti-abuso (lo que protege el saldo):** **5 tiendas nuevas por persona y día** (eran 2 hasta
el 2026-09-16: el dueño de varios negocios se topaba con el tope) · 80 llamadas
por conversación · 300 por persona y día · **3 000 por día en todo el sitio** · 24 h de vida de una
conversación a medias (después se puede retomar igual, se crea otra). Si se pasan, el asistente avisa
con cariño y **no gasta**.

---

## §14. QUÉ FALTA (pendientes honestos)

* ⏳ **🆕 Que el jefe pruebe EN SU CELULAR la versión 8** (§2octies): que la primera pantalla sea el
  **botón morado de la ubicación**, que el permiso del GPS salga ahí, que después le pida las 8 fotos, y
  que el compositor nuevo (📷 con su menú · 🎤 del buscador · ➤) se sienta bien con el dedo — sobre todo
  **el envío automático al segundo** después de dictar. La lógica va probada por HTTP
  (`__tia_prueba18.php`, **50 ✅ · 0 ❌**); falta su dedo.
* ⏳ **🆕 Que el jefe pruebe el botón «Agregar mi tienda» del buscador** (§2septies): buscar un rubro,
  tocar el botón negro/rosado, mandar la foto de ese producto y verlo publicado en su tienda. La lógica
  está probada por HTTP (`__tia_prueba17.php`, **57 ✅ · 0 ❌**) y falta su dedo en su celular.
* ⏳ **🔑 El botón de Google del login NO devuelve al maestro** (sí lo hacen entrar con el número/correo):
  `google_login.php`/`google_callback.php` no llevan el `redirect`, así que ese camino cae en el panel.
  Es el único cabo suelto del «loguearlo y abrirlo en el maestro»; se arregla guardando el destino en la
  sesión del OAuth (hay que probarlo con una cuenta de Google de verdad).
* ⏳ **Que el jefe lo pruebe con su celular y publique su primera tienda de verdad** (ahí se prueban
  la cámara real, el dictado real, el permiso de ubicación y **la galería múltiple real**).
* ⏳ **🆕 Que el jefe pruebe el botón `🆕 Crear otra tienda` al terminar una tienda** (y los tres
  submenús en ventana emergente del menú ⋯ y del 📷). La lógica está probada por HTTP
  (`__tia_prueba15.php`, 56 ✅) y el aspecto, medido con Chrome aparte (`__tia_ver.py modales`); lo que
  falta es **su dedo en su celular**: que la segunda tienda nazca aparte, que la primera no se toque y
  que las ventanas se sientan como las de WhatsApp.
* ⏳ **🆕 Y que pruebe el camino de los MUCHOS productos**: `🛍️ Agregar otro producto` (rondas de 2 con el
  asistente) contra el inventario del panel (`productos.php?n=ID#crear`, cámara + voz, sin tope) y la
  **tanda de fotos** (`📷 Mandar fotos de mis productos`, que la IA clasifica de golpe). Si el jefe
  prefiere que el asistente ofrezca **más de 2 seguidos**, se sube `TIENDA_IA_PRODUCTOS_CON_IA`; si
  prefiere que **no** se ofrezca tanto, se baja.
* ⏳ **🆕 Y el copy/opiniones de las tiendas VIEJAS**: los cambios valen para **toda tienda nueva**, y para
  las que ya están publicadas hay un rehacedor (`__tia_mejorar_tienda.php`, §11) que se corre tienda por
  tienda o en tanda. Falta decidir con el jefe **si se le pasa a las ~1 500 tiendas** (el copy se reescribe
  con el catálogo real y se les siembran 3 opiniones): son ~2 llamadas de IA por tienda.
* **Los rubros extras no se ven en la ficha**: la tienda **sale** en todos sus rubros (la página del
  rubro, el buscador y «cerca de mí» usan `rubro_filtro_id/slug`), pero en su ficha no se listan; si el
  jefe los quiere ver ahí, eso es trabajo de `negocio.php`, no del constructor.
* **«Todas las anteriores» es una nota en la descripción**, no una cobertura real por distrito: si algún
  día se quiere que una tienda viva en varios distritos, hay que tocar la base y el buscador.
* **La reunión de fotos con lo que la IA vio**: hoy `datos['visto']` se usa para proponer productos;
  también podría servir para **escribir la descripción** de la tienda con lo que se ve.
* **El mensaje de voz grabado** (que él mencionó): necesita una **clave de transcripción** aparte
  (DeepSeek no transcribe audio). Hoy el dictado en vivo cubre el caso y es gratis.
* **Editar la tienda después**: hoy el asistente crea la tienda y hasta 2 productos; para cambiar lo
  ya publicado se usan `productos.php`, el panel, el buscador y el WhatsApp del jefe. Un «El maestro,
  ahora editemos» sería el siguiente paso natural.
* **Que el jefe pueda subir los productos 3.º, 4.º…**: hoy llegan por WhatsApp y los sube él con
  `__pub_productos.py` / `productos.php`. Se podría automatizar con una cola, pero eso ya es otro
  módulo.
* **Aprovechar la visión para más**: el nombre del letrero (para confirmar que el nombre escrito es
  el que se ve), el precio de la pizarra, los horarios del cartel.
  ✅ **El nombre del letrero ya se lee** (versión 5, §5octies). Quedan **el precio de la pizarra y los
  horarios del cartel**: hoy el horario se pregunta con 4 chips y «Después».
* ✅ **Lo que la IA ve ya sirve para la descripción** (versión 5): `datos['visto']` y
  `datos['productos_vistos']` entran en el copy (trampa 29). Lo que **falta** es usarlos para
  **proponer más productos** en la conversación nueva (hoy los productos se proponen desde la
  descripción ya publicada, §5quater).
* ⏳ **Que el jefe lo pruebe con SUS fotos** (es lo que manda): que entre a `/crear-tienda`,
  toque el botón del saludo y mande **8 fotos** de un negocio de verdad. Ahí se ve si el letrero de su
  celular se lee, si **el candado de las 8 fotos** es cómodo o estorba, y si el **compositor de WhatsApp**
  (micrófono y emojis) se siente como el de verdad. Enlace para él: **https://dechimbote.com/crear-tienda**
  (y **`/caminante/`** sigue igual para la captura en campo).
* **Aviso al jefe por Telegram con la tienda nueva ya existe** (`aviso('tienda_nueva')`, con
  «creada por El maestro 🛠️»).
* **Revisar el diseño en un celular de verdad**: la medición sin cabeza del 2026-09-15 confirma la
  estructura (nada debajo de la barra, solo el chat scrollea, 16 px), pero el **aspecto** final
  —colores, tamaños, cómo se siente— solo se juzga en la mano.

---

## §15. REGISTRO

| Fecha | Qué pasó |
|---|---|
| **2026-09-20 (noche) — 🔴 LAS BARRAS DE EL MAESTRO, ARREGLADAS (la vuelta del §2octies b.2)** | **LA ORDEN DEL JEFE, con dos capturas en la mano (la de El maestro rota y la de El guía bien):** *«corrige inmediatamente las barras de uso de la sección del maestro, está horrible, está muy mal hecho; tienes que dejarlo tal cual como está en la guía»*. **Los 4 bichos, todos de la misma familia (una clase que se usa para dos cosas): (1)** la fila del compositor llevaba **`class="tia-barra"`**, que es **la barrita del progreso de la cabecera** → la fila quedaba de **12 px y blanca** (los botones se salían y pisaban el chip «📍 Ubicación») y **la barrita del progreso** se volvía una **píldora blanca**; ahora la fila es **`.tia-composer__linea`** y la barrita volvió a su línea de **4 px**. **(2)** seguía viva la regla vieja **`.tia-enviar { min-height: 46px; padding: 10px 18px }`** → el **➤ salía como un óvalo de 36 × 46 px**; borrada (con el cuadro viejo `.tia-texto`). **(3)** **`‹.tia-composer›` no era `position: relative`** → el **menú de la cámara** y el aviso del micrófono, que son `absolute`, se anclaban a la página y el menú salía en **y = −84 px**: el dueño tocaba el 📷 y **no veía nada**; ahora el menú sale **encima del botón**. **(4)** el ➤ usaba un naranja de respaldo distinto (`#e07a1f`): ahora usa **el del sitio** (`#ea6a12`), porque esta es una **página sola** y no carga `base.css`. **Quedó pieza por pieza igual a la guía** (medido en un Chrome aparte con **`__maestro_barra.py`**, y la guía con **`__maestro_barra.py guia`**): 📷 36 px `#f0f2f5` · 🎤 42 px `#ea6a12` · cuadro 34 px `#f1f2f4` radio 999 · ➤ 36 px `#ea6a12`, sobre la **franja blanca con su línea arriba**. **Desplegado:** `tienda_ia.css` **`?v=12`** (El Supremo quedó con su franja transparente propia, `supremo.css ?v=8`) · **trampas 52, 53 y 54**. |
| **2026-09-20 — 🔴 VERSIÓN 8: LA UBICACIÓN PRIMERO Y EL COMPOSITOR DE LA GUÍA** | **LOS DOS PEDIDOS DEL JEFE DEL DÍA** (el detalle completo y la prueba, en **§2octies**). **(1) 📍 LA UBICACIÓN ES LO PRIMERO Y ES OBLIGATORIA:** *«cuando carga, lo primero que va a aparecer es un botón pidiendo ubicación, eso es lo primero; luego aparece el texto ya con la ubicación recibida y luego recién preguntamos que suban las fotos (8 siempre); la ubicación como primer dato es vital… que aparezca dentro de la conversación el botón de ubicación, en color morado, que diga "ubicación"; si no comparte su ubicación, pues no continúa»*. La **primera pantalla ya es el botón MORADO** (`morado: true` → `.tia-chip--morado`, valor `gps`): se fue el paso previo de «crear mi tienda». El GPS saca el **distrito real** (los 30 negocios más cercanos votan, la receta del Caminante) y el bot confirma *«📍 ¡Ubicación recibida! Estás en **Chimbote** 🎯»* y **pasa derecho a las 8 fotos**. Sin ubicación **no sigue**: si escribe otra cosa, se le insiste con el mismo botón; y si no quiere dar el GPS, **puede escribir su distrito** (la salida que eligió el jefe). La **calle** sigue saliendo de las fotos (el GPS da coordenadas, no nombres de calle). Al ser local ya **no se pregunta la zona dos veces**. Los pasos se renumeraron (`ubicacion 1`, `fotos 2`, `tipo 3`…). **(2) 🎯 LA BARRA DE ESCRIBIR, IGUAL A LA DE LA GUÍA:** *«quiero llevar este mismo estilo de los botones —el de cámara (el que abre el modal de "Abrir cámara" o "Abrir galería"), el de grabar, el input y el de enviar— al maestro, tal cual como lo tenemos ahorita en la guía, tal cual»*. Ahora es **📷 (con su menú chiquito encima) · 🎤 (el micrófono del buscador: mismo icono, naranja, con latido rojo y **envío automático al segundo** cuando se apaga por el silencio) · cuadro delgado · ➤**; se fueron el **😊**, el **📎**, el **🎤 verde de WhatsApp**, los **dos botones grandes de foto** y la **ventana modal `#tiaSheetFoto`**. ⚠️ **El Supremo sigue con el compositor de WhatsApp** (comparten el CSS: trampa 51). **(3) 🐞 Dos bichos cazados por la prueba 18 (50 ✅ · 0 ❌):** la primera pantalla seguía siendo «crear mi tienda» (se cambió) y **`api/tienda_ia.php` se comía la bandera `morado`** porque rearma las opciones con una lista blanca (trampa 50). Versiones nuevas: **`tienda_ia.js?v=13`** y **`tienda_ia.css?v=10`**. |
| **2026-09-16 — 🛒 «AGREGAR MI TIENDA»: EL BOTÓN NEGRO/ROSADO DEL BUSCADOR** | **EL PEDIDO DEL JEFE Y CÓMO QUEDÓ** (§2septies). Textual: *«cuando muestre los resultados, abajo del botón "ver cumpleaños cerca de mí" pon un botón negro con texto rosado que diga "agregar mi tienda". Lo que hará este botón es: si no está logueado, loguearlo; y si ya está logueado, abrir el maestro crear tienda y le dirá "dame las imágenes para el producto" —en este caso cumpleaños— y lo agregará el producto a su tienda»*. **(1) 🔍 EL BOTÓN**, en los resultados de búsqueda (`buscar.php`) y **debajo** del botón «📍 Ver … cerca de mí» (dentro del bloque de ubicación, así sale también en el modo «cerca de mí» y cuando no hay resultados): clase `.agrega-tienda`, **negro `#111` con letra rosada `#ff5ea8`** (hover negro puro con `#ff86bd`), 16 px y ancho completo. **Sin sesión pasa primero por el login** (`login.php?redirect=…`) y **vuelve solo** al maestro; **con sesión** va derecho. **(2) 🛒 EL PRODUCTO YA DICHO (`prod`)**: el término que se buscó viaja en la URL (`/crear-tienda?modo=producto&prod=cumpleaños`) y el asistente **no pregunta cómo se llama el producto**: nace con ese nombre y lo primero que pide son **SUS FOTOS** —*«📷 Mándame la foto de cumpleaños»*—, y después el precio y **queda publicado en su tienda**. El atajo es **`tienda_ia_producto_pedido()`** y tiene 3 reglas: solo en modo `producto`, solo si **todavía no hay producto** (si no, no toca nada) y **con una sola tienda no se le pregunta cuál** (con varias, el atajo se completa al tocar la suya; **de paso se ahorra la llamada de IA** que proponía nombres de producto). `prod` viaja en **cada** petición (arranque, JSON y el `FormData` de las fotos) y el atajo es **idempotente**: la segunda visita no duplica el producto ni pierde la foto. **(3) 🐞 TRES BICHOS CAZADOS POR LA PRUEBA 17 (57 ✅ · 0 ❌):** el `redirect` del login iba **doble codificado** (el `%` viajaba como `%25`), **la rama `multipart` del `POST` tiraba el `modo`**, así que **la foto del producto caía en el flujo de «tienda nueva»** (trampa 49: el modo «agregar un producto» estaba roto justo al mandar la foto), y **el `redirect` del login se perdía en el POST** (el formulario no lo llevaba: `login.php?redirect=…` **siempre** terminaba en el panel — arreglado con un campo escondido, como en `registro.php`; beneficia a TODAS las puertas que usan ese parámetro). **(4) DESPLEGADO:** `buscar.php`, `crear_tienda_ia.php`, `api/tienda_ia.php`, `includes/tienda_ia.php`, `login.php` y `assets/js/tienda_ia.js` (**`?v=12`**). |
| **2026-09-17 (2.ª orden) — «NO RECORTES NADA, PONLO A SONAR SOLO Y EMPIEZA POR LAS TIENDAS QUE YA EXISTEN»** | **LAS TRES ÓRDENES NUEVAS DEL JEFE, cumplidas el mismo día.** Textual: *«recibe las canciones con cualquier duración que el Treblo.com te lo envíe: no pierdas tiempo recortando ni reeditando; si te lo envía de 30, 60 segundos o lo que sea, tú solo lo pones en la web con auto reproducir… sé que no suena si entran directo, pero la mayoría entra por clic, ahí sí se auto reproducirá. Comienza con las tiendas ya creadas y me dices para cuántas tiendas te alcanzó esas 4 API»*. **Lo que se cambió:** **(1) `CANCION_RECORTAR = false`** — el audio se guarda **tal cual llega** (se baja y se archiva: **sin ffmpeg, sin cortes y sin recomprimir**; lo único que se mide es la duración, para el registro y para el reloj de la ficha) y **las 3 canciones de prueba se volvieron a bajar del CDN con `cancion_rehacer()`**: quedaron en **39,38 s · 29,95 s · 38,59 s** (la de *Sabor y Fuego* llegó de 30 exactos, como él dijo) y antes estaban recortadas a 40,000000 s —la receta del recorte queda documentada en el §16.3 por si algún día se vuelve a querer—. **(2) `CANCION_PLAYER_AUTOPLAY = true`** — el `<audio>` de la ficha va con **`autoplay` + `preload="auto"`** y, si el navegador lo bloquea (entrada directa sin clic), **el primer toque del visitante en cualquier parte de la página la arranca** (ese gesto es el permiso que pide el navegador); el botón ▶️ sigue ahí, y el subtítulo muestra **la duración real** (*«canción de 0:39»*, *«de 0:30»*). **(3) El tope diario quedó en 0 (sin tope)** y nació la herramienta de la tanda: **`python __cancion_lote.py plan\|encolar\|tanda\|procesar\|estado`** (sonda `__cancion_lote.php`), que **nunca encola más de lo que se puede pagar** (si quedan 300 créditos, encola 3), se salta las tiendas que ya tienen canción y los avisos de empleo, y canta **por orden de vistas** (las primeras de la lista: *Sra. Cinthia*, *NOVEDADES GAELA CHIMBOTE*, *MADAÍ Restaurant Chifa*, *Plaza de Armas de Santa*, *Essalud Coishco*…) o por `nuevas`, `viejas` o `catalogo`. **(4) 🔴 LA RESPUESTA A SU PREGUNTA, medida con `plan`:** el sitio tiene **1 677 tiendas**, **1 666 activas sin canción**, y las 4 cuentas de Treblo tienen **0 créditos** → **alcanza para 0 tiendas**. Como cada canción cuesta **100 créditos**, para cantar las 1 666 que ya existen hacen falta **166 600 créditos**; en cuanto recargue, **una sola orden** (`tanda 1666 vistas`) apunta lo que alcance y las va cantando sola. **Camino nuevo probado de verdad** (rehacer las 3 canciones sin gastar créditos) y las 3 fichas verificadas por HTTP con `autoplay` y su duración real. Todo en el **§16** (nuevo **§16.12** para la tanda). |
| **2026-09-17 — 🎵 LA CANCIÓN DE CADA TIENDA (el jingle de 40 segundos)** | **EL PEDIDO Y LA REALIDAD.** El jefe mandó una carta al programador (§16): *«cada vez que se cree una tienda, se le genere automáticamente una canción comercial, pegajosa y de 40 segundos… el prompt debe incluir el nombre de la tienda y su rubro… se debe usar la API de Treblo (Sonauto), endpoint `POST /v1/generations/v3`, parámetro `length_range`… guardar el audio en el hosting y registrar la ruta en la base… 4 API keys para rotar créditos… avisarme cuando estén por agotarse… probar con 3 tiendas»*. **Se hizo todo eso** — módulo nuevo (`includes/cancion.php`, `api/cancion_worker.php`, `includes/cancion_player.php`, `includes/config_cancion.php`, `includes/vista_canciones_admin.php`, 2 tablas nuevas) y **3 canciones de verdad en 3 tiendas publicadas** (NOVEDADES GAELA CHIMBOTE, Sabor y Fuego, Llantería El Doctor), cada una de **40,000000 s exactos** y **469 KB**, sonando en su ficha. **🔴 PERO LA CARTA SE EQUIVOCABA EN TRES COSAS, y las tres se midieron con la API delante:** **(1) la API NO acepta 40 en `length_range`** (solo múltiplos de 30: `[40,40]` → 422), así que se pide `[30,60]` y **los 40 s exactos los corta el motor con ffmpeg** (`apad,atrim=0:40,afade=t=out:st=38:d=2` — con `-t 40` quedaban **39,975 s**); **(2) los `tags` son de una lista cerrada** (un tag inventado **rechaza toda la petición** con 422 «Invalid tags») y **no se pueden mandar tags + lyrics + prompt juntos**; **(3) los créditos NO alcanzan para nada parecido a «todas las tiendas»**: **1 canción = 100 créditos**, la cuenta 1 tenía **1 200 (12 canciones)** y las cuentas 2, 3 y 4 estaban **en 0** — y el sitio crea **entre 15 y 48 tiendas al día**. De esas 12 canciones quedan **0**: 3 son los jingles de las 3 tiendas de prueba y **9 se gastaron en las pruebas de la API** (incluidas 5 que se perdieron al descubrir la trampa de los `tags`). **El hosting no trae ffmpeg**: se le subió un **ffmpeg estático** (`_cancion/ffmpeg`, 76 MB) y el motor tiene recorte de reserva en PHP puro. **Lo que hay que decidir: recargar créditos** (sonauto.ai, las 4 cuentas ya están configuradas) **o** dejar la canción solo para las tiendas que el jefe elija desde el Súper Admin (**🎵 Canciones (Treblo)**, con el saldo y el consumo de cada cuenta). Detalle completo, medidas y herramientas: **§16**. |
| **2026-09-16 — 🔑 «OLVIDÉ MI CONTRASEÑA» (las dos puertas)** | **La pregunta del jefe** (*«¿actualmente cómo un usuario que se creó su tienda con El maestro puede recuperar su contraseña?»*) **dejó al descubierto que NO PODÍA**: sin puerta en `/login`, sin correos (el sitio no tiene ni una llamada a `mail()`) y con cuentas sin correo real (`<número>@dechimbote.com`), la clave que El maestro muestra **una sola vez** era irrecuperable. **Se le pusieron las DOS PUERTAS que eligió (§3ter):** **(1) 🔑 PUERTA DEL DUEÑO** — página nueva **`recuperar.php`** (URL amable **`/recuperar`** en el `.htaccess`) + enlace **«¿Olvidaste tu contraseña?»** en `/login`: pide el **número o el correo**, deja un **PEDIDO** en la tabla nueva **`directorio_claves_pedidas`** (se crea sola) y le manda al jefe el aviso nuevo de Telegram **`clave_olvidada`**; **nunca dice si la cuenta existe** (misma respuesta siempre, y con el WhatsApp del administrador ya escrito al lado) y tiene **dedupe de 30 min + tope de 5 pedidos por IP y hora**. **(2) 🔑 PUERTA DEL JEFE** — en **Súper Admin → 👥 Usuarios**: tarjeta **«🔑 Olvidaron su contraseña (N)»** con cada pedido (quién, con qué entra, su tienda, la IP) y sus botones **`🔑 Darle clave nueva`** / **`🙈 Descartar`**, **globito naranja** en el menú y **`🔑 Restablecer contraseña`** en cada cuenta. Genera una clave con el **mismo formato de El maestro** (3 letras + 1 número, sin O ni 0), la guarda con hash, **cierra las sesiones abiertas**, marca el pedido como atendido y muestra la clave **UNA sola vez** con el mensaje ya escrito y el **botón verde de WhatsApp** al dueño. **⛔ A un administrador no se le cambia la clave** desde ahí. **Probado de verdad:** `__sonda_clave.php` (motor: tabla, pedido sin duplicar, la clave nueva abre y la vieja ya no, sesiones cerradas, pedido atendido, admin bloqueado) · `__recuperar_http.py` (envío real del formulario con cookie y CSRF; `/recuperar.php` y `/recuperar` en 200; el enlace en `/login`) · `__sonda_admin_clave.php` (la pestaña 👥 Usuarios renderizada sin navegador: **los 11 elementos comprobados = 1**) · `__regresion_http.py` (`/`, `/buscar`, `/en-vivo`, `/login.php`, `/superadmin.php` sin sesión: todo bien). **0 rastros** (cuentas 999000*, pedidos, registros del aviso) y el aviso quedó **encendido**. ⚠️ **Trampa nueva:** la configuración de avisos **se cachea en memoria**, así que apagarla *después* de haberla leído no apaga nada en esa misma petición → al jefe le llegó **un aviso de PRUEBA** («PRUEBA Clave (borrar)», ya borrado de la base). |
| **2026-09-16 — EL COPY DE VERDAD, LAS OPINIONES Y LAS FOTOS DE PRODUCTOS** | **📝💬📷 LAS TRES COSAS QUE PIDIÓ EL JEFE DESPUÉS DE VER SU TIENDA** (`/neg/novedades-gaela-chimbote`: *«quedó muy floja su descripción y sin opiniones; eso debe ser automático al crear la tienda»* · *«lo ideal sería pedirle al usuario que siga subiendo fotos de sus productos e ir clasificando la inteligencia artificial… la IA siempre pedirá fotos y dará opciones»* · *«debe ir mejorando el copywriting según se vayan agregando más productos y repetir los botones de llamada a la acción en el copywriting»*). **(1) 📝 EL COPY YA NO ES UN PÁRRAFO PLANO:** `tienda_ia_ia_descripcion()` está reescrita para escribir el copy **con el kit de colores del sitio** (`cz-tit`, `cz-sub`, `cz-caja`, `cz-lista`, `cz-precio`, `cz-cta`, `cz-cta-final`) y **los botones de llamada a la acción repetidos** (2 de WhatsApp con su mensaje y `{URL}`, 1 de llamar), con **listón de validación** (`tienda_ia_copy_ok()`: ≥300 caracteres, ≥3 `<h3>`, `cz-cta-final`, ≥2 botones, sin teléfonos escritos) y respaldo si no pasa. **(2) 🔁 Y EL COPY MEJORA SOLO:** `tienda_ia_mejorar_descripcion()` lo vuelve a escribir **después de cada producto** y al terminar cada tanda de fotos, con el catálogo de verdad (y sus precios). **(3) 💬 LAS OPINIONES SON AUTOMÁTICAS:** al publicar se siembran **3 opiniones con contexto** (`fuente = 'maestro'`, fechas repartidas, 4-5⭐, sin avisar por Telegram), idempotentes y **con respaldo local** si la IA falla. **(4) 📷 LA TANDA DE FOTOS:** paso nuevo **`productos_lote`** con el botón **`📷 Mandar fotos de mis productos`**: el dueño manda varias fotos, **una sola llamada** las clasifica (nombre + descripción + **precio solo si está escrito**), se publican todos con su foto y se reescribe el copy; el paso se queda abierto para mandar más. **(5) 🐞 Dos bichos cazados en el camino (trampas 40 y 41):** `directorio_servicios` **no tiene columna `orden`** y el `catch` callado dejaba la lista de productos vacía (el copy salía sin catálogo y sin ningún error), y el copy **no se generaba** cuando la tienda solo tenía productos (contexto). **(6) 📝 Y SE ARREGLÓ LA TIENDA DEL EJEMPLO:** con el rehacedor nuevo (`__tia_mejorar_tienda.php`) «Novedades Gaela Chimbote» pasó de **353 caracteres sin botones** a un copy de **1676 caracteres con 3 bloques, 2 botones de WhatsApp y 1 de llamada**, y de **0 a 3 opiniones** (nota 4.7) — comprobado por HTTP en su ficha. Probado con **`__tia_prueba16.php`** (**36 ✅ · 0 ❌ · 0 restos**) y sin regresiones en la **15** (**57 ✅ · 0 ❌**). **Desplegado:** `includes/tienda_ia.php`, `includes/config_tienda_ia.php`, `crear_tienda_ia.php` y `assets/js/tienda_ia.js` (**`?v=11`**). |
| **2026-09-16 — un dueño, VARIAS TIENDAS (y los submenús en ventana emergente)** | **🆕 EL BOTÓN QUE FALTABA + LOS MODALES** (orden del jefe: *«cuando acabo de crear una tienda no hay forma de crear otra tienda… un usuario puede tener varias tiendas, no olvidar: un mismo usuario puede tener varios negocios. Implementa la opción de poder crear una nueva tienda al acabar de crear una y pon submenús en popup escondidos, ejemplo al abrir la cámara puede ser un modal que carga las 2 opciones, y asimismo mete otros modales donde lo creas conveniente»*). **(1) 🆕 `Crear otra tienda`** en la tarjeta final (`fin`), en `publicado` y en el `mas_productos` viejo: cierra la conversación que estaba abierta (`tienda_ia_cerrar_conversacion()` → `abandonada`) y abre **una limpia en el paso de las fotos** (`tienda_ia_nueva_tienda()`, con saludo propio: *«esta es otra tienda, de otro negocio»*), sin tocar **nada** de lo que ya publicó. La puerta contesta con **`nueva_tienda: true`** y el navegador **borra la pantalla y pinta la nueva**. **(2) 🐞 El bicho de raíz:** la conversación terminada **seguía `en_curso`** y por eso el dueño volvía a ver la misma tarjeta para siempre; y el cierre del flujo viejo (`tienda_ia_publicar_flujo()`) **reabría** una conversación que el publicador ya había cerrado (le faltaba poner el `estado` antes de guardar). **(3) 🪟 Tres ventanas emergentes escondidas** (`hidden`, se cierran con Cancelar, tocando el fondo o con Escape): **📷 la cámara con sus 2 opciones** (el 📷 del compositor y el botón `📷 Abrir cámara` ya no disparan la cámara de frente), **🏪 Mis tiendas** (todos sus negocios con su enlace, desde el menú ⋯) y **🆕 la confirmación de crear otra tienda** (con el aviso de que lo publicado no se toca). **(4) 🛡️ El único freno:** una tienda publicada **sin dueño todavía** (falta el WhatsApp) no se puede abandonar — quedaría huérfana — así que se le pide el número antes. **(5)** El tope del día pasó de **2 a 5 tiendas**. **(6) 🛍️ «¿Cómo puede el usuario seguir creando más productos?»** (segunda pregunta del jefe, el mismo día): al llegar a 2 el asistente dejaba un **callejón sin salida** (*«Ya tienes tus 2 productos 👌 Si quieres más, entra con tu número y tu clave»*, sin botón ni enlace) y en `publicado` lo mismo pero **sin decir nada**. Ahora el tope de 2 es **por RONDA**: el botón **`🛍️ Agregar otro producto`** abre otra (`tienda_ia_ronda_productos()`, con su aviso *«¡Vamos con otro! Ya llevas 2 productos en «X»»*), un contador nuevo (`productos_total`) **numera** bien (*«Producto 3:»* en vez de *«tu primer producto»*) y el mensaje final lleva el enlace al **inventario con cámara y voz** (`productos.php?n=ID#crear`, sin tope), la misma puerta que el botón **`➕ Producto`** de cada negocio en la ventana **🏪 Mis tiendas**. Probado con **`__tia_prueba15.php`** (**57 ✅ · 0 ❌ · 0 restos**) y visto con **`__tia_ver.py modales`** (Chrome aparte: las tres abren con un toque real, sin errores de JS, y Escape las cierra). **Desplegado:** `includes/tienda_ia.php`, `api/tienda_ia.php`, `includes/config_tienda_ia.php`, `crear_tienda_ia.php`, `assets/js/tienda_ia.js` (**`?v=10`**) y `assets/css/tienda_ia.css` (**`?v=9`**). ⚠️ **Ojo con la trampa 39**: la primera subida se hizo **sin cambiar el `?v=`** y Cloudflare siguió sirviendo el JS viejo (se cazó comprobando por HTTP que no traía `tia-tienda__mas`) — por eso las versiones quedaron en **10 y 9**. |
| **2026-09-16 (mañana) — el compositor, 2.ª vuelta** | **💬 EL ÁREA DE MENSAJES IDÉNTICA A LA DE WHATSAPP (orden del jefe, con una CAPTURA de su celular en la mano: *«elimina inmediatamente ese bloque de íconos que tapan toda la pantalla en modo mobile, y en modo mobile el bloque de cámara/galería debe ser visible solo cuando se necesite, no estar siempre visible… y hazlos idénticos al WhatsApp, hasta en color, sobre todo en modo mobile, IDÉNTICO»*).** Lo que estaba mal y se arregló: **(1) 🔴 EL BICHO GORDO — `[hidden]` no ganaba** (trampa 37): como `.tia-emoji` y `.tia-foto` traen `display: grid`, el **cajón de los 48 emojis** y los **dos botones de cámara/galería** se veían **en todos los pasos** y la barra medía **199 px**; ahora hay **`[hidden] { display: none !important; }`** y el 📎/📷 del compositor **solo salen en los pasos que piden foto** → la barra quedó en **88 px**. **(2) Los colores son los de WhatsApp**: el botón redondo pasó del **negro** al **VERDE `#00a884`** con el ícono blanco, los íconos a **gris `#54656f`**, el campo a **`#111b21`** y el texto de ayuda **«Mensaje»** a **`#8696a0`**; la franja de abajo **ya no tiene caja propia** (es del color del chat, como WhatsApp) y queda **una pastilla blanca** con sombra de 1 px. **(3) El 😊 pasó a la IZQUIERDA del campo** (como WhatsApp) y el cajón de emojis **se cierra solo al cambiar de paso**. La barra quedó así: **`[😊] [ Mensaje…… ] [📎] [📷] [🎤 verde]`** con los íconos de foto solo cuando se necesitan. **Verificado con `__tia_ver.py`** (Chrome aparte, 390 px, sin tocar el navegador del jefe): una sola fila, verde `rgb(0,168,132)`, grises `rgb(84,101,111)`, `[hidden]` funcionando y **88 px** de alto — y las fotos del antes/después quedaron en `__tia_vista_movil_*.png`. **Desplegado:** CSS **`?v=7`**, JS **`?v=8`** y la página. |
| **2026-09-15 (noche, 3.ª parte) — versión 7** | **🎯 DOS PUERTAS Y EL FLUJO DEL TIPO DE NEGOCIO** (las 9 órdenes del jefe, en el **§1sexies**; el paso a paso, en el **§2ter**; el compositor, en el **§2quater**; las puertas, en el **§8**). **(1) Solo quedan dos formas de crear una tienda:** **El caminante** (intacto, sigue en 200) y **El maestro**; las otras tres puertas (`registrar_negocio.php`, `crear_negocio.php`, `guardar_asistente.php`) se **borraron del hosting** (dan 404, verificado con `curl`) y **todos los enlaces del sitio** (portada, menú, pie, panel, buscador, rubro, reclamos, registro, sitemap, chat 🥷) apuntan a `/crear-tienda`. **(2) 📷 8 fotos sí o sí:** con 4 el asistente dice *«Todavía no podemos empezar 📸 Trabajamos a partir de 8 fotos»* y **no avanza** (y no se pierde ninguna). **(3) 🏪🛵🌐 El tipo de negocio** (paso nuevo `tipo`) con sus tres ramas: tienda o local → **su ubicación y su zona** · vendedor ambulante → **su horario** · vende por internet → **los distritos donde entrega**, en **tarjetas 2×2 con miniatura y color** (hechas por nosotros: los distritos no tienen foto) y el botón **azul «🗺️ Todos los distritos»** debajo. **(4) 🎉 La tienda SE PUBLICA AL INSTANTE** con el nombre y el rubro confirmados —**sin teléfono y sin dueño**, y con **sus 2 productos** escritos por la IA (con la foto donde los vio y su descripción)— y sale el **menú de edición en grilla de 2 columnas**: `🛍️ Editar mis productos` · `🖼️ Editar la portada` · `📝 Editar otro dato` · `✅ No editar nada` (sin botón de eliminar y sin hablar de «editar»). **(5) ✏️ Editar un producto** = su **foto** (nueva o una de las 8) y su **descripción** (él escribe y **la IA le da el formato**, respetando lo que dijo); **la portada** se cambia con una foto nueva que pasa a ser la primera de la galería. **(6) 📱 El WhatsApp es LO ÚLTIMO**, con el texto que dictó el jefe (*«sería bueno que tus clientes también puedan llamarte o escribirte por WhatsApp… ¿a qué número quieres que te escriban?»*), y es lo que le da **dueño** a la tienda (cuenta = su número + clave, que se le muestra una sola vez). **(7) 💬 La barra de escribir es la de WhatsApp**, copiada «igual, muy igual»: campo **«Mensaje»**, 😊 emojis, 📎 adjuntar, 📷 cámara y el **micrófono negro redondo** que **dicta en vivo** y **se vuelve ➤ enviar**, con **corte automático a los 2 minutos**. **(8) Los rubros que se ofrecen para sumar** salen ahora del **mapa de afinidades** del rubro elegido (antes, en el flujo nuevo, salían los rubros más usados del sitio y el menú quedaba raro: a una cevichería le ofrecía «Melamina / Muebles»). **Cazado y arreglado en el camino (trampas 33-36):** publicar sin dueño insertaba `dueno_id = 0` y **la llave foránea `fk_negocio_dueno` lo rechazaba** (el dueño veía «No pude publicar tu tienda 😅») · el paso `editar` se guardaba **después** de guardar y el dueño volvía al menú de rubros (¡y cada toque **publicaba otra tienda**!) · `tia_opciones()` **no dejaba pasar las banderas de dibujo** (tarjetas, azul, grilla) y todo salía como chips sueltos · y **`tienda_ia.css` seguía sirviendo la versión vieja** en la URL `?v=5` ya cacheada (el compositor salía sin estilos: se subió a **`?v=6`**, el JS a **`?v=7`**). Probado con **`__tia_prueba13.php`** (**72 ✅ · 1 ❌**, y el único ❌ era de la propia prueba: buscaba el enlace dentro de un `json_encode` que escapa las barras): con 4 fotos no avanzó, con 8 sí ✅ · distritos en tarjetas con color + el azul ✅ · **tienda #1776 publicada y activa, sin dueño, con la descripción de la IA, 2 productos y 8 fotos**, enlace **HTTP 200** ✅ · el menú de edición con sus 4 botones en grilla y **sin «eliminar»** ✅ · la descripción del producto reescrita por la IA, su foto y la portada cambiadas ✅ · el WhatsApp al final con **dueño #45** puesto ✅ · **0 restos** — y con **`__tia_prueba14.php`** (**18 ✅ · 0 ❌**) las **tres ramas** del tipo de negocio. |
| **2026-09-15 (noche, 2.ª parte) — versión 6** | **📸 OCHO FOTOS Y «CONFIRMA ANTES DE PUBLICAR»** (las 5 órdenes del jefe, en el **§1quinquies**; el paso a paso, en el **§2bis**; la lectura y la seguridad, en el **§5octies**). **(1) Mínimo 8 fotos** (eran 5) y el asistente **dice qué fotos quiere**: *«Afuera de tu tienda 🏪 · dentro 🛋️ · tus productos 📦 · tus máquinas o herramientas 🧰 · tu personal 👥 · tu tarjeta 💳 · tu folleto 📄»* — y a la IA se le dice también, porque **una tarjeta o un folleto traen nombre, teléfono y dirección**. **(2) 🔴 LA SEGURIDAD DEL NÚMERO** (lo que pidió: *«se puede dañar una tienda que ya exista… preguntaría si es el número correcto»*): si el WhatsApp que da **ya es de alguien**, el asistente **NO publica ni actualiza nada** y pregunta —paso nuevo `tel_ocupado`— *«Ese número ya tiene la tienda «X». ¿Es tuya?»*; si dice que no, se le borra el número y se le vuelve a preguntar. Así **nada suyo cae en la cuenta de otra persona** (ni se le pegan datos a la tienda que ya existía). **(3) El nombre y el número se confirman ANTES de publicar** (nombre con un toque desde el letrero, número en su paso propio). **(4)** Se completaron los campos de **`registrar_negocio.php`** que faltaban: **📍 la dirección** (paso nuevo, y si la IA la leyó en las fotos **se la propone**) y **📘 las redes** (Facebook, Instagram y TikTok en una sola pregunta, opcional), y **`vendedor` pasó de 3 a los 5 tipos de esa página** (`fisica · ambulante · domicilio · nacional · mayorista`). El orden final: `fotos(8) → nombre_ok → rubro_ok → rubro_mas → tel_leido → whatsapp → PUBLICAR (en silencio) → vendedor → direccion → distrito → horario → redes → ESTRENO`. **Cazado y arreglado en el camino (trampas 31 y 32):** un chip de otro paso se guardaba como si fuera una dirección («Todas las anteriores» quedó de dirección) y el reparto de las redes partía la frase por palabras (`instagram="insta"`). Probado con **`__tia_prueba10.php`** (motor, 8 fotos, la dirección y las redes), **`__tia_prueba11.php`** (navegador, 8 fotos por `foto[]`) y **`__tia_prueba12.php`** (🔴 **la seguridad: un cebo con tienda real al que le dan su número → el asistente pregunta, no publica y el cebo queda byte a byte idéntico** ✅). **0 restos** (tiendas, cuentas, fotos y conversaciones de las 3 pruebas borradas). |
| **2026-09-15 (noche) — versión 5** | **📷 «CREA TU TIENDA CON NOSOTROS»: LAS 5 FOTOS PRIMERO** (las 7 órdenes del jefe, en el **§1quinquies**; el paso a paso, en el **§2bis**; la lectura de las fotos, en el **§5octies**). El saludo dice *«Crea tu tienda con nosotros 🚀 Mándame 5 fotos…»*, **el botón abre la galería del celular** (y en ese paso **no hay botón de cámara**), el dueño manda **mínimo 5 fotos** y **UNA sola llamada de visión a 1600 px** saca de ahí **el nombre del letrero, el rubro, el teléfono del aviso, la dirección, si es local o delivery y lo que vende** (ficha de 8 etiquetas con lector tolerante). Con eso: **nombre en un toque**, **rubro entre los reales del directorio**, **el teléfono se pregunta siempre** (puede ser el de la imprenta: es el único dato que puede hacer daño) y **la zona se propone sola** si el letrero traía la dirección. **En cuanto da su WhatsApp la tienda YA ESTÁ PUBLICADA** — pero **sin decírselo**: la conversación sigue `en_curso`, cada respuesta de después la **actualiza en vivo** (sin que él sepa que edita) y **el enlace, su usuario y su clave se le muestran al final, como estreno** (orden textual del jefe: *«hazlo creer que recién está creando su sitio, pero en sí el sitio ya está creado»*). Los botones dejaron de decir «✏️ Editar algo»: ahora dicen **`📝 Cambiar algún dato`**. **Cazado y arreglado en el camino (trampas 26-30):** el modelo contesta las 8 etiquetas **en un solo renglón** (el lector por líneas dejaba la ficha vacía y le mostraba el protocolo crudo al dueño) · **`trim()` con caracteres multibyte rompía el UTF-8 y borraba el nombre del letrero** · la publicación silenciosa **no puede cerrar la conversación** (se cortaría el flujo) · la descripción **necesita el contexto de las fotos** porque el relato del dueño ya no existe · y las conversaciones quedan **huérfanas** al borrar su cuenta (herramienta nueva `__tia_limpiar_conv_huerfanas.php`). Probado con **`__tia_prueba10.php`** (motor: lectura con fotos reales del sitio, mínimo de 5, publicación silenciosa, estreno) y **`__tia_prueba11.php`** (navegador: página + CSRF + **5 fotos por `foto[]`** + estreno), más **`__tia_parser_test.php`** (local y gratis). **0 restos**: tiendas #1757 y #1758 borradas, 0 cuentas, 0 fotos, 0 huérfanas. |
| **2026-09-15 (tarde, 2.ª parte) — versión 4** | **LAS 15 ÓRDENES DE LA TARDE** (tabla completa en el §1quater): **🏷️ rubros múltiples** (principal + otros hasta 4, guardados en `directorio_negocio_rubros`: la tienda sale en todos sus rubros) · **🧠 la IA PIENSA** si el negocio tiene local o trabaja llevando (`LÍNEA 3: LOCAL/DOMICILIO/IGUAL`) y le sale el chip **`🚚 No tengo local: lo llevo a su casa`** · **🗺️ «Todas las anteriores»** en la zona (base Chimbote + la nota en la descripción) · **📝 el copy de la descripción lo escribe la IA** con lo que vio en las fotos · **🚀 LA PUBLICACIÓN ES AUTOMÁTICA** (fuera el paso de aprobación y el botón «Publicar mi tienda»; se publica al terminar el horario y lo que se ve es *«Así ha quedado tu tienda»* con su enlace y **`✏️ Editar algo`**, que actualiza la tienda en línea) · **🔴 botón rojo** para guardar los datos con el aviso de que la clave no se vuelve a mostrar · **📷 «Abrir cámara» / «Abrir galería»** en vez de «otra» · **la tira con las fotos ya subidas** para ponérselas al producto (copiando el archivo) · **fuera el botón de eliminar** y **fuera el WhatsApp de «más productos»**: al segundo producto **felicita, da el enlace y termina** · **el mensaje al WhatsApp del jefe con el enlace de la tienda** (regla de oro). Probado con `__tia_prueba8.php` (v4) y `__tia_prueba9.php`: ✓ 0 restos **y las fotos de la tienda de prueba del jefe intactas** (¡ojo con la trampa 23 de la limpieza!). |
| **2026-09-15 (tarde, 3.ª parte)** | **EL COPY DE VERDAD, TIENDA Y PRODUCTOS:** el jefe vio su tienda de prueba publicada y dijo *«tiene muy mal copyright, tiene el mismo que el usuario escribió; la IA no interfirió, no la mejoró»* (era cierto: se publicó con la versión 3, que aún no escribía copy). Se hizo: **(1) el copy de los PRODUCTOS también lo escribe la IA mirando su foto** (`tienda_ia_ia_descripcion_producto()`, antes iban sin descripción) y **(2) la sonda `__tia_copy_tienda.php` + `__tia_run_copy.py`** para mejorar el copy de una tienda **ya publicada** (lee sus fotos, enseña el antes y el después y solo escribe con `go`). Su tienda **#1741 «Willy Jara»** quedó mejorada: la descripción pasó del texto dictado a *«Willy Jara te lleva arena gruesa, fina, piedra chancada y desmonte hasta tu obra en Chimbote. **Sus camiones azules se ven trabajando en la calle**…»* (los camiones azules salen de **mirar sus fotos**) y sus 2 productos ya tienen su descripción. Verificado por HTTP en su ficha. |
| **2026-09-15 (mañana) — versión 3** | **EL REDISEÑO DE USABILIDAD:** **página sola de pantalla completa** (fuera cabecera y pie del sitio; la barra de escribir ya no es `position: fixed`, que era lo que hacía que **el pie del sitio apareciera dentro del área de chat**), **🖼️ galería que acepta VARIAS fotos** (1 a 8, en un solo envío y **una sola llamada a la IA**), **mensajes reescritos** y **🚪 el arranque que distingue logueado de no logueado**. Medido con Chrome sin cabeza: 0 elementos debajo de la barra, 0 pies de sitio en la página, 16 px en el campo. |
| **2026-09-15 (rediseño de usabilidad)** | **VERSIÓN 3 — «Hazlo como página, una página normal» (órdenes del jefe, muy enojado con la usabilidad):** **(1) página sola de pantalla completa**: `crear_tienda_ia.php` **ya no incluye cabecera ni pie del sitio** y la barra de escribir **dejó de ser `position: fixed`** (era lo que hacía que el **pie del sitio apareciera dentro del área de chat**); ahora es una columna `100dvh` donde **nada queda debajo de la barra** (medido: 0 elementos). **(2) 🖼️ Galería MÚLTIPLE**: se pueden marcar varias fotos de una vez, se suben **en un solo envío** (`foto[]`) y **la IA las mira en UNA sola llamada** (3 fotos = 2,2 s y 951 tokens, antes eran 3 llamadas); **de 1 a 8 fotos** (con 1 ya se publica, desde la 3.ª lo recomienda). **(3) Mensajes reescritos** otra vez (más cortos y diciendo qué se espera). **(4) Nuevo paso `arranque`**: el asistente **distingue si estás logueado o no** —sin cuenta invita a crear su **primera tienda**; con tiendas ofrece **agregar un producto** o **crear otra**— y al publicar **siempre** ofrece **el primer producto**. **(5) El número se pide como «el número de tu tienda**: a ese te escriben tus clientes». **(6) Los productos se proponen con lo que la IA VIO en las fotos** (`datos['visto']`). **(7)** Se le preguntó y eligió: **página sola** y **2 productos** con el asistente. Probado con `__tia_prueba8.php` (motor: arranque en sus 3 caras, lotes de fotos, captura rechazada, 2 productos, dueño que vuelve) y `__tia_prueba9.php` (**el camino real por HTTP: página + CSRF + `foto[]`, 3 fotos en un POST**) + medición del diseño con Chrome sin cabeza. **0 rastros** de las dos pruebas. |
| **2026-09-14 (noche, 3.ª parte)** | **SI EL WHATSAPP YA EXISTE, SE ACTUALIZA (orden del jefe: *«se le dice que vas a actualizar su tienda con los nuevos datos y le vas a agregar su nuevo producto; nunca se dice que no se puede»*):** si el número ya tiene tienda **y el nombre que escribió es de una tienda suya**, el asistente **actualiza esa tienda** (rubro, zona, cómo vende, WhatsApp, horario, descripción; **las fotos nuevas van de portada** y las viejas se quedan) y **no toca el nombre ni el enlace**; después le ofrece **agregarle un producto nuevo**. Si el nombre no es de ninguna tienda suya, le crea **un negocio nuevo a su nombre** (puede tener dos). En ningún caso se le dice «no se puede» ni se le duplica la tienda. Probado con `__tia_prueba7.php`: misma persona 3 veces → **1 tienda actualizada (2 productos, 6 fotos, la tilde conservada)** + 1 tienda nueva con otro nombre, y **0 rastros** al final. |
| **2026-09-14 (noche, 2.ª parte)** | **TEXTOS CORTO Y DIRECTO (orden del jefe: *«me aburriste, hazlo minimalista, dinámico, más directo»*):** se reescribió **todo** el guion (1 o 2 líneas por mensaje), los 40 avisos y errores intermedios, las tarjetas del navegador y los prompts de la IA («UNA frase, máximo 15 palabras»). Antes: *«¡Hola! Soy El maestro y te voy a ayudar a armar tu tienda. Aquí no hay formularios…»*. Ahora: *«¡Hola! 🛠️ Soy El maestro. Te armo la tienda en 5 minutos 🚀 Te pregunto, me contestas (o me hablas 🎙️) y ya.»* + *«¿Cómo se llama tu tienda?»*. 🐞 **Y se arregló un fallo gordo que solo se veía en el navegador: en la primera pantalla FALTABA LA PREGUNTA** (solo salía el saludo). |
| **2026-09-14 (noche)** | **Cierre pedido por el jefe:** al publicar, el asistente avisa que **las fotos son nítidas y claras** y que **las comprime** para que carguen rápido (sin hablar nunca de «megabytes»), y le da **la hora exacta** en que ya se verá todo (*«a partir de las 21:10 tu sitio ya estará listo»*, calculada en hora de Lima + 10 minutos, `tienda_ia_hora_aviso()`). El mismo recordatorio va en cada producto y en la tarjeta final. Medición del WebP con foto realista: **583 KB → 66 KB (89 % menos)** + versiones de 18,6 KB y 6,1 KB. Prueba: `__tia_prueba6.php`. |
| **2026-09-14 (tarde)** | **VERSIÓN 2 (órdenes del jefe):** **📷 Cámara + 🖼️ Galería** en cada paso de foto · **fuera la pregunta de cuántos productos** (primero la tienda, y el texto que aclara *«todavía no subas productos, estás creando tu tienda»*) · **la tienda se publica primero** y después se le ofrece **crear su primer producto** · **felicitación + eliminar con un clic o crear otro** · **2 productos con el asistente** y, para más, **WhatsApp al jefe (955041690) con el mensaje ya escrito** · **cuenta del dueño con usuario = su WhatsApp y contraseña de 3 letras + 1 número** (sin O ni 0) con el botón de guardarla en su propio WhatsApp · **se entra con el número de teléfono** (`login()` + «Correo o número de teléfono») · **abrir la página ya no escribe en la base**. Probado con `__tia_prueba5.php`: cuenta creada y **entrada verificada**, 2 productos en línea, borrado en un clic, mensaje del WhatsApp correcto y **0 rastros al final**. |
| **2026-09-14 (mañana)** | **Módulo creado, desplegado y probado.** Página privada `/crear-tienda` (más `?modo=producto`), motor con la **clave propia** de DeepSeek y visión, `reasoning_effort: none` (medido), 2 tablas nuevas, pestaña **🛠️ El maestro (tiendas IA)** en el Súper Admin, 2 opciones nuevas en el chat 🥷, botón en el panel del dueño y `redirect` en el registro. 12 archivos subidos por FTP; 4 sondas de prueba (todas borradas del hosting). **Ninguna tienda de prueba quedó publicada** (la publicación se probó en simulacro y la base quedó en 0 conversaciones y 0 llamadas). Costo medido: **US$ 0,000106 por llamada de texto y US$ 0,000137 con foto** (≈ US$ 0,002 por tienda de 1 producto). |

---

## §16. 🎵 LA CANCIÓN DE LA TIENDA (el jingle de 40 segundos) — 2026-09-17

> **Cuándo leer esto:** cuando haya que tocar, revisar o explicar **la canción que se le hace a cada
> tienda** (el jingle de 40 s que suena en su ficha), el saldo de las cuentas de Treblo, o cuando se
> quiera generar la canción de una tienda a mano.
> **Motor:** `deploy/includes/cancion.php` · **ajustes y las 4 claves:** `deploy/includes/config_cancion.php`
> · **obrero:** `deploy/api/cancion_worker.php` · **reproductor:** `deploy/includes/cancion_player.php`
> · **panel:** `deploy/includes/vista_canciones_admin.php` (Súper Admin → 🎵 Canciones (Treblo)).
> **Estado:** ✅ **EN PRODUCCIÓN y probado con 3 tiendas de verdad** (2026-09-17).
> 🔴🆕 **SEGUNDA ORDEN DEL JEFE (2026-09-17, la que manda HOY) — «NO RECORTES NADA Y PONLO A SONAR SOLO»:**
> *«recibe las canciones con cualquier duración que el Treblo.com te lo envíe: no pierdas tiempo
> recortando ni reeditando; si te lo envía de 30, 60 segundos o lo que sea, tú solo lo pones en la web
> con auto reproducir… sé que no suena si entran directo, pero la mayoría entra por clic, ahí sí se auto
> reproducirá. Comienza con las tiendas ya creadas y me dices para cuántas tiendas te alcanzó esas 4 API»*.
> Eso cambió tres cosas: **`CANCION_RECORTAR = false`** (se guarda **tal cual llega**, sin ffmpeg: las 3
> canciones de prueba quedaron de **0:39, 0:30 y 0:39**), **`CANCION_PLAYER_AUTOPLAY = true`** (suena sola
> y, si el navegador la bloquea, el primer toque del visitante la arranca) y **el tope diario fuera**
> (`CANCION_MAX_POR_DIA = 0`), con una herramienta nueva para cantar **en tanda** las tiendas que ya
> existen: **`python __cancion_lote.py plan|encolar|tanda|procesar|estado`** (**§16.12**).
> **El dato que frena todo: hay 1 666 tiendas esperando canción y los créditos están en 0** (§16.6).
> **⚠️ Y con 0 créditos:** la cuenta 1 tenía 1 200 (12 canciones) y las otras tres estaban vacías
> (§16.6). Antes de crear tiendas nuevas, leer el §16.6: la canción es lo primero que se queda sin gas.

### 16.1 Lo que pidió el jefe (la carta del 2026-09-17)

1. *«Cada vez que se cree una tienda, se le genere automáticamente una canción comercial, pegajosa y de
   40 segundos»*, para **fidelizar la visita** (que el cliente se quede y vuelva).
2. El prompt lleva **el nombre de la tienda y su rubro** (que cada jingle sea único).
3. **API de Treblo (Sonauto)**, `POST /v1/generations/v3`, con el parámetro `length_range`.
4. Al crear la tienda el robot **lanza la petición, espera (polling o webhook) y guarda el audio**.
5. El archivo se guarda **en el hosting** y su ruta queda **en la base de datos** para reproducirla.
6. **4 claves** (4 cuentas) que se usan **en orden**, pasando a la siguiente cuando una se agota, con
   **registro del consumo** de cada una y **aviso al jefe** cuando estén por agotarse.
7. Probar con **3 tiendas de ejemplo** y documentarlo en esta guía.

Todo eso está hecho. Lo que **no** decía la carta (y ahora se sabe) está en el §16.2 y el §16.6.

### 16.2 🔴 LA VERDAD DE LA API DE TREBLO (SONAUTO) — medido, no supuesto

La carta se equivocaba en tres cosas. Comprobado con la API delante el 2026-09-17
(esquema oficial: `https://api.sonauto.ai/openapi.json`; la documentación web responde 403 a los robots):

| Lo que decía la carta | Lo que de verdad pasa |
|---|---|
| «Se debe usar `length_range`» con 40 s | ✅ Existe, **pero solo acepta múltiplos de 30** (0..270 y 30..300). **`[40,40]` → 422** (`Input should be a multiple of 30`). El rango útil es **`[30,60]`** |
| «solo acepta duraciones múltiplos de 30» | ✅ Cierto. Y **la canción de 40 s exactos la hace el motor**, no la API (§16.3) |
| «4 API keys para rotar créditos» | ✅ Cierto, pero **1 canción = 100 créditos** y **las cuentas 2, 3 y 4 estaban en 0** (§16.6) |

Y estas otras cosas **no estaban escritas en ninguna parte** y se descubrieron a golpes (§12, trampas 42-45):

* **`tags` es una lista cerrada.** Un tag inventado —«catchy commercial jingle», «upbeat latin pop»,
  «spanish vocals»— **rechaza TODA la petición** con `422 «Invalid tags: …»` y **no genera nada**.
  Los que se comprobó que valen: `jingle · commercial · pop · latin pop · upbeat · happy · bright ·
  modern · catchy · spanish · dance · electronic · energetic · acoustic · chill · warm · tropical ·
  reggaeton · cumbia · salsa · indie pop · vocal · female vocalist · male vocalist · duet · kids ·
  party · celebration · uplifting · fun · smooth · percussion · brass · guitar · piano · handclaps ·
  anthem`. Los que **no**: `catchy commercial jingle · upbeat latin pop · spanish vocals · cheerful ·
  synth pop · good vibes · whistling · corporate · advertising`.
* **No se pueden mandar `tags` + `lyrics` + `prompt` juntos** («cannot provide all three tags, lyrics,
  and prompt»): **dos como máximo**. El motor manda **letra + prompt** (la letra en español es lo que
  asegura que se cante el nombre de la tienda); `negative_tags` no cuenta para esa regla.
* **Estados:** `PENDING · GENERATING · SAVING · SUCCESS` (+ fallos). **`SAVING`** (guardando el audio en
  su CDN) no está en su documentación y el motor lo tomaba por un fracaso: ahora **solo
  FAILURE/ERROR/CANCELED son fracaso** y cualquier otro estado se espera.
* **El audio vive en `cdn.treblo.com` y el enlace es temporal:** hay que bajarlo enseguida (el motor lo
  descarga en cuanto la tarea está lista y lo archiva en el hosting).
* **`GET /v1/credits/balance` es GRATIS** (`{"num_credits":…,"num_credits_payg":…}`): con eso el motor
  sabe cuánto queda y en qué cuenta, sin gastar nada.
* **El cobro es por canción y da igual la duración**: una canción de 196 s costó lo mismo (100 créditos)
  que una de 95 s. Por eso se pide `[30,60]` y no más.
* **`output_format: mp3` + `output_bit_rate: 128`**: MP3 lo entiende cualquier celular (iOS incluido) y
  se puede cortar también sin ffmpeg (los fotogramas del MP3 son independientes).

### 16.3 ⏱️ LOS 40 SEGUNDOS EXACTOS (la receta que sí funciona) — **HOY APAGADA POR ORDEN DEL JEFE**

> ⚠️ **Esto ya NO se usa** (orden del 2026-09-17: *«no pierdas tiempo recortando ni reeditando»*).
> `CANCION_RECORTAR = false` → el audio se guarda **tal cual llega** y **ffmpeg no se toca** (de hecho el
> binario podría borrarse: solo hace falta si alguien vuelve a encender el recorte). Se deja escrito
> porque es la receta medida y porque algún día puede volver a pedirse.

La API **no puede** entregar 40 s. Así que se pide **`length_range: [30,60]`** (de ahí sale material de
sobra) y el motor lo deja en **40,000 s clavados** con una sola orden de ffmpeg:

```
ffmpeg -y -i <lo que devolvió la API> \
       -af "apad,atrim=0:40,afade=t=out:st=38:d=2" \
       -c:a libmp3lame -b:a 96k -ac 2 <destino.mp3>
```

* **`apad,atrim=0:40`** es el corazón: `apad` rellena y `atrim` corta en el segundo exacto.
* **El fundido** son los 2 últimos segundos (`afade=t=out:st=38:d=2`), como pedía la carta.
* Si la canción de la API dura **más de 40 s**, se toman **sus últimos 40 s** (con `-ss <duración-40>`):
  así el jingle **termina donde termina la canción** y el fundido cae sobre el final de verdad.
* Si durara **menos de 40 s**, se repite con `aloop=loop=-1:size=2e+09` y se corta igual: nunca queda un
  hueco de silencio.

**🔴 Lo que NO funciona (medido, decodificando el audio, no el encabezado):** `-t 40` deja el archivo en
**39,975 s** (el MP3 de la API trae su propia etiqueta de retardo del codificador y el corte se come
~25 ms), y `-stream_loop -1` con `-t 40` deja **39,849 s**. Con la receta de arriba, el archivo queda en
**1 764 000 muestras = 40,000000 s** exactos (comprobado con `ffprobe` **y** contando las muestras
decodificadas), pesa **469 KB** (96 kbps estéreo) y suena en cualquier celular.

### 16.4 🎬 FFMPEG EN EL HOSTING (no venía: se le subió)

El hosting **no trae ffmpeg** (comprobado con `__sonda_cancion_cap.php`: `which ffmpeg` no lo encuentra,
`/usr/bin/ffmpeg` no existe) pero **sí deja ejecutar programas** (`shell_exec`, `exec`, `proc_open`
disponibles; `max_execution_time` 300 s; 9,2 TB libres; PHP 8.3.33). Así que se le subió un **ffmpeg
estático para Linux x64** (76 MB) con **`python __cancion_subir_ffmpeg.py go`** a:

```
/home/u196269909/domains/dechimbote.com/public_html/_cancion/ffmpeg
```

(la raíz viva del sitio es ese `public_html`, y el FTP `/` es exactamente esa carpeta: lo dijo `__DIR__`
en `__cancion_rutas.php`). El motor lo **prueba de verdad** antes de usarlo (`ffmpeg -version`): si algún
día no está o no se puede ejecutar, **no se rompe nada** — usa el **recorte de reserva en PHP puro**
(`cancion_recortar_php()`), que corta el MP3 en el fotograma exacto (error máximo 26 ms) y repite la
canción si hace falta, **pero sin fundido**. En `includes/config_cancion.php`:
`CANCION_FFMPEG`, `CANCION_FFPROBE` (opcional, no se subió) y `CANCION_FFMPEG_APAGADO` (para probar el
camino de reserva).

### 16.5 🧱 CÓMO ESTÁ HECHO (los archivos y el camino de una canción)

**Las tablas** (se crean con `instalar_canciones.php`; ⚠️ **no** se puede llamar `migrar_*.php` porque
**Hostinger responde 403 a ese nombre**, ver trampa 46):

* **`directorio_canciones`** — la cola **y** el archivo de cada canción: `negocio_id`, `nombre`, `rubro`,
  `distrito`, `estado` (`pendiente|generando|listo|error`), la **cuenta usada** (`clave_n`), el
  `task_id` de Treblo, el prompt y la letra que se mandaron, la **`ruta`** del MP3, su **`duracion`**
  (40.000), sus `bytes`, el error si lo hubo y los intentos.
* **`directorio_cancion_claves`** — el **registro del consumo** de las 4 cuentas: `saldo`, `agotada`,
  **`usadas`** (cuántas canciones lleva cada una) y, en la fila `n = 0`, el **latido** del módulo y la
  fecha del último aviso al jefe.

⚠️ **La ruta del audio NO se guarda en `directorio_negocios`**: la ficha la busca en `directorio_canciones`
por `negocio_id` (una sola verdad, y así no se toca la tabla de las 1 677 tiendas ni su vista).

**El camino de una canción (y por qué es así):**

1. **Al publicar la tienda** (`tienda_ia_publicar()`, justo después del aviso de Telegram y de HubSpot)
   se llama a **`cancion_encolar()`**: apunta la fila `pendiente` con el prompt y la letra ya escritos
   (`cancion_prompt()` y `cancion_letra()`, con el nombre y el rubro adentro). **No se espera nada**: el
   dueño recibe su tienda al instante.
2. **`cancion_disparar_obrero()`** avisa al obrero interno **al terminar la petición**
   (`register_shutdown_function` + `fastcgi_finish_request`): así el aviso no le cuesta ni un segundo al
   dueño. *(⚠️ Por eso mismo el disparo NO puede hacerse en medio de la publicación: cortaría la
   respuesta que el dueño está esperando.)*
3. **`api/cancion_worker.php`** (con `CANCION_WORKER_TOKEN`; sin la clave responde **403**) es el que
   trabaja: **pide** la canción a Treblo, **pregunta cada 6 s** cómo va, la **baja**, la **recorta a
   40 s**, la **guarda** y **anota** todo. Sigue trabajando aunque el que lo llamó ya se haya ido
   (`ignore_user_abort(true)`), con un presupuesto de 240 s.
4. **🫀 El latido (`cancion_latido()`, desde la ficha de cualquier tienda):** como el hosting **no tiene
   un cron nuestro**, el sitio se da cuerda solo: si hay una canción esperando y nadie la está moviendo
   (se cortó la conexión, el hosting reinició), **la siguiente visita a cualquier ficha la arranca**.
   Tiene un candado en la base (una vez cada 30 s) y se dispara **después** de que el visitante ya tiene
   su página. Esto **ya se vio funcionando solo** el 2026-09-17: dos canciones se procesaron sin que
   nadie las llamara, por el tráfico normal del sitio.
5. **La rotación de las 4 cuentas** (`cancion_clave_para_usar()`): se usa la primera que tenga crédito
   para una canción; si la API contesta **402/403/429** o la tarea falla por créditos, esa cuenta se marca
   **agotada** y se pasa sola a la siguiente. Cada uso suma en `usadas` y el saldo real se vuelve a
   preguntar (gratis) al terminar cada canción.
6. **Los topes de seguridad** (en `config_cancion.php`): `CANCION_MAX_POR_DIA` (6 canciones automáticas
   al día, para que una tanda de tiendas no se lleve el saldo en una hora), `CANCION_ACTIVA` (apaga el
   módulo entero) y el saldo mínimo (no se pide una canción que no se puede pagar).

**Los archivos, en una línea cada uno:**

| Archivo | Qué hace |
|---|---|
| `includes/config_cancion.php` | Las **4 claves**, el prompt, la letra, los tags válidos, los topes y los precios. Vive en `includes/`, que el `.htaccess` bloquea entero (**las claves nunca llegan al navegador**) |
| `includes/cancion.php` | El motor: cola, rotación de cuentas, la API, el recorte a 40 s, el aviso al jefe y las consultas del panel |
| `api/cancion_worker.php` | El obrero interno (lo llama el servidor, nunca el navegador) |
| `includes/cancion_player.php` | La barrita con ▶️ que se pinta en la ficha |
| `includes/vista_canciones_admin.php` | La pestaña del Súper Admin |
| `instalar_canciones.php` | Crea las 2 tablas (se autodestruye; ya se usó) |

### 16.6 💰 LOS CRÉDITOS: EL MURO (lo más importante de todo el módulo)

**Medido el 2026-09-17:**

| Cuenta | Créditos al empezar | Canciones |
|---|---|---|
| **cuenta 1** | **1 200** | 12 |
| cuentas 2, 3 y 4 | **0** | 0 |
| **TOTAL** | **1 200** | **12 canciones** |

* **1 canción = 100 créditos**, sin importar la duración.
* El sitio tiene **1 677 tiendas** y crea **entre 15 y 48 tiendas al día** (medido en la semana anterior).
  Con lo que había: **12 canciones**. Para todas las tiendas de hoy harían falta **167 700 créditos**.
* **De esas 12 se gastaron las 12**: 3 son los jingles de las 3 tiendas de prueba (las que se pueden oír)
  y **9 se fueron en las pruebas de la API** — incluidas **5 que se perdieron** al descubrir que un tag
  inventado hace fallar la petición *después* de haberla aceptado antes (la carta daba por hecho que los
  créditos sobraban). **Saldo hoy: 0.**
* **🔔 El aviso automático al jefe** (`cancion_avisar_si_pocos()`, por Telegram, con
  `CANCION_CREDITOS_AVISO = 400`): avisa cuando el saldo sumado baja de 4 canciones (una vez cada 12 h)
  y, cuando ya no alcanza ni para una, cada hora. Con la clave que pidió el jefe («avisar cuando queden
  menos de 100 créditos») el aviso llegaría **cuando ya no se puede hacer ninguna**: por eso el umbral
  está más arriba y se cambia en una línea.

**Lo que hay que decidir (jefe):**

1. **Recargar créditos** en las 4 cuentas (sonauto.ai; las claves ya están configuradas y el motor las
   rota solo). **La cuenta es simple: cada 100 créditos = 1 tienda cantada.** Con **12 000 créditos**
   (≈ 120 canciones) se cubren unos días de tiendas nuevas; para **las 1 666 tiendas que ya existen**
   harían falta **166 600 créditos** (≈ 1 666 canciones).
2. **O dejar la canción solo para las tiendas que él elija**: en **Súper Admin → 🎵 Canciones (Treblo)**
   está el botón **`🎵 Generar`** (se pone el id de la tienda) — y con `CANCION_ACTIVA = false` el módulo
   deja de cantar solo en cada tienda nueva.
3. **O darle más claves** de Sonauto (otras cuentas): se agregan **en una línea** a `CANCION_CLAVES` y el
   motor las rota solas en orden (cuenta 1 → 2 → 3 → 4 → …). Con 4 cuentas de 1 200 créditos al mes son
   48 canciones al mes; hacen falta cuentas con más saldo (o comprar créditos) para el directorio entero.

### 16.12 🎼 LA TANDA DE LAS TIENDAS QUE YA EXISTEN (orden del jefe, 2026-09-17)

*«Comienza con las tiendas ya creadas y me dices para cuántas tiendas te alcanzó esas 4 API.»*

La herramienta es **`python __cancion_lote.py`** (usa la sonda `__cancion_lote.php`, que se sube y se
borra sola en cada corrida):

| Comando | Qué hace | ¿Gasta créditos? |
|---|---|---|
| `python __cancion_lote.py plan` | Cuántas tiendas esperan canción, el saldo y **hasta dónde alcanza**, con las primeras 10 de la lista | **No** |
| `python __cancion_lote.py encolar [n] [orden]` | Apunta la tanda en la cola: **nunca encola más de lo que se puede pagar** (si quedan 300 créditos, encola 3) | **No** |
| `python __cancion_lote.py tanda [n] [orden]` | Apunta **y canta** de una sola vez | **Sí** (100 por canción) |
| `python __cancion_lote.py procesar` | Canta lo que esté en la cola, llamando al obrero en bucle hasta que la cola se vacíe **o se acaben los créditos** | **Sí** |
| `python __cancion_lote.py estado` | La cola, las listas, los errores y las últimas canciones | **No** |

**Órdenes de la tanda** (`vistas` por defecto): **`vistas`** (las que más se miran — las primeras son
*Sra. Cinthia*, *NOVEDADES GAELA CHIMBOTE*, *MADAÍ Restaurant Chifa*, *Plaza de Armas de Santa*,
*Essalud Coishco*…), **`nuevas`**, **`viejas`** (por id) y **`catalogo`** (las que tienen más productos).
Se saltan solas las tiendas que ya tienen canción y los **avisos de empleo** (no son un negocio con local).

**Lo que dice la foto de hoy (2026-09-17, medido con `plan`):**

| Dato | Valor |
|---|---|
| Tiendas en el sitio | **1 677** |
| Tiendas **activas sin canción** (las que esperan) | **1 666** |
| Créditos en las 4 cuentas | **0** |
| **Alcanza para** | **0 tiendas** |
| Recorte de la canción | **NO** (se guarda tal cual llega) |
| Auto reproducir en la ficha | **sí** |
| Tope de canciones por día | **sin tope** |

→ Con **0 créditos el módulo no canta ni una**: en cuanto el jefe recargue, **una sola orden**
(`python __cancion_lote.py tanda 1666 vistas`) apunta lo que los créditos alcancen y las va cantando sola,
y el mismo comando se puede volver a correr cuando recargue más.

### 16.7 🎧 EL REPRODUCTOR EN LA FICHA

Se pinta **debajo de los botones** de la tienda (WhatsApp/Llamar), en las **tres plantillas** (A, B y C),
y **solo en la plantilla activa** — como se hace con las opiniones: las otras dos viajan ocultas en el
mismo HTML, y si se pintara en las tres habría tres reproductores de la misma canción.

* 🆕 **MINIMALISTA, EN PLOMO Y BLANCO (orden del jefe, 2026-09-18: *«cambia el color negro por un color
  plomo y blanco, discreto, muy minimalista el reproductor»*):** la barra ya **no es negra**: fondo blanco,
  borde gris claro (`#e5e7eb`), botón ▶️ plomo sobre gris muy claro, textos y línea de avance en **plomo**
  (`#6b7280` / `#9ca3af`), sin sombras fuertes ni brillos. Y **se borró el texto «La canción de esta
  tienda 🎵»** (misma orden): ahora solo se ve **el nombre de la tienda · la duración**, el tiempo a la
  derecha y la línea de avance. ⚠️ `CANCION_PLAYER_TITULO` sigue definido pero **ya no se pinta**: no
  volver a ponerlo.
* Dice el nombre de la tienda y **su duración de verdad**
  (*«canción de 0:39 hecha con IA»*, *«de 0:30»*…): la que Treblo haya mandado, no un 0:40 fijo.
* **🎵 SÍ SUENA SOLA (orden del jefe, 2026-09-17 y confirmada el 2026-09-18: «la canción debe estar en
  autoplay»):** el `<audio>` va con **`autoplay`** y **`preload="auto"`** → **suena sola en cada
  visita**. Si el navegador lo bloquea (entrada directa, sin ningún clic), **no se pierde**: el
  reproductor queda con su botón ▶️ y **el primer toque/clic/tecla del visitante en cualquier parte de
  la ficha vuelve a intentar arrancarla** (ese gesto ya es el permiso que pide el navegador), más un
  reintento con `canplay`. Para que **nunca** suene sola: `CANCION_PLAYER_AUTOPLAY = false`. Para que
  suene sola **solo la primera visita**: `CANCION_PLAYER_SOLO_UNA_VEZ = true`.
* 🆕 **SOLO SUENA CON LA PÁGINA DELANTE (orden del jefe, 2026-09-18: *«solo se debe escuchar si el usuario
  está en la web; si bloquea su celular o se va a otra aplicación, como WhatsApp, ya no se debe seguir
  escuchando»*):** el reproductor **SE PARA** en cuanto la página deja de estar a la vista —
  `visibilitychange` (bloquear el celular, cambiar de aplicación o de pestaña), más `blur`, `pagehide` y
  `freeze` como respaldo—. ⚠️ **Al volver NO arranca sola**: el visitante decide con el ▶️ (para que no lo
  sorprenda la música al salir del chat). ⚠️ **Trampa resuelta en el mismo paso:** el `blur` de la ventana
  también salta al tocar el **mapa de Google** de la ficha (va en un `<iframe>` y se lleva el foco), así
  que si el elemento con el foco es un iframe de la propia página **la música NO se para** (el visitante
  sigue en la web). Comprobado en la ficha del gym: tocar el mapa → sigue sonando · irse a WhatsApp → se
  para · volver → sigue en pausa.
* **`?v=<fecha>`**: rompe la caché de Cloudflare (si algún día se rehace la canción, se oye la nueva).
* El rojo del botón al sonar y la barra de avance son del propio reproductor (CSS y JS dentro del
  `include`, sin tocar `header.php` ni subir otra versión de los estilos del sitio).

### 16.8 🖥️ LA PESTAÑA DEL SÚPER ADMIN (🎵 Canciones (Treblo))

En `superadmin.php?seccion=canciones` (`includes/vista_canciones_admin.php`):

* **Cuántas canciones hay**: listas, en cola, con error y las pedidas hoy (con su tope diario).
* **El saldo de las 4 cuentas** y, sobre todo, **cuántas canciones más se pueden hacer** con lo que
  queda; con el botón **`🔄 Consultar el saldo ahora`** (la consulta es gratis).
* **El registro del consumo de cada cuenta**: créditos, **canciones hechas** y si está agotada.
* **`▶️ Procesar la cola`** (por si el disparo automático se perdió) y **`🎵 Generar`** para pedir la
  canción de una tienda a mano (con su id) — así se le puede regalar una canción a una tienda que ya
  existía.
* **`🔔 Probarme el aviso por Telegram`** y la tabla de **las últimas 20 canciones** con su duración
  exacta, su peso, con qué cuenta se hicieron y un botón **▶️ oír**.

### 16.9 🧪 CÓMO SE PRUEBA (y cómo NO gastar créditos)

| Herramienta | Para qué |
|---|---|
| `python __sonda_run.py __cancion_prueba.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&accion=estado"` | **Gratis.** Tablas, saldo de las 4 cuentas, si ffmpeg funciona y el resumen del módulo |
| `… "&accion=encolar&negocio=<id>"` | **Gratis.** Apunta la canción de esa tienda (con `forzar`) y muestra la letra y el prompt |
| `… "&accion=procesar&n=1"` | **Gasta 100 créditos.** Pide y cierra la canción de la primera de la cola |
| `… "&accion=ver&negocio=<id>"` | **Gratis.** Qué quedó en la fila, el archivo en disco, su duración medida y el HTML del reproductor |
| `… "&accion=rehacer&fila=<id>"` | **Gratis.** Vuelve a bajar la canción del CDN y la guarda otra vez (sirve para rehacerla sin gastar créditos, y para cambiar de política de recorte) |
| `… "&accion=diagnostico"` | **Gratis.** Manda el mismo cuerpo que el motor pero con `length_range [40,40]` (el **canario**) para que la API diga qué campo le molesta |
| `python __cancion_lote.py plan\|encolar\|tanda\|procesar\|estado` | **La tanda de las tiendas que ya existen** (ver §16.12): `plan` y `estado` son gratis |
| `python __sonda_run.py __cancion_rutas.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x` | **Gratis.** Dónde está la web en el servidor y si el ffmpeg subido se ejecuta |
| `python __sonda_run.py __sonda_cancion_cap.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x` | **Gratis.** Qué puede hacer el hosting (shell, ffmpeg, salida a internet, tablas) |
| `python __cancion_subir_ffmpeg.py [go]` | Sube el **ffmpeg estático** (76 MB) a `_cancion/ffmpeg` |
| `curl "https://dechimbote.com/api/cancion_worker.php?t=PON_AQUI_EL_TOKEN_DEL_OBRERO&n=1"` | Mueve la cola a mano (sin la clave da 403) |

**🐤 EL TRUCO DEL CANARIO (para probar la API sin gastar créditos):** cualquier petición con
`length_range: [40,40]` es **rechazada siempre** (no es múltiplo de 30) y **no crea ninguna canción**;
la respuesta, en cambio, dice **todo lo demás que está mal** (los tags inválidos, las tres cosas juntas,
etc.). Así se averiguó la lista de tags válidos y las reglas del §16.2 **sin gastar un crédito**.
⚠️ **Y la lección de la que costó 5 canciones: nunca se prueba contra esta API con un cuerpo que sea
válido** — cada petición aceptada se cobra, aunque después no se use el audio.

### 16.10 ✅ LO QUE SE PROBÓ (3 tiendas de verdad, el 2026-09-17)

| Tienda | Cola | Canción | Duración medida | Peso |
|---|---|---|---|---|
| **1791 · NOVEDADES GAELA CHIMBOTE** (Calzado) | fila #10, pedida y cerrada en **29,4 s** | `assets/uploads/canciones/cancion-novedades-gaela-chimbote-1791.mp3` | **39,38 s** (lo que mandó Treblo) | 631 KB |
| **1747 · Sabor y Fuego** (Restaurantes) | fila #11 | `assets/uploads/canciones/cancion-sabor-y-fuego-1747.mp3` | **29,95 s** (Treblo la mandó de 30) | 480 KB |
| **1750 · Llantería El Doctor** (Reparación de llantas) | fila #13 (la #12 se quedó en `SAVING` y se volvió a pedir) | `assets/uploads/canciones/cancion-llanteria-el-doctor-1750.mp3` | **38,59 s** | 619 KB |

⚠️ **Esas tres duraciones son las definitivas desde la segunda orden del jefe** («cualquier duración que
Treblo te lo envíe»): las tres se **volvieron a bajar del CDN con `cancion_rehacer()`** y se guardaron
**tal cual llegaron**, sin ffmpeg ni cortes. Antes de esa orden las tres estaban recortadas a
**40,000000 s exactos** (1 764 000 muestras, 469 KB) — y esa receta sigue en el §16.3 por si algún día se
vuelve a querer (`CANCION_RECORTAR = true`). Lo que se comprobó igual que entonces: las tres fichas
responden **200** con **un solo** reproductor, la canción en su `src`, **`autoplay`** y su duración real
en el subtítulo (*«canción de 0:39»*, *«de 0:30»*): `/neg/novedades-gaela-chimbote-2`,
`/neg/sabor-y-fuego`, `/neg/llanteria-el-doctor`.

**Lo que falta probar con el dedo del jefe:** entrar a una de esas fichas en su celular, tocar ▶️ y oír
si el jingle le gusta (la letra, la voz, el ritmo). El nombre de la tienda se canta en el coro, porque
la letra se manda escrita (en español) y no la inventa el modelo.

### 16.11 ⚠️ LO QUE FALTA / PENDIENTES HONESTOS

* **Los créditos (§16.6).** Es lo único que impide que esto funcione a escala. Hoy: **0**.
* ⏳ **Que el jefe oiga los 3 jingles** y diga si el estilo es el que quiere: **se cambia en una línea**
  (`CANCION_PROMPT` y `CANCION_LETRA` en `config_cancion.php`) y las siguientes canciones salen con el
  estilo nuevo. Si quiere **voz de hombre o de mujer**, se agrega el tag
  (`male vocalist` / `female vocalist`, ambos válidos).
* **El obrero no cancela canciones viejas:** si un día se quiere «olvidar» una canción en cola, se borra
  su fila de `directorio_canciones`.
* **No hay aviso al dueño de la tienda** cuando su canción ya está lista (hoy se entera al entrar a su
  ficha). Se podría avisar por su WhatsApp, pero eso ya es otro módulo.
* **La canción se genera en el idioma que se le pide en la letra** (español). Falta oír si la
  pronunciación es buena en todas (la letra y los tags son lo único que la guía).
* **`cancion_rehacer()`** existe para volver a bajar y recortar sin gastar créditos (se usó para dejar
  las 3 canciones en 40,000000 s exactos): sirve también si algún día se pierde el archivo del hosting.

---

## §16.13 🎼 LAS CANCIONES QUE MANDÓ EL JEFE EN DESCARGAS (17–18/09/2026)

> **Cuándo leer esto:** cuando el jefe deje canciones en `C:\Users\Usuario\Downloads` y diga *«convierte
> audio a texto los primeros segundos y ponle su canción a cada tienda»*. Aquí está el camino completo,
> los comandos y las trampas. **Es el mismo camino para cada tanda nueva.**

### Lo que pidió (textual, 2026-09-17 noche)

1. *«En la carpeta descargas encontrarás canciones. Usando python convierte audio a texto los primeros
   10 segundos y en base a eso ponle su canción a cada tienda en modo autoplay.»*
2. *«El reproductor ponlo debajo del número de teléfono. Un reproductor negro con controles plateados.»*
3. *«Solo se reproduce 1 vez la primera vez que entran, no las próximas veces.»*
4. *«Volumen al 50 %. Optimizado para modo móvil pequeño pero ancho.»*
5. *«Se me olvidó decirte que lo conviertas a ogg u otro formato que reduzca el peso del archivo.»*
6. *«Todo para Android.»*
7. *«Si va a tardar mucho entonces no los conviertas a ogg y súbelos tal cual, ya están comprimidos en
   mp3.»* → ⚠️ **aclaración al jefe: convertir no es lo que tarda** (es ~1 s por canción: 41 canciones
   pasaron a OGG en segundos). Lo que tarda es **oír** las canciones (ver «Dónde se fue el tiempo»).

### 🔑 EL SECRETO DEL EMPAREJAMIENTO: LA CANCIÓN CANTA SU ID

Las canciones del diseñador **empiezan cantando el número (el ID de la tienda) y su nombre**, y muchas
lo repiten en el coro: «¡Ocho! ¡El Salpreso, para mí!», «Nueve, Simbote Restaurante», «50 y 4, alquiler
de departamento moderno Nuevo Chimbote», «Cuarenta y siete, Global Car Chimbote». Por eso el pedido del
jefe («convierte audio a texto los primeros 10 segundos») **funciona**: el número sale en la
transcripción.

⚠️ **Pero el nombre sale deformado** («Ospedaje Chimote», «Sin Vote Restaurando», «Boticas Farmax») y el
buscador de parecidos **por nombre no sirve** (daba 0,00–0,48 de parecido contra la lista real). **Lo que
manda es el número cantado.** Cuando en 10 s no se entiende, se oye más largo:

| Pasada | Cuándo |
|---|---|
| **10 s** | siempre, primero (lo que pidió el jefe) |
| **25 s** | las que en 10 s solo dejaron el número o nada |
| **60 s** | las mudas o las que seguían sin nombre (el nombre aparece en el coro, no al principio) |
| **oír 20 s con `beam_size=5`** | los casos difíciles puntuales («dieciocho» salió como «10 y 8»; «cuarenta y cuatro» como «43-4») |

### 🛠️ LAS HERRAMIENTAS (todas en `D:\RELAX`, prefijo `__`)

| Comando | Qué hace |
|---|---|
| `python __cancion_transcribir.py 10` | **Oye** los primeros 10 s de cada audio de Descargas (faster-whisper `base`, español; el modelo ya está en la caché de HuggingFace, **no necesita internet**). Guarda `__cancion_transcripcion.json` y **se salta** lo ya oído |
| `python __cancion_transcribir.py 25 --solo "a.mp3\|b.mp3"` | Pasada larga solo de esos (separa con `\|`, no con comas: hay nombres con coma) |
| `python __cancion_mapa_gen.py` | Escribe **`__cancion_mapa.json`**: qué audio es de qué tienda, con el nombre y el slug reales de la base. Los pares van escritos en el propio script (es el registro); los audios que ya no están en Descargas se saltan |
| `python __cancion_a_ogg.py 40` | Convierte a **OGG/Opus 40 kbps** con el nombre `cancion-<slug>-<id>.ogg` en `__cancion_ogg\` |
| `python __cancion_publicar.py [go]` | Sube los OGG a `assets/uploads/canciones/` y **apunta la fila** en `directorio_canciones` (sonda `__cancion_asignar.php`). Sin `go` es simulacro |
| `python __cancion_verificar.py todo` | Mira **cada ficha por HTTP**: 200, un solo reproductor, su archivo, debajo del teléfono, volumen 50 %, autoarranque, «solo la 1.ª visita» y que el audio sea un OGG de verdad |
| `python __cancion_convertir_hosting.py [go]` | Pasa a OGG las canciones que ya estaban en MP3 en el hosting (las cantadas por Treblo) y borra el MP3 viejo |
| `python __sonda_run.py __cancion_tiendas.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&accion=listado"` | La lista de tiendas (id, nombre, slug, rubro) para el mapa |
| `python __sonda_run.py __cancion_canciones.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&accion=listado"` | Las canciones ya puestas (para verificar y para saber qué sigue en MP3) |

⚠️ **Las dos sondas van en archivos distintos a propósito** (`__cancion_tiendas.php` y
`__cancion_canciones.php`): el lanzador guarda la respuesta con **el nombre del archivo**, así que si las
dos acciones vivieran en el mismo `.php` se pisarían la una a la otra.

### 🎧 CÓMO QUEDÓ EL REPRODUCTOR (§16.7 reescrito)

* **Debajo del número de teléfono**, en las 3 plantillas: en la **A**, dentro de la tarjeta de datos justo
  después de la fila 📞 Teléfono (y si la tienda no tiene teléfono, en ese mismo sitio, donde iría);
  en la **B**, debajo de la fila de datos; en la **C**, dentro del bloque 📞 Contacto, debajo del
  teléfono. **Ya no va debajo de los botones de WhatsApp/Llamar.**
* **Negro con controles plateados** (nada de morado): fondo `#171a1f→#000` con borde plata, botón ▶️ de
  plata pulida, barra de avance plateada y textos en gris claro. El CSS y el JS siguen dentro del
  `include` (no se toca `header.php`).
* **📱 Bajo y ancho (celular)**: 54 px de alto, todo el ancho, textos de 11-12 px en un renglón; abajo de
  420 px el reloj se esconde y el botón baja a 34 px para que el nombre de la tienda no se corte.
* **🔊 Volumen al 50 %**: `audio.volume = 0.5` (`CANCION_PLAYER_VOLUMEN`), también al tocar ▶️.
* **🔊 SUENA SOLA SIEMPRE (orden del jefe, 2026-09-18: «la canción debe estar en autoplay»)**: el
  `<audio>` sale del HTML con **`autoplay`** y **`preload="auto"`**, así que arranca sola **en cada
  visita**. Si el navegador bloquea el arranque (entrada directa, sin ningún clic), **el primer toque
  del visitante la arranca** y además se reintenta con `canplay` (cuando el audio ya tiene datos).
  ⚠️ Del 2026-09-17 al 2026-09-18 valió lo contrario («solo se reproduce 1 vez la primera vez que
  entran; no las próximas veces», con la marca `dch_cancion_oida` en el navegador): **esa orden quedó
  reemplazada**. Para volver a la de antes se pone **`CANCION_PLAYER_SOLO_UNA_VEZ = true`**.
* **OGG/Opus de 40 kbps**: **1 979 KB → 435 KB** en una canción de 84 s (**79-81 % menos**, medido),
  Android/Chrome lo reproduce de sobra (el MP3 solo tenía a favor el iPhone, y el jefe dijo «todo para
  Android»). Se conserva **la duración completa**: no se recorta nada.

### 📊 LO PUBLICADO (50 tiendas con canción)

| Lote | Cuándo | Canciones | Detalle |
|---|---|---|---|
| **1** | 17/09 23:24–23:40 | **27** | 26 tiendas + la de Minabel (venía en OGG de Treblo). Rubros: mercados, hospital, plazas, restaurantes, boticas, hoteles, radios, inmobiliarias… |
| **2** | 17–18/09 23:58–00:09 | **14** | Barberías (71, 72, 74, 75) y alquileres/inmobiliarias (50, 51, 54, 55, 57, 58) + Global Car Chimbote (47), Idílico Gusto (60), Gabriel Motors (44), Dental Bermúdez Blas (45) |
| **3** | 18/09 00:17–00:18 | **6** | Salón/spa (68) y barberías (69, 70, 76, 77, 78) |
| **4** | 18/09 00:20–00:35 | **14** | Barberías (81, 82, 83, 85, 86, 88, 90, 91, 92) y las que ya tenían canción (2.ª toma). **Este lote ya salió con `__cancion_rapido.py`** (§16.14) |
| **+3** | — | **3** | Las 3 de Treblo que estaban en **MP3** (`Llantería El Doctor` 1750, `Sabor y Fuego` 1747, `NOVEDADES GAELA CHIMBOTE` 1791) se pasaron a OGG: 604→202, 469→149 y 616→220 KB |

**Total: 59 tiendas con canción** (56 puestas desde Descargas + 3 convertidas). Peso: **19,08 MB** en
total, **68,1 s de media** por canción (los MP3 originales de esos mismos temas sumaban ~90 MB). Todas en
**OGG** (0 en MP3). Verificado por HTTP: **43 de 44 fichas perfectas** en la primera corrida, **6 de 6** en
la tercera y **8 de 8** en la cuarta (la única que «falla» el chequeo es la 5, *Plaza de Armas de
Chimbote*, que **no tiene teléfono**: el reproductor queda donde iría el teléfono y eso es correcto).

### ⏳ LO QUE QUEDÓ SIN PUBLICAR (a la espera de la decisión del jefe)

Son **versiones repetidas** de tiendas que ya tienen su canción (el diseñador manda a veces dos tomas):
`Corazón_de_la_región.mp3` (2.ª de Hospital Regional, 2), `Libertad_sobre_ruedas.mp3` (2.ª de Gabriel
Motors, 44), `Tu_mejor_sonrisa_en_Bermúdez_Blas.mp3` (2.ª de Dental Bermúdez Blas, 45) y las **3 OGG de
Treblo** (cantan Centro Cívico 4, MegaPlaza 3 y Mercado Modelo 1). **Se publicó la toma MÁS NUEVA** de
cada par (por fecha de descarga) y la otra quedó en Descargas.

### ⏱️ DÓNDE SE FUE EL TIEMPO (medido, para no repetirlo)

* **Oír es lo caro: 10,9 minutos de cómputo puro** = **81 audios oídos** (51 en la pasada de 10 s + 13 de
  25 s + 17 de 60 s) más ~10 cargas del modelo (3 s cada una) y los oídos puntuales de 20 s.
  **La culpa es del formato del pedido:** el nombre **no** está en los primeros 10 s de muchas canciones
  (está en el coro, o solo está el número), así que hubo que oír 25 s, 60 s y hasta repetir con más
  cuidado.
* El resto fue subir (4 rondas de FTP + 47 archivos), verificar por HTTP (50 fichas + 50 audios) y el
  trabajo repetido **3 veces** porque las canciones llegaron en **3 oleadas**.
* 💡 **Para la próxima tanda, la mitad del tiempo se ahorra** si el diseñador deja **el número y el
  nombre en los primeros 5 segundos, hablados y claros** (o si el archivo se llama
  `<id>-<nombre>.mp3`): el emparejamiento sale de una sola pasada de 10 s.

---

## §16.14 ⚡ EL CAMINO CORTO (orden del jefe, 18/09/2026) — `__cancion_rapido.py`

> **Esto reemplaza al camino largo del §16.13 para cualquier tanda nueva.** Lo pidió el jefe así:
>
> *«Tomas una canción en Descargas, conviertes 10 segundos a texto, lees el texto; si no ves el ID,
> pasas al siguiente. Encuentras el ID: conviertes a OGG y publicas (hosting). No hay necesidad de ir
> por todo ni estar confirmando tantas veces ni escuchando más de 20 segundos jamás… porque si no se
> reconoce el ID en los 10 segundos, entonces no vale ese archivo y se crea más adelante cuando
> acabemos y falte para algunas tiendas. Siempre subiendo y mostrando resultados; nunca insistas más de
> 2 veces y máximo 20 segundos: lo normal, 10 segundos.»*

**Una sola orden hace todo** (oír → decidir → convertir → subir → apuntar en la base → comprobar la ficha):

```bash
python __cancion_rapido.py          # simulacro: solo oye y dice qué haría
python __cancion_rapido.py go       # oye, y lo que tiene ID lo publica de una
```

* **10 s** de oído y, si no hay ID, **un solo** intento más de **20 s**. Nunca más. Si tampoco, el
  archivo **no vale** y se rehace al final (queda apuntado en `__cancion_rapido_estado.json`).
* **Nunca se oye dos veces el mismo archivo** (el estado lo recuerda): volver a correrlo solo procesa lo
  nuevo que haya caído en Descargas.
* Si el ID corresponde a una tienda **que ya tiene canción** (2.ª toma), no se publica: se aparta.
* Al final borra de Descargas lo publicado y lista lo que quedó sin ID.
* **Medido:** las 17 canciones del lote 4 se oyeron y decidieron en **1,6 minutos** (el camino largo del
  §16.13 tardaba ~15 minutos con las mismas canciones).

### 🔤 LO QUE EL TRANSCRIPTOR OYE MAL (y cómo lo resuelve el lector)

| Lo que canta | Lo que escribe el transcriptor | Cómo se resuelve |
|---|---|---|
| «ochenta y cinco» | «80 y 5» | decena + «y» + unidad → **85** (y se botan el 80 y el 5) |
| «ochenta y ocho» | «hochenta y ocho» | lista de deformaciones (`hochenta`, `setenta`→`senta`, `cincuenta`→`sin cuenta`…) |
| «noventa y uno» | «Noventa y uno» | decena + unidad → **91** (el «uno» suelto **no** cuenta: solo vale como ID si es la primera palabra) |
| «Barbería 007» | «Barbería 0, 0, 7» | los dígitos sueltos se juntan: **007** |
| «Barbería Barbertshop» | «864 Barberia Barber Chop» | número de 3 cifras **sin relación con el nombre** → se descarta; el nombre reconocido manda |
| «Blessed Barber Studio» | «Blessed Barber Studio» | el nombre completo (100 %) confirma el número |
| «ciento veintiséis» | «¡Siento 26!» | «ciento»/«cien»/«siento» + el número que sigue (1 o 2 cifras) → **126** |
| «ciento cuatro» | «100% 4» | el `%` se come la palabra: **104** (igual con «100 3» = 103) |
| «ciento veintidós» | «122» | los de 3 cifras de la familia **100-199 SÍ valen** («ciento X» suena natural); de **200 para arriba** solo si el nombre lo respalda |
| «Ivanna Herrera Nuevo Chimbote» | «Y van a herrera nuevo chinbote» | basta **una palabra propia larga** (≥ 6 letras) que salga igual: «herrera» vale, «poder» no |

⚠️ **Trampa que ya mordió (y quedó arreglada):** el **nombre** solo vale si coincide **entero** (100 %).
Con la mitad se publicó una canción a *Farmacia Dios Con Su Poder* porque la letra decía «poder»; y con
una sola palabra genérica («motor», «barrio») se eligió *La casa de las motos* para una canción de
Gabriel Motors. Las palabras genéricas (`GENERICOS` en el script) no cuentan nunca.
⚠️ **Y si se oyen varios números y ninguno tiene que ver con el nombre, NO se publica nada**: mejor
dejarla para el final que ponerle a una tienda la canción de otra (pasó con «Libertad sobre ruedas»:
dice 43 al principio y el coro canta *Gabriel Motors*).

### 🖐️ LAS DECISIONES A MANO (`__cancion_a_mano.json`)

Cuando el oído deja un caso en el aire pero el agente **sí puede resolverlo** leyendo lo que la canción
canta (el número **y** el nombre), no se adivina ni se pregunta: se escribe en
**`__cancion_a_mano.json`** con su motivo, y `__cancion_rapido.py` lo respeta **antes** de decidir por su
cuenta. Formato: `{"archivo.mp3": {"id": 124, "motivo": "…"}}`, y **`"id": 0` significa NO PUBLICAR**.

| Archivo | Qué se hizo | Por qué (lo que canta) |
|---|---|---|
| `Navaja_al_filo.mp3` | **124** LANRET 98 - Barbershop | «124 … BARBERSHOP» (el transcriptor escribe «124 LAMRE28R BARBERSHOP»); el número y el rubro coinciden |
| `Tu_mejor_estilo.mp3` | **84** Barberia Barbertshop | «864, Barbería Barber Chop»: el 864 no es de nadie y el nombre apunta a la 84 |
| `Libertad_sobre_ruedas.mp3` | **NO PUBLICAR** | dice «43-4» pero **todo el coro canta Gabriel Motors** (44, que ya tiene canción): es una 2.ª toma, y no se le pone a Taller Jhon una canción que canta el nombre de otra tienda |

### 📋 LO QUE QUEDÓ SIN PUBLICAR (lote 4 y lote 5, 18/09/2026)

| Archivo | Por qué |
|---|---|
| `Corazón_de_la_región.mp3` | 2.ª toma del **Hospital Regional (2)**, que ya tiene canción |
| `Flow_en_Chimbote.mp3` | 2.ª toma de **BLESSED BARBER STUDIO (92)** |
| `Tu_mejor_sonrisa_en_Bermúdez_Blas.mp3` | 2.ª toma del **Dental Bermúdez Blas (45)** (y en 20 s no canta ningún ID) |
| `Libertad_sobre_ruedas.mp3` | dice 43 pero el coro canta *Gabriel Motors* (44, que ya tiene canción): **ambigua, no se publica** |
| `Brilla_con_luz_propia.mp3`, `Brillar_en_Chimbote.mp3` | en 20 s no cantan ningún ID (el transcriptor no saca número): **no valen, se rehacen** |
| 3 OGG de Treblo (Centro Cívico, MegaPlaza, Mercado Modelo) | cantan el nombre pero **ningún ID**: sus tiendas (4, 3 y 1) ya tienen canción del lote 1 |

### 📊 LOTES 4 Y 5 (18/09/2026)

* **Lote 4** (00:20–00:35): 14 canciones → **9 publicadas** (81, 82, 83, 85, 86, 88, 90, 91, 92) y 5
  apartadas (2.ª tomas y las ambiguas de arriba).
* **Lote 5** (06:59–07:12): **35 audios oídos en 2,9 minutos** → **17 publicadas**
  (**84, 97, 101, 102, 103, 104, 105, 107, 108, 110, 113, 118, 120, 121, 122, 124, 126**), 11 apartadas
  por ser 2.ª toma de una tienda que ya tenía canción y 7 sin ID.
* **Total: 76 tiendas con canción** · 24,58 MB · 69,1 s de media · **todas en OGG** (0 en MP3).
