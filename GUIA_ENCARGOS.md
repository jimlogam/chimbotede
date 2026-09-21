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


# GUÍA — INFORMACIÓN EN VIVO 🔴🔒 (el chat en vivo del sitio)

> **Módulo nuevo del 2026-09-15, pedido por el jefe.** Es la única guía de este módulo: léela
> entera antes de tocarlo.
> Página pública: **`/en-vivo`** (⚠️ `/pedidos` —la URL del primer día— **redirige 301** aquí)
> · Archivo: `deploy/pedidos_sin_vendedor.php` · Motor: `deploy/includes/pedidos_sin_vendedor.php`
> · Aviso Telegram: tipo **`pedido_sin_vendedor`**.
> ℹ️ **El nombre para la gente es «Encargos»; los archivos, las funciones (`pedido_*`) y las tablas
> siguen diciendo `pedido`/`pedidos`.** No hace falta renombrarlos: solo cambia lo que se ve.

---

## 1. QUÉ ES (la idea del jefe, textual)

> *«Una persona está buscando alcohol isopropílico o una botella de medio litro de gas… o algo que mi
> página web no ha encontrado resultados suficientes. Por defecto el sitio me manda una notificación.
> ¿Qué tal si en lugar de mandarme una notificación me mandara un mensaje a ese tablón diciendo "se ha
> buscado kerosene, no se han encontrado resultados; si tienes el producto en tu stock publícalo
> ahora mismo"? Y cuando la persona le dé clic, se abre el creador de productos o de tiendas según
> esté logueado o no… Así el usuario tendrá más posibilidades de encontrar su producto y el vendedor
> también podrá ofrecer el suyo.»*
>
> *«Al comprador: no hemos encontrado tu producto, pero nuestros vendedores se van a comunicar
> contigo, déjanos tu número de WhatsApp. Y automáticamente en el tablón lo publicaremos diciendo
> "el usuario número tal está buscando quien le venda zapatillas importadas de la marca Nike número
> 41". Así el encargo será específico y todos podrán darle clic y enviarle sus propuestas.»*

**EL NOMBRE CAMBIÓ TRES VECES EL MISMO DÍA (2026-09-15) y el que manda es «INFORMACIÓN EN VIVO»** —
así se llama el módulo, el chat y el título de la página:
1. **«Pedidos sin vendedor»** (unas horas).
2. **«Encargos»** (unas horas más; *«es lo que dice una señora en la farmacia: ¿me lo encargas?»*).
3. **«Información en vivo»** ✅ **definitivo**, elegido por el jefe al ver el chat lleno de movimiento:
   *«procura ocupar todo el ancho de la página… y el nombre va a ser información en vivo; así se va a
   llamar, ya no se va a llamar encargo ni tablón»*.
⚠️ **La URL nueva es `/en-vivo`** (la que usan el menú, el chat y el botón del chatbot) y **`/encargos`
sigue funcionando** (hay avisos y enlaces ya repartidos con esa dirección). `/pedidos` redirige 301.
🗒️ Se descartaron en su momento **«Lo que buscan»**, **«Se busca»** (en el Perú es el cartel policial)
y **«Nuevos pedidos»** (suena a pedidos que YA entraron y choca con los «Pedidos» del Súper Admin, que
son los clics a WhatsApp).
⚠️ La palabra **«tablón» sigue retirada del sitio** (orden del jefe del 2026-09-13): este módulo
**no se llama así**, no tiene chat privado y no revive la mensajería B2B. Nunca escribirla en la web.
📐 **EL CHAT VA A TODO EL ANCHO**: se pinta en `.mchat-ancho`, **fuera** del contenedor centrado de
900 px donde viven los encargos y los formularios (si se mete dentro, vuelve a quedar estrecho).

---

## 2. LAS 3 REGLAS QUE LO SOSTIENEN (si se rompen, el módulo se cae solo)

### Regla 1 — NO SE PUBLICA UNA MENTIRA
Medido el 2026-09-15 con dos sondas de solo lectura sobre 6 días de búsquedas reales:

| Se buscó | El buscador dijo | El sitio SÍ tenía |
|---|---|---|
| cerveza | **1** resultado | 18 tiendas + 19 productos |
| camaras | 0 | 6 tiendas + 2 productos |
| ollas | 0 | 13 tiendas |
| jugo de piña | 0 | 56 tiendas de jugo |
| cemento | 1 | 27 tiendas + 10 productos |
| refrigerador | 1 | 37 tiendas |
| alcohol isopropílico | 0 tiendas | **1 producto publicado** |

El número que da `buscar.php` cuenta **TIENDAS CON ESA PALABRA EN EL NOMBRE**, no productos. Por eso
`pedido_oferta_real()` comprueba **3 niveles** antes de publicar nada:
1. **PRODUCTO** — `directorio_servicios.titulo` (el nivel que le faltaba al buscador).
2. **RUBRO** — «frases de unión» (`categoria_por_clave_texto()`); si el rubro tiene tiendas, hay oferta.
3. **TIENDA** — nombre o descripción de un negocio activo (siempre **sin filtros**: la verdad del directorio).

Con el término completo primero y, si no hay nada, con sus palabras **solo si el término tiene 1 o 2
palabras** (con 3+ el visitante fue específico —«Nike talla 41»— y ahí sí queremos el encargo).

### Regla 2 — NO SE PUBLICA BASURA, Y LO REPETIDO NO SE DUPLICA
`pedido_termino_util()` tira: pruebas (`xyzzy`, `zzzznoexiste`…), teléfonos (`931103286`), 2 letras,
sin letras, +60 caracteres y el nombre exacto de una tienda activa. Un robot (`stats_es_bot()`) no
publica. Y si el mismo término vuelve, **el encargo no se duplica: SUMA** → *«🔥 7 personas lo buscan»*,
que es justo lo que lo hace atractivo para el vendedor.

### Regla 3 — EL WHATSAPP DEL COMPRADOR NO SE PUBLICA
El jefe quería verlo en el tablón; se cumple **enmascarado** (`931 ••• 286`) y el enlace real lo
fabrica el servidor **solo** para el vendedor que deja sus datos y toca «Enviar mi oferta» → responde
**302 a `wa.me/<comprador>` con el mensaje ya escrito** (el vendedor solo toca Enviar). Así el número
no queda en el HTML para que lo cosechen los robots. **Comprobado por HTTP**: el número crudo NO
aparece en la página pública. El número del **vendedor** sí es público: lo publica él, a propósito.

---

## 3. LOS DOS TIPOS DE PEDIDO (los dos casos que pidió el jefe)

| tipo | Cuándo nace | Lo que dice la tarjeta |
|---|---|---|
| `sin_vendedor` | 0 resultados **y** comprobado que nadie lo vende | «Nadie lo vende todavía» |
| `pocos` | 1-3 resultados, y la oferta real es escasa (<4 tiendas/productos) | «Solo 2 tiendas lo tienen» |

Con **4 o más** coincidencias reales **no se publica pedido**: el buscador simplemente mostraba poco
de lo mucho que hay, y al visitante se le enseña el bloque **«😉 Sí hay: mira quién vende…»** (con los
productos, su tienda y su WhatsApp). Con más de 3 resultados tampoco se publica nada.

---

## 4. CÓMO FUNCIONA POR DENTRO (el viaje completo)

1. **El buscador** (`buscar.php`, tras `$total = count($resultados)`): si hay término y `$total <= 3`
   llama a `pedido_publicar($termino, ['resultados' => $total, 'distrito_id' => …, 'rubro_id' => …])`.
   - `hay_oferta` → no publica y se pinta `pedido_oferta_html()`.
   - `publicado` / `sumado` → se pinta `pedido_publicado_html()` (**arriba** de los resultados, en los
     dos casos: también cuando hay 1-3, sin quitarle nada de lo que ya tenía).
2. **La página `/en-vivo`** (regla `^encargos/?$` del `.htaccess` → `pedidos_sin_vendedor.php`;
   `/pedidos` tiene su regla **301** encima para no dejar enlaces rotos):
   - Publica pedidos a mano (`accion=pedir`), el comprador deja su WhatsApp (`accion=whatsapp`,
     **solo si el encargo no tiene número, o si es su autor** — se compara la IP hasheada) y el vendedor
     ofrece (`accion=ofrecer`).
   - Lista los **abiertos** (24 por página) y los **conseguidos** (8) = prueba social, que es lo que
     evita el problema que hundiría un tablón: **verse vacío**.
   - Todo sin JavaScript: los formularios viven en `<details>`.
3. **El aviso al jefe** (`aviso('pedido_sin_vendedor', …)`): cuatro motivos — `nuevo` · `crecido`
   (a la 3.ª búsqueda) · `propuesta` (un vendedor ofreció) · `comprador` (dejó su WhatsApp) — con los
   dos enlaces de moderación. Se enciende/apaga en **Súper Admin → 📱 Telegram**.
4. **Moderación con un toque** (igual que empleos, sin panel ni login):
   `/en-vivo?moderar=<token>&accion=ocultar|conseguir|reabrir`.

## 5. 💬 EL CHAT EN VIVO Y 📲 LOS AVISOS POR TELEGRAM (lo que pidió el jefe después)

### 5.1 El chat («Chimbote en vivo») — va ARRIBA en /en-vivo y también en la portada
El jefe vio la primera versión (una lista con líneas) y la rechazó. Pedido textual (2026-09-15):

> *«Yo lo que quiero encontrar es un CHAT ACTIVO con mensajes que van llegando cada dos segundos, con
> búsquedas no encontradas, con clic realizado en tiendas, con negocios que están publicando nuevos
> productos… lo mismo que me llega a mi Telegram. Yo quiero un chat que esté vivo, que se mantenga
> fluido, que el que lo esté mirando en ese momento se lleve la oportunidad… si es necesario
> repitiendo las publicaciones de abajo arriba… siempre bajo la lógica de los 150 últimos pero nunca
> estático.»*

- **Motor:** `deploy/includes/muro.php` (`muro_chat_html()`, `muro_msg_html()`, `muro_chat_js()`,
  `muro_estilos()`) · **el AJAX:** `deploy/api/muro_json.php`.
- **Se pinta:** en **`/en-vivo`**, lo PRIMERO después de la cabecera (`muro_chat_html(14, 420, true)`),
  y en la **portada** (`muro_portada_html()` en `index.php`, antes de Empleos: título
  **«🛒 Nuevos pedidos sin vendedor»** + 3 encargos abiertos + el chat).
- **El ritmo es de 2 segundos** (`MURO_RITMO_MS`): el navegador tiene una **cola**; primero suelta los
  mensajes **nuevos de verdad** (los pide al servidor cada 20 s con `?desde=<último id>`), y cuando la
  cola se vacía **RECICLA** los últimos 40 —lo pidió el jefe— para que el chat **nunca se pare**.
  ⚠️ El reciclado usa **la hora real** del mensaje: nunca se hace pasar uno viejo por nuevo.
- **Los 150:** el chat mantiene 150 burbujas (`data-tope`): al entrar una, la primera sale.
  ⚠️ **NO se borra nada de `directorio_avisos_log`** (de ahí salen los Récords, las Estadísticas y los
  informes del panel). El «borrado del 1 al llegar el 151» se hace **en el navegador**.
- **Se agrupan los iguales seguidos** («buscó «X» **×3**»).
- 🔴 **REGLA DE ORO DEL JEFE (2026-09-15, textual):** *«no se usa la palabra Bot ni robot ni araña ni
  nada que tenga que ver con automatizaciones de spiders o navegadores: para eso usamos la palabra
  OPERADORES o la palabra USUARIOS»*. → En el chat **no existe ninguna de esas palabras**: el que
  busca, el que toca «Llamar» o el que pide precio es **«Un usuario»**; el que publica es **«Un
  negocio»**; el encargo lo publica **«El directorio»**. Y **tampoco en los textos de alrededor**
  (se cambiaron el mensaje de «no pudimos validar» y el de la caja de Telegram).
  Al terminar cualquier cambio: buscar `robot|bot|araña|spider` en el HTML y dejarlo en **cero**
  (lo único que puede quedar son los `estado = 'robot'` internos del motor de avisos: eso es de la
  BASE DE DATOS, no se ve y **no se toca** porque mueve los informes).
- 🔒 **NUNCA se publica el `resumen` del registro tal cual:** trae *usuario y **CONTRASEÑA*** de las
  tiendas nuevas (lo cazó `__muro_prueba.php`). El texto lo construye `muro_evento_datos()`. Tampoco
  se publica IP, nombre de personas, teléfonos ni correos.
- ⚠️ **TRAMPA 1 GRABADA (2026-09-15): el AJAX no mostraba NUNCA un mensaje nuevo.** En `muro_listar()`
  el `?` del `id > ?` va **antes** que los `?` del `tipo IN (…)` (cuenta el orden en el SQL, no el de
  las variables); si se pasan al revés, el `id` recibe un nombre de tipo y el `catch` se traga el error
  **en silencio**. Además, dentro de la subconsulta la tabla **no tiene alias** (`l` no existe ahí).
- ⚠️ **TRAMPA 2 GRABADA (2026-09-15): el chat se quedaba VACÍO.** El filtro de tipos iba FUERA de la
  subconsulta y el lote era de 300 filas: como las últimas 300 filas de usuarios eran casi todas
  `pagina_404` (los 404 son la mitad del movimiento), al filtrar después no quedaba **ninguna**. Ahora
  el `tipo IN (…)` y el `estado IN (…)` van **DENTRO** de la subconsulta y el lote es de **2 000**.
  **Regla general: el `LIMIT` de un lote tiene que aplicarse a las filas que interesan, no a todas.**
- **De dónde sale:** de `directorio_avisos_log` —*exactamente* lo que le llega al Telegram del jefe—,
  con `es_bot = 0` y la lista blanca `muro_tipos()`: encargos, búsquedas, clic de precio, botón de
  llamar, visitas a tienda y a producto, opiniones, reclamos, tienda/producto/fotos nuevos, producto
  borrado, cuenta nueva, empleos, postulaciones, reportes y clics de anuncio. **Lo que NO sale:** los
  resúmenes del sistema (hora, día, informes), los 404, los errores y las alertas del servidor.

- **📋 CADA MENSAJE ES UN CLON DEL TELEGRAM DEL JEFE** (pedido suyo, 2026-09-15: *«tienes que ser
  más informativo, así como me mandas al Telegram igualito… se ve la IP, se ve la hora que entró, se
  ve qué buscó, cuál fue la URL… ocupa todo el ancho de la pantalla»*). El formato es **título en
  mayúsculas + el dato + las líneas de detalle** (`muro_evento_datos()` → `titulo` y `detalles[]`,
  pintados en `.mchat__titulo` / `.mchat__det`), y la burbuja va a **ancho completo** (sin `max-width`):

  ```
  👁️ VISITA A UNA TIENDA
  Un usuario
  🏪 Distribuciones Olano SAC
  👤 Anónimo · 📱 móvil
  ✅ Persona real: esa IP ya había entrado hoy, abrió más de una página en esta visita
  🌐 132.184.130.169 · navegando en el sitio
  🔗 https://dechimbote.com/neg/distribuciones-olano-sac
  🕐 15/09 10:47
  ```

  De dónde sale cada dato: **título** de `muro_tipos()['titulo']` (los mismos rótulos del Telegram) ·
  **tienda** de `negocio_id` · **👤 Anónimo / Usuario registrado** de `usuario_id` · **📱/💻** del
  `user_agent` (`muro_dispositivo()`) · **✅ Persona real** del propio `resumen` del aviso (solo en las
  visitas) · **🌐 IP** del registro · **📄 resultados** del `resumen` («cerveza (18 res.)») ·
  **🔗 URL** reconstruida por tipo (búsqueda, encargo, `/neg/<slug>`, `/empleos`) · **🕐 dd/mm H:i** de
  `creado_en` (hora de Lima). El `resumen` crudo **nunca** se publica entero (§5.1, regla inviolable).
- 🟢 **EL CONTADOR DE GENTE EN LÍNEA** (pedido del jefe, 2026-09-15, textual): *«los usuarios que se
  muestran online multiplícalos por cuatro y el número mínimo va a ser 27… si hay uno, 27 + 4; si hay
  dos, 27 + 8… muéstralo con un símbolo de destello verde de personas que están en línea… eso le va a
  servir al usuario para que vea que nuestro sitio web sí tiene poder»*.
  → **`mostrado = MURO_EN_LINEA_BASE (27) + MURO_EN_LINEA_FACTOR (4) × personas reales en el sitio`**.
  Las personas reales se cuentan con los **latidos** que el sitio ya mide (`directorio_stats_sesiones`
  con `ultimo_activo` de los últimos `MURO_EN_LINEA_MIN` = 3 minutos, vía `muro_en_linea()`), y la
  cifra se refresca sola con el AJAX (`en_linea` en `api/muro_json.php`).
  ⚠️ **Está escrito en el código y aquí: la cifra MOSTRADA no es el conteo crudo**, es un escaparate
  por decisión del jefe (el real va en `reales`). Para volver al dato exacto: `MURO_EN_LINEA_BASE` a
  `0` y `MURO_EN_LINEA_FACTOR` a `1`.
- ⚠️ **LA IP SE MUESTRA COMPLETA** (decisión del jefe, 2026-09-15, después de advertirle por escrito:
  *«se ve la IP… tal cual como me lo mandas a Telegram»*). ⚠️ **Riesgo asumido y consciente:** en su
  Telegram la IP es un dato privado suyo; en una página abierta, una IP completa **identifica a una
  persona** (geolocalización y hasta ataques a su conexión) y es un dato personal protegido
  (Ley 29733). **Si él cambia de opinión** (o llega una queja), se pone `MURO_IP_ENMASCARADA` en `true`
  en `includes/muro.php` y queda `181.176.•••.•••` otra vez, **sin tocar nada más**. En su Telegram
  sigue llegando completa siempre.
- 📲 **El chat ofrece el aviso por Telegram**: el pie lleva un botón azul **«📲 Recibir estos mensajes
  en mi Telegram»** que baja a la caja de suscripción (`/en-vivo#telegram`, ver §5.2).

### 5.1-bis 👆 EL CLIC EN EL CHAT: «YO LO VENDO» Y EL WHATSAPP DEL CLIENTE
Pedido textual del jefe (2026-09-15, después de ver el chat funcionando):

> *«Los mensajes van apareciendo; cuando el usuario hace clic, se detiene. Una vez que se detiene
> aparecen las opciones "yo lo tengo / yo lo vendo" y automáticamente se le pide su número de
> WhatsApp; y después que deja su número de WhatsApp recién se procede a enviarle el número. Así, si
> el usuario agrega un número que no está registrado en nuestra tienda, se le invita a registrarse: se
> le dice «no está registrado en esta tienda». Si el usuario ya está logueado, le salen las opciones de
> "yo vendo este producto" y le aparece el botón de WhatsApp del cliente para que le pueda mandar su
> mensaje personalizado; y si no está registrado, igual se detiene el scroll de mensajes y le aparece
> la opción de registrarse.»*

- **Quién hace qué:** el **JS del chat** (`muro_chat_js()` en `includes/muro.php`) detiene el chat
  (`pausado = true`: se apagan el ritmo de 2 s y el AJAX), marca la burbuja y abre el **panel**
  (`#mchatPanel`); el **endpoint** `api/muro_accion.php` decide y devuelve el JSON.
- **Los 4 caminos (comprobados por HTTP el 2026-09-15):**

| Lo que hace la persona | Lo que recibe |
|---|---|
| Toca un mensaje y **no** da su WhatsApp | `pide_wa` → «Déjanos tu WhatsApp y te pasamos el dato del cliente» (campo de teléfono) |
| Da un WhatsApp que **no está** en el directorio | `no_registrado` → **«Ese WhatsApp no está registrado en esta tienda»** + **📝 Registrarme gratis** (`/crear-tienda`) |
| Da un WhatsApp **registrado** (tienda o cuenta del sitio) | `ok` → el **número del cliente** + **💬 Escribirle por WhatsApp** con el mensaje ya escrito (y queda constancia de su oferta en el encargo) |
| Está **logueado** | Igual que el anterior, pero **sin pedirle nada**: el contenedor va con `data-logueado="1"` y el panel va directo a las opciones |

- **Si el cliente no dejó número** → `sin_comprador`: se le dice con claridad y se le ofrecen
  **🛠️ Publicar mi producto** (`/crear-tienda?modo=producto&q=<término>`) y **🛒 Ver todos los encargos**.
- **Al cerrar el panel (✕) el chat sigue donde estaba.** Y la invitación «🛠️ Yo lo vendo ›» solo
  aparece en los mensajes que traen algo buscado (búsqueda o encargo); en el resto, el clic abre el
  panel igual.
- 🔒 **Seguridad de este endpoint** (entrega el teléfono de una persona): solo **POST** con **token
  CSRF** de la página; el `id` tiene que ser un aviso real de usuario; el encargo no puede estar
  oculto; **tope de 5 números por IP y hora**; y cada entrega **queda registrada** como oferta en el
  encargo (el comprador lo ve y al jefe le llega el aviso `pedido_sin_vendedor` → `propuesta`).
- ⚠️ **TRAMPA GRABADA (2026-09-15): el endpoint tiene que cargar el motor de encargos.** Sin
  `require_once includes/pedidos_sin_vendedor.php` no existen `pedido_whatsapp_normal()` ni
  `pedido_norm()`: **rechazaba hasta los WhatsApp válidos** («no parece un WhatsApp») y no encontraba
  el encargo del mensaje.
- ⚠️ **Y cuidado con el BOM:** al reescribir archivos con PowerShell quedaron dos con **BOM UTF-8**
  (`includes/pedidos_sin_vendedor.php` y `buscar.php`) y ese BOM **se imprime antes del JSON** → la
  respuesta deja de ser JSON válido (y puede romper cabeceras). Comprobación: los 3 primeros bytes no
  deben ser `EF BB BF`.

### 5.2 📲 Los avisos por Telegram a los vendedores
Pedido del jefe: *«tiene que tener la opción de recibir notificaciones en mi Telegram… le pones una
cajita ahí para que pongan su usuario de Telegram»*. Decisión suya: **solo encargos de su rubro y su
zona**.

⚠️ **LA VERDAD TÉCNICA:** ese chat **no puede escribirle a alguien por su `@usuario`** (la API solo
deja mandar a quien YA inició el chat). Por eso la caja **no pide el usuario**: genera un **código**
y muestra un botón que abre `t.me/Jimmychimbote_bot?start=<codigo>` (telegram_subs.php); al tocar **START**, el webhook
guarda su `chat_id` y desde ahí sí recibe los avisos. `/stop` lo da de baja.

- **Motor:** `deploy/includes/telegram_subs.php` · **tabla:** `directorio_telegram_subs`
  (código, chat_id, rubro_id, distrito_id, activo, topes…). La crea el admin o la sonda
  (`define('TG_SUBS_INSTALAR', 1)`).
- **El webhook** (`api/telegram_bot.php`) ya no ignora a los desconocidos: procesa `/start <código>`,
  `/stop` y, a cualquier otro, le explica en dos líneas cómo suscribirse. **El jefe sigue teniendo sus
  comandos** (`/buscar`, `/ayuda`): ese flujo no se tocó.
- **Cuándo avisa:** al nacer un encargo nuevo (`pedido_publicar()` → `pedidos_avisar_suscritos()`),
  y el envío se hace en el **shutdown** (cuando el visitante ya recibió su página), igual que los
  avisos del jefe: la web nunca se frena por Telegram.
- **A quién:** suscriptores activos del **mismo rubro** (o «todos») y de la **misma zona** (o toda la
  provincia). Topes: **1 aviso por encargo** y **10 al día** por persona.
- ⚠️ **TRAMPA GRABADA A FUEGO (2026-09-15):** MariaDB **revienta** con
  *«Illegal mix of collations (utf8mb4_general_ci,COERCIBLE) and (utf8mb4_unicode_ci,COERCIBLE) for
  operation '<>'»* al comparar **un parámetro con un literal `''`** (`IF(? <> '', …)`, `chat_id <> ''`).
  La conexión va en `general_ci` y las tablas en `unicode_ci`. **Solución: decidir en PHP** (o usar
  `CHAR_LENGTH(...) > 0`). Costó una activación que fallaba **en silencio** (lo cazó `__tg_diag.php`).
- **Prueba de punta a punta:** `python __sonda_run.py __tg_prueba.php tg-2026-9kQ7` (pide la
  suscripción, la activa con el chat del jefe para que él vea el mensaje real, dispara el aviso y
  borra lo de la prueba). Resultado el 2026-09-15: **1 aviso enviado** ✅.

## 6. DÓNDE ESTÁ ENGANCHADO

- **Menú hamburguesa** (`includes/header.php`): «🛒 Encargos».
- **Chat 🥷** (`includes/chatbot.php`): pregunta rápida «🛒 Ver qué buscan y nadie vende».
- **Buscador**: los dos bloques (encargo publicado / «sí hay»).
- **Botón «🛠️ Yo lo vendo»**: abre `/crear-tienda?modo=producto&q=<término>` — **El maestro** con el
  nombre del producto ya puesto (y si no tiene cuenta, se la crea con su WhatsApp). Es la conexión con
  el otro módulo: la demanda publicada llena el catálogo.

## 6. TABLAS (auto-instalación defensiva)

- `directorio_pedidos_busqueda`: slug · termino · norm · **tipo** · **resultados** · distrito_id ·
  rubro_id · buscado_n · propuestas_n · whatsapp (comprador) · aviso_comprador · estado
  (`abierto|conseguido|oculto`) · token · ip_hash · dispositivo · primera_vez · ultima_vez ·
  conseguido_en · origen · creado_en.
- `directorio_pedidos_propuestas`: pedido_id · negocio_id · usuario_id · nombre · whatsapp · mensaje ·
  precio_txt · estado (`visible|oculto`) · ip · creado_en.
- `directorio_telegram_subs` (**📲 las suscripciones del jefe y de los vendedores**): codigo ·
  chat_id · nombre · usuario_id · negocio_id · rubro_id · distrito_id · activo · avisos_n ·
  avisos_fecha · ultimo_pedido_id · ip · creado_en · activado_en.
- **El muro no tiene tabla**: es una lectura de `directorio_avisos_log` (ver §5.1).

Se crean solas desde el panel del admin o con la sonda instaladora
(`__pedidos_instalar.php`, `define('PEDIDOS_INSTALAR', 1)`, `python __sonda_run.py __pedidos_instalar.php <clave>`).
`pedidos_columnas_asegurar()` añade columnas nuevas a instalaciones viejas (los `migrar_*.php` están
bloqueados por el antivirus). **Fechas: siempre hora de Lima (las genera PHP); el MySQL va en UTC.**

## 7. CÓMO SE COMPRUEBA (por HTTP, sin navegador)

- `python __psv_prueba.py` — publica un encargo, ofrece, verifica el enmascarado y el botón del constructor.
- `python __psv_prueba2.py` — verifica que el botón del vendedor responde **302 a `wa.me/<comprador>`**.
- `python __sonda_run.py __tg_prueba.php tg-2026-9kQ7` — prueba los avisos por Telegram de punta a punta
  (pide la suscripción, la activa con el chat del jefe para que vea el mensaje real, dispara el aviso
  y borra lo de la prueba).
- `python __sonda_run.py __muro_prueba.php muro-2026-9kQ7` — mira 3 resúmenes REALES por tipo de aviso
  (para comprobar que lo que se publicaría no lleva usuario, clave ni IP).
- `python __sonda_run.py __muro_vivo.php murovivo-2026-9kQ7` — inserta **un aviso de prueba** en el
  registro y enseña qué devolvería el AJAX (así se comprueba el «en vivo»; también lista los últimos
  avisos con su `es_bot`).
- `python __sonda_run.py __muro_limpiar.php murolimpia-2026-9kQ7` — borra esas filas de prueba
  (clave `prueba_vivo`), sin tocar ningún aviso real.
- `python __sonda_run.py __tg_diag.php tg-diag-2026-9kQ7` — aísla errores de SQL de la suscripción
  (así se encontró el choque de collations).
- `python __sonda_run.py __psv_limpiar.php psv-limpia-2026-9kQ7 [go]` — borra los datos de prueba
  (encargos inventados, propuestas, WhatsApp de prueba y suscripciones de prueba; sin `go` es simulacro).

## 8. LO QUE QUEDA PENDIENTE (y lo que NO hay que hacer)

- **Añadir tipos al muro**: hoy salen las visitas, los clics de precio, las llamadas, las búsquedas, las
  opiniones, los encargos y las altas. Para añadir otro tipo basta con meterlo en `muro_tipos()` **y
  escribir su texto seguro** en `muro_evento_datos()` (jamás el `resumen` del registro: trae claves).
- **El chat 🥷 todavía no publica encargos**: cuando no encuentra algo, lo resuelve la IA. Engancharlo
  es el siguiente paso natural (mismo motor, `origen='chat'`).
- **El bloque de la portada** (como el slide de noticias) está por decidir con el jefe.
- ⚠️ **El registro guarda usuario y clave en texto claro** en el resumen de `usuario_nuevo` /
  `tienda_nueva` (lo pone «El maestro» al crear la cuenta). No es un problema de este módulo, pero
  conviene limpiarlo en `includes/tienda_ia.php` cuando se pueda.
- **No** añadir un chat dentro de la lista, **no** publicar el número del comprador, **no** publicar el
  `resumen` del registro en el muro y **no** usar la palabra «tablón» en ninguna parte del sitio.
