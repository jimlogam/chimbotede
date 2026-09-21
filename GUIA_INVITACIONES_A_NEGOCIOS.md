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


# GUÍA — 📨 INVITACIONES A LOS NEGOCIOS POR WHATSAPP · dechimbote.com

> **Cuándo leer esta guía:** antes de tocar el **botón 📨 de invitación** de la pestaña 🏬 Tiendas del
> Súper Admin (colores, mensaje, usuario y contraseña), o cuando el jefe diga *«lee la guía de
> invitaciones a negocios»*, o si hay que **volver a invitar**, **cambiar el mensaje** o **medir cómo
> va la campaña de invitaciones**.
> **Archivos que toca:** `deploy/includes/invitaciones.php` (**motor**, nuevo) ·
> `deploy/includes/vista_tiendas_admin.php` (la vista y el botón) · `deploy/superadmin.php` (las 2
> acciones POST). Documentación del panel: `GUIA_SUPERADMIN_PANEL.md` **§4.4**.
> **Estado:** ✅ EN PRODUCCIÓN desde el **2026-09-17** (probado en vivo con sondas, sin navegador).
> 🎄 **El 2026-09-19 el mensaje se reescribió DOS veces: primero personalizado por rubro y, al final del
> día, en la VERSIÓN CORTA que eligió el jefe** (*«la C está bonita»*) — §2 y §2.1.
> **Se hizo en una sola sesión**, con dos pedidos del jefe (el botón y, después, el usuario/contraseña).

---

## 0) LO ESENCIAL EN 30 SEGUNDOS

- En **https://dechimbote.com/superadmin.php?seccion=tiendas**, debajo de cada tienda hay un
  **botón 📨 Invitar**. Un clic **abre WhatsApp con el mensaje ya escrito** al número de esa tienda
  (la invitación + **su usuario y su contraseña**).
- El botón tiene **4 colores** según cuántas veces se le mandó: **⚫ 0 · 🟠 1 · 🟢 2 · 🔵 3 o más**.
- El envío **queda apuntado** (una fila por envío) y, al costado, un **↺** corrige si el jefe toca de más.
- La **cuenta de la tienda se crea la primera vez** que se toca el botón (usuario = su número de
  teléfono, contraseña de **3 letras + 1 número**, sin la `O` ni el `0`) y **la tienda queda a su nombre**.
- La misma contraseña **se repite** en el 2.º y 3.er envío (está guardada por tienda).
- Todo se probó con **sondas** (nunca abriendo el navegador: Regla de Oro n.º 5) y las sondas
  **se borran solas** del servidor.

---

## 1) DÓNDE VIVE (archivos y URL)

| Archivo | Papel |
|---|---|
| `deploy/includes/invitaciones.php` | **El motor** (nuevo, 2026-09-17): las 2 tablas que se crean solas, el conteo, las credenciales, el mensaje, el enlace y el nivel de color. Todas las funciones van con el prefijo **`invitacion*`** |
| `deploy/includes/vista_tiendas_admin.php` | La pestaña 🏬 Tiendas: pinta la **leyenda**, el **botón 📨 + ↺**, el **formulario oculto** `#sati-inv-form` y el **JavaScript** del clic |
| `deploy/includes/invitacion_ficha.php` | **El MISMO botón 📨 pero dentro de la FICHA** (nuevo, 2026-09-17 — §12): `invitacion_ficha_accion()` (el POST) y `invitacion_ficha_html()` (el bloque). **Solo lo ve el súper admin** |
| `deploy/negocio.php` | La ficha: llama al POST al principio y pinta el bloque debajo de los botones de WhatsApp/Llamar |
| `deploy/superadmin.php` | Las 2 acciones POST (**`tienda_invitar`** y **`tienda_invitar_deshacer`**) y el `require_once` del motor. Además: al **borrar una tienda** se borran sus invitaciones |
| `deploy/includes/clave_recuperar.php` | **No se tocó**: de ahí se usan `clave_generar()` (3 letras + 1 número) y `clave_restablecer()` (clave nueva a una cuenta) |
| `D:\RELAX\__inv_verificar.php` | **Sonda de prueba** (se sube, se lee y se borra sola): verifica la vista del panel y hace la ida y vuelta completa |
| `D:\RELAX\__inv_dueno.php` | **Sonda del censo** de dueños (solo lectura) |
| `D:\RELAX\__inv_ficha_verificar.php` | **Sonda del botón de la FICHA** (§12): mide la ficha **como admin** y **como público**, y hace la ida y vuelta del POST con limpieza |
| `D:\RELAX\__inv_estado.php` | Sonda del **estado real** de la campaña (solo lectura): qué invitaciones hay, con qué credenciales, quiénes son los admins y una tienda por nombre (`&q=`) |
| `D:\RELAX\__inv_rubros.php` | Sonda del **MENSAJE PERSONALIZADO** (§2.1, nueva del 2026-09-19, solo lectura): devuelve **los 122 rubros con el deseo que les toca** (`genericos` = cuántos se quedan con el genérico, hoy **0**) y **el mensaje completo de las tiendas que se pidan** (`&ids=1926,1554` o `&rubro=bodegas`, con una contraseña de mentira: **no crea cuentas**) |
| `D:\RELAX\__inv_verificar_resultado.json` y `__inv_verificar_go_resultado.json` | Las respuestas medidas (quedan como evidencia) |

**URL:** `https://dechimbote.com/superadmin.php?seccion=tiendas` (el panel) y
`https://dechimbote.com/neg/<slug>` (la ficha, donde también está el botón si eres el admin — §12)

---

## 2) EL MENSAJE — 🎄 PERSONALIZADO POR RUBRO (reescrito el 2026-09-19)

> **Pedido textual del jefe (2026-09-19):** *«el mensaje que se envía con la invitación… quiero que lo
> personalices, en este caso por ejemplo **payasito crespín animación infantil**… que pongas algo tipo
> "esta Navidad sabemos que vas a conseguir tus sueños o conseguir tus metas y vas a tener muchísimas
> ventas y nosotros queremos ser parte de ese proceso… que sigas llevando felicidad a los niños y nunca
> falte clientes en tu empresa"… dile que **le obsequiamos una tienda virtual** para su negocio, que
> **recibimos más de 4000 visitas diarias** de Chimbote, Santa, Nuevo Chimbote y Coishco… explícale
> **qué significa estar fuera de Facebook** (aparecer en Google, que lo encuentren de otras ciudades sin
> cuenta de Facebook, porque mucha gente prefiere Google o la inteligencia artificial)… **sigue
> publicando en Facebook y también en su página: doble opción de conseguir clientes**… **Feliz Navidad**,
> el usuario y la contraseña, **"ha llegado el momento de llevar tu empresa al siguiente nivel"** y que
> cualquier duda me llame por WhatsApp o deje un mensaje de voz… **usa estos textos como referencia y
> hazlo más corto y más persuasivo, que el cliente lo vea como un premio a su trabajo y como la
> oportunidad de llevar su empresa al siguiente nivel»**. Y una orden expresa: **no se menciona el
> nombre de la empresa** (nada de «somos de …»).

**El mensaje que sale hoy** — 🆕 **VERSIÓN CORTA «C», la que eligió el jefe el 2026-09-19** (*«muy grande,
tienes que hacerlo más corto, más directo, redúcelo a la mitad: sale un botón que dice "leer más" y mucha
gente no lo va a leer»*) — y que arranca **diciendo claro desde el inicio que le vamos a ayudar a vender**.
Así lo recibe, por ejemplo, el **Payasito Crespín** (id 1926):

```text
🎄 Falta poco para esta Navidad y queremos que te conozcan muchos clientes, Payasito Crespín 🎄

Tu trabajo con los niños merece más clientes, y para eso te obsequiamos tu tienda virtual, ya lista y funcionando:
https://dechimbote.com/neg/payasito-crespin

Tu primera web fuera de Facebook: ahora sí apareces en Google. Hoy recibimos más de 4,000 visitas de la gente que sí te va a contratar 🛒

Ingresa como dueño
usuario= 950692806
Contraseña= WS1A.

Es tu momento de llevar tu empresa al siguiente nivel.
```

- 🔴 **EL SALUDO YA NO ES «¡Feliz Navidad!» (orden del jefe, 2026-09-19 noche).** Textual: *«actualmente
  estoy mandando un mensaje que dice Feliz Navidad y quiero que cambiemos el contexto porque no estamos
  todavía en Navidad… se trata de decir **esta Navidad queremos que te contraten mucho**, **esta Navidad
  queremos que muchos clientes te conozcan**, **esta Navidad queremos que vendas a muchos clientes**»*.
  Por eso el mensaje **no felicita** la Navidad (que aún no llega) sino que **anuncia que falta poco**:
  el arranque dice **«🎄 Falta poco para esta Navidad y queremos que te conozcan muchos clientes,
  {nombre} 🎄»** (el «falta poco para esta Navidad» se agregó al inicio de todo en la misma orden:
  *«porque recuerda que todavía no estamos en Navidad»*), y **detrás va el mensaje de entrada
  personalizado (el mérito del rubro) y el link de su tienda**.
- 🔴 **EL MENSAJE QUEDÓ PELADO — 3 TROZOS BORRADOS (misma orden, 2026-09-19 noche).** Textual del jefe:
  *«borra: "sin necesidad de tener cuenta", "Esta Navidad queremos que te contraten mucho y que vendas a
  muchos clientes", "Cualquier duda, llámame por WhatsApp. 🎁"»*. O sea: fuera la explicación de la cuenta,
  fuera la frase de la Navidad del cuerpo (que ya está en el saludo) y **fuera el cierre de WhatsApp con el
  🎁** — el mensaje ahora **termina en «Es tu momento de llevar tu empresa al siguiente nivel.».**
  ⚠️ **No volver a agregar esos tres trozos** salvo que el jefe lo pida. (Nota histórica: de aquel
  párrafo de Google ya no queda nada escrito — el jefe lo volvió a dictar entero, ver la viñeta de abajo.)
- 🔴 **EL PÁRRAFO DE GOOGLE, OTRA VEZ (2026-09-19 noche).** El jefe lo volvió a dictar y **quitó las
  ciudades**: *«tu primera web fuera de Facebook: ahora sí apareces en Google. Hoy recibimos más de 4,000
  visitas de la gente que sí te va a comprar (icono)»*. Ahora dice **«Tu primera web fuera de Facebook»**
  (ya no «Es tu primera **página** web») y **la lista «de Chimbote, Nuevo Chimbote, Santa y Coishco» se
  fue** — ⚠️ **eso antes era una orden suya** (quería que se supiera de dónde son las visitas): si la
  quiere de vuelta, se le pregunta antes de reponerla. Historia de este párrafo: versión 1 «…apareces en
  Google y te encuentran de otras ciudades. Recibimos más de 4,000 visitas al día… Sigue publicando en
  Facebook y también aquí: vendes el doble.» → esas dos frases ya se habían ido en el cambio anterior.
  ⚠️ El **icono** lo eligió el agente (**🛒**, el carrito del que compra): el jefe solo dijo «(icono)», así
  que si quiere otro (💰 ✅ 🤝) es cambiar **un carácter**.
- 🛒 **EL VERBO FINAL CAMBIA SEGÚN EL NEGOCIO** (`invitacion_verbo()`, §2.2): el Payasito recibe
  «…la gente que sí te va a **contratar**», una bodega «…que sí te va a **comprar**» y un hospedaje o una
  inmobiliaria «…que sí te va a **alquilar**».
- 🪪 **LAS CREDENCIALES VAN EN 3 LÍNEAS (misma orden, textual):** *«"Entra con tu usuario 950692806 y tu
  contraseña WS1A." cambiar por: "ingresa como dueño / usuario= 950692806 / Contraseña= WS1A.",
  respetando los saltos de línea para mejor visualización»*. Antes era **una sola línea**; ahora son tres
  (`\n` de verdad en el código): **«Ingresa como dueño» · «usuario= …» · «Contraseña= ….».**
  ⚠️ **Los saltos son sagrados** (es lo que pidió): no volver a juntarlas en un renglón.
- **Es la mitad de texto que la versión larga** (≈95 palabras contra ≈150) y **1 solo párrafo explicativo**:
  fuera los ✅, fuera la frase de «Tus clientes te escriben a», fuera la explicación larga de Google.
- 🎯 **Lo primero que se ve (antes del «Leer más»)**: el saludo + **«merece más clientes»** + el enlace.
  Por eso **el usuario y la contraseña van arriba de todo lo posible**, en su propio párrafo corto:
  **si quedaran escondidos detrás del «Leer más», el dueño no podría entrar.**
  ⚠️ **Ojo con el «Leer más»:** desde el 2026-09-19 las credenciales ocupan **3 líneas** (antes 1) y el
  mensaje lleva además el párrafo de Google encima. Si algún día el dueño no ve su usuario, **lo que se
  acorta es el párrafo de Google, NUNCA las credenciales** (son lo único que no puede faltar).
- **La única frase que cambia de tienda en tienda es el MÉRITO** (`invitacion_merito()`, §2.1): *«Tu bodega
  merece más clientes…»*, *«Tu ceviche merece más clientes…»*, *«Tu barbería merece más clientes…»*,
  *«Tu trabajo con los niños merece más clientes…»*. Si no hay ninguna pista, sale **«Tu trabajo»**, que le
  queda bien a cualquier negocio.
- El **enlace de la ficha** lo arma `url_negocio($slug)` — es absoluto, así que llega tocable, y va
  **solo en su línea**.
- ⚠️ **Sin punto final después del enlace** (el jefe lo escribió con punto): WhatsApp se come el
  punto dentro del enlace y el enlace queda mal. Tampoco se puso la línea suelta con un `.`.
- ⚠️ **No lleva el enlace del login**: el jefe lo decidió así (*«No, solo usuario y contraseña»*).
  Si algún día lo quiere, es **una línea** en `invitacion_mensaje()`.
- ⚠️ **NO se nombra la empresa** (orden del jefe): el mensaje habla del regalo y de la tienda del dueño,
  nada más. El dominio aparece **solo dentro del enlace de SU tienda**, que es el regalo y no se puede quitar.
- ⚠️ **No se pone el año** («esta Navidad», sin «2027»): así el mensaje **no envejece** ni miente si se
  manda en otro diciembre.
- El mensaje lo arma **`invitacion_mensaje($tienda, $cred)`**; el enlace, **`invitacion_url()`**
  (usa `url_whatsapp()`, que le pone el **51** solo cuando el número tiene 9 dígitos).

### 2.1 🏷️ CÓMO SE ELIGE EL MÉRITO (los 3 pasos, `invitacion_merito` · `invitacion_rubro_de` · `invitacion_giro`)

| Paso | Función | Qué mira |
|---|---|---|
| 1 | **`invitacion_giro($t)`** | **El NOMBRE y los primeros 400 caracteres de la DESCRIPCIÓN** de la tienda (`LEFT(descripcion,400)`). Si ahí aparece un giro claro (`payasit`, `botarga`, `veterinari`, `barber`, `botica`, `cevicher`… ~60 pistas, con `\b` para que «spa» no salte dentro de «espacio») devuelve **ese rubro y MANDA sobre el de la base** |
| 2 | **`invitacion_rubro_de($t)`** | Si no hubo pista: el **rubro de la base** (`categoria_id` → `directorio_categorias.slug`), con caché por petición |
| 3 | **`invitacion_merito($slug)`** | La frase: **122 rubros mapeados uno por uno** + **~140 familias** por trozo de slug + **«Tu trabajo»** de último recurso |

- 🔴 **¿Por qué el paso 1 es imprescindible?** Porque el rubro de la base a veces es un **paraguas** o está
  mal puesto, y el mensaje tiene que quedarle bien al negocio. El caso que lo descubrió:
  **«Payasito Crespín — Animación Infantil y Shows» (id 1926) está guardado en el rubro `eventos`
  (Decoración / Eventos)** — con el rubro pelado el mensaje le decía *«Tu trabajo en las fiestas»*; con la
  pista del nombre/descripción le sale **«Tu trabajo con los niños»**, que es lo que pidió el jefe.
  Lo mismo pasa con una tienda llamada «veterinaria dias» guardada en `bodegas` (le sale *«Tu veterinaria»*).
- 📊 **Medido el 2026-09-19 con la sonda `__inv_rubros.php`: de los 122 rubros activos, NINGUNO se queda
  con la frase genérica** (los 2 últimos —Paraderos y Encomiendas— se mapearon ese día).
- ⚠️ **Todo va dentro de `try/catch` y con caché**: si algo falla, sale **«Tu trabajo»**.
  **Un mensaje sin personalizar es mejor que una invitación que no se puede mandar.**
- 🔧 **Si el jefe pide cambiar una frase**: se toca **una línea** del mapa `$mapa` de `invitacion_merito()`
  (o se agrega la pista en `invitacion_giro()` si es un giro que el rubro no distingue).
- 📌 **`invitacion_deseo()` sigue en el archivo, pero el mensaje de hoy NO la usa**: es el texto del deseo
  largo que el jefe pidió el 2026-09-19 a la mañana (*«que sigas llevando felicidad a los niños y nunca
  falte clientes en tu empresa»*) y queda lista para los **mensajes de seguimiento** (2.º y 3.er envío, §10),
  donde sí cabe un texto largo.

### 2.2 🛒 CÓMO SE ELIGE EL VERBO FINAL (`invitacion_verbo`, pedido del jefe 2026-09-19 noche)

La última frase del párrafo de Google dice **de quién son esas visitas**, y el verbo cambia según el tipo
de negocio. **Son tres verbos y nada más**, en las palabras del jefe: *«la gente que sí te va comprar /
alquilar / contratar»*.

| Verbo | Cuándo | Ejemplos |
|---|---|---|
| **comprar** | tiendas, comercios, comida (lo que se lleva) | bodegas, restaurantes, farmacias, ferreterías, ropa, ópticas, joyerías, grifos, panaderías, librerías, pescaderías |
| **alquilar** | lo que se usa o se ocupa por un tiempo | **hoteles y hospedajes**, **inmobiliarias**, **alquiler de habitaciones / herramientas / drones / scooters / local para eventos**, coworking, canchas sintéticas |
| **contratar** | servicios: oficios, salud, belleza, profesionales, fiestas, clases, avisos de empleo | animación infantil, barberías, peluquerías, gimnasios, mecánicos, electricistas, pintores, dentistas, doctores, veterinarias, abogados, contadores, escuelas, fotógrafos, agencias de viajes, **empleos** |

- Recibe **el mismo slug que el mérito** (`invitacion_merito()`), así que el paso 1 y el 2 de §2.1 valen
  igual: **si la tienda dice su giro en el nombre o la descripción, ese giro manda** (el Payasito Crespín
  está en el rubro `eventos` → «contratar», que es lo correcto para él).
- Si no hay ninguna pista, sale **`comprar`** (lo que le queda bien a una tienda).
- 📊 Medido el 2026-09-19 con un script temporal sobre **84 rubros reales**: el reparto quedó en
  **comprar** para todo el comercio y la comida, **alquilar** para hospedaje/inmobiliaria/alquileres y
  **contratar** para todos los servicios. 🔴 **Trampa que se dejó fuera a propósito**: la familia
  `deporte` NO está en la lista de alquilar, porque «**ropa-deportiva**» habría caído en «alquilar» —
  una tienda de ropa deportiva se **compra**, no se alquila (por eso solo entra `cancha`).
- 🔧 **Si el jefe pide otro verbo** (p. ej. `reservar` para restaurantes): se agrega a `invitacion_verbo()`
  y a la frase del párrafo; es **una línea**.

---

## 3) LOS 4 COLORES Y EL CONTEO

| Color | Clase CSS | Cuándo |
|---|---|---|
| ⚫ Negro `#111827` | `.sati-inv--n0` | no se le mandó ninguna invitación |
| 🟠 Naranja `#ea580c` | `.sati-inv--n1` | 1 vez |
| 🟢 Verde `#15803d` | `.sati-inv--n2` | 2 veces |
| 🔵 Azul `#1d4ed8` | `.sati-inv--n3` | **3 veces o más** (un 4.º envío sigue azul) |

- El número de envíos se ve en el **globito** del botón y en el **`title`** (con la fecha del último
  envío y con el usuario/contraseña que lleva el mensaje).
- Arriba de la rejilla hay una **leyenda** con los 4 colores (para no tener que adivinarlos).
- **El conteo es automático**: un clic = una invitación apuntada (lo eligió el jefe). El **↺** quita
  la **última** apuntada, con confirmación, y está **deshabilitado** en 0.
- ⚠️ **El ↺ NO borra la cuenta ni la contraseña**: si se vuelve a invitar, el mensaje sale con **la
  misma clave** (si se borrara, al dueño le llegaría una clave nueva y la anterior dejaría de servir).
- Una tienda **sin WhatsApp ni teléfono** no lleva botón: sale apagado en rojo (**📨 Sin número**) y
  **no enlaza** — la regla del sitio es que **ningún botón de WhatsApp abre un chat en blanco**.

### Cómo se comporta el clic (importante para entender el código)

| Caso | Qué pasa |
|---|---|
| **`data-listo="1"`** (la tienda ya tiene credenciales guardadas) | El `href` viene **armado desde el servidor** → es un enlace normal (`target="_blank"`), abre **al instante**, y el envío se apunta de fondo con un `fetch` |
| **`data-listo="0"`** (primera invitación) | Hay que **crear la cuenta y la contraseña**, así que el clic **abre una pestaña en blanco DENTRO del gesto** (`window.open`) y, cuando el servidor responde con el enlace, esa pestaña se va a WhatsApp. Después el `href` queda armado y el próximo clic es instantáneo |

### 🔴 DÓNDE Y CÓMO CAMBIA EL COLOR (el código, paso a paso)

**Lo que se guarda NO es el color: es el CONTEO** (una fila por envío en `directorio_invitaciones`).
El color es **una cuenta que se hace al pintar**, así que no hay nada que «actualizar» en la base:

1. **`invitacion_nivel($n)`** (`includes/invitaciones.php`) devuelve **0, 1, 2 o 3**:
   `max(0, min(3, $n))` → de 3 en adelante **siempre 3** (el 4.º envío sigue azul).
2. **`sat_boton_invitacion()`** (`includes/vista_tiendas_admin.php`) le pone la clase
   **`sati-inv--n{nivel}`** al `<a>` del botón. También `invitacion_etiqueta($n)` arma el `title`
   («invitaciones enviadas 3 veces»).
3. **Al recargar la página**, el conteo sale de la base: **`invitaciones_mapa()`** cuenta las filas de
   los 30 ids de la página (una sola consulta con `IN (...)`, aparte del listado).
4. **SIN recargar** (justo después del clic) lo repinta el JavaScript **`pintar(id, n)`** del final de
   `vista_tiendas_admin.php`: cambia el `className`, actualiza el **globito** (`.sati-inv__n`) y el
   `title`. Por eso el color cambia **al instante**, sin que la página se refresque.
5. **El ↺ usa el mismo `pintar()`**: al quitar una invitación el botón **vuelve al color anterior**
   (🔵 3 → 🟢 2 → 🟠 1 → ⚫ 0) también sin recargar.
6. **Los colores viven en 2 sitios** del `<style>` de `includes/vista_tiendas_admin.php` y hay que
   cambiarlos **juntos** (si se cambia uno solo, la leyenda miente):

| Qué se pinta | Reglas CSS |
|---|---|
| El **botón** | `.sati-inv--n0` · `.sati-inv--n1` · `.sati-inv--n2` · `.sati-inv--n3` |
| Los **puntos de la leyenda** | `.sati-inv-leyenda .p0` · `.p1` · `.p2` · `.p3` |

7. ⚠️ **El rojo apagado (`.sati-inv--sin`, «📨 Sin número») NO es un nivel**: es el aviso de que la
   tienda no tiene WhatsApp ni teléfono (esas tiendas no se pueden invitar). No se toca al cambiar
   los 4 colores.
8. **Los 4 colores no dependen del rubro, del estado ni del distrito**: solo del **número de envíos**.

---

## 4) 🔑 EL USUARIO Y LA CONTRASEÑA (segunda parte del pedido)

> **Pedido textual:** *«en el primer mensaje también se debe incluir el usuario y su contraseña ·
> Usuario: numero de telefono · Contraseña: 3 letras y un número ejemplo abc8, a fin de evitar
> errores no usaremos el 0 ni el o»*.

**No es texto decorativo: es la cuenta de verdad.** Al tocar 📨 por primera vez, la tienda recibe su
cuenta y la contraseña que va en el mensaje **entra de verdad** al login (se comprobó con
`password_verify()` en la sonda).

### 4.1 De dónde sale la contraseña

De **`clave_generar()`** (`includes/clave_recuperar.php`) — la MISMA que usan **«El maestro»** 🛠️ y el
botón **🔑 Restablecer contraseña** de 👥 Usuarios: **3 letras + 1 número mezclados, sin la `O` ni el
`0`** (constantes `TIENDA_IA_CLAVE_LETRAS` = `ABCDEFGHIJKLMNPQRSTUVWXYZ` y `TIENDA_IA_CLAVE_NUMEROS` =
`123456789`). Que sea el mismo formato hace que el dueño **reconozca su clave** de siempre.
> ⚠️ **No inventar otro generador**: si algún día se cambia el formato, se cambia en
> `config_tienda_ia.php` y las tres puertas cambian juntas.

### 4.2 El usuario: el teléfono, bien normalizado

Se toman los **últimos 9 dígitos** si el número viene con el 51 (`+51 934 274 553` → `934274553`).
🔴 **Esto no es un adorno:** `login()` compara el número **tal como se escribe**, así que una cuenta
guardada como `51934274553` **no entraría** escribiendo `934274553`. La cuenta se crea con la misma
convención que usa El maestro: **correo interno `<numero>@dechimbote.com`, `tipo='dueno'`, `activo=1`**.

### 4.3 Qué se hace según el estado de la tienda (lo decidió el jefe)

| Caso | Qué hace el 📨 la primera vez |
|---|---|
| **Tienda sin dueño** (1 531 de 1 706) | Le **crea la cuenta** (usuario = su número) y **la tienda queda a su nombre** (`dueno_id`) → entra, la ve y la edita |
| **«Dueño» = el ADMINISTRADOR** (172) | Igual: el admin **no es un dueño de verdad** (es el que las cargó) y **a una cuenta de admin NO se le puede cambiar la clave**, así que se le crea la cuenta al número de la tienda y `dueno_id` pasa del admin al dueño nuevo. El jefe **sigue manejando esa tienda desde el Súper Admin** |
| **Dueño de verdad** (3) | Se le da **clave NUEVA** a ESA cuenta (`clave_restablecer()`: **cierra sus sesiones abiertas**) y el mensaje lleva **su** usuario (su teléfono) |
| **El número de la tienda es el del admin** (desde el **2026-09-19** el número del administrador es el **908785164**; el `955041690` es hoy el número **personal** del jefe y quedó en `ADMIN_WHATSAPP_VIEJOS`) | 🔒 **No se toca nada**: `clave_restablecer()` se niega con cuentas de administrador y **la invitación sale sin las 2 líneas**, con un **aviso en pantalla** al jefe. 🆕 **2026-09-19:** `invitaciones.php` lo comprueba **a propósito** con `telefono_es_del_admin()` y **no se crea ninguna cuenta con el número del administrador** |

> 📊 **El censo está medido, no supuesto** (sonda `__inv_dueno.php`, 2026-09-17):
> **1 706 negocios · 1 531 sin dueño · 172 con dueño = el administrador · 3 con dueño de verdad** ·
> **13 usuarios** en total (4 `dueno`, 2 `admin`). Los 2 admins son **id 1 «Administrador»** y
> **id 9 «Jimmy Logam»** (jimmylopez483@gmail.com, tel. 955041690).

### 4.4 La clave SE GUARDA en claro (a propósito)

En `directorio_usuarios` solo queda el **hash bcrypt** (irreversible). Como la invitación se puede
mandar **2 o 3 veces**, si se generara otra clave en cada envío **al dueño le llegarían claves
distintas y la anterior dejaría de funcionar**. Por eso la clave se guarda **tal cual** en
`directorio_invitacion_claves` (una fila por tienda): **se genera UNA vez y se repite siempre**.

> 🔒 Es la única tabla del proyecto con claves en claro. Vive dentro del Súper Admin: `includes/`
> está bloqueado por `.htaccess` (`RedirectMatch 403`) y **ninguna API la expone**. Si algún día
> molesta, la alternativa es un mensaje de seguimiento que no repita la clave — pero entonces el
> dueño que borró el chat se queda sin ella.

---

## 5) LAS TABLAS (se crean solas, sin migradores)

**`directorio_invitaciones`** — una fila = una invitación enviada:

| Columna | Para qué |
|---|---|
| `id` | autonumérico (el ↺ borra el último de esa tienda: `ORDER BY id DESC LIMIT 1`) |
| `negocio_id` | la tienda — `INDEX (negocio_id, enviado_en)` |
| `admin_id` | quién la mandó (`NULL` si fue una sonda) |
| `canal` | `whatsapp` |
| `enviado_en` | fecha y hora (sale en el `title` del botón) |

**`directorio_invitacion_claves`** — una fila por tienda, con **su usuario y su contraseña**:

| Columna | Para qué |
|---|---|
| `negocio_id` | **PRIMARY KEY** (una sola credencial por tienda) |
| `usuario_id` | la cuenta de `directorio_usuarios` que se creó o se le cambió la clave — `INDEX` |
| `usuario` | con qué entra (su teléfono, o su correo si la cuenta es vieja) |
| `clave` | la contraseña **en claro** (para repetirla en cada envío) |
| `creado_en` | cuándo se generó |

> ⚠️ **La tabla del aviso del antivirus:** NO hay que subir ningún `migrar_*.php` (el hosting los
> devuelve 404). Las tablas se crean con **`CREATE TABLE IF NOT EXISTS`** la primera vez que hacen
> falta, y si la consulta falla **se reintenta creando la tabla** (`invitaciones_instalar()` /
> `invitaciones_claves_instalar()`).

---

## 6) LAS ACCIONES POST Y EL JAVASCRIPT

**En `superadmin.php`** (junto a `tienda_estado`):

```php
} elseif ($accion === 'tienda_invitar' || $accion === 'tienda_invitar_deshacer') {
    $r = ($accion === 'tienda_invitar')
        ? invitacion_enviar($id, (int)($admin['id'] ?? 0))
        : invitacion_deshacer($id);
    if (invitacion_es_ajax()) {          // ← el `fetch` de la tarjeta
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($r);            // {ok, n, url, usuario, clave, creada, aviso, msg}
        exit;                            // ← sale ANTES del flash() y del redirect
    }
    flash((string)($r['msg'] ?? ''), !empty($r['ok']) ? 'exito' : 'warning');
}
```

- El `fetch` manda la cabecera **`X-Requested-With: XMLHttpRequest`** (`invitacion_es_ajax()`), así
  que **no se deja un aviso raro** en la pantalla siguiente.
- El formulario oculto **no lleva `action`** (igual que los demás botones del panel): el POST va a la
  URL actual y **los filtros y la página se conservan**.
- Devuelve `url` (el enlace de WhatsApp ya armado) para que el JavaScript **abra WhatsApp** con él.

---

## 7) CÓMO PROBARLO (sin abrir el navegador — Regla de Oro n.º 5)

```bash
cd D:\RELAX
C:\xampp\php\php.exe -l deploy\includes\invitaciones.php      # 1) lint (php NO está en el PATH)
python __subir_uno.py includes/invitaciones.php               # 2) subir SOLO lo modificado
python __subir_uno.py includes/vista_tiendas_admin.php
python __subir_uno.py superadmin.php

$env:PYTHONIOENCODING='utf-8'                                 # 3) la consola de Windows rompe con «→» sin esto
python __sonda_run.py __inv_verificar.php inv-revert-2026-9kQ7 x     # solo lectura: crea las tablas y pinta la vista
python __sonda_run.py __inv_verificar.php inv-revert-2026-9kQ7 go    # + ida y vuelta COMPLETA y limpieza
python __sonda_run.py __inv_dueno.php      inv-revert-2026-9kQ7 x    # censo de dueños (solo lectura)

curl.exe -s -o NUL -w "%{http_code}\n" "https://dechimbote.com/superadmin.php?seccion=tiendas"   # 302 = bien
```

**Qué tiene que salir** (medido el 2026-09-17, página 1 del panel de 1 706 tiendas):

| Comprobación | Valor medido |
|---|---|
| `html_bytes` de la vista | 133 700 · **30 tarjetas** |
| Botones | **30 `--n0`** · **3 «sin número»** · 0 con credenciales todavía |
| CSS | alto fijo `height:var(--sati-foto)` ✅ · `repeat(5,minmax(0,1fr))` ✅ · corte PC en `min-width:980px` ✅ |
| Leyenda / formulario / JS | presentes ✅ |
| **Camino A** (tienda sin dueño, tienda 2) | usuario `934274553` · clave `RVS8` · `clave_formato` ✅ · **`clave_entra` (password_verify) ✅** · `cuenta_ok` ✅ · `dueno_despues` = cuenta nueva ✅ · 2.º envío **misma clave** ✅ · ↺ 1→0 ✅ · `clave_sobrevive_al_deshacer` ✅ |
| **Camino B** (dueño = admin, tienda 1598) | usuario `972634240` · clave `7ZYI` · todo igual ✅ y **`dueno_original` 9 → cuenta nueva → restaurado a 9** ✅ |
| Limpieza | `filas_despues 0` · `claves_despues 0` · `usuarios_total 13` · `quedo_limpio` ✅ |
| HTTP | panel **302** · `/includes/…` **403** · sondas **404** (borradas) ✅ |

> ✅ **La prueba de fuego de este módulo es `clave_entra`**: el mensaje puede estar lindo y no
> servir para nada si la contraseña no entra. Siempre hay que ver `password_verify() === true`.
> ⚠️ **La prueba crea y borra una cuenta real**: por eso elige tiendas **sin dueño de verdad** (a un
> dueño real le cambiaría la clave) y **restaura `dueno_id`** al terminar.

---

## 8) PROBLEMAS QUE APARECIERON Y CÓMO SE RESOLVIERON

Cada uno de estos **costó una vuelta**; están aquí para no repetirlos.

1. **Las fotos no quedaban de la misma altura.** Con `aspect-ratio` el alto dependía del **ancho de
   la columna** (y con 5 columnas cambia). ✅ **Solución:** `height` fijo con la variable
   **`--sati-foto`** (152 px en móvil, **130 px en PC**) + `object-fit:cover`. Además el nombre se
   limita a **2 líneas** (`-webkit-line-clamp:2` + `min-height:2.4em`) para que las tarjetas de una
   fila midan lo mismo.
2. **«En PC 5 columnas» y el ancho real.** El panel **no** usa todo el ancho: `<main>` mide
   **1200 px** (`--max-ancho` en `assets/css/base.css`) y el `.sa-wrap` se queda con **1168 px**
   (16 px de relleno por lado). ✅ **Solución:** como las media queries miran **el viewport** y no el
   contenedor, el corte de 5 columnas se puso en **980 px** (no en 1024): así salen 5 columnas también
   con la ventana **sin maximizar**. En cualquier PC ancha la rejilla recibe siempre 1168 px →
   **5 columnas de ~226 px**.
3. **La contraseña no se puede leer.** En la base solo hay **hash bcrypt**. ✅ **Solución:** se
   **guarda en claro por tienda** (`directorio_invitacion_claves`) para poder repetirla en el 2.º y
   3.er envío (§4.4). Es lo que pidió el jefe (*«si ya se mandó 2 veces»* → el mensaje tiene que ser
   el mismo).
4. **`login()` compara el número tal como se escribe.** Una cuenta guardada como `51934274553` **no
   entra** escribiendo `934274553`. ✅ **Solución:** `invitacion_telefono_usuario()` deja el número en
   **9 dígitos** antes de buscar o crear la cuenta (y el correo interno se arma con esos 9).
5. **172 tiendas «con dueño» eran del ADMINISTRADOR** y a un admin **no** se le cambia la clave
   (`clave_restablecer()` se niega, a propósito: una sesión robada no puede dejar al jefe fuera).
   Sin darse cuenta, **172 tiendas habrían salido sin usuario ni contraseña**. ✅ **Solución:** el
   motor decide por **`tipo`**: si el «dueño» es un **admin**, la tienda entra por el camino de
   **crear la cuenta** (y `dueno_id` pasa del admin al dueño nuevo, permitido solo en ese caso:
   `WHERE dueno_id = <admin>`). **Esto se descubrió midiendo**, no leyendo el código.
6. **El primer clic necesita una ida al servidor** (hay que crear la cuenta antes de armar el enlace),
   y un `window.open` **después** del `fetch` lo **bloquea el navegador** (perdió el gesto del
   usuario). ✅ **Solución:** la pestaña se abre **en blanco DENTRO del clic**
   (`window.open('about:blank','_blank')`) y, cuando llega la respuesta, **esa** pestaña se manda a
   WhatsApp (`w.location.href = d.url`). Si el navegador la bloquea igual, el enlace se abre en la
   misma pestaña.
7. **`sat_listar()` no puede llevar el conteo de invitaciones.** Si se metía el subquery ahí y la
   tabla fallaba, **la pestaña 🏬 Tiendas se quedaba en blanco**. ✅ **Solución:** el conteo y las
   credenciales van en **consultas aparte** (`IN (...)` con los ids de la página, todos casteados a
   `int`) con `try/catch` que **reintenta creando la tabla**; si aun así falla, devuelve `[]` y las
   tarjetas se pintan igual con 0.
8. **El POST del `fetch` dejaba un `flash()`** que aparecía en la pantalla siguiente. ✅ **Solución:**
   `invitacion_es_ajax()` + `echo json_encode(...)` + `exit` **antes** del `flash()` y del redirect.
9. **La respuesta JSON no traía `creada`.** Lo detectó la sonda (el campo salía vacío aunque la cuenta
   se había creado). ✅ **Solución:** se agregó `'creada' => !empty($cred['creada'])` a
   `invitacion_enviar()`.
10. **Falso positivo de la propia sonda:** `substr_count($html, 'data-listo="1"')` daba 1 aunque no
    hubiera ninguna credencial — estaba contando **el texto de un comentario del JavaScript**. ✅
    **Solución:** contar con **regex sobre el `<a>`** (`/<a class="sati-inv[^>]*data-listo="1"/`).
11. **La sonda eligió la tienda 1**, cuyo teléfono (`955041690`) es el **del administrador** (hoy el número del administrador es **908 785 164**): probaba
    el caso raro y no el normal. ✅ **Solución:** elegir la tienda entre las que **no tienen cuenta**
    para ese número (y probar **los dos caminos**, A y B).
12. **La consola de Windows rompía** al imprimir `→` (`__sonda_run.py` con `cp1252`:
    `UnicodeEncodeError`). ✅ **Solución:** `$env:PYTHONIOENCODING='utf-8'` antes de correr la sonda.
13. **El punto final del enlace** en el texto del jefe: WhatsApp lo incluye en el enlace. ✅
    **Solución:** se quitó el punto (y la línea suelta con un `.`), respetando el resto **palabra por
    palabra**.

> 🔴 **Recordatorio de siempre (trampa del FTP):** el FTP **entra** en `/public_html`, que es una
> **copia vieja anidada**; la raíz viva es **`/`**. `__subir_uno.py` y `__sonda_run.py` ya hacen
> `cwd('/')` y comprueban la marca `assets/css/carrito.css`: **no quitar esa comprobación**.
> Detalle: `GUIA_DESPLIEGUE_Y_ENTORNO.md` §11.1.

---

## 9) CONSEJOS PARA LA PRÓXIMA SESIÓN

1. **No abrir el navegador.** El jefe da los clics él mismo (Regla de Oro n.º 5). Todo se prueba con
   la sonda `__inv_verificar.php`, que además **limpia lo que crea**.
2. **No generar credenciales al pintar el listado.** Si eso pasara en la vista, **abrir la página
   crearía 30 cuentas**. Las credenciales se generan **solo en el clic** (en el POST).
3. **Antes de «arreglar» el conteo, mirar el `title` del botón**: ahí está la fecha del último envío y
   el usuario/contraseña con que salió el mensaje.
4. **Cuidado con quitar la clave de `directorio_invitacion_claves`:** sin esa fila, el siguiente envío
   **regenera la contraseña** y al dueño le llega una distinta (y la anterior deja de servir).
5. **No probar con tiendas de dueños de verdad** (les cambia la clave): para eso la sonda busca tiendas
   sin dueño y, si toca una del admin, **restaura `dueno_id`**.
6. **El orden del despliegue no se cambia:** `php -l` → `python __subir_uno.py <ruta>` → verificar por
   HTTP. **Sondas con `__sonda_run.py`** (jamás con `__ep_run.py`, que escribe en la copia vieja).
7. **La consola siempre en UTF-8** (`$env:PYTHONIOENCODING='utf-8'`) cuando se corren scripts Python.
8. **Si el jefe pide cambiar el mensaje**, se toca **una sola función**: `invitacion_mensaje()`
   (y si lo que quiere es otra deseo para un rubro, **una línea** del mapa de `invitacion_deseo()` — §2.1);
   el enlace y el mensaje salen de ahí para todos los caminos (y las dos líneas de la cuenta solo se
   agregan si hay credenciales).
9. **Si hay que rehacer la tabla** (por ejemplo tras una limpieza), no hay que hacer nada: se crea
   sola en la primera lectura y en el primer envío.
10. **El botón «sabe» si la tienda tiene número**: si el jefe dice *«a esta tienda no le sale el
    botón»*, casi siempre es que **no tiene WhatsApp ni teléfono** (sale `📨 Sin número`, en rojo).

---

## 10) USOS A FUTURO (ideas, ninguna implementada todavía)

| Idea | Cómo se haría |
|---|---|
| **Filtro «sin invitar» en la pestaña** | Una píldora más en `vista_tiendas_admin.php`. ⚠️ **El `WHERE` de `sat_where()` NO debe llevar el `EXISTS` a la tabla de invitaciones** (si la tabla falla, se cae el listado): va como consulta aparte, como el conteo de hoy |
| **«No invitar dos veces el mismo día»** | Comparar `MAX(enviado_en)` con hoy antes de apuntar (`invitaciones_mapa()` ya devuelve `ultima`) y avisar en el `title` |
| **Mensajes de seguimiento distintos (2.º y 3.er envío)** | El motor ya sabe el nivel (`invitacion_nivel($n)`): se pueden usar 3 textos (recordatorio suave → «¿ya viste tu tienda?» → último aviso). Hoy, a pedido del jefe, **el texto es el mismo** |
| **Marcar «respondió»** | Cruzar con `directorio_negocios.dueno_id` / productos nuevos: **una tienda que ya está a nombre de su número respondió** (la sonda ya calcula eso) |
| **Tablero de la campaña** | «Invitaciones por día · tiendas reclamadas después de la invitación · cuántas siguen en ⚫» — una consulta sobre `directorio_invitaciones` (encaja con 📈 Estadísticas y récords) |
| **Exportar el estado a CSV** | `negocio_id · nombre · teléfono · veces invitada · último envío · usuario` para trabajar fuera del panel |
| **Invitación en lote** | 🔴 **Cuidado**: mandar 300 WhatsApp seguidos desde el mismo número es la receta para que **WhatsApp lo bloquee por spam**. Si se hace, de a pocos, con pausas y con textos distintos |
| **Reusar el motor para otros avisos** | El mismo patrón (mensaje + `wa.me` + color por nivel) sirve para los **avisos de empleo**, para «**reclama tu tienda**» del Caminante o para recordarle a un dueño que le faltan fotos/productos |
| **Cambiar la clave vieja antes de crearla** | Si el jefe quiere que **su** cuenta siga siendo la dueña, la llave está en `invitacion_credenciales()`: hoy, cuando el dueño es real, se le da clave nueva a **esa** cuenta |
| **Enlazar con `/recuperar`** | Ya existe el módulo de claves (`clave_recuperar.php` + aviso de Telegram `clave_olvidada`): si el dueño pide su clave, el jefe se la resuelve sin tocar nada de este módulo |

---

## 11) REGISTRO DE LO HECHO (2026-09-17)

| Qué | Detalle |
|---|---|
| **Pedido 1** | *«todas las imágenes de tiendas deben tener la misma altura, altura corta, y en modo PC muéstralo en 5 columnas, y debajo de cada tienda pon un botón de invitación»* (con los 4 colores del conteo) |
| **Pedido 2** | *«en el primer mensaje también se debe incluir el usuario y su contraseña: Usuario: numero de telefono · Contraseña: 3 letras y un número, sin el 0 ni el o»* |
| **Lo que eligió el jefe** | conteo **automático** al tocar el botón · **↺ para corregir** · **crear la cuenta** de la tienda cuando no la tenga · **clave nueva** en las tiendas con dueño de verdad · **sin** línea del enlace del login |
| **Archivos subidos** | `includes/invitaciones.php` (22 288 bytes) · `includes/vista_tiendas_admin.php` (48 371) · `superadmin.php` (71 608) |
| **Medido** | 1 706 tiendas · 30 tarjetas por página · 3 sin número · censo de dueños (§4.3) · sondas borradas (404) |
| **Probado** | camino A y B completos, `password_verify` OK, conteo 1-2-3-4, ↺ 3-2-1-0, base **sin cambios** al terminar |

### 11.1 🎄 EL MENSAJE PERSONALIZADO DE NAVIDAD (2026-09-19 — §2 y §2.1)

| Qué | Detalle |
|---|---|
| **Pedido (1.ª vuelta, mañana)** | *«quiero que lo personalices… que el cliente lo vea como un premio a su trabajo y como la oportunidad de llevar su empresa al siguiente nivel»* (con el Payasito Crespín como ejemplo y la orden de **no nombrar la empresa**) |
| **Pedido (2.ª vuelta, tarde)** | *«muy grande, tienes que hacerlo más corto, más directo, redúcelo a la mitad de texto… sale un botón que dice "leer más" y mucha gente no lo va a leer… tienes que ser claro desde un inicio: **te vamos a ayudar a vender**»* → el jefe pidió **ver ejemplos ANTES de tocar nada**, se le dieron **3 versiones (A directa · B mínimo · C como premio)** y eligió **la C** (*«la C está bonita»*) |
| **Lo que se hizo** | `invitacion_mensaje()` reescrita **en la versión C corta** (≈95 palabras contra ≈150 de la primera) y **4 funciones nuevas**: `invitacion_giro()` · `invitacion_rubro_de()` · `invitacion_merito()` (la que usa el mensaje) · `invitacion_deseo()` (guardada para el seguimiento) |
| **El mérito** | **122 rubros** mapeados + **~140 familias** por trozo de slug + **«Tu trabajo»** de último recurso · **0 rubros** se quedan con la frase genérica |
| **Lo que descubrió el paso del giro** | el **Payasito Crespín está guardado en el rubro `eventos`**, así que el nombre y la descripción (**no** el rubro) son los que deciden: hoy arranca con *«Tu trabajo con los niños merece más clientes…»* |
| **Archivo subido** | `includes/invitaciones.php` (**73 741 bytes**; antes 22 288) — un solo archivo, sin tocar la vista ni `superadmin.php` |
| **Medido** | sonda `__inv_rubros.php`: **122 rubros / 0 genéricos** · el mensaje completo del Payasito Crespín (id 1926), de «veterinaria dias» (id 1554, guardada en `bodegas`) y de «La Waffleria Chimbote» (id 1829) sale con su frase correcta y **idéntico desde el panel y desde la ficha** (`mensaje == mensaje_panel`) |
| **Sin romper nada** | `__inv_verificar.php` (x): 30 tarjetas · leyenda · formulario · JS · 20 ⚫ · 10 listos · `__inv_ficha_verificar.php` (admin, Payasito Crespín): bloque 📨 dentro de la ficha, 🟠 1 · `href` de WhatsApp ✅ · HTTP: panel **302** · ficha **200** · `/includes/…` **403** |
| **Decisión de redacción** | **no se pone el año** («esta Navidad», sin «2027»): así el mensaje **no envejece** ni miente si se manda en otro diciembre |

---

## 12) 📨 EL BOTÓN TAMBIÉN EN LA FICHA DEL NEGOCIO (2026-09-17 — segunda parte del pedido)

> **Pedido textual del jefe (mismo día, después de probar el del panel):** *«cuando estoy logueado como
> súper administrador (jimmylopez…) **ahí** debe aparecer un botón de enviar invitación… así le mandaré
> un WhatsApp»* — con el pantallazo de la **ficha** de una tienda (Miru Snack Bar, plantilla A) al lado.
> O sea: **el botón 📨 va también dentro de la propia ficha**, para invitar a la tienda que se está
> mirando sin ir a buscarla al panel.

### 12.1 Qué se hizo

| Pieza | Dónde | Qué hace |
|---|---|---|
| `invitacion_ficha_puede()` | `includes/invitacion_ficha.php` | **Solo el súper admin**: `es_admin()` (se guarda en memoria) |
| `invitacion_ficha_accion($negocio)` | ídem | Atiende el **POST** del botón y del ↺: **403** si no es admin, `csrf_verificar()`, y responde **JSON** al `fetch` (o redirige a WhatsApp si el navegador no corre JavaScript) |
| `invitacion_ficha_html($negocio)` | ídem | El **bloque** (botón + ↺ + leyenda + formulario oculto + JavaScript). Devuelve **`''`** si el que mira no es admin |
| Llamada + pintado | `negocio.php` | `invitacion_ficha_accion($negocio)` **al principio** (antes de pintar) y `<?= $invf_en_A ?>` **debajo de los botones de WhatsApp/Llamar** |

- **Es el mismo motor**: `invitacion_enviar()` / `invitacion_deshacer()` de `includes/invitaciones.php`.
  Un clic desde la ficha vale **exactamente lo mismo** que uno desde el panel: el mensaje lleva la
  invitación **+ usuario y contraseña**, queda apuntado en `directorio_invitaciones` con el id del admin
  y el botón cambia de color con los **mismos 4 colores** (⚫🟠🟢🔵).
- 🔒 **El público no ve nada**: `invitacion_ficha_html()` devuelve cadena vacía para visitantes y dueños
  (medido: la ficha pública **no tiene ni un `invf__`** en el HTML), y el POST sin sesión de admin
  responde **403 «Solo el súper administrador puede invitar tiendas.»** (comprobado con `curl`).
- El bloque se pinta **en UNA sola plantilla** (`$invf_en_A/B/C`), como la canción y las opiniones: si se
  pintara en las tres, el **formulario oculto y el JavaScript viajarían tres veces** en el mismo HTML.
  📊 Dato medido: **las 1 705 tiendas activas usan la plantilla A** (la vista `vista_negocio_ficha_completa`
  no tiene ninguna B ni C), así que el botón se ve en todas; el código de B y C queda por si algún día se usan.

### 12.2 Cómo se prueba (sin abrir el navegador — Regla de Oro n.º 5)

```powershell
cd D:\RELAX
C:\xampp\php\php.exe -l deploy\includes\invitacion_ficha.php
C:\xampp\php\php.exe -l deploy\negocio.php
python __subir_uno.py includes/invitacion_ficha.php
python __subir_uno.py negocio.php

$env:PYTHONIOENCODING='utf-8'
# 1) la ficha CON sesión de admin (mide el bloque 📨 dentro de la página)
python __sonda_run.py __inv_ficha_verificar.php inv-ficha-2026-9kQ7 x "&slug=miru-snack-bar-chimbote&como=admin"
# 2) la ficha como la ve el público (NO debe aparecer ningún `invf__`)
python __sonda_run.py __inv_ficha_verificar.php inv-ficha-2026-9kQ7 x "&slug=miru-snack-bar-chimbote&como=publico"
# 3) la ida y vuelta del POST (tienda sin dueño: crea cuenta de verdad) y su limpieza
python __sonda_run.py __inv_ficha_verificar.php inv-ficha-2026-9kQ7 x "&go=1"
python __sonda_run.py __inv_ficha_verificar.php inv-ficha-2026-9kQ7 x "&limpiar=1&t=<id de la tienda que salió>"

# 4) por HTTP: la ficha pública 200 y limpia · el POST sin sesión 403 · el panel 302 · includes 403
curl.exe -s -o NUL -w "%{http_code}\n" "https://dechimbote.com/neg/miru-snack-bar-chimbote"
curl.exe -s -X POST -H "X-Requested-With: XMLHttpRequest" -d "accion=tienda_invitar&id=1751" "https://dechimbote.com/neg/miru-snack-bar-chimbote"
curl.exe -s -o NUL -w "%{http_code}\n" "https://dechimbote.com/superadmin.php?seccion=tiendas"
```

**Lo medido el 2026-09-17** (página de la ficha, `miru-snack-bar-chimbote`, id 1751):

| Comprobación | Valor medido |
|---|---|
| Como **admin** | `invf_en_pagina` ✅ · `invf_btn` ✅ · **1** solo `id="invf-form"` ✅ · JS ✅ · leyenda ✅ · `data-inv-id="1751"` ✅ · ⚫ nivel 0, «sin invitación enviada» ✅ |
| Como **público** | `invf_en_pagina` ❌ (ni un `invf__` en 411 840 bytes) · el botón **WhatsApp de la tienda sigue igual** ✅ |
| Tienda **ya invitada** (1733 · Decoraciones Chimbote) | 🟠 nivel 1 · `data-listo="1"` · `href` = `https://wa.me/…` con el mensaje y la clave ✅ |
| **Ida y vuelta del POST** | JSON `{ok:true, n:1, url:wa.me/…, usuario:934274553, clave:4UED, creada:true}` ✅ (cuenta creada y tienda a su nombre) |
| **Limpieza** | cuenta borrada · `filas_despues 1` · `claves_despues 1` · `dueno_id` restaurado · `usuarios_total 14` (los de siempre) ✅ |
| HTTP | ficha **200** · POST sin sesión **403** · panel **302** · `/includes/…` **403** ✅ |

> ⚠️ **La prueba `go=1` crea una cuenta de verdad**: elige siempre una tienda **sin dueño** y limpia al
> final (nunca una tienda de un dueño real, que perdería su clave).

### 12.3 🐞 El error de fecha que apareció al medir (arreglado en LOS DOS botones)

El `title` salía **«último envío el 17/09/2026 am Thursdayam26 09:07»**. No era un problema de datos:
en `date()` la **`a`** es *am/pm*, la **`l`** el día de la semana y la **`s`** los segundos, así que
`date('d/m/Y a las H:i')` estaba formateando el texto «a las». ✅ **Solución:** escaparlo —
`date('d/m/Y \a \l\a\s H:i', $ts)` → **«17/09/2026 a las 09:07»**. Estaba en los dos sitios
(`vista_tiendas_admin.php` **y** `invitacion_ficha.php`) y **se corrigió en los dos**.

### 12.4 Lo que hay que saber para la próxima sesión

1. **El mensaje, las credenciales, los colores y las tablas son los mismos** de §2 a §5: no hay nada
   nuevo que mantener. Solo cambia **dónde** está el botón.
2. **Si el jefe dice «no veo el botón en la ficha»,** mirar en este orden: (1) ¿está **logueado** como
   admin? (el botón no existe para nadie más); (2) ¿la tienda tiene WhatsApp o teléfono? Si no, sale
   **«📨 Sin número»** en rojo; (3) ¿la página se sirvió de una **caché** del navegador? recargar.
3. **La ficha pública NO se cachea de forma especial** (el sitio ya arranca sesión en el `header`), así
   que no hay riesgo de que el bloque del admin se le sirva a un visitante: se decide **en el servidor**,
   petición por petición.
4. **El POST va a la MISMA URL de la ficha** (`/neg/<slug>`, sin `action` en el formulario), así que
   funciona con la URL amigable y **no hay endpoint nuevo que mantener**.
5. Si algún día se quiere el botón también en **otras páginas** (producto, buscador, categoría), el
   camino es el mismo: `require_once includes/invitacion_ficha.php` + `invitacion_ficha_accion($negocio)`
   al principio + `<?= invitacion_ficha_html($negocio) ?>` donde se quiera el bloque.

---

> **Guías hermanas:** `GUIA_SUPERADMIN_PANEL.md` (§4.1 a §4.4: la pestaña 🏬 Tiendas entera) ·
> `GUIA_BOTONES_WHATSAPP.md` (la regla de oro de los mensajes de WhatsApp: **nunca un chat en
> blanco**) · `GUIA_RECLAMOS_DE_TIENDAS.md` (la puerta del dueño que reclama su negocio) ·
> `GUIA_DESPLIEGUE_Y_ENTORNO.md` (subir, sondas y la trampa del FTP).
