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


# GUÍA DE 👑 «EL SUPREMO» — LA VENTA EN VIVO (`/supremo`) · dechimbote.com

> **Cuándo leer esta guía:** cuando haya que tocar, revisar o explicar **El Supremo**, la página
> **privada del Súper Administrador** para crear tiendas **delante del cliente** (el «modo vendedor»)
> y los **códigos** que entrega (la cabecera, la música y las imágenes de los productos).
> **Qué resuelve:** el jefe está con un cliente, le toma 3 fotos al negocio, y en 4 o 5 minutos la
> tienda queda **publicada en internet**, con su portada, su música y sus productos, y con los
> **códigos listos para copiar** y pegar en Gemini/Flow mientras el cliente mira.
> **Estado:** ✅ **EN PRODUCCIÓN** (creado, desplegado y probado el **2026-09-18**: **37 de 37**
> pruebas de la sonda del camino real, más la verificación de las 4 vistas sin abrir el navegador).
> **Archivos hermanos:** `GUIA_CONSTRUCTOR_DE_TIENDAS.md` (🛠️ El maestro, su hermano y su motor),
> `GUIA_OFERTA_50_VENTAS.md` (💰 **la oferta que cierra la venta y su calculadora**),
> `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` (las portadas en lote), `GUIA_PROMPTS_DE_MUSICA.md` (el formato
> de la canción), `GUIA_SUPERADMIN_PANEL.md` (el panel donde vive su botón).

---

## §1. QUÉ ES Y POR QUÉ ES DISTINTO DE EL MAESTRO

Pedido del jefe (2026-09-18, textual y resumido): *«actualmente tenemos una página para crear tiendas
con Inteligencia artificial llamada El maestro. Necesito que crees algo similar para mí, que soy el
súper administrador, pero llamada **El Supremo**… **el supremo solamente va a ser usado por el súper
administrador, nadie más lo puede usar**, así que el único enlace se encuentra en mi panel de súper
administrador… me va a servir para **crear tiendas en vivo delante del cliente**… Voy a entrar en
**modo vendedor** y me va a mostrar, además de lo que ya hace El maestro, también **promos, códigos
que voy a escribir en Gemini para poder crear cabeceras, código para poder crear música en Google
Flow, y también código para generar tres productos que invente en base al contexto que tengan las
tiendas**… yo desde El Supremo, con la ayuda de un Pro, voy a poder también darle una portada a una
cabecera… puede que la usabilidad no sea tan sencilla, **puedes hacerlo un poco más elaborado**, para
que me pregunte **cuál es la portada, cuáles son los productos que deseo incluir, cuáles son los
productos que deseo crear con Inteligencia artificial** (las imágenes) **y quedarte esperando las
imágenes. En promedio todo debe tomar 4 a 5 minutos**»*.

**Las cuatro diferencias con 🛠️ El maestro:**

| | 🛠️ El maestro (`/crear-tienda`) | 👑 El Supremo (`/supremo`) |
|---|---|---|
| **Quién entra** | Cualquiera (el dueño del negocio) | **Solo el Súper Administrador** |
| **Para qué** | Que el dueño arme su tienda solo, en su casa | Que el jefe la arme **en vivo, delante del cliente** |
| **Qué entrega al final** | La tienda publicada y su enlace | La tienda **+ 4 códigos listos para copiar** (cabecera, música, imágenes de los productos y el mensaje con la clave del cliente) |
| **Los productos** | Los que la IA vio en las fotos | Los que vio **+ 3 que inventa la IA** con el contexto, y el jefe elige cuáles llevan imagen |

Lo que **NO** se duplica (es el mismo código de El maestro, tal cual): la lectura de las fotos con
visión, los rubros reales del directorio, la publicación de la tienda (slug, rubros, fotos, cuenta,
aviso al jefe, HubSpot y su canción), el WebP, el copywriting y las opiniones automáticas.

---

## §2. 🔒 SOLO EL SÚPER ADMINISTRADOR (las tres cerraduras)

1. **El único enlace** que existe a la página es el botón de la pestaña
   **👑 El Supremo (venta en vivo)** del panel: `superadmin.php?seccion=supremo`.
   **No se enlaza desde ninguna página pública** (ni portada, ni menú, ni pie, ni buscador).
2. **La página** (`supremo.php`) comprueba `es_admin()`: sin sesión manda al login con
   `?redirect=supremo`, y con una cuenta que no es de administrador responde **403** con un aviso.
3. **La puerta de la API** (`api/supremo.php`) lo vuelve a comprobar en **cada** petición (GET y POST)
   y también el CSRF: sin ser administrador responde **403** (`no_admin`).

Comprobado por HTTP el 2026-09-18 (sin abrir ninguna pestaña):

| URL | Sin sesión |
|---|---|
| `/supremo` | **302** → `login.php?redirect=supremo` |
| `/api/supremo.php` | **403** `{"ok":false,"error":"no_admin",…}` |
| `/superadmin.php?seccion=supremo` | **302** → login |
| `/crear-tienda` (El maestro) | **200** (intacto) |

---

## §3. EL FLUJO, PASO POR PASO (el del modo vendedor)

| # | Paso (`paso`) | Qué dice / qué hace | Quién aporta |
|---|---|---|---|
| 0 | `arranque` | 👑 *«El Supremo — modo vendedor. En **4 o 5 minutos** dejamos la tienda del cliente publicada…»* → `🚀 Empezar la venta` · `❓ Cómo se usa` | local |
| 1 | `ayuda` | El resumen de los 5 movimientos de la venta (por si el jefe lo abre en frío) | local |
| 2 | `ubicacion` | 📍 **LO PRIMERO ES LA UBICACIÓN** (orden del jefe, 2026-09-18: *«no importa en qué circunstancias, siempre se debe saber la ubicación desde donde se creó la tienda… para pedirla solamente suficiente componer el botón de ubicación»*). El botón **`📍 Compartir mi ubicación`** (principal, GPS) y, si el GPS falla, los distritos en tarjetas. Con eso se guarda **lat/lng + distrito** y sigue a las fotos | local (GPS del navegador) |
| 3 | `fotos` | 📷 *«Las fotos del negocio del cliente (3 a 8)»*. 🔴 **SIN BOTÓN DE CONFIRMACIÓN** (orden del jefe, 2026-09-18: *«después de subir las fotos aparece un botón de confirmación «ya está, seguir»: es un paso innecesario; si ya lo subió, de frente debe decir qué nombre se le ha ocurrido»*): con la primera foto ya subida y leída, el motor **pasa solo** a la pregunta del nombre. Mientras responde **puede mandar más fotos** (el paso del nombre deja los botones de cámara/galería a la vista) | local + **IA mira y lee** (letrero, rubro, teléfono, lo que vende) |
| 4 | `nombre_ok` | ✅ **EL NOMBRE, PROPUESTO Y CON BOTONES.** La IA propone un nombre y se pregunta **`✅ Sí: «X»`** · **`❌ No, otro nombre`**; al «No» propone **el siguiente** (de una cola de 4 que se pide en **una sola llamada** al entrar: por eso el «No» es instantáneo) y desde la segunda propuesta aparece **`✏️ Escribir`** (el cursor se va solo al área de escribir). **Lo que el cliente escriba vale como respuesta** en cualquier momento (`tienda_ia_nombre_validar()`). Motor: `supremo_ia_nombres()` + `supremo_nombre_siguiente()` | local + **IA propone** |
| 5 | `rubro_ok` | 🏷️ *«Por las fotos diría que es **ferretería**. ¿Le atino?»* → **`✅ Sí`** · **`❌ No, es otro`**. Con el «No» salen las opciones en **GRILLA 2 × 2**: **tres rubros reales del directorio + `✏️ Ninguno de estos`** (se rellenan con los rubros que complementan al candidato para que la grilla siempre sea 2 × 2). El «Sí» guarda el rubro **real** del directorio (`tienda_ia_rubros_candidatos()`, sin IA) | local + **IA leyó** |
| 6 | `rubro_mas` | 🏷️ *«¿Le sumas otros rubros? Hasta 4 en total»* → **GRILLA 2 × 2 con cuatro rubros** y, abajo, **`✅ Continuar`** (antes decía «Ya está, seguir») | local |
| 7 | `tipo` | 🏪 **GRILLA 2 × 2 con CUATRO botones**: `Tiene su local` · `Es ambulante` · `Vende por internet` · `Vende a todo el país` | local |
| 8 | `horario` · `entregas` | 🕐 El horario del ambulante (**5 opciones EN DOS COLUMNAS**, no se escribe) o los distritos de entrega en tarjetas + `✅ Continuar`. 🔴 **LA DIRECCIÓN YA NO SE PREGUNTA** (orden del jefe, 2026-09-18: *«se supone que ya pusimos la ubicación, ya no debe pedir dirección»*): quien tiene su local pasa **derecho al WhatsApp del cliente**. El paso `direccion` sigue vivo **fuera del flujo** (menú ⋯ y 🧠 «Piensa mejor») para completarlo si hace falta | local |
| — | `distrito` · `direccion` | 📍 **Fuera del flujo**: la zona y la dirección se pueden corregir **en cualquier momento** (menú ⋯ → `📍 Compartir ubicación`, o el botón de 🧠 «Piensa mejor»). Al terminar, la venta **vuelve al paso donde estaba** (`sup_volver`) | local |
| 9 | `cliente` | 📱 **EL WHATSAPP ES OBLIGATORIO** (orden del jefe: *«no aceptamos tiendas sin número de WhatsApp, simplemente no aceptamos»*: ya no existe el botón «sin número»). Se **propone el número que la IA leyó en las fotos** (*«De sus fotos saqué este número: 943112233. ¿Es su WhatsApp?»*) con **`✅ Sí, es …`** · **`❌ No, es otro`** · **`✏️ Escribir el número`**; lo que escriba vale como respuesta. Con ese número se le crea su cuenta (usuario = su número) y **la tienda se publica** | local + **IA leyó** |
| — | **🚀 LA PUBLICACIÓN** | `supremo_publicar()`: se crea la tienda **a nombre del cliente** y **se le conoce la clave**. ⚡ **NO GASTA IA** (orden del jefe, 2026-09-18: *«está tardando un poquito… procuremos optimizar el resultado lo más rápido»*): la ficha nace con el **relato en texto plano** y en el mismo movimiento se arman **los 8 productos**; ahí, con los productos ya dentro, se escribe **la descripción definitiva** (una llamada) y se siembran **las primeras opiniones SIN IA** (respaldo local, con los productos de verdad). Para El maestro no cambia nada: el copy en la publicación es un parámetro nuevo (`tienda_ia_publicar(..., $sin_copy = true)`). | local + **IA escribe** (en el paso siguiente) |
| 10 | `sup_portada` | 🎨 **YA NO SE PREGUNTA** (orden del jefe: *«la portada va a ser solamente un texto artístico encima de una foto que tenga que ver con el rubro… una foto genérica con un texto artístico encima»*): el motor deja `sup_portada = 'ia'` y sigue derechito a los productos. El **código de la cabecera** (§4.1) pide justo eso: foto genérica del rubro + el nombre como texto artístico protagonista | local |
| 11 | `sup_portada_fotos` | 📷 la tira con **las fotos del cliente** (queda **fuera del flujo**, igual que `direccion`: se llega desde el menú) | local |
| 12 | `sup_productos` | 🛍️ **LOS 8 PRODUCTOS, SIN PREGUNTAR NADA** (orden del jefe: *«no preguntes qué productos: de frente crea los productos de frente y pide las imágenes… tú elige siempre; la tienda siempre tendrá ocho productos»*). Los arma `supremo_armar_productos()` **al entrar al paso**: primero los que la IA **vio** en las fotos (hasta 8, con su foto) y los que falten los **inventa** con el contexto, en tandas de 4. Los inventados quedan **marcados para pedir su imagen** (`sup_con_ia`) | **IA crea** |
| 13 | `sup_codigos` | 👑 **LOS CÓDIGOS**: el panel se abre **solo** con las 4 tarjetas y su botón de copiar. Mientras el diseñador dibuja, la venta sigue | local |
| 14 | `sup_imagenes` | 📥 **La sala de espera**: se suben **todas las imágenes de una vez** (hasta 12). El motor **lee el ID impreso** dentro de cada foto y la pone en su producto; a la cabecera la reconoce por el nombre del negocio. Lo que no se puede ubicar **no se publica** (se avisa cuál) | **IA lee los IDs** |
| 15 | `sup_cancion` | 🎵 **LA CANCIÓN**: se sube el audio que el jefe **bajó de Flow** (botón **🎵 de la cabecera**, en cualquier momento de la venta) y queda **sonando en la ficha del cliente**, con su reproductor ahí mismo. Se guarda tal cual llega y **no gasta créditos de Treblo**. Subir otra la reemplaza. Detalle: §4-bis | local (sin IA, sin créditos) |
| 16 | `sup_piensa` | 🧠 **PIENSA MEJOR** (orden del jefe: *«un botón de piensa mejor que lo que hace es analizar lo que el usuario publicó y volver a reestructurar los datos y, si es necesario, pedir datos que se haya faltado»*). **Una** llamada de IA que repasa la tienda y **reescribe la descripción** + **mejora los títulos y precios** de los productos (nunca crea ni borra). Debajo, un botón por **cada dato que falta** (medido por `supremo_lo_que_falta()`, sin IA): se abre su paso y **se vuelve aquí mismo**. Detalle: §3-bis | **IA reescribe** + local |
| 17 | `sup_oferta` | 💰 **LA OFERTA**: la cuenta que cierra la venta. Se escribe el precio de un producto (o se toca el suyo, que ya trae precio) y sale **en vivo**: ingreso con 50 ventas, el 10 % que cobrarían otros, la tarifa, **el ahorro** y la **comisión efectiva**. Con la frase para decir en voz alta y el **aviso de que con un producto muy barato la cuenta no luce**. Si no se quiere, se salta. Detalle: **`GUIA_OFERTA_50_VENTAS.md`** | local (aritmética, **sin IA**) |
| 18 | `sup_fin` | 🎉 **Venta cerrada** con sus minutos, **su enlace visible** y sus productos. Los botones, en este orden: **`🏬 Ver la tienda (abrir)`** — es un **ENLACE de verdad**, se abre en otra pestaña para enseñársela al cliente — · **`📲 Mandarle sus datos por WhatsApp`** · `👑 Otra venta en vivo` · `💰 Ver la oferta y el guion otra vez` · `👑 Ver los códigos otra vez` | local |

🔗 **UN SOLO ENLACE EN EL MENSAJE DE WHATSAPP** (orden del jefe, 2026-09-18: *«en estos mensajes de
WhatsApp no pueden haber dos links en el mismo mensaje, solamente el WhatsApp de la tienda que se ha
creado»*): el texto que se le manda al cliente lleva **solo el enlace de SU tienda** (+ su usuario y su
clave); el panel se explica en palabras, sin enlace. Lo vigila la prueba 28-quater.

⚠️ **`sup_inventar` y `sup_ia_elegir` quedaron FUERA del flujo** el 2026-09-18 (los reemplazó el paso
de los 8 productos). Sus ramas siguen en el guion y en el motor **solo** para que una venta vieja
guardada en la base (con ese paso) se pueda reabrir sin romperse. Ya no se llega a ellas.

🚫 **Y EL PASO DE LOS 8 PRODUCTOS NO PIDE ELEGIR NADA** (orden del jefe, 2026-09-18: *«nuevamente me
pregunta «toca los productos que sí van a la tienda»: eso ya es por las puras, se supone que ya estoy
aceptando los negocios que ha creado la guía… no debe mostrarme ese mensaje porque me está haciendo
elegir nuevamente»*). El guion ya no traía la rejilla, pero **el API se la seguía mandando** en el campo
`items` (`sup_json_sesion()` la llenaba con `supremo_productos_vistos()`): el navegador pintaba la
elección igual. Desde ahora **los `items` salen SOLO del guion**, así que ningún paso puede mostrar una
rejilla que no haya pedido.

📝 **CERO TEXTOS DE RELLENO** (misma orden): se quitaron
el **comentario de la visión** en el paso de las fotos (*«se ve tu tienda bien surtida de rollos de
plástico…»*, que además era un mensaje para el vendedor y **no decía el número** que pedía confirmar),
el aviso de «**aquí están los códigos**, copia cada uno con su botón y pégalo» (el panel ya se abre solo)
y el «le escribí su descripción» de la publicación. Y el `comentario` **tampoco entra en el relato** que
se guarda como descripción provisional: es un mensaje para el vendedor, no texto de la ficha.

**⏱️ El cronómetro:** la cabecera lleva un reloj con la venta en marcha (verde hasta los 5 minutos,
naranja hasta 7:30 y rojo después). Se enciende con el primer movimiento y se para al cerrar. En la
pestaña del panel se ve el **promedio real de minutos por venta**.

**💰 Y el botón de la oferta va SIEMPRE en la cabecera** (no solo en su paso): el jefe abre la
calculadora en cualquier momento de la venta, con el cliente mirando. **No gasta nada** (es
aritmética, no IA) y se puede tocar el precio cuantas veces quiera.

---

## §3-bis. 📍 LOS BOTONES QUE ESTÁN SIEMPRE (la ubicación y «piensa mejor») — 2026-09-18

Los dos se pueden tocar **en cualquier momento de la venta**, en medio de cualquier paso, y **no
desordenan el flujo**: la venta sigue exactamente donde estaba. El mecanismo es uno solo y vive en
`supremo_volver(&$d)`: el paso de origen se guarda en `datos['sup_volver']` y el paso suelto, al
terminar, **vuelve ahí** (y si no hay nada guardado, al WhatsApp del cliente). Lo usan los pasos
`direccion`, `horario`, `entregas` y `distrito`.

### 📍 Compartir la ubicación (menú ⋯, SIEMPRE)

* **En el menú ⋯ hay un `📍 Compartir ubicación`** (orden del jefe: *«en el menú de opciones poner ahí
  también el acceso directo a compartir ubicación, así se puede compartir ubicación en cualquier
  momento»*), y el paso `ubicacion` **abre la venta pidiéndola** con ese mismo botón.
* El navegador manda el GPS a la API (`accion: 'ubicacion'`) → `supremo_ubicacion_ahora()`:
  * guarda `lat` / `lng` y saca el **distrito** del punto (`tienda_ia_distrito_por_gps`);
  * **si la tienda ya está publicada, lo escribe en su ficha** (`directorio_negocios.lat/lng/distrito_id`):
    es la ubicación con la que la ve el cliente final en «cerca de mí»;
  * **no mueve el paso**. Si la venta estaba en `ubicacion` (o en `distrito`), sí la manda a las fotos
    (es su camino natural).
* Si el GPS falla o el navegador no da permiso, el navegador manda `accion: 'zona'` →
  `supremo_zona_abrir()`: abre el paso de la zona (botón + distritos en tarjetas) recordando de dónde
  vino, y al elegir devuelve la venta a su sitio.

### 🧠 Piensa mejor (botón 🧠 de la cabecera y menú ⋯)

* Abre el paso `sup_piensa` desde donde esté la venta (`accion: 'piensa'` → `supremo_piensa_abrir()`),
  guardando el paso de origen en `datos['sup_piensa_volver']` para que **«💰 Seguir con la oferta»**
  devuelva la venta a su sitio (y, si venía del flujo normal canción → piensa, siga a la oferta).
* **Una sola llamada de IA** (`supremo_ia_piensa()`) que devuelve `NOTA`, `DESCRIPCION` y `PRODUCTOS`
  (títulos mejor escritos y un precio razonable si faltaba). `supremo_piensa_aplicar()` **escribe en
  la ficha** la descripción nueva y **actualiza los productos que ya existen**: nunca crea, nunca
  borra, nunca cambia el nombre ni el enlace de la tienda.
* **Lo que falta se mide sin IA** (`supremo_lo_que_falta()`): zona, fotos (menos de 3), dirección,
  horario, WhatsApp del cliente (si aún no se publicó), rubro y los productos (menos de 8, o sin foto).
  Cada uno es un botón `ir:<paso>`: se abre su paso y se vuelve a `sup_piensa`.
* **«🧠 Analizar otra vez»** limpia la marca y **vuelve a llamar a la IA** (el vendedor decide cuándo).
  Entrar de nuevo al paso **no** repite la llamada: se recuerda con `datos['sup_piensa_hecha']`.
* **En el flujo normal está entre la canción y la oferta** (pasos 16 → 17): la venta repasa antes de
  hablar del dinero, y con `💰 Seguir con la oferta` se pasa a la cuenta.

---

## §4. 👑 LOS CUATRO CÓDIGOS (lo que hace único al Supremo)

Se arman **con los datos reales de esa tienda** y se muestran en tarjetas con su botón **📋 Copiar**
(regla inviolable n.º 1 del proyecto: al jefe no se le pide seleccionar texto, se le da el bloque
entero para un clic). Salen de `supremo_codigos($d)`.

### 1 · 🎨 LA CABECERA (la portada) — para **Gemini/Flow de imágenes**, con una imagen adjunta

**Es un TEXTO ARTÍSTICO ENCIMA DE UNA FOTO DEL RUBRO** (orden del jefe, 2026-09-18, con un ejemplo a la
vista: piñatas y estanterías de una piñatería/librería de fondo y el nombre en letras grandes con
contorno y adornos encima). La foto **no** es el local del cliente: es una **imagen genérica del rubro**
que se entiende de un golpe de vista, y el protagonista es el nombre.

```
PEDIDO DE 1 IMAGEN — dechimbote.com (la CABECERA de la tienda)

Crea una imagen con un fondo de una ferretería y un texto que diga «Ferretería El Tornillo».
Copia los estilos de diseño, color, forma y fuentes de la imagen ingrediente que te adjunto.
Añade además, en texto plano y discreto, el número 1882.
…
```

* El **fondo** sale de lo que la IA **leyó en las fotos** («ferretería» → «una ferretería»): el
  artículo lo pone `supremo_fondo_palabras()` (femenino por la terminación). Si no leyó nada, se
  singulariza el rubro del directorio.
* La imagen pide la **mercadería del rubro llenando el fondo** (que se vea de qué es la tienda) y el
  **nombre en letras grandes, artísticas y con volumen**, con contorno grueso y colores que contrasten,
  «como un rótulo pintado a mano», con adornos del rubro alrededor que no tapen las letras.
* El **texto** es el nombre tal cual y el **número** es el **ID de la tienda** (para poder asociar
  la portada cuando llegue), pequeño, en cifras simples y en una esquina.
* Lleva las órdenes de la casa: foto real (nada de 3D), limpia y con colores comerciales, el nombre
  como único texto con diseño, y **prohibido copiar los textos de la imagen ingrediente** (R17).

### 2 · 🎵 LA MÚSICA — para el **generador de música de Google Flow**

Las 5 líneas obligatorias del formato del proyecto, con la regla vigente del **2026-09-18**:

* Empieza **siempre** con `Activa tu modo de crear música y crea la siguiente canción:` (sin esa
  línea el generador no sabe si queremos música, video o PDF).
* El **telonero da un TEXTO DE BIENVENIDA** (nombre + qué es + distrito) y **no dice ningún número
  ni código** (la vieja regla del ID en letras queda en pausa).
* El **Cuerpo** son ~50 palabras de la **descripción REAL** que la IA le acaba de escribir a la
  tienda (`supremo_descripcion_guardada()`), sin emojis (`supremo_descripcion_plana()`).
* El **género** sale del rubro con un mapa de 40+ oficios (`supremo_genero_musica()`), con respaldo
  «pop latino alegre y moderno con base bailable».

### 3 · 🛍️ LAS IMÁGENES DE LOS PRODUCTOS — para **Flow de imágenes**

**📱 VAN DE UNO EN UNO (orden del jefe, 2026-09-18, desde el celular: *«los prompts de imagen dámelos
de uno en uno cuando estoy en mobile»*).** En el panel de códigos, la tarjeta de los productos **no
muestra una carta larga**: muestra **un prompt por producto**, con **‹ ›** para pasar al siguiente,
**puntos numerados** para saber por dónde va (el que ya copió queda **en verde**) y un botón grande
**📋 Copiar este prompt**. Cada prompt es **corto (~1 000 caracteres) y autosuficiente**, así que se
pega en Flow **en un mensaje aparte**, se espera su imagen y se pasa al siguiente:

```text
PEDIDO DE 1 IMAGEN — dechimbote.com (producto 2 de 3)

🔴 PRIMERO: escribe DENTRO de la foto, pequeño, discreto y en LETRA BLANCA, el número 12368
(cifras simples y rectas, en una esquina, sin adornos, sin cajas y sin sombras).
Sin ese número la imagen no me sirve.

Crea una FOTO REAL de «Balde de pintura blanca» para la tienda «Ferretería El Tornillo»,
ferretería en Chimbote, Áncash (Perú).
QUÉ SE VE: pintando una pared con rodillo, con el balde abierto y la brocha al costado
QUÉ ES (para que salga bien): pintura lavable para paredes de interior, rinde bastante.
 Se vende a S/ 45.00.
· Foto orgánica y real, en 1:1 (cuadrada), limpia, ordenada, elegante y con colores
  comerciales vivos. PROHIBIDO dibujo, caricatura, 3D o render.
· El ÚNICO texto de la imagen es el número 12368. Nada de teléfonos, precios, logotipos,
  carteles, marcas de agua ni el nombre del negocio. Todo en español.
· Si aparece una persona: trabajando y de perfil, sin rostro identificable.

✅ RECUERDA ANTES DE ENTREGAR: esta imagen tiene que llevar escrito el número 12368.
```

Lo que **no** se puede perder de cada prompt (si falta, la imagen no sirve): la orden del número **al
principio Y al cierre**, el contexto del negocio y el rubro, el «QUÉ SE VE» (el plano que inventó la
IA) y las órdenes de la casa condensadas. La **carta completa** (los N productos juntos, con sus 4
órdenes y su manifiesto) **sigue disponible** en el botón *«📋 Copiar los N juntos»*, para quien la
quiera de una sola vez en la computadora. Motor: `supremo_codigo_producto_uno()` ·
`supremo_codigo_productos()` (la carta) · `piezas` en `supremo_codigos()`.

* `0) 🔴 LO PRIMERO DE TODO — LA ORDEN OBLIGATORIA DEL NÚMERO` con la lista
  «IMAGEN N.º 1 → escribe el número 12338»…
* Las **4 órdenes del jefe**: 100 % en español · 100 % orgánicas · limpio, elegante y de impacto ·
  **el único texto es el número del ID**.
* Cada pedido lleva **QUÉ ES** (la frase que inventó la IA), **LA ACCIÓN QUE QUIERO VER** (el
  «plano» que dio la IA) y los detalles de estructura y ambiente limpio.
* Al final, el **MANIFIESTO** para que el diseñador devuelva qué se ve en cada imagen.
* Solo entran los productos marcados en `sup_ia_elegir` (`sup_con_ia`); si no hay ninguno, la
  tarjeta lo dice y no se pide nada.

### 4 · 📲 EL MENSAJE PARA EL CLIENTE — **ES UN BOTÓN DE WHATSAPP**

**Orden del jefe (2026-09-18): *«el mensaje para el cliente debe ser un botón de WhatsApp para
mandarle el mensaje»*.** Ya no se copia y se pega a mano: la tarjeta trae un **botón verde de
WhatsApp** (el verde oficial con **su ícono**, grande para el dedo) que abre **el chat de ESE
cliente** —su número ya va puesto— **con el mensaje escrito**: su enlace, su usuario y su clave.
El vendedor solo le da **enviar**.

* Enlace: `https://wa.me/51<sus 9 dígitos>?text=<el mensaje>`, con la misma receta del resto del
  sitio (motor: `supremo_wa_cliente()`).
* **Sin número no hay botón** (nunca un enlace roto): en ese caso queda el texto para copiar.
* Debajo del botón sigue estando **el mensaje completo** por si prefiere copiarlo.
* **Y el cierre de la venta (`sup_fin`) lleva el mismo botón** como opción con `url`.

---

## §4-bis. 🎵 LA CANCIÓN: SUBIRLA DESDE EL SUPREMO (2026-09-18)

> **Lo que dijo el jefe:** *«No hay modo de subir la canción descargada al Supremo.»* Y era verdad:
> El Supremo **daba el código** de la música (para Google Flow) pero **no había dónde meter el
> audio** que sale de ahí. Hasta hoy la publicaba un agente con `__cancion_uno.py` (convertía a OGG
> y la subía): el jefe dependía de que alguien corriera el script.

**Ahora lo sube él, cuando quiera:**

* **Botón 🎵 en la cabecera** (aparece **en cuanto la tienda está publicada**): abre el panel de la
  canción. También está en el menú **⋯ → 🎵 Subir la canción de la tienda**.
* **Desde cualquier paso**: el audio se manda con `valor = cancion_subir` y el motor lo atiende
  **antes del switch**, así que no hay que llegar al paso `sup_cancion` para subirlo.
* **Paso propio `sup_cancion`** en el recorrido (entre las imágenes y la oferta): si la tienda **ya
  tiene** canción, la muestra con su **reproductor** y ofrece **cambiarla**; si no, pide el audio.
* **El reproductor** (▶️) está en el panel y en una **tira** dentro de la página cuando la canción ya
  está puesta, para oírla antes de enseñar la ficha.

**Cómo se guarda (reutiliza el motor del sitio, sin inventar nada):**

| Paso | Qué pasa |
|---|---|
| 1 | Se valida: que **sea un audio** (mp3, m4a, aac, ogg, oga, opus, wav, flac, wma), que pese menos que el **tope real del servidor** (**19 MB** medidos; se calcula con `upload_max_filesize`/`post_max_size`) y que no venga vacío. |
| 2 | Se mueve a `assets/uploads/canciones/tmp/` y **`cancion_guardar_audio()`** (el del módulo de canciones) la deja en `assets/uploads/canciones/cancion-<slug>-<id>.<ext>` **midiendo su duración de verdad**. |
| 3 | 🛡️ Si **no se le puede medir la duración**, no es una canción: se borra y se avisa (**no se traga cualquier archivo**). |
| 4 | Se guarda la fila en **`directorio_canciones`** con `estado = 'listo'` → **`cancion_de_negocio()` la encuentra** y el reproductor de la ficha (`cancion_player.php`) la pone a sonar **sola**. |
| 5 | Si la tienda ya tenía canción, la fila se **actualiza** (no se duplica) y el archivo viejo se borra si cambió de nombre. |

* 🆓 **NO se llama a Treblo: no gasta ni un crédito.** Esta es **la vía gratis** de tener la canción.
* 🎧 **Se guarda tal cual llega** (orden del jefe: *«no pierdas tiempo recortando ni reeditando»*):
  no se recorta, no se recomprime y no se toca el formato.
* ⚠️ El `.mp3` **no se comprime** como las fotos (es música): el navegador lo sube entero.
* Prueba: **`__sup_cancion.php`** (11 de 11: sube un audio fabricado en el servidor, comprueba que la
  ficha lo encuentra, que un `.txt` se rechaza, que la segunda subida reemplaza sin duplicar la fila,
  que el atajo del botón funciona **desde otro paso**, y borra todo al terminar).

---

## §5. CÓMO ESTÁ HECHO (arquitectura)

```
assets/js/supremo.js ──POST/GET──▶ api/supremo.php ──▶ includes/supremo.php ──▶ api.deepseek.com
 (la conversación, el reloj,          (la puerta: solo el   (el motor: pasos, guion,    (el mismo modelo
  el panel de códigos, la              admin + CSRF)         los 4 códigos, la           con visión de
  rejilla y la sala de espera)                              publicación y las fotos)    El maestro)
                                                                   │
                    includes/config_supremo.php ◀──────────────────┤ (topes y textos del módulo)
                                                                   ▼
        includes/tienda_ia.php  ◀── SE REUTILIZA: visión, rubros, publicación, WebP, copywriting
                                                                   ▼
   directorio_ia_tiendas (modo='supremo') · directorio_ia_tiendas_log · directorio_negocios · …
```

| Archivo | Qué es |
|---|---|
| `deploy/includes/config_supremo.php` | El nombre, el emoji, los topes (3 a 8 fotos, **8 productos por tienda** — los 3 de la IA son los que se piden por llamada —, lote de 12 imágenes, meta de 5 minutos) y los colores. |
| `deploy/includes/supremo.php` | **El motor**: los 18 pasos, el guion con respaldo local, los 4 códigos, la IA que arma los 8 productos y la que lee los IDs, la publicación, la portada, la sala de espera, **la ubicación (`supremo_ubicacion_ahora` / `supremo_zona_abrir`) y 🧠 «Piensa mejor» (`supremo_ia_piensa` / `supremo_piensa_aplicar` / `supremo_lo_que_falta`)**. |
| `deploy/api/supremo.php` | **La puerta**: `es_admin()` + CSRF, GET/POST/JSON/multipart. Acciones: `responder` · `reiniciar` · `salir` · **`ubicacion`** (GPS) · **`zona`** (elegir distrito a mano) · **`piensa`** (abrir «Piensa mejor»). |
| `deploy/supremo.php` | **La página** (URL amable **`/supremo`**): pantalla completa, el reloj, el panel de los códigos, **el panel de la oferta (💰)**, **el botón 🧠**, la rejilla y la sala de espera. |
| `deploy/assets/js/supremo.js` · `assets/css/supremo.css` | La conversación (chips, tarjetas de distrito, dictado, fotos múltiples) y **lo propio**: el panel de códigos, el reloj, la rejilla de productos, la tira de lo que llegó, **`compartirUbicacion()` / `irAZona()` / `abrirPiensa()`**. Reutiliza `tienda_ia.css`. |
| `deploy/includes/vista_supremo_admin.php` | La pestaña **👑 El Supremo** del Súper Admin: ventas, minutos por venta, productos, imágenes, gasto y el **botón que abre la página**. |
| `deploy/superadmin.php` · `deploy/.htaccess` | El enlace del menú (negro y oro), la sección `supremo` y la regla de la URL amable. |

### Las dos líneas que se tocaron de El maestro (compatibles hacia atrás)

1. `tienda_ia_actual()` acepta el modo **`supremo`** (antes solo `nueva` y `producto`): así El Supremo
   usa **la misma tabla y el mismo motor de sesiones**, sin duplicar el guardado, el chat ni el
   medidor de consumo. Su tope diario de tiendas no aplica (quien crea tiendas ahí es el jefe).
2. `tienda_ia_stats($dias, $modo_excluir = '')`: la pestaña de El maestro le pasa `'supremo'` para
   que **cada módulo muestre sus números limpios** (por defecto no filtra: nadie más cambia).

> ⚠️ **TRAMPA DE PHP (la misma que mordió al escribir `crear_tienda_ia.php`):** dentro de un
> comentario de bloque, la secuencia **asterisco-barra** cierra el comentario. Por eso en `supremo.php`
> la URL amable **no** se escribe con los asteriscos de markdown: el archivo no compila.

> 🔴 **LA CACHÉ DEL HOSTING (2026-09-18, nos mordió de verdad):** los estáticos se sirven **cacheados
> por URL**, así que después de subir el JS o el CSS **el navegador (y hasta `curl`) siguen recibiendo
> los bytes viejos** aunque el FTP diga «SUBIDO». Comprobado ese día: `supremo.js` daba 34 178 bytes
> por HTTP y 49 763 con `?v=99`. → **Cada vez que se cambie el JS o el CSS hay que SUBIR el número de
> `?v=` en `supremo.php`** (hoy van en **`supremo.js?v=9`** y **`supremo.css?v=7`**) y volver a subir la
> página. La página misma lleva `Cache-Control: no-store` para que el HTML nunca se quede viejo. Lo
> comprueba `__sup_ver.php` (exige ver los dos `?v=` nuevos en el HTML).

---

## §5-bis. 📷 EL PESO DE LAS FOTOS (2026-09-18)

> **La pregunta del jefe:** *«¿Se puede comprimir las imágenes o cambiar su tamaño antes de subirlo
> al hosting? Porque pesan 4 MB en promedio cada foto tomada con el celular… o en todo caso
> tendríamos que crear una aplicación Android que tome fotos nítidas pero en tamaño pequeño, es
> decir máximo 800 px como valor máximo para su ancho o largo.»*

**Respuesta: sí, ya se hace — y no hace falta ninguna aplicación.** La compresión ocurre **en el
propio celular, dentro del navegador, antes de subir la foto**
(`deploy/assets/js/imagen_optimizar.js`: `canvas` → **WebP**, con `imageOrientation` para respetar
las fotos de lado). Es la misma librería que usan El maestro, El caminante, los editores del panel y
los banners: **está enganchada en todas las puertas del sitio**.

**Lo que se midió el 2026-09-18** (foto de 12 MP **3024 × 4032** con mucha textura, el peor caso:
**8,97 MB**; encoder WebP con la misma calidad que usa el navegador, `libwebp -quality 82`):

| Tamaño máximo (lado mayor) | Peso que sube | Veces más liviana |
|---|---:|---:|
| 3024 px — como sale de la cámara | **8,97 MB** | — |
| 1600 px — **lo que se usaba** | 1,34 MB | 7× |
| **800 px — LO QUE SE USA HOY** | **192 KB** | **47×** |
| 640 px (si algún día se quiere aún menos) | 97 KB | 92× |

* 🔴 **Y había un problema de verdad detrás de la pregunta:** con **8 fotos de 4 MB** la tanda pesaba
  **32 MB** y **no cabía** en el `post_max_size` del hosting (**20 MB** medidos): la subida **fallaba**.
  A 800 px las 8 fotos juntas pesan **~1,5 MB** y suben al instante — y el cliente **no gasta sus
  datos móviles**.
* ⚙️ **Se cambia en UNA línea:** `SUPREMO_FOTO_LADO` (800) y `SUPREMO_FOTO_CALIDAD` (0,82) en
  `deploy/includes/config_supremo.php`. El valor viaja a la página (`SUP_CFG.foto_lado`) y el JS lo
  usa en `optimizarTodas()`.
* 👁️ **Y el jefe lo ve:** la burbuja de la subida dice **«Comprimidas antes de subir: 4,1 MB → 190 KB»**.
  Esa es la prueba, en su propia pantalla.
* 🛡️ Si algo falla (navegador viejo, formato raro), la librería **devuelve el archivo original**: una
  foto nunca se pierde por culpa del compresor. Y **el servidor vuelve a optimizar igual**
  (`img_guardar_subida()`: WebP + versiones de 300 y 800 px), así que la compresión del navegador es
  una mejora de velocidad, no la única defensa.
* 📱 **¿Y una app Android? No hace falta y sería peor:** tendría que instalarse (Play Store o APK),
  pedir permisos de cámara y galería, y mantenerse actualizada… para hacer **exactamente lo mismo**
  que ya hace el navegador. Además el sitio sirve a **1 500 negocios**: una app propia sería una
  barrera para el dueño, mientras que el navegador **funciona en Android y en iPhone sin instalar
  nada**. Si algún día se quiere una cámara propia con más control, el camino es una **PWA** (la
  misma web instalable), no una app aparte.
* ⚠️ **Ojo con bajarlo en TODO el sitio:** en una ficha, la **portada** se ve a pantalla completa (una
  computadora de 1 920 px pide más de 800 px de ancho y se vería blanda). Para las **fotos de
  productos y del interior** 800 px es ideal; para las **portadas** conviene 1200-1600. Hoy El Supremo
  usa **800** porque es lo que pidió el jefe; si algún día se quiere otro tope en el resto del sitio,
  se pasa `{maxLado: N}` a `CZImg.engancharTodos(...)` en la página que sea.

---

## §6. 💰 LO QUE CUESTA Y LO QUE TARDA (medido el 2026-09-18)

* **4 llamadas de IA** en una venta completa, **US$ 0,0005 a US$ 0,0010** (medido en las sondas:
  US$ 0,000291 · 0,000539 · 0,000744 · 0,000988). Es **la quinta parte de un centavo**.
* El desglose: 1 de **visión** (lee las 3 fotos de una vez), **escritura de la descripción** (la de
  El maestro), 1 de **invención de productos** (+1 corta si el modelo se queda corto) y 1 de
  **visión** para leer los IDs de las imágenes que llegan.
* **Lo que tarda la venta** depende del jefe, no del robot: la conversación son 9 toques y las
  llamadas de IA suman **~10 segundos** (medido: 10-11 s de reloj en la sonda, que hace todo de
  corrido). Lo demás es el diseñador dibujando en Flow, que va en paralelo.

---

## §7. 🧪 CÓMO SE PRUEBA (sin publicar nada de verdad)

Dos sondas temporales, las dos se suben con **`__sonda_run.py`** (quedan en la **raíz viva `/`** y se
borran solas al terminar):

```powershell
# 1) EL CAMINO COMPLETO (58 comprobaciones, con IA de verdad y limpieza total al final)
python D:\RELAX\__sonda_run.py __sup_prueba.php sNd4-supremo-chimbote-9kQ7 go

# 2) LAS VISTAS Y LOS CÓDIGOS, SIN ABRIR EL NAVEGADOR (pestaña + página + regresión de El maestro
#    + el bloque «nuevo» del 2026-09-18: ubicación, horario de 5 opciones, «lo que falta» y los botones)
python D:\RELAX\__sonda_run.py __sup_ver.php sNd4-sup-ver-9kQ7 go

# 3) EL PARSER, con el texto crudo del modelo (no gasta ni una llamada)
python D:\RELAX\__sonda_run.py __sup_parse.php sNd4-sup-parse-9kQ7 go

# 3-bis) 🎵 LA CANCIÓN: subirla, que la ficha la encuentre y borrar la prueba (11 comprobaciones)
python D:\RELAX\__sonda_run.py __sup_cancion.php sNd4-sup-cancion-9kQ7 go

# 4) 😵 «solo veo las opciones y no las preguntas»: la respuesta EXACTA que recibe el navegador
python D:\RELAX\__sonda_run.py __sup_api.php sNd4-sup-api-9kQ7 go

# 5) 🖥️ QUE LA PREGUNTA SE VEA SIEMPRE (el JS de verdad, con un DOM falso: 7 casos, incluidos
#    `sin_mensajes` —la API devolviendo botones sin texto—, `piensa` y `ubicacion`, que además
#    COMPRUEBA que el botón del menú ⋯ manda el GPS a la API)
node D:\RELAX\__sup_js_test.mjs todos

# 6) 📐 VER LA PANTALLA (y medirla) sin abrirle nada al jefe
python D:\RELAX\__sonda_run.py __sup_pantalla.php sNd4-sup-pantalla-9kQ7 go "&paso=rubro"
#    Los pasos simulados (lo que se ve en la foto) son: piensa · nombre · rubro · tipo · cliente · fin
#    (y con cualquier otro valor, o sin &paso, se abre el MENÚ ⋯ como antes).
python D:\RELAX\__sup_bajar.py            # baja el HTML y lo borra del hosting
python D:\RELAX\__sup_medir.py 390 844    # mide anchos/altos reales (y la cabecera hijo por hijo) con Chrome sin cabeza
#    y para verla:  chrome --headless=new --window-size=390,844 --screenshot=... __sup_pantalla.html

# 7) 🔎 SI UNA SONDA DEVUELVE 500 (el hosting no deja ver el fatal en el cuerpo):
python D:\RELAX\__sup_err.py __sup_prueba.php sNd4-supremo-chimbote-9kQ7 go   # muestra el fatal con archivo y línea
```

⚠️ **Al medir, la trampa:** Chrome sin cabeza **ignora `--window-size` con `--dump-dom`** y renderiza
a ~484 px; con `--screenshot` **recorta la foto** al tamaño pedido, así que *parece* que la página se
desborda y no es verdad (nos pasó el 2026-09-18: perseguí un desborde que era solo el recorte).
Para medir sirve igual porque **el CSS de móvil es `max-width: 640px`**: a 484 ya se aplican las
reglas del celular, y `__sup_medir.py` lista **los elementos que se salen** (tienen que ser **0**) y
**la cabecera hijo por hijo** (así se ve si los botones se fueron a una segunda fila).
El iframe de 390 px **no carga en headless**, así que no se usa.

**Lo que hace `__sup_prueba.php`** (**67 de 67** el 2026-09-18) — y por qué hay que volver a correrla si
se toca el motor: abre una venta de verdad, **empieza por la ubicación** (le manda unas coordenadas de
Chimbote y comprueba que la tienda **nace con ese `lat/lng` y su distrito**), manda **3 fotos generadas
con GD** (una dice «FERRETERIA EL TORNILLO», y la IA la lee), comprueba que **al subir las fotos NO hay
botón de confirmación** (se pasa solo al nombre), que **la IA propone el nombre y se pregunta con Sí/No**
(y que el «No» propone **el siguiente** y añade **✏️ Escribir**), que **el rubro se pregunta con Sí/No** y
que la grilla de opciones es **2 × 2** (tres rubros + «Ninguno de estos»), que **«Cómo atiende» son cuatro
botones en grilla 2 × 2**, que **«Tiene su local» ya NO pregunta la dirección**, que **el WhatsApp trae el
número leído con Sí/No/Escribir y no tiene la opción de saltarlo**, **publica la tienda de verdad** (con
`datos['prueba'] = 1`: sin avisos por Telegram, sin HubSpot y sin pedir canción), comprueba que **la
tienda queda a nombre del cliente**, que **la portada y los 8 productos se arman solos** (sin preguntar
nada), que **en la ficha sale un banner cada dos fichas** (8 productos ⇒ 4 banners de NUESTRA empresa),
que los 4 códigos salen con el nombre y el ID, que **el mensaje del cliente lleva UN SOLO enlace**, que el
cierre trae **el botón «Ver la tienda» con su URL cliqueable** y debajo el de WhatsApp, que **una imagen
con el ID impreso entra al producto correcto**, que la cabecera se pone de portada, y que **📍 compartir
la ubicación y 🧠 «Piensa mejor» funcionan y devuelven la venta a su paso**. Y al final **borra todo**
(negocio, fotos, productos, opiniones, cuenta del cliente, sesiones del Supremo y sus llamadas) y lo
comprueba.

⚠️ **La sonda usa el WhatsApp de prueba `900111222`** y borra su cuenta al terminar. Si alguna vez
queda algo, se ve en la salida (`limpieza`) y se quita a mano.

🧪 **El gancho de diagnóstico:** con `$GLOBALS['SUPREMO_DIAG'] = 1` (lo pone la sonda) el motor guarda
en `$GLOBALS['SUPREMO_DIAG_RAW']` la **respuesta cruda del modelo** y cuántos productos parseó. Sin
el gancho no se guarda nada. **Fue lo que permitió cazar el bicho del §9.**

---

## §8. 🔎 LO QUE HAY QUE MIRAR SI ALGO NO SALE

| Síntoma | Dónde se mira |
|---|---|
| La tienda no se publica | El chat dice el motivo exacto («😅 …»). Casi siempre falta el **nombre** o el **WhatsApp**. |
| La IA no arma los 8 productos | Míralo con el gancho de diagnóstico (§7): el modelo contesta bien; el fallo suele ser del parser o de **una variable pisada** (ver §9, bichos 8 y 9). |
| Una imagen no se publica al llegar | El motor **no adivina**: si no lee el número dentro de la foto, la deja fuera y dice cuál es. Se le pide al diseñador que la rehaga con su ID impreso. |
| El reloj se queda parado | El reloj arranca con el **primer POST** (el segundo toque): si solo se abrió la página, marca 0:00 a propósito. |
| Los números de El maestro salen raros | Se está mirando la pestaña equivocada: las ventas del Supremo viven en `modo = 'supremo'` y su pestaña las separa. |
| El botón 📍 no da la ubicación | El navegador pidió permiso y no se le dio (o el sitio no va por HTTPS). Se abre igual el paso de la zona con los distritos en tarjetas: se toca el suyo y sigue. |
| «Piensa mejor» no cambia nada | Si la IA no contestó, el paso lo dice y se sigue igual (§3-bis): **no** se queda la pantalla trabada. Con `🧠 Analizar otra vez` se le pide de nuevo. |

---

## §9. 🔴 LOS BICHOS QUE YA NOS MORDIERON (no repetirlos)

Los tres los cazó **la sonda del camino real**, no la vista:

1. **`$cuantos` contra `$cuantas`.** En `supremo_ia_inventar()` la variable local se llama `$cuantos`
   y al parser se le pasaba `$cuantas` (**no existía** → `null`). Como `count($out) >= null` se cumple
   siempre, el parser devolvía **siempre un solo producto** (con el modelo contestando los 3
   perfectos) y la repetición tampoco entraba (`1 < null` es falso). **Moraleja:** cuando la IA parece
   fallar, primero se comprueba **nuestro** parser con su texto crudo (sonda del §7-3), no el prompt.
2. **La barra suelta del «1 |».** Al quitar el número de delante de «1 | Cemento | 28 | …» queda una
   **barra suelta**: sin limpiarla, el primer campo sale VACÍO y el producto se pierde. Se limpia con
   `trim($l, " \t|")` y con los `while` que quitan campos vacíos de los extremos.
3. **El modelo parte una línea larga.** Cuando la frase o el plano son largos, la respuesta trae un
   salto de línea **a mitad de campo** y esos productos se perdían. Antes de partir en líneas se
   vuelven a pegar las que **no** empiezan con «2 |»
   (`preg_replace('/\n(?!\s*\d+\s*[\|\.\)\-])/u', ' ', $texto)`).
4. **Columnas ambiguas en el JOIN de las estadísticas.** `costo_usd` y `creado_en` existen en las DOS
   tablas (`directorio_ia_tiendas` y su log) → MySQL responde «column is ambiguous» → como iba dentro
   de un `try`, la **lista de ventas salía vacía sin decir nada** y el gasto marcaba 0. Ahora todas
   las columnas del log van con su alias **`l.`**. (En `tienda_ia_stats()` el filtro del modo se
   resuelve con un `NOT IN (SELECT id …)` justo para no caer en lo mismo.)
5. **El `edit` con un ancla repetida.** El bloque del motor se coló dentro del `guion` porque el
   comentario `/* ---- 👑 LOS CÓDIGOS */` era idéntico en los dos `switch`, y como el `guion` iba
   primero, **PHP ejecutaba el caso del guion y el motor se caía al respaldo** (`arranque`). Antes de
   insertar en este archivo, **comprobar con `grep` que el ancla es única**.
6. **🔴 «SOLO VEO LAS OPCIONES, NO LAS PREGUNTAS» (2026-09-18, el jefe desde su celular).** No era
   el servidor (comprobado con **`__sup_api.php`**: el saludo viaja completo en `mensajes`) ni el
   pintado (comprobado con **`__sup_js_test.mjs`**: el JS sí pinta las burbujas): era que **hay
   caminos del motor que devuelven las opciones SIN texto**. `supremo_recibir()` devuelve
   `['tipo'=>'codigos','opciones'=>…]` **sin `mensajes`** en varios pasos (los códigos, la sala de
   espera sin fotos, el panel de la oferta…), y el navegador pinta **solo lo que llega**: la pantalla
   se quedaba con botones y sin pregunta. **Arreglado en dos sitios:**
   * el API manda **siempre** la pregunta del paso en el campo **`pregunta`** (`sup_json_sesion()`),
   * y el JS la pinta **si no está ya a la vista** (`asegurarPregunta()`), marcando cada burbuja con
     **`data-pregunta="firma"`** para **no duplicarla nunca**.
   ✅ **Regla nueva: ningún camino del motor puede dejar la pantalla sin pregunta** (lo vigila el
   test de los 4 casos, incluido `sin_mensajes`).
7. **El ancho en el celular (y la trampa de medirlo).** La cabecera del Supremo lleva más piezas que
   la del Maestro (⏱️ reloj, 👑, 💰) y sus botones **no se encogen**: en una pantalla de 360-390 px el
   `.tia-top` podía pedir más ancho del que hay. Ahora **se envuelve** (`flex-wrap`) y la app no pasa
   del ancho de la pantalla. ⚠️ **Al medirlo, ojo:** Chrome sin cabeza **ignora `--window-size` con
   `--dump-dom`** (renderizó a 484 px y la captura de 390 salía «cortada», lo que parece un desborde
   y no lo es). Para medir de verdad: **`__sup_medir.py`** (mide con `getBoundingClientRect()` y
   lista los elementos que se salen) y **`__sup_pantalla.php` + `__sup_bajar.py` + Chrome
   `--screenshot`** para verla.

8. **🔴 LA VARIABLE DEL BUCLE PISADA (`$t`) — «la IA solo me dio 6 productos» (2026-09-18).** En
   `supremo_armar_productos()` el bucle de las tandas era `for ($t = 0; …)` y **dentro** se guardaba la
   clave del producto en **la misma `$t`**: al salir del `foreach`, `$t` quedaba con un texto
   («martillo de acero»), el `$t++` no hacía nada y la condición **`$t < $tandas` (texto contra número,
   que en PHP 8 se compara como texto)** daba falso a la primera vuelta → **una sola tanda** y la tienda
   se quedaba con 6 de los 8 productos, como si la IA hubiera fallado. **Es la misma familia del bicho
   1** (`$cuantos`/`$cuantas`). ✅ **Regla: dentro de un `for` no se reusa nunca la variable del
   contador** (y los intentos se cuentan con un nombre propio: `$tanda`). Se cazó porque la sonda
   imprime las **trazas** (`tandas`, `parse1`, `final`, `creados`).
9. **🔴 `$loguear()` SE DEJABA LOS MENSAJES (2026-09-18).** El cierre `$loguear` del motor devolvía
   `tipo`, `opciones` e `items`… **y no `mensajes`**. Todo lo que el motor había ido diciendo por el
   camino («Le armé sus 8 productos…», «Portada puesta», «Según tu ubicación están en Chimbote…»)
   **nunca llegaba a la pantalla**: el chat se quedaba con la pregunta del paso y nada más. **Es la otra
   mitad del bicho 6** («solo veo las opciones, no las preguntas»): allí se tapó el síntoma pintando la
   pregunta del paso, aquí se arregló la causa. ✅ `$loguear` ahora devuelve `'mensajes' => $msgs`.
10. **Las constantes nuevas tienen que estar SUBIDAS.** La sonda devolvió **500 sin cuerpo** y el
   culpable era `SUPREMO_PRODUCTOS_TOTAL` (definida en local, **no subida** a `includes/config_supremo.php`).
   El mensaje del fatal no se ve en el hosting: para eso está **`__sup_err.py`** (§7-7), que imprime
   `fatal`, `archivo` y `linea`. ✅ **Regla: al tocar la config, `php -l` + subir el archivo ANTES de la sonda.**
11. **La sonda se muere por un `null` transitorio.** `tienda_ia_cargar()` **se traga los errores y
   devuelve `null`**; si la base tiene un hipo, la sonda siguiente revienta con un `TypeError` que
   parece del motor (nos pasó una vez: la misma sonda pasó **58 de 58** al repetirla). La API está
   protegida (`$s2 ?: $s`), pero al leer los resultados de la sonda: **si falla algo raro, repetirla
   antes de creer que el bicho es del código.**
12. **🔴 LA GRILLA 2 × 2 NO SE LLENA SOLA (2026-09-18).** `tienda_ia_chips_prototipo()` **corta en cuanto
   encuentra un candidato**: con el rubro leído «ferretería» devolvía **un solo** chip y la grilla del
   rubro salía con **2 botones** (Ferreterías + Ninguno), no con los 4 que pidió el jefe. Ahora el
   cierre `$chips_rubro` del guion **rellena hasta 3** (primero con los rubros que **complementan** al
   candidato y, si aún falta, con los más usados del sitio) y solo entonces añade «Ninguno de estos»:
   **la grilla siempre sale 2 × 2**. ✅ Regla: cuando se pide una grilla de N, hay que **contar los
   botones en la sonda**, no confiar en que la función devuelva los que uno imagina.
13. **🔴 DOS ENLACES EN UN WHATSAPP (2026-09-18).** El mensaje para el cliente llevaba **el enlace de su
   tienda Y el del panel** (`url('panel.php')`): el jefe lo prohibió —*«en estos mensajes de WhatsApp no
   pueden haber dos links en el mismo mensaje»*—. Ahora el panel **se explica en palabras** y el único
   enlace es el de la tienda. Lo mide la prueba **28-quater** (`preg_match_all('#https?://#')` tiene que
   dar **1**).
14. **🔴 «VER LA TIENDA» QUE NO ABRÍA LA TIENDA (2026-09-18).** En el paso de los códigos, «Ver la
   tienda» era una **opción con valor** (`ver_tienda`) que el motor respondía diciendo la URL **en un
   mensaje** y volviendo a pintar el mismo panel: el jefe lo vio claro —*«presiono ver la tienda y no me
   muestra la tienda, me muestra los códigos para la cabecera»*—. Un botón que dice «ver» **tiene que ser
   un ENLACE de verdad** (`['url' => …]`): el JS del Supremo ya abre los `url` en otra pestaña. Lo mismo
   se hizo en el cierre (`sup_fin`) y se le sumó el **botón de WhatsApp al cliente** en los códigos.
15. **🔴 LA REJILLA QUE SEGUÍA SALIENDO (2026-09-18).** El paso de los 8 productos ya no pedía elegir,
   pero el jefe **la seguía viendo**: la rejilla la mandaba el **API** (`items` desde
   `supremo_productos_vistos()`), no el guion. ✅ **Regla: lo que se pinta sale SIEMPRE del guion**; si
   el API rellena campos por su cuenta, el paso puede acabar pidiendo algo que ya no existe.
16. **⚡ LA PUBLICACIÓN ESPERABA A LA IA (2026-09-18).** Publicar hacía **dos llamadas** (el copywriting
   y las opiniones) antes de contestar: el jefe lo sintió —*«ya llegué a la parte donde va a poner el
   número… está tardando un poquito»*—. Ahora publica **sin IA** (relato en texto plano) y el copy bueno
   + las opiniones (estas **sin IA**, del respaldo) se hacen **en el paso de los 8 productos**, que es
   donde el vendedor ya está esperando. ✅ Y el copy sale **mejor**: nombra los productos de verdad.
17. **🔴 EL `comentario` DE LA VISIÓN TERMINABA EN LA FICHA (2026-09-18).** `supremo_relato()` metía en
   el relato el comentario que la IA escribe **para el vendedor** («vi tus fotos con letreros…, ¿me
   mandas una mejor?»), y ese texto quedaba como **descripción de la tienda** del cliente. Fuera. ✅
   Regla: el texto que habla con el vendedor **nunca** es texto de la ficha.

---

## §10. REGISTRO

| Fecha | Qué se hizo |
|---|---|
| **2026-09-18** | **Módulo creado, desplegado y probado.** Pedido del jefe en la misma sesión: la página privada `/supremo`, el modo vendedor, los 4 códigos, la portada, la elección de productos y de imágenes de IA, y la sala de espera. 11 archivos subidos por FTP (2 de ellos tocados de El maestro, compatibles). **37 de 37** pruebas del camino real + las 4 vistas verificadas sin abrir el navegador + las 6 rutas comprobadas por HTTP. **Ninguna tienda de prueba quedó publicada.** |
| **2026-09-18** (misma sesión, después) | **💰 LA OFERTA, dentro del módulo.** El jefe entregó su oferta de las **50 ventas** en 8 bloques y se convirtió en herramienta: el **paso `sup_oferta`**, el **panel 💰 de la cabecera** con la **calculadora en vivo** (sin gastar IA), la **frase** para decir en voz alta, los **tres textos para copiar** (la oferta, el guion de 6 pasos y las condiciones) y el **aviso del producto barato**. Con su **hoja de Excel** de 5 pestañas y fórmulas vivas. Probado: **44 de 44**. Todo el detalle: **`GUIA_OFERTA_50_VENTAS.md`**. |
| **2026-09-18** (misma sesión, 3.ª parte) | **📱 LOS PROMPTS DE IMAGEN, DE UNO EN UNO.** Pedido del jefe desde el celular: *«los prompts de imagen dámelos de uno en uno cuando estoy en mobile»*. La tarjeta de los productos ya **no** muestra la carta larga (7 711 caracteres): muestra **un prompt por producto** (~1 000 caracteres, autosuficiente, con su número al principio y al cierre), con **‹ ›**, **puntos numerados** (los copiados en verde) y **«📋 Copiar este prompt»**; la carta completa queda en un botón secundario. 🔴 Y se descubrió **la caché del hosting** (servía el JS y el CSS viejos aunque el FTP dijera «SUBIDO»): desde ahora, al cambiar el JS/CSS hay que **subir el `?v=` de `supremo.php`** (hoy **`js?v=5` · `css?v=4`**) y la página lleva `Cache-Control: no-store`. Probado: **46 de 46** en el camino real + las 4 vistas (que ahora exigen el `?v= nuevo`). |
| **2026-09-18** (misma sesión, 4.ª parte) | **🔴 «SOLO VEO LAS OPCIONES, NO LAS PREGUNTAS» — ARREGLADO.** El jefe lo reportó desde su celular. Se investigó sin abrirle nada: **`__sup_api.php`** demostró que **el servidor sí manda la pregunta**; **`__sup_js_test.mjs`** demostró que el JS sí la pinta; y el bicho de verdad era que **hay caminos del motor que devuelven las opciones SIN texto** (los códigos, la sala de espera sin fotos, el panel de la oferta…). Desde ahora **el API manda siempre la pregunta del paso en `pregunta`** y **el JS la pinta si falta** (marcada con `data-pregunta` para no duplicarla): **es imposible que la pantalla se quede con botones y sin pregunta**. De paso: la cabecera **se envuelve** para no pedir más ancho que la pantalla, el chat tiene **suelo de alto**, y las herramientas nuevas (`__sup_api.php`, `__sup_js_test.mjs`, `__sup_pantalla.php` + `__sup_bajar.py` + `__sup_medir.py`) quedan documentadas en el §7. Verificado: **46 de 46** + vistas OK + el test del JS en sus 4 casos. |
| **2026-09-18** (misma sesión, 5.ª parte) | **📲 EL MENSAJE DEL CLIENTE, COMO BOTÓN DE WHATSAPP.** Orden del jefe: *«el mensaje para el cliente debe ser un botón de WhatsApp para mandarle el mensaje»*. La tarjeta 4 del panel ahora lleva **el botón verde con el ícono oficial** que abre **el chat del cliente** (su número con el 51) con **el mensaje ya escrito** (enlace + usuario + clave): solo hay que darle enviar. Debajo queda el texto para copiar, y **el cierre de la venta lleva el mismo botón**. Sin número **no hay botón** (nunca un enlace roto). Motor: `supremo_wa_cliente()`. Verificado: **48 de 48** en el camino real (con dos pruebas nuevas: el enlace con su número y su texto dentro, y el botón del cierre) + las 4 vistas + **la captura de la pantalla** con Chrome sin cabeza. |
| **2026-09-18** (misma sesión, 6.ª parte) | **🎵 SUBIR LA CANCIÓN DESDE EL SUPREMO.** Orden del jefe: *«no hay modo de subir la canción descargada al Supremo»*. Se añadió el **paso `sup_cancion`**, el **botón 🎵 de la cabecera** (visible en cuanto la tienda está publicada) con su panel, el **reproductor** para oírla y el **atajo que la sube desde cualquier paso**. Reutiliza el motor de canciones del sitio (`cancion_guardar_audio()` + `directorio_canciones`), **no gasta créditos** de Treblo y **no recorta ni recomprime** (tal cual llega). Tope de subida: **19 MB** (el real del servidor). Prueba nueva: **`__sup_cancion.php` (11 de 11)** — sube un audio fabricado, comprueba que la ficha lo encuentra, rechaza lo que no es audio, reemplaza sin duplicar la fila y **borra todo** al terminar. Verificado además: el camino completo (**48 pruebas**) + las 4 vistas + el test del JS (5 casos, con el botón 🎵 visible solo con tienda publicada) + la captura de la pantalla. |
| **2026-09-18** (misma sesión, 7.ª parte) | **📷 LAS FOTOS SE COMPRIMEN A 800 px ANTES DE SUBIR** (pregunta del jefe: *«¿se puede comprimir las imágenes…? pesan 4 MB cada foto del celular… máximo 800 px»*). Se midió (foto de 12 MP de **8,97 MB**): a **1600 px subía 1,34 MB** y a **800 px sube 192 KB** (**47 veces menos**). Se puso **800 px / calidad 0,82** en `SUPREMO_FOTO_LADO` y `SUPREMO_FOTO_CALIDAD` (una línea), el valor viaja a la página y el JS lo usa en `optimizarTodas()`. 🔴 **Y se descubrió el problema de fondo:** con 8 fotos de 4 MB la tanda pesaba **32 MB** y **no cabía en el `post_max_size` (20 MB)** del hosting → la subida fallaba; a 800 px son **~1,5 MB**. La burbuja ahora muestra **«Comprimidas antes de subir: 4,1 MB → 190 KB»**. **No hace falta ninguna app Android** (la compresión ya ocurre en el navegador del celular, Android y iPhone, sin instalar nada). Detalle: **§5-bis**. |

| **2026-09-18** (misma sesión, 8.ª parte) | **📍 LA UBICACIÓN PRIMERO, LOS 8 PRODUCTOS Y 🧠 «PIENSA MEJOR»** (las 7 órdenes del jefe, una por una): **1)** *«lo primero que debe pedir es la ubicación… después vienen las imágenes… para pedir la ubicación solamente suficiente componer el botón de ubicación»* → nace el paso **`ubicacion`** (botón `📍 Compartir mi ubicación` + distritos de respaldo) **antes** de las fotos, y se acabó el paso `distrito` a mitad del flujo (**la dirección ya no vuelve a preguntar la zona**). **2 y 7)** *«en el menú de opciones poner ahí también el acceso directo a compartir ubicación, así se puede compartir ubicación en cualquier momento»* → `📍 Compartir ubicación` **siempre en el menú ⋯** (`accion:'ubicacion'` con GPS; si falla, `accion:'zona'` con los distritos), y **si la tienda ya está publicada se corrige en su ficha** (`lat/lng/distrito_id`). **3)** *«cuando pidas el horario ofrece opciones… cinco»* → los 5 horarios exactos que pidió (+ `⏭️ Después`). **4)** *«inventes productos, no preguntes qué productos… tú elige siempre; la tienda siempre tendrá ocho productos»* → `supremo_armar_productos()`: **8 productos automáticos** (primero los que la IA vio en las fotos, con su foto; los demás inventados con el contexto) y **fuera las preguntas y la rejilla de elección** (`sup_inventar` y `sup_ia_elegir` quedan solo como legado para ventas viejas). **5)** *«de los ocho productos cada dos productos incluirás un banner… el banner es de la empresa de nosotros»* → comprobado en la sonda sobre la ficha real: **8 fichas ⇒ 4 banners** de `banners_para()` (los del sitio, no de la tienda). **6)** *«debe existir un botón de piensa mejor que analiza lo que el usuario publicó, reestructura los datos y pide los datos que se hayan faltado»* → **botón 🧠 de la cabecera** (y menú ⋯), paso **`sup_piensa`** entre la canción y la oferta: **una llamada de IA** que reescribe la descripción y mejora títulos/precios, más **un botón por cada dato que falta** que abre su paso y **devuelve la venta a donde estaba** (`sup_volver` / `sup_piensa_volver`). 🔴 **Dos bichos cazados por la sonda** (y arreglados): la **variable del bucle pisada** (`$t`) que dejaba la tienda con 6 productos de 8, y **`$loguear()` devolviendo las opciones sin `mensajes`** (la otra mitad de «no se ven las respuestas»). Probado: **58 de 58** en el camino real, **7 de 7** casos del test del JS (con dos nuevos: `piensa` y `ubicacion`, que comprueba el GPS del menú) y las vistas + la captura de la pantalla en el celular. Al terminar la prueba **no quedó ninguna tienda de prueba**. Detalle: **§3**, **§3-bis**, **§7** y **§9**. |

| **2026-09-18** (misma sesión, 9.ª parte) | **⚡ LA VENTA, MÁS RÁPIDA Y SIN ESCRIBIR: «esto es para demostrar cuán rápido creamos una tienda»** (las órdenes del jefe, una por una). **1)** *«el botón de confirmación «ya está, seguir» es un paso innecesario: si ya lo subió, de frente debe decir qué nombre se le ha ocurrido»* → **al subir las fotos se pasa SOLO al nombre** (y se pueden mandar más fotos mientras se responde). **2)** *«que diga qué nombre se le ha ocurrido y pregunte con dos botones sí o no… y si dice no, propone el siguiente con tres botones: sí, no, escribir»* → `supremo_ia_nombres()` pide **4 ideas en una sola llamada**, se proponen **de una en una** con **Sí · No** (y **✏️ Escribir** desde la segunda, que **manda el cursor al área de escribir**); **lo que el cliente escriba vale como respuesta**. **3)** *«cuando pregunta el rubro debe preguntarme sí o no… y las opciones en grilla de dos columnas y dos filas: tres rubros y «ninguno de estos»»* → **Sí/No** y la **grilla 2 × 2** (rellenada con rubros que complementan al candidato). **4)** *«¿le sumo otros rubros? … solo debe mostrar una grilla de 2 × 2 y abajo el botón continuar»* → **4 rubros en grilla + `✅ Continuar`**. **5)** *«cómo atiende … hazlo en una grilla de 2 × 2 para que tenga opción a cuatro botones»* → **4 botones** (se sumó «Vende a todo el país»). **6)** *«ya pusimos la ubicación, ya no debe pedir dirección»* → **la dirección salió del flujo**. **7)** *«no aceptamos tiendas sin WhatsApp»* → **fuera la opción de saltarlo** y **se propone el número leído en las fotos** con **Sí · No · Escribir**. **8)** *«la portada va a ser un texto artístico encima de una foto del rubro»* → **la portada ya no se pregunta** y el código pide justo eso. **9)** *«toca los productos que sí van» es innecesaria* → ya eran automáticos (los elige el sistema). **10)** *«que se vea la URL y sea clickeable, y abajo un botón de WhatsApp»* + *«no pueden haber dos links en el mismo mensaje»* → **`🏬 Ver la tienda (abrir)`** es un enlace de verdad y el mensaje del cliente lleva **UN SOLO enlace**. Probado: **67 de 67** + las vistas + el JS (7 casos) + **las capturas de los pasos en el celular** (`__sup_pantalla.php &paso=nombre|rubro|tipo|cliente|fin`). |

| **2026-09-18** (misma sesión, 10.ª parte) | **⚡ LA VENTA, MÁS RÁPIDA Y SIN RELLENO** (el jefe probándolo en vivo, orden por orden). **1)** *«la IA pierde tiempo presentando un resumen: «se ve tu tienda bien surtida de rollos de plástico…» y **no dice el número**… procuremos evitar textos de relleno y pasar siempre directo a la acción»* → **fuera el comentario de la visión** (y fuera de la descripción que se guarda: era un mensaje para el vendedor) y el **número se dice tal cual** en la pregunta del WhatsApp. **2)** *«el horario… nuevamente están apilados en filas: divídela en dos columnas»* → **los 5 horarios en grilla de 2 columnas**. **3)** *«está tardando un poquito… optimizar lo más rápido»* → **la publicación ya NO gasta IA** (`tienda_ia_publicar(..., $sin_copy = true)`, compatible: El maestro no cambia): la tienda nace con el relato y **el copy bueno + las opiniones se hacen en el paso de los 8 productos**, que es donde el vendedor ya espera. **4)** *«nuevamente me pregunta «toca los productos que sí van a la tienda»… no debe mostrarme ese mensaje»* → era el **API** mandando la rejilla vieja: ahora los `items` salen **solo del guion**. **5)** *«ese texto «aquí están los códigos, copia cada uno» es innecesario»* → **el paso de los códigos ya no dice nada** (el panel se abre solo). **6)** *«aquí lo que debería decir es «clic aquí para cargar la canción»»* → **botón grande que abre el selector de audio**. **7)** *«el botón ver la tienda no funciona: me muestra los códigos»* → **es un ENLACE de verdad** (y se le sumó el **botón de WhatsApp al cliente** en los códigos, sin llegar al final). Probado: **72 de 72** + las vistas + el JS (7 casos) + **las capturas del horario (2 columnas) y de los códigos** en el celular. |

> **Lo que queda pendiente (honesto):** la **música automática** de la tienda (`includes/cancion.php`)
> sigue dependiendo de los créditos de Treblo, que están en 0: por eso el código de música de Flow es
> la vía de verdad hoy. Y el **generador de canciones por tienda** (el módulo de Treblo) es un camino
> aparte que no toca El Supremo.
