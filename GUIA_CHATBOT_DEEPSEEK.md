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


# GUIA_CHATBOT_DEEPSEEK.md — El chat de ayuda de dechimbote.com («El guía» 🧭, motor: API de DeepSeek)

> 🧭 **CAMBIO DE PAPEL Y DE NOMBRE (2026-09-17, orden del jefe): el bot YA NO SE LLAMA «El ninja» y
> YA NO ES EL ASISTENTE DE TODO EL SITIO.** Ahora es **«El guía» 🧭**, el **anfitrión de cada tienda**:
> se pinta **solo dentro de las fichas** (`/neg/<slug>`, constante `CHATBOT_SOLO_FICHAS`) y llega con
> **toda la información de esa tienda precargada** (horario, ubicación y referencia, qué hay cerca,
> productos y precios, cómo se compra y si la ficha está sin reclamar). Se presenta hablando del día
> en una frase corta («una mañana fría»), su botón es un **círculo futurista con halo** que al cargar
> hace el efecto de abrirse y cerrarse, y sigue ofreciendo **crear tu propia tienda con El maestro**.
> **Todo el detalle está en §2duodecies** (que es lo que manda hoy). Lo de «El ninja» sitio-entero
> que se cuenta más abajo queda como historia: sigue siendo válido el motor, el guion, el buscador,
> la visión, el anfitrión 🎩 y la cuota, pero **el botón ya no vive fuera de las fichas**.
> 🔧 **Segunda tanda del mismo día** (al verlo, §2duodecies punto 11): letra **más chica** (14 px) y
> bocadillos a todo el ancho · **solo 3 opciones**, chiquitas (el resto en el 💡) · las respuestas en
> **primera persona** («tenemos», «nuestro horario») · los productos en **tabla** a todo el ancho ·
> fuera el pie «Asistente automático · Hablar con una persona» · **🧹 fijo en la cabecera** para volver
> a empezar.
>
> 🔴 **LO QUE MANDA HOY (2026-09-20, §2duodecies punto 14 y su punto 15): el guía es un CHATBOT NORMAL.**
> Se le **borró toda la voz** (⛔ el botón compuesto 🎤🛑➤, la grabadora y la marca de grabación: lo que se
> cuenta en los puntos 11, 12 y 13 de ese apartado y en las trampas 36 y 37 queda **ARCHIVADO**), la
> ventana va **pegada ARRIBA y A LA DERECHA al 85 % × 85 %** (ya no centrada) y el pie tiene
> **📷 cámara · 🎤 micrófono · cuadro de escribir · ➤ enviar**: **📷 cámara** (abre un **menú chiquito
> encima del botón**, como el ☰ del sitio: **Abrir cámara** · **Abrir galería**) y **🎤 el micrófono
> COPIADO TAL CUAL DEL BUSCADOR** (mismo icono, mismo naranja, misma forma, mismo motor: **punto 15**),
> que convierte la voz en texto **en el cuadro de al lado** y, si el micrófono **se apaga porque escuchó
> silencio**, **manda el mensaje solo al segundo** (**punto 16**; si lo parás con el dedo, no).
> ⛔ **Nada de iconos compuestos**: la 📷 solo abre su ventana, el 🎤 solo dicta y el ➤ solo envía.
> ⚠️ **El buscador por voz** (`assets/js/buscador_voz.js`, `GUIA_BUSCADOR_VOZ.md`) **NO se tocó**: sigue
> como estaba (el guía usa una **copia** de su motor, no el archivo).

> ⚠️ **DOS COSAS DE ESTA GUÍA QUE ESTABAN VIEJAS Y QUEDARON CORREGIDAS EL 2026-09-17:**
> 1. **El chat NO se limita por tiempo**: los minutos (§2bis) **se retiraron el 2026-09-13**. Hoy se
>    limita por **CANTIDAD DE PREGUNTAS AL DÍA: 20 el visitante · 50 el registrado · 300 el ⭐ Premium**
>    (`CHATBOT_PREGUNTAS_VISITANTE/REGISTRADO/PREMIUM`, ver §2duodecies punto 7).
> 2. **El nombre del bot lo puede pisar la base**: `titulo`, `nombre` y `emoji` guardados en el panel
>    (Súper Admin → 🧭 El guía (chat)) **mandan sobre `config_chatbot.php`**. Si se cambia el nombre en
>    el archivo y en la web sigue saliendo el viejo, es esto (trampa 32 de §8).

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Módulo nuevo del 2026-09-13** (pedido del jefe: *«ayúdame a crear un chatbot usando la API de
> DeepSeek; el bot lo insertaremos en el sitio y responderá a preguntas como…»*).

---

## §0. Qué es, en una frase, y cómo queda instalado

Es **«El ninja» 🥷**, **una ventanita de chat que aparece en TODAS las páginas del sitio** (botón
flotante **"🥷 El ninja"**). El visitante pregunta **escribiendo o hablando 🎙️** y el bot contesta con
datos **reales del sitio** (enlaces y pasos exactos) y con **los datos del día** (fecha, hora, clima
de Chimbote y **nuestras noticias locales**). Responde usando la **API de DeepSeek**
(`deepseek-chat`), pero **la clave vive solo en el servidor**: el navegador nunca la ve.

> 📰 **LAS NOTICIAS SON NUESTRAS Y NO SE SALE DEL SITIO (2026-09-13, regla que no se negocia):** el
> bot **nunca** manda al visitante a un medio de afuera (ni Andina, ni RPP, ni Google Noticias). Todas
> las noticias que ofrece son las que publicamos en **`/noticias`** y **cada titular enlaza a nuestra
> ficha `/noticia/<slug>`**. La lista nacional por RSS (Agencia Andina/RPP) que el chat ofrecía **se
> retiró ese día**. Detalle: **§2ter** y **`GUIA_NOTICIAS_DIARIAS.md` §9**.

**Cómo se ve (pedido del jefe):** la ventana **NO ocupa toda la pantalla**. 🔴 **LO QUE MANDA HOY
(2026-09-20): `85 %` de ancho × `85 %` de alto, centrada y flotando sobre la página**, con el fondo
oscurecido y desenfocado, y se cierra con **la ✕ de la cabecera** (al lado de la 🧹), tocando fuera o con
Escape. (Los «70 %» y «de borde a borde en alto» que se leen más abajo son **históricos**: cada orden
nueva del jefe reemplazó a la anterior. Detalle de la última: **§2duodecies, apartado 13**.)

Las preguntas que pidió el jefe, y que ya están cubiertas:

1. ¿Cómo me registro?
2. ¿Cómo pongo un producto en mi negocio?
3. Dame 10 consejos de marketing para mi negocio → **solo para quien tiene sesión iniciada** 🔒
4. ¿Cómo borro mi sitio?
5. ¿Cómo busco empleo?
6. ¿Cómo veo tiendas cerca de mí?
7. ¿Cómo publico mi tienda o negocio?

Y además: cuánto cuesta, cómo contacto a una tienda / hago un pedido, cómo edito mi tienda,
"quiero hablar con una persona", **el clima de Chimbote hoy** y **las noticias locales de hoy**
(las nuestras, de `/noticias`).

**⏱️ El chat tiene tiempo limitado (2.º pedido del jefe, 2026-09-13):** **1 minuto al día** sin
cuenta · **3 minutos al día** con cuenta gratis · **sin límite** para los miembros ⭐ **Premium
(S/ 30 al mes)**. Cuando se acaba, el chat **se cierra solo** y ofrece el siguiente paso
(registrarse, o pasar a Premium). Detalle en **§2bis**.

**📅 Saluda con un mensaje personalizado y la noticia llega en la pregunta 3 o 4 (2026-09-13, 4.º pedido
del jefe; antes el saludo llevaba la noticia):** el jefe pidió *«el saludo debe empezar con un mensaje
personalizado tipo "hoy será un día soleado, te saluda el ninja, ¿tienes alguna pregunta para mí?"»* y
*«el usuario hará su pregunta y el bot responderá y en la pregunta 3 o 4 dirá: hoy "noticia X" es bueno
estar informado»*. Saluda **por franjas horarias** («Buenas trasnochadas» antes de las 3, «Buenas
madrugadas» de 3 a 7, «Buenos días» de 7 a 12, «Buenas tardes» de 12 a 5 pm, «Buenas noches» de 5 pm a
medianoche) **sin decir la hora exacta**; dice **de frente cómo está el cielo** (sin «en Chimbote» y **sin
temperaturas**; de noche en pasado: «hoy estuvo soleado») y **la noticia ya no va en el saludo**: sale en
la **pregunta 3 o 4**, en una línea y con el titular como enlace. Clima y noticias se piden **UNA sola vez
al día** y se guardan **24 h** para todas las conversaciones. Detalle en **§2ter**.

**🔗 Los enlaces NUNCA se muestran completos** (pedido del jefe): siempre con un **nombre** en otro
color y subrayado («visita [la sección de anuncios](…)»). Detalle en **§2octies**.

**⚡ Habla corto y sin jerga, y la ubicación se pide dentro del chat (2026-09-13):** respuestas de
**25 palabras** (una o dos líneas, una sola pregunta al final), **prohibido** decir «panel», «banner»,
«estadística» o cualquier ruta, y cuando alguien busca («pollo») el bot da **los resultados reales** y
ofrece **«📍 Ver las más cercanas a mí»**: pide el GPS y ordena por distancia **sin salir del chat**.
Detalle en **§2nonies**.

**🪟 La ventana mide el 90 % × 85 %** con **fuente de 18 px** (el sitio usa 16) y las **opciones van
apiladas** (una encima de otra) y se van solas. Detalle en **§2octies**.

**🗂️ Ocupa el lugar de los tablones (misma orden del jefe):** «oculta tablones y deja el chatbot» →
en el **panel del usuario** (`/perfil`) ya no aparece «🗂️ Mis tablones» y en su lugar está la tarjeta
y la sección de **El ninja**.

> 🗑️ **RETIRADO (2026-09-13, orden del jefe):** los **tablones** ya no existen en el sitio. El **chat
> comunitario** (la página `chat_comunidad.php`, su API, la banda del último mensaje y el botón
> flotante **💬 Chat**) se **borró** del hosting y de `deploy`, y la **mensajería privada entre tiendas
> (B2B)** se retiró: sus archivos (`tablones.php`, `tablon.php`, `api/tablon_enviar.php`,
> `api/tablon_estado.php`, `migrar_tablones.php`) **ya no están en `deploy`** (quedaron guardados en
> `D:\RELAX\_ARCHIVO_RETIRADO_CHAT_Y_TABLONES_2026-09-13\`). Motivo: el jefe ordenó **quitar del sitio
> toda referencia a los tablones** — el chat comunitario estaba **vacío o casi vacío** (y un tablón
> público sin gente da peor impresión que no tenerlo) y la **mensajería entre tiendas nunca se publicó**
> (sus enlaces solo generaban **404 y confusión**); además así queda **un solo botón flotante** (🥷 El
> ninja). **Hoy no queda ningún tablón ni código vivo de ellos.**

**Estado (2026-09-13):** instalado, con **la clave de DeepSeek puesta** (vive en
`includes/config_chatbot.php`; en las guías no se copia) y probado en producción: las respuestas de
la IA salen con `"fuente":"ia"`.

---

## §1. Los archivos (y qué toca cada uno)

| Archivo | Qué es | ¿Se toca seguido? |
|---|---|---|
| `deploy/includes/config_chatbot.php` | **Ajustes de fábrica**: la clave, el modelo, los topes y los tiempos por defecto. | Poco (casi todo se cambia desde el panel) |
| 🧭 `deploy/includes/chatbot_ficha.php` | **EL GUÍA EN LA TIENDA** (2026-09-17): carga el contexto de la ficha donde está el visitante (horario, dirección y referencia, cercanos, productos y precios, dueño/reclamo), arma su saludo con la frase del día, contesta **sin gastar tokens** lo que se pregunta de una tienda y le da al modelo el bloque con todos sus datos. Ver **§2duodecies**. | Sí (es el módulo nuevo) |
| `deploy/includes/chatbot_ajustes.php` | **Los ajustes editables desde el Súper Admin** (tabla `directorio_chatbot_ajustes`): catálogo, lectura, aplicación y guardado. | Sí (al añadir un ajuste nuevo) |
| `deploy/includes/vista_chatbot_admin.php` | El **panel del jefe** (Súper Admin → 🥷 Ninja (chat)): formulario, uso y gasto del día y las últimas preguntas. | Sí (diseño del panel) |
| `deploy/includes/chatbot_kb.php` | **Lo que el bot SABE del sitio**: las respuestas verificadas y los 10 consejos. | Sí (cuando cambia una página o un botón) |
| `deploy/includes/chatbot_diario.php` | **El contexto del día**: fecha, hora, clima de Chimbote (Open-Meteo) y **NUESTRAS noticias locales** (`/noticias`), guardado 24 h. ⛔ Ya **no** pide noticias nacionales por RSS. | Sí (ciudad, fuentes) |
| `deploy/includes/chatbot.php` | **Motor**: mira quién pregunta, aplica límites y el reloj, llama a DeepSeek, guarda el registro y tiene la **red de seguridad** local. | Poco |
| `deploy/includes/chatbot_buscar.php` | 🔎 **El buscador vivo**: interpreta la pregunta, busca en la base del sitio (tiendas, productos y **el rubro de las frases de unión**) y arma la respuesta corta. Ver **§2nonies c-bis**. | Sí (al ampliar el diccionario o el emparejador) |
| `deploy/includes/chatbot_ofrecer.php` | 🎩 **El anfitrión**: detecta el MOMENTO (calor, hambre, un cumpleaños…) o lo que se ve en una FOTO y ofrece los negocios que le sirven. Ver **§2decies**. | Sí (al añadir un momento nuevo) |
| `deploy/api/chatbot.php` | **La puerta**: lo único que ve el navegador. `GET` = datos para abrir el chat; `POST` = la pregunta. | Poco |
| `deploy/includes/chatbot_widget.php` | El **widget** (HTML + CSS): el botón, la ventana, las preguntas rápidas, el 🎙️. | Sí (diseño) |
| `deploy/assets/js/chatbot.js` | La **conversación** en el navegador (memoria de 12 h, markdown mínimo, fotos y el modal de la cámara). ⛔ **Sin voz** desde el 2026-09-20 (§2duodecies punto 14). | Sí (⚠️ subirle el `?v=`) |
| `deploy/includes/footer.php` | Donde vive la **única línea** que lo pinta en todo el sitio. | Casi nunca |
| `deploy/cache/.htaccess` | Bloquea por web la carpeta de trabajo (contadores y registro de preguntas). | Nunca |
| `deploy/cache/chatbot/rl/` | Contadores de los límites (los crea solo). | — |
| `deploy/cache/chatbot/tiempo/` | **Cronómetros del reloj de uso** (uno por persona y día). | Se borran solos a los 3 días |
| `deploy/cache/chatbot/diario/AAAAAMMDD.json` | **El contexto del día** (clima + **nuestras noticias**), para no pedirlo en cada conversación. | 1 por día |
| `deploy/cache/chatbot/log/AAAA-MM-DD.jsonl` | El **registro de preguntas** (una línea por pregunta). | Se lee, no se edita |
| `__ver_chat_log.py` (raíz del proyecto) | Para **leer** ese registro desde la PC del jefe. | Herramienta |
| `__chat_tiempo.py` (raíz) | Para **probar el reloj** sin esperar minutos (ver/agotar/borrar). | Herramienta |
| `__test_chatbot_kb.php` (raíz) | Prueba del guion y del emparejador **sin gastar saldo**. | Herramienta |
| `__test_chatbot_tiempo.php` (raíz) | Prueba del **reloj de uso** (1 min / 3 min / premium). | Herramienta |
| `__test_chatbot_dia.php` (raíz) | Prueba del **contexto del día** (clima, noticias y caché 24 h). | Herramienta |
| `__chat_costo.py` (raíz) | **Cuánto gasta** el chat y cuántas preguntas quedan con el saldo (lee el registro real). | Herramienta |
| `__test_chatbot_modelos.php` (raíz) | Qué **modelos** acepta la clave y el **saldo** de la cuenta (sin imprimir la clave). | Herramienta |
| `__test_chatbot_vision.php` (raíz) | Prueba la **visión**: manda una imagen al modelo y comprueba que la describe. | Herramienta |
| `__test_chatbot_prod.php` (raíz) | Prueba **contra el sitio en producción**: mensaje del minuto + una imagen real. | Herramienta |

**La línea del footer** (es todo lo que hay que saber para insertarlo en una página nueva):

```php
require_once __DIR__ . '/chatbot_widget.php';
echo chatbot_widget_html();
```

⚠️ Para **esconderlo en una página concreta**, esa página define antes de su `footer.php`:
`define('CHATBOT_OCULTO', true);`

---

## §2. La clave de DeepSeek (ya está puesta)

> ✅ **Estado 2026-09-13: la clave ya está puesta** en `includes/config_chatbot.php` y probada
> (`"fuente":"ia"`). **La clave NO se copia en las guías ni en ningún archivo del proyecto que no sea
> `includes/config_chatbot.php`.** Si algún día hay que cambiarla (otra cuenta, rotación), el
> procedimiento es este:

1. Entrar a **https://platform.deepseek.com** → **API keys** → **Create new API key**.
   La clave empieza con **`sk-`** y **se muestra UNA sola vez** (copiarla en ese momento).
2. Pegarla en **`deploy/includes/config_chatbot.php`**, en esta línea:

```php
if (!defined('CHATBOT_DEEPSEEK_KEY'))  define('CHATBOT_DEEPSEEK_KEY', 'sk-…AQUÍ…');
```

3. Subir **solo ese archivo** y probar:

```powershell
C:\xampp\php\php.exe -l deploy\includes\config_chatbot.php
python __subir_uno.py includes/config_chatbot.php
```

4. Probar de verdad (desde la PC, sin abrir el navegador):

```powershell
curl.exe -s -H "Content-Type: application/json" --data-binary "@pregunta.json" https://dechimbote.com/api/chatbot.php
# (pregunta.json = {"mensaje":"como me registro","historial":[]})
```

Si la respuesta trae **`"fuente":"ia"`**, la clave está funcionando. Si trae **`"fuente":"local"`**,
la API no se usó (clave vacía, saldo, red o error) y el bot contestó con el guion: el motivo exacto
queda en el registro (§6) y **llega un aviso al Telegram** cuando el problema es de clave o de saldo.

🔒 **Por qué la clave está segura ahí:** `includes/` está bloqueado por web en el `.htaccess` de la
raíz (`RedirectMatch 403 ^/includes/`). **Comprobado el 2026-09-13:** `https://dechimbote.com/includes/config_chatbot.php`
devuelve **403**. Nunca poner la clave en la raíz, en `assets/` ni en JavaScript.

💰 **Saldo:** la cuenta de DeepSeek del jefe tenía **US$ 1,67** el 2026-09-13. Con `deepseek-chat` y
el guion ya en caché de contexto, **una pregunta cuesta del orden de US$ 0,0003** (unas 5 000
preguntas con ese saldo). Los topes de §5 existen justamente para que nadie lo vacíe.

---

## §2bis. ⏱️ EL TIEMPO DE USO — 🗑️ RETIRADO EL 2026-09-13 (hoy manda la CUOTA DE PREGUNTAS)

> **🗑️ ESTA SECCIÓN ES HISTORIA.** Lo que se describe aquí (3 minutos el visitante, 6 el registrado,
> Premium sin límite, cronómetro, aviso de los últimos segundos, `__chat_tiempo.py`) **ya no existe**:
> las constantes `CHATBOT_SEG_*`, `CHATBOT_AVISO_SEG` y `CHATBOT_PRUEBA_AVISO` **no están en ningún
> archivo de `deploy`** y no hay ningún reloj en el chat. Desde el 2026-09-13 se limita por
> **PREGUNTAS AL DÍA: 20 el visitante · 50 el registrado · 300 el ⭐ Premium (0 = sin límite)**, contadas
> en el servidor con un archivo por persona y día (`cache/chatbot/cuenta/<clave>_<AAAAMMDD>.txt`).
> **El detalle y las constantes viven en §2duodecies punto 7.** La regla de oro que sigue valiendo:
> **los números NUNCA se le dicen al visitante** (ni los que lleva ni los que le quedan); cuando se le
> acaban, el chat se cierra y le ofrece el siguiente paso (cuenta gratis, o ⭐ Premium).

**Pedido textual del jefe (2026-09-13, 2.ª tanda de tiempos, HOY SIN EFECTO):** *«aumenta los tiempos del chatbot: no
registrado 3 minutos, registrado 6 minutos. No es necesario poner reloj ni contador de tiempo: solo
llega a cumplir el tiempo y 10 segundos antes informa que no tienes permitido hablar mucho tiempo con
extraños, que lo mejor es que te registres y sean amigos. Una vez registrado su tiempo vuelve a ser 6
minutos más, pero no se lo digas.»*

| Quién | Tiempo al día | Qué ve |
|---|---|---|
| Visitante sin cuenta | **3 minutos** | **Ningún reloj.** A los 10 s del final, el bot habla solo: *«a los extraños no les hablo mucho rato… regístrate gratis y seamos amigos 😉»*. Al cumplirse el tiempo, el chat **se cierra solo** con el mismo argumento y el botón de registro |
| Registrado (cuenta gratis) | **6 minutos** | **Ningún reloj.** Al cumplirse el tiempo se cierra y le ofrece **⭐ Hacerme Premium (S/ 30 al mes)** |
| Miembro ⭐ Premium | **sin límite** | Nunca se cierra; no se le crea ni cronómetro |
| Al registrarse | su reloj **vuelve a empezar** (6 minutos) | **No se le dice** nada del tiempo |

**⛔ REGLA DE ORO DEL TIEMPO: NUNCA SE DICEN LOS MINUTOS.** Ni al visitante (3), ni al registrado (6),
ni al Premium. Está en cuatro sitios que tienen que seguir de acuerdo: el **aviso de cierre**, el
**aviso de los últimos segundos**, las **respuestas locales** (`tiempo`, `precio`, `premium`) y el
**guion del modelo** (reglas 12, 12-bis y 12-ter: *«ni 1 minuto, ni 3, ni 6, ni media hora»*). En su
lugar: «te atiendo **un ratito**», «con cuenta hablas **bastante más tiempo**», «Premium **no tiene
límite**».

**Las reglas del reloj (importantes):**

1. **Manda el servidor, no el navegador.** Lo que el visitante ve... nada: **no hay contador**. El
   navegador solo programa dos cosas con el tiempo que le dice el servidor (`tiempo.restante`): el
   **aviso de los últimos 10 s** y el **cierre** al cumplirse el tiempo. Borrar el `localStorage`,
   recargar o abrir otro navegador **no devuelve el tiempo**.
2. **Arranca con el PRIMER MENSAJE**, no al abrir la ventana: leer el saludo y las preguntas
   frecuentes no gasta nada.
3. **Es por persona y por día** (fecha de **Lima**): al día siguiente vuelve a tener su tiempo.
4. **Se identifica por usuario o por IP:** con sesión, por su **id de usuario** (`u9`); sin sesión, por
   su **IP en hash** (`ip8245b…`). ⚠️ Efecto secundario aceptado: varias personas de la misma red
   comparten el ratito (es el precio de que no se pueda burlar).
5. **Cuando se cumple, el chat se cierra de verdad:** se apaga el cuadro de escribir (queda "Chat
   cerrado por hoy"), sale el motivo con el botón del siguiente paso y **a los 15 segundos la ventana
   se cierra sola** (`CHATBOT_CIERRE_SEG`). El botón flotante se pone gris (sin iconos de reloj).
6. **El candado de los 10 consejos 🔒 y el atajo de clima/noticias van antes:** esos no gastan tiempo
   (son avisos, no respuestas).
7. **El bot lo sabe:** el guion del sistema le dice su plan y cuántos segundos le quedan, así que
   nunca se contradice (y si va justo, contesta más corto).

**Dónde se cambian los tiempos** (todo en `includes/config_chatbot.php`, y se sube el archivo):

```php
if (!defined('CHATBOT_TIEMPO_ACTIVO'))  define('CHATBOT_TIEMPO_ACTIVO', true);   // false = sin reloj
if (!defined('CHATBOT_SEG_VISITANTE'))  define('CHATBOT_SEG_VISITANTE', 180);    // 3 minutos
if (!defined('CHATBOT_SEG_REGISTRADO')) define('CHATBOT_SEG_REGISTRADO', 360);   // 6 minutos
if (!defined('CHATBOT_SEG_PREMIUM'))    define('CHATBOT_SEG_PREMIUM', 0);        // 0 = SIN LÍMITE
if (!defined('CHATBOT_AVISO_SEG'))      define('CHATBOT_AVISO_SEG', 10);         // aviso 10 s antes
if (!defined('CHATBOT_CIERRE_SEG'))     define('CHATBOT_CIERRE_SEG', 15);        // cierre automático
if (!defined('CHATBOT_PREMIUM_PRECIO')) define('CHATBOT_PREMIUM_PRECIO', 'S/ 96 al mes');
```

> 🔴 **EL PRECIO DE PREMIUM CAMBIÓ A «S/ 96 al mes» (2026-09-21, orden del jefe).** El jefe dictó los
> **5 planes** del sitio para la página pública de precios (**`/nosotros#precios`**, la sección
> «Nosotros» del menú ☰: Gratis S/ 0 · Emprende S/ 20 · Vende Más S/ 50 · **Premium S/ 96** · Aliados
> S/ 150) y pidió **alinear el chat**: antes el bot decía «S/ 30 al mes» y había **dos precios** en el
> sitio. **⚠️ La trampa:** el `define` de `config_chatbot.php` es solo el valor de fábrica —
> **el que manda es el que está GUARDADO en la tabla `directorio_chatbot_ajustes`**, porque
> `chatbot_ajustes_aplicar()` corre **antes** y define la constante (ver §arriba). Cambiar solo el
> archivo **no cambia lo que dice el bot**: hay que **actualizar la fila `precio_premium`** (se hizo
> con una sonda de escritura, con simulacro primero, y se leyó de vuelta: `S/ 30 al mes` →
> `S/ 96 al mes`). Se cambiaron, además, el `'defecto'` de `includes/chatbot_ajustes.php` (para que el
> panel muestre el número bueno) y las **respuestas del KB** (`includes/chatbot_kb.php`): las de
> **`precio`** y **`premium`** —que se arman en `chatbot_faq_respuesta()`, **no** en el texto fijo de
> la entrada, que está tapado por el constructor— ahora **enlazan a `/nosotros#precios`**.

🔧 **Interruptor de pruebas `CHATBOT_PRUEBA_AVISO`** (en el mismo archivo, **debe quedar en `false`**):
en `true`, el aviso de los últimos segundos también sale a los usuarios registrados. Sirve para
comprobar el aviso en un navegador con sesión (el del jefe está logueado); en producción los
registrados no reciben ese aviso.

**⭐ Premium = el plan que YA existe en el sitio.** No hay dos premiums: se usa
`directorio_usuarios.plan` (`gratis` / `premium` con `plan_hasta`) a través de
**`reglas_plan_usuario()`**, que devuelve `plan` y `es_premium`. Para darle Premium a
alguien que pagó (**S/ 96 al mes** desde el 2026-09-21): **Súper Admin → 👥 Usuarios → su fila → Plan →
`premium`** (se puede poner **Indefinido** o **30 días**). Aplica al instante, sin tocar archivos.

> ⚠️ **La función se llamaba `reglas_tablones_usuario()` y se renombró el 2026-09-13** a
> **`reglas_plan_usuario()`** (y ya **no** devuelve ventana de mensajes ni topes de tiendas, que eran
> datos del módulo B2B ya retirado). La usan `panel.php` e `includes/chatbot.php`. Las constantes
> **`PLAN_GRATIS`**, **`PLAN_PREMIUM`** y **`SITE_WHATSAPP_PREMIUM`** **siguen existiendo**.

**⚠️ Lo que el bot promete de Premium (y lo que NO).** Hoy solo promete **el chat sin límite de
tiempo**, porque es lo único que se puede entregar de verdad. **NO promete los tablones B2B** (250
mensajes / 100 tiendas): ese módulo **se retiró del sitio el 2026-09-13** (ver el bloque 🗑️ de
arriba), así que ese beneficio **no volverá**: si algún día hay otro beneficio Premium, se añade la
frase en `CHATBOT_PREMIUM_BENEFICIOS` (`config_chatbot.php`) y se sube. **Regla:** al bot no se le pone
un beneficio que el sitio todavía no da.

**Cómo se prueba sin esperar los minutos de verdad:**

```powershell
python __chat_tiempo.py ver                      # últimas preguntas del registro, con su IP en hash
python __chat_tiempo.py listar                   # qué cronómetros hay en el servidor
python __chat_tiempo.py agotar ip<HASH>          # deja su cronómetro de hoy agotado (prueba el cierre)
python __chat_tiempo.py borrar  ip<HASH>         # le devuelve el tiempo (¡siempre al terminar!)
```

⚠️ **Borrar siempre el cronómetro de pruebas al terminar**: si no, esa IP se queda sin chat hasta
mañana. Y **no tocar los archivos `u<id>_*.json`** de usuarios reales salvo que sea para deshacer una
prueba propia (el 2026-09-13 se borró uno, `u9_*`, creado durante la prueba del reloj).

---

## §2ter. 📅 El contexto del día: fecha, hora, clima de Chimbote y NUESTRAS noticias

**Pedido textual del jefe (2026-09-13):** *«debes saludar informando la fecha, la hora y el clima
pronosticado para el día de hoy; y según la hora del chat debes decir si es un día soleado o de poco
sol. La ciudad es Chimbote, en el departamento de Áncash. El clima para todos es el mismo, así que si
lo obtienes la primera vez del día ya sirve para todas las demás conversaciones. También ofrécele otro
tipo de información como las 10 últimas noticias a nivel nacional. Eso también lo obtienes solo la
primera vez: lo grabas y sirve por 24 horas, es decir 1 día para todas las conversaciones.»*

> 📰 **CAMBIO DEL 2026-09-13 (el que manda hoy):** de aquel pedido, **las noticias ya no son
> nacionales ni de fuera**. El jefe ordenó después: *«las noticias del chatbot ahora se leen de
> dechimbote.com (no de páginas de afuera)… El enlace que se le da al visitante es SIEMPRE el campo
> "url" (nuestra página /noticia/<slug>). PROHIBIDO mandar al visitante al medio de afuera.»* → la
> lista nacional por RSS (Agencia Andina/RPP) **se retiró** y en su lugar van **nuestras noticias**
> (las del robot de `/noticias`). El clima sigue igual.

**Qué hace (archivo nuevo: `includes/chatbot_diario.php`):**

| Pieza | De dónde sale | Cada cuánto |
|---|---|---|
| Fecha y hora de Chimbote | Del propio servidor (PHP en `America/Lima`) | Siempre al instante, gratis |
| **Clima de hoy** | **Open-Meteo** (gratis, sin clave ni registro): `api.open-meteo.com`, Chimbote = `−9.0745, −78.5936` | **1 vez al día**, guardado 24 h |
| **Nuestras noticias** (Chimbote · Nuevo Chimbote · Santa) | **Nuestra tabla** `directorio_noticias` (lo que publica el robot en `/noticias`): `chatbot_diario_noticias_propias()` | Se lee **siempre de la base** (no de la caché) |
| El resto (saludo corto, 7 preguntas, consejos) | El guion del sitio | Instantáneo |

**Cómo se guarda:** `cache/chatbot/diario/<AAAAMMDD>.json` (carpeta bloqueada por web: **403**). La
primera conversación del día paga la consulta (1-2 s) y **todas las demás leen el archivo**: el clima
de Chimbote es el mismo para todos. Al cambiar el día de Lima, el archivo es otro.
⚠️ Si internet falla se graba un **marcador de fallo** con caducidad corta (`CHATBOT_DIARIO_FALLO_MIN`,
20 min) para que **no se intente en cada conversación** y nadie espere.

**El saludo** son **dos burbujas** que se ven al abrir el chat: la primera la pone `chatbot_saludo()`
(al instante, sin internet) y la segunda `chatbot_saludo_dia()`. **Así sale hoy de verdad** (comprobado por
HTTP el 2026-09-13):

```
Buenos días! 🥷 Te saluda **El ninja**.

🌤️ Hoy el cielo está nublado.
¿Tienes alguna pregunta para mí? 🥷
```

*(La primera línea de la segunda burbuja es el cielo del momento —«soleado», «nublado»…, y de noche en
pasado: «Hoy estuvo soleado»—. **La noticia NO va aquí**: ver el párrafo siguiente.)*

**📰 DÓNDE SALE LA NOTICIA: EN LA PREGUNTA 3 O 4 (orden del jefe, 2026-09-13).** El jefe pidió que el
saludo fuera un mensaje personalizado y que la noticia apareciera **cuando el visitante ya está
conversando**: *«en la pregunta 3 o 4 dirá: hoy "noticia X" es bueno estar informado»*. Se hace en
`chatbot_noticia_turno()` (`includes/chatbot_diario.php`), llamada desde **`api/chatbot.php`** —el único
sitio por el que pasan TODAS las respuestas (locales, de la IA y los avisos)—, que **añade una línea al
final** de la respuesta:

```
📰 Si te interesa, hoy salió esto: [Nuevo Chimbote evalúa riesgo](https://dechimbote.com/noticia/…).
```

*(📰 **Así se dice desde el 2026-09-14** — orden del jefe: *«no así como titulares con enlaces muy
llamativos o titulares muy largos: el título de la noticia en el chatbot no puede ser más de cuatro
palabras… no tratar de repetirlo cada rato, sino ser discreto para comentar noticias»*. Antes decía
«📰 **Hoy**: <titular de 15 palabras>. Es bueno estar informado»: eso ya no existe. Ver §2undecies.)*

| Detalle | Cómo funciona |
|---|---|
| ¿Cuándo? | En la **pregunta 3** (y si por lo que sea no salió, en la **4**). Se cuenta con los mensajes de **usuario** que manda el navegador + la pregunta actual |
| ¿Se repite? | **No.** Si el enlace de esa noticia ya está en el historial, la función devuelve `''` y **no se añade nada**; y desde el 2026-09-14 **tampoco si el tema ya salió** (si en la conversación ya viaja un `/noticia/` o el visitante ya pidió noticias, no se insiste: *«no tratar de repetirlo cada rato»*) |
| ¿Cómo se ve el título? | **Cortado a 4 palabras** (`chatbot_titular_corto()`, §2undecies) y **sin negritas ni signos de admiración**: es una etiqueta discreta, no un titular de periódico |
| ¿De quién es? | **Nuestra** (`chatbot_diario_noticia_propia()`): el titular es el enlace a `/noticia/<slug>`. **Nunca** un medio de afuera |
| ¿Cuesta? | **Nada**: es una consulta a nuestra base y se pega al texto. No gasta tokens ni cuota |
| ¿Y si preguntan por noticias antes? | Se contestan igual (con la lista de las nuestras, §9 de `GUIA_NOTICIAS_DIARIAS.md`); el añadido del turno 3-4 no se duplica porque el enlace ya viaja en el historial |
| ¿Y el modelo? | La **regla 16** del prompt le prohíbe adelantarla en las dos primeras respuestas y repetirla si ya salió |

**Lo de «soleado o de poco sol» según la hora:** el clima se traduce de código WMO a
**soleado · poco sol · nublado · lluvioso · neblina**, y la frase **cambia de día a noche** usando el
amanecer y el atardecer REALES de hoy que da Open-Meteo:
- De día: «es un día soleado (cielo limpio)» / «es un día de poco sol (hay nubes y claros)».
- De noche: «de noche el cielo está despejado (el día fue soleado)» — porque a las 3 de la mañana no
  se puede decir «es un día soleado».

**El cielo y las noticias NO se le preguntan al modelo:** se contestan con el archivo del día
(`"fuente":"dia"`), así el cielo y **los titulares con sus enlaces** salen **exactos y sin gastar
saldo**. El modelo los recibe igual en su guion (bloque «EL DÍA DE HOY») por si pregunta de otra
forma («¿llevo casaca?», «¿algo nuevo en Cascajal?»).

**Si el visitante pide MÁS noticias** (orden del jefe: *«si el visitante pide más, se le manda a la
sección»*), la respuesta es la lista de **nuestras** noticias con el **titular como enlace**
(`/noticia/<slug>`) y al final la sección `https://dechimbote.com/noticias`. Si todavía no hay ninguna
publicada, solo se manda la sección: **nunca** una lista de un medio de afuera.

**Ajustes** (`includes/config_chatbot.php`): `CHATBOT_DIARIO_HORAS` (24),
`CHATBOT_DIARIO_FALLO_MIN` (20), `CHATBOT_CLIMA_CIUDAD/LAT/LNG`, `CHATBOT_CLIMA_TIMEOUT`,
`CHATBOT_NOTICIAS_TIMEOUT` y `CHATBOT_NOTICIAS_CHIMBOTE_URL/_CLAVES` (la búsqueda externa que queda
**solo como último recurso del saludo**, para cuando el sitio no tenga nada publicado; la palabra clave
es **CHIMBOTE**, y valen «nuevo chimbote» y «santa»). ⛔ **`CHATBOT_NOTICIAS_FUENTES` (Andina/RPP) ya no
existe**: se borró con la lista nacional.

**Cómo probarlo (no gasta tokens):**

```powershell
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_dia.php   # clima, noticias, caché 24 h, saludo y guion
# y para forzar que se vuelva a pedir (borra el archivo del día):
python -c "import json,ftplib;cfg=json.load(open(r'C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json',encoding='utf-8'));f=ftplib.FTP();f.encoding='utf-8';f.connect(cfg['host'],21,timeout=60);f.login(cfg['user'],cfg['password']);print(f.delete('/cache/chatbot/diario/'+__import__('datetime').datetime.utcnow().strftime('%Y%m%d')+'.json'));f.quit()"
```

## §2quater. 🗂️ Los tablones (RETIRADOS) y el chat en su lugar

Orden del jefe (2026-09-13): *«por ahora reemplaza el lugar de tablones, es decir oculta tablones y
deja el chatbot»*, y **ese mismo día, más tarde, ordenó quitar del sitio toda referencia a los
tablones**. Se hizo así:

- **`panel.php`**: se quitó la tarjeta rosa «Tablones nuevos» y la sección «🗂️ Mis tablones». En su
  lugar está la **tarjeta «🥷 EL NINJA · Pregúntame aquí →»** y la sección **«🥷 El ninja, tu
  asistente»**, con un botón que **abre el chat** (`window.NINJA_ABRIR()`, que expone
  `assets/js/chatbot.js`).
- Ya **no** se consultan `no_leidos_tablones_usuario()` ni `listar_tablones_usuario()` en el panel:
  esas funciones **se borraron de `includes/helpers.php`** con el resto del módulo.
- En el **menú hamburguesa** (`includes/header.php`) se quitó la opción **«Tablón comunitario»**, y en
  la **ficha de negocio** (`negocio.php`) se quitaron el modal «Abrir conversación» y su JS (estaba
  apagado con `$tablon_ctx = null` desde el 2026-09-10) junto con los 3 huecos del botón.
- **`superadmin.php`**: se quitaron la pestaña **🗂️ Tablones**, el contador «Tablones B2B» y la acción
  `tablon_eliminar`; también las llamadas a `borrar_tablones_de_negocio()` en `superadmin.php` y
  `editatiendas.php`. **Ya no queda ninguna referencia a tablones en el Súper Admin** (era el pendiente
  de la sesión anterior de esta misma guía: **RESUELTO**).

> 🗑️ **RETIRADO (2026-09-13, orden del jefe):** el módulo completo (chat comunitario + mensajería
> entre tiendas) se quitó del sitio y sus archivos ya **no** están en `deploy` — detalle en el bloque
> 🗑️ del §0.

---

## §2quinquies. 👁️ MIRAR: fotos, capturas y compartir pantalla (pedido del jefe, 2026-09-13)

> 🔴 **CÓMO ES HOY (2026-09-20, orden posterior del jefe, §2duodecies punto 14):** el 📷 del guía abre
> una **ventana modal con DOS botones en una sola fila** —**tomar foto con la cámara** (blanco con letra
> negra) y **subir de la galería** (negro con letra blanca)— y **nada más**. 🗄️ **«Compartir mi pantalla»
> se retiró ese día** (el jefe pidió dos opciones, no tres). Lo que sigue valiendo de este apartado: que el
> bot **mira** las fotos con el modelo de visión, que **comprime** antes de mandarlas y que **no se
> guardan** en el servidor. Donde abajo se lea «compartir pantalla», es **historia archivada**.

**Pedido textual del jefe:** *«si me conviene o no poder ver imágenes: ejemplo "mira mi pantalla" →
ocultará el chat y tomará una foto… si eso es posible perfecto; caso contrario copiará la url donde
está el usuario y hará captura de pantalla y dirá lo que ve o aconsejará según la pregunta.»*

**Respuesta corta: SÍ es posible, y ya está funcionando con la MISMA clave de DeepSeek.** No hay que
contratar nada más ni usar otro proveedor, y **NO cuesta más que el modo texto** (ver el cuadro de
abajo). El 2026-08-21 DeepSeek abrió su modelo multimodal
**`deepseek-v4-flash-vision-exp`** ([aviso oficial](https://api-docs.deepseek.com/zh-cn/news/news260821/)):
se llama igual que el de texto (mismo `chat/completions`, misma clave) pero acepta imágenes, y cuesta
**exactamente lo mismo que V4-Flash**; cada imagen se reescala sola a ~800×800 px y añade como mucho
**384 tokens** (medidos ~600 en nuestro afiche de prueba: unos **US$ 0,00013**).
👉 Por eso **NO se hizo el plan B** (el de la captura por URL): además de que una captura hecha por un
servicio externo **no vería la sesión del usuario** (en su panel saldría la pantalla de login), con la
visión de verdad el visitante manda **lo que él está viendo**, que es justo lo que se pidió.

**Cómo lo usa el visitante (todo en el chat):**
1. Toca el botón **📷** (al lado del cuadro de escribir) y elige:
   - **📷 Tomar foto / elegir foto** → se abre la cámara del celular o la galería/capturas.
   - **🖥️ Compartir mi pantalla** → en computadora el navegador le deja elegir la pestaña y el bot
     ve la pantalla tal como la tiene (esta opción **solo se muestra si el navegador la soporta**).
2. Ve una **vista previa** con el peso de la imagen y **escribe qué quiere** («¿por qué no me sale el
   botón de guardar?») o toca ➤ directamente.
3. El bot **la mira** y le contesta con lo que ve + el consejo.

**Y si el visitante pide «mira mi pantalla» sin adjuntar nada**, el chat le ofrece **dos botones**
dentro de la conversación («📷 Tomar foto / elegir foto» y «🖥️ Compartir mi pantalla») en vez de
dejarlo sin salida. Se dispara con frases como *mira mi pantalla / no me sale / no funciona / no
encuentro / me sale error* (`pideMirar()` en `assets/js/chatbot.js`).

**Cómo está hecho (4 piezas):**
| Pieza | Qué hace |
|---|---|
| `assets/js/chatbot.js` | Captura (cámara/galería/screen share), **comprime a JPEG** (lado máximo 1280 px, calidad 0,72; si pesa mucho baja a 0,5), muestra la vista previa y la manda en el campo `imagen` (data URL base64). |
| `api/chatbot.php` | Recibe `imagen`, corta si excede el tope y la pasa al motor. |
| `includes/chatbot.php` | `chatbot_imagen_valida()` (solo `data:image/(jpeg\|png\|gif\|webp)`; ≤ `CHATBOT_IMG_MAX_BYTES`), cupo diario (`CHATBOT_IMG_DIA`), y arma el mensaje de DeepSeek **en bloques** (`text` + `image_url`) usando **`CHATBOT_MODELO_VISION`**. |
| `includes/config_chatbot.php` | `CHATBOT_IMAGENES_ACTIVO`, `CHATBOT_IMG_MAX_BYTES` (1,6 MB), `CHATBOT_IMG_DIA` (20/día), `CHATBOT_MODELO_VISION`, `CHATBOT_IMG_MAX_TOKENS`. |

**Reglas que se respetan (importantes):**
- **Las imágenes NO se guardan**: ni en disco, ni en la BD, ni en el registro. Se mandan a DeepSeek
  (el único sitio donde viven unos segundos: 48 MiB es el máximo de cuerpo que acepta su API) y se
  tiran. En el registro del chat solo queda **el peso en KB** (`"img": 224`), nunca la imagen.
  Esto cumple la **regla inviolable de capturas** del proyecto: *ninguna captura de pantalla se
  publica ni se guarda en el hosting*.
- El bot tiene **regla 14** en su guion: puede ver imágenes, **nunca dice que no puede**, y **no
  repite datos personales** que aparezcan en la imagen (teléfonos, correos, DNI, tarjetas) si no se
  lo piden.
- Si la imagen es muy pesada, de otro formato o el cupo del día se agotó, el bot lo explica con
  simpatía y ofrece seguir con palabras.

**Cómo probarlo:**
```powershell
# 1) Que la clave acepte el modelo con visión (y qué modelos hay)
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_modelos.php
# 2) Visión de verdad: le manda una imagen al modelo y comprueba que la describe
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_vision.php
# 3) Contra el sitio en producción (mensajes del minuto + una imagen real)
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_prod.php
```

**Prueba real hecha el 2026-09-13 (en producción):** se le mandó un afiche de compra de ropa y el bot
contestó: *«Veo un afiche bien llamativo 📷: dice "¿Mucha ropa en tu closet? ¡Compramos tu ropa de
mujer juvenil!"… Abajo aparece un número de contacto, pero no lo repito para cuidar ese dato»* + dos
consejos y cómo publicar su tienda. El registro quedó con `"img":224` y `"modelo":"vision"`.

**¿Sale más caro el modelo con visión? NO. Medido el 2026-09-13 con la clave real:**

| Qué se midió | Resultado |
|---|---|
| ¿Es otro modelo (y otra tarifa)? | **No**: `deepseek-chat` y `deepseek-v4-flash-vision-exp` **devuelven el mismo `modelo=deepseek-flash`** y la tabla oficial les pone **el mismo precio** por token |
| Lo que **añade una imagen** | **~600 tokens de entrada** medidos (el tope oficial es **384**; la imagen se reescala sola a ~800×800 px, así que una foto de 2000×2000 y una de 5000×5000 cuestan **lo mismo**) |
| Eso, en dinero | **≈ US$ 0,00013** por imagen (fuera de punta) = **+9 %** sobre una pregunta normal (US$ 0,0014) |
| En el chat real | La pregunta con imagen costó **US$ 0,0016** frente a **US$ 0,0014** de una de texto. La diferencia **no fue por la visión** (el modelo escribió más: 666 tokens de salida frente a ~170) |
| Con US$ 5 | **1 000 imágenes = ~US$ 0,13 extra.** Nada |

👉 **La visión no es un modelo "premium": es el mismo modelo con ojos.** Lo que de verdad mueve el
gasto es **cuánto escribe** el bot (la salida cuesta $0,66/M) y **la hora** (en punta, el doble).
Mientras no manden fotos, el gasto es idéntico al de solo texto.

---

## §2sexies. 👀 LO QUE HIZO EL USUARIO REGISTRADO + el TONO (pedido del jefe, 2026-09-13)

**Pedido textual:** *«si el usuario está registrado puedes decirle las últimas páginas que visitó, o
qué productos le interesaron, o si pidió precio de algún producto… siempre hablando con un tono
coqueto y directo como gran amigo»*.

### De dónde sale cada dato (todo real; nada inventado)

| Qué sabe | De dónde |
|---|---|
| **Tiendas que visitó** | Tabla **`directorio_vistas`** (`usuario_id`, `negocio_id`, `fecha`): las 5 últimas distintas, con el enlace `/neg/<slug>` |
| **Productos que miró** | La misma tabla con `producto_id` → `directorio_servicios` (título, precio, unidad) y su tienda |
| **Lo que buscó** | Tabla **`directorio_historial_busqueda`** (`usuario_id`, `termino`, `fecha`): los 5 últimos términos |
| **A quién le pidió precio** | Archivo **`cache/chatbot/actividad/u<ID>.jsonl`**, que escribe **`api/lead.php`** cuando el usuario toca el botón de WhatsApp (el sitio **no** guardaba esa acción en la BD: solo avisaba al Telegram). Se anota **después de la redirección** para no hacerle esperar |
| **Lo que marcó con ❤️** | El carrito «Me interesa» vive en el navegador (`localStorage: cz_carrito_v1`): **el widget lo manda** en cada pregunta y **solo se usa si hay sesión** |

### Cómo se usa (3 momentos)

1. **Al abrir el chat** (solo con sesión): si hay actividad, aparece una burbuja corta y con gracia
   («👀 Por cierto, Jimmy, no me digas que te olvidaste: le diste una miradita a **X**…»). Es una
   línea, no una lista: la lista sale si la pide.
2. **Si pregunta «¿qué he visto?» / «¿qué me interesó?» / «¿a quién le pedí precio?»**: se contesta
   **localmente** (`"fuente":"actividad"`, sin gastar saldo) con la lista completa y enlaces. Si la
   pregunta además pide **una recomendación** («recomiéndame algo de lo que vi»), se le pasa a la IA,
   que usa los mismos datos y sabe aconsejar.
3. **En cualquier conversación**: la IA recibe el bloque **«LO QUE ESTE USUARIO HIZO EN EL SITIO»**
   y lo usa cuando viene al caso (sin repetirlo en cada respuesta).

🔒 **Solo para usuarios con sesión.** A un visitante se le dice, con simpatía, que eso se lo contamos
cuando tenga cuenta (con el enlace a `/registro`) — **no se le enseña historial de nadie**.
**Nunca dice «según mis registros» ni «tengo tus datos»**: eso suena a vigilancia y está prohibido en
su guion (regla del bloque de actividad).

### 🥷 El tono: coqueto y directo, como un gran amigo

Está en el bloque **CÓMO HABLAS** del guion (`chatbot_prompt_sistema()`): español de Perú, cercano,
**con chispa y un piropo ligero de amigo** («no me digas que te olvidaste de mí 😏», «te tengo fichado
👀»), uno que otro emoji, **la respuesta útil primero** y una pregunta corta que empuje a la acción
(«¿te lo consigo?», «¿le damos?»). Límites escritos: **nada vulgar, nada sexual, nada sobre el cuerpo
ni el aspecto**, nada de piropos a menores, **si el usuario está molesto o pide formalidad se baja el
tono al instante**, y **nunca promete descuentos, plazos ni favores que el sitio no dé**.

**Probado en el navegador con la sesión del jefe (2026-09-13):**
- Abrió el chat y salió la burbuja del día + la de actividad («no me digas que te olvidaste…»).
- Preguntó «¿qué he visto?» → lista real: *Ferretería "Victor Manuel* Carranza barrio*, *Distribuidora
  de Ladrillos*… y sus búsquedas: «Polos», «Desayunos», «pollo a la brasa», «auto», «Hospital…» ✅
- Preguntó «recomiéndame algo según lo que he visto» → la IA contestó *«Con lo que andas mirando,
  Jimmy, te veo dos caminos 👀…»* con los enlaces de SUS tiendas ✅

### 🚪 La ✕ y la papelera: QUITADAS… 🔴 y la ✕ VOLVIÓ (2026-09-20)

Orden del jefe (2026-09-13): *«el icono de la X cerrar y la cesta de borrar… bórralos, porque si el usuario
quiere salir bastará con dar clic a la izquierda en una parte libre que no esté el chatbot»*. Se quitaron
los dos botones de la cabecera (queda solo el 💡 de las preguntas frecuentes). Para salir: **clic en el
fondo oscurecido** (o la tecla `Escape`). La conversación se sigue recordando 12 h en el navegador.

> ⚠️ **ORDEN POSTERIOR (2026-09-20, la que manda hoy):** *«al ladito de la escoba agrégale una ✕ para poder
> cerrar la guía»* → la **✕ volvió** a la cabecera (`#cbotCerrar`, `.cbot-head__btn--x`, justo después de
> la 🧹). El fondo oscurecido y `Escape` **siguen cerrando también**, y la papelera sigue sin volver (para
> eso está la 🧹, que limpia y vuelve a empezar). Detalle: **§2duodecies, apartado 13**.

### 🔤 La fuente del chat: más grande que la del sitio

El jefe notó que el chat se veía más chico que la página. El sitio usa `body { font-size: 16px }`
(`assets/css/base.css`), y el chat pasó a **17 px** en los mensajes y en el cuadro de escribir (y a
16,5 px el botón flotante en escritorio). **Medido en el navegador: chat 17 px vs página 16 px** ✅.
Si algún día se quiere más grande, es `.cbot-msg` y `.cbot-input` en `includes/chatbot_widget.php`.

---

## §2septies. 🛠️ El panel de configuración (Súper Admin → 🥷 Ninja (chat))

**Pedido del jefe (2026-09-13):** *«en el título solo escribe Ninja, ya no pongas "asistente de
Chimbote" ni otros mensajes que solo roban espacio como "te respondo al instante"; y pon un panel de
configuración enlazado a mi panel de Superadmin.»*

**El título y la limpieza del widget:** la cabecera del chat muestra **solo el título** (`CHATBOT_TITULO`,
por defecto **Ninja**) con su avatar; **se quitó** la línea «En línea · te respondo al instante» y el
punto verde. El botón flotante dice **🥷 Ninja** y el saludo quedó corto: *«Buenos días! 🥷 Soy **El
ninja**. ¿En qué te ayudo?»* (el nombre largo se usa al hablar; el título visible es corto).

**El panel** está en **`superadmin.php?seccion=chatbot`** (menú **🥷 Ninja (chat)**) y lo pinta
`includes/vista_chatbot_admin.php`. Todo se guarda en la tabla **`directorio_chatbot_ajustes`**
(clave/valor, auto-instalación defensiva cuando entra un admin), y lo aplica
**`includes/chatbot_ajustes.php`**.

| Qué se puede cambiar sin tocar archivos | Clave |
|---|---|
| Encender/apagar el bot (desaparece de todo el sitio, sin borrar nada) | `activo` |
| **Título de la ventana**, nombre del bot, emoji | `titulo`, `nombre`, `emoji` |
| **Tiempos**: visitante, registrado, Premium (0 = sin límite), aviso antes del final, cierre automático | `seg_visitante`, `seg_registrado`, `seg_premium`, `aviso_seg`, `cierre_seg` |
| Precio de Premium, fotos (on/off), fotos por persona y día, tope de mensajes al día, días de registro | `precio_premium`, `imagenes`, `img_dia`, `limite_global_dia`, `log_dias` |
| ⏳ **Cómo se siente la espera**: las frases del «pensando» (separadas por «·»), lo que dura cada una y el piso de espera en milisegundos | `pensando_frases`, `pensando_ms`, `espera_min_ms` |
| Modelo de texto y **la clave de DeepSeek** (se guarda solo si escribes una nueva; nunca se muestra) | `modelo`, `clave` |

**Lo que además se ve en ese panel (sin configurar nada):**
- Estado (encendido/apagado), **preguntas de hoy**, cuántas contestó la IA, **fotos miradas hoy** y
  **gasto estimado de hoy** en dólares (con los precios oficiales).
- Un resumen en palabras de cómo está funcionando (qué tiempo tiene cada tipo de usuario).
- Si la **clave** está puesta (con sus últimos 4 caracteres) y qué modelos se usan.
- **Las últimas 12 preguntas** con quién las hizo (visitante/registrado), si traían foto, cómo se
  contestaron (IA / guion / datos del día / tiempo agotado / tope) y los tokens que costaron.
- **Acciones rápidas:** *🌤️ Volver a pedir el clima y las noticias* (borra el archivo del día y lo
  vuelve a consultar) y *⏱️ Devolver el tiempo de hoy a todos* (borra los cronómetros, útil si
  alguien se quedó sin chat por una prueba).

**⚠️ EL ORDEN DE CARGA ES SAGRADO:** `chatbot_ajustes.php` (1) lee la tabla, (2) define las constantes
y (3) **recién entonces** carga `config_chatbot.php`. Por eso **todos** los archivos del chat incluyen
`chatbot_ajustes.php` y **no** `config_chatbot.php` a secas: si se cargara primero el de defectos,
los `define` con `if (!defined(...))` dejarían los valores del panel sin efecto.

**Probado en vivo (2026-09-13, con la sesión de admin del jefe):** se cambió `aviso_seg` de 10 a 12
en el panel → el `GET /api/chatbot.php` empezó a devolver **12** ✅ → se devolvió a 10 desde el panel
y volvió a **10** ✅. El botón de refrescar clima/noticias reescribió el archivo del día (comprobado
por FTP: `guardado 01:51:34`, clima 20,1 °C, 10 noticias — *hoy esa lista son las nuestras, no las de
Andina: ver §2ter*). La tabla de últimas preguntas mostró 12
filas reales.

---

## §2octies. 🔗 Los enlaces con nombre · 🪟 90 % × 85 % · 🔤 fuente 18 px · 📋 opciones apiladas

**Pedido textual del jefe (2026-09-13, 2.ª tanda):** *«hazlo un poco más grande de ancho, que llene
hasta el 90 %, y de largo el 85 %. La fuente es lo más pequeña… y los links, cuando presentes, solo
preséntalos con un texto en color y subrayado: no escribas el link completo con http ni con www. Por
ejemplo, si quieres que entren a la sección de anuncios, el mensaje solamente debería decir "visita la
sección de anuncios"… así nada más la palabra "anuncios" se vería en otro color y subrayado para que
sepan que se trata de un link. Creo que dar la hora ya no es necesario, sí saludar de acuerdo a la
hora… luego automáticamente haz desaparecer botones con opciones; las opciones las haces aparecer
apiladas una encima de otra; te dejo a tu criterio qué opciones podrías mostrar apiladas.»*

| Qué | Cómo quedó | Dónde |
|---|---|---|
| **Tamaño** | ⚠️ **Histórico de aquel día.** Hoy (2026-09-20) manda **`85vw` × `85vh` centrada** (`top:7.5vh; left:7.5vw`; `85dvh` donde el navegador lo entiende) → **§2duodecies, apartado 13** | `includes/chatbot_widget.php` (`.cbot-panel`) |
| **Fuente** | **18 px** en mensajes y cuadro de escribir (el sitio usa 16 px), 19 px el título, 17 px las opciones; los mensajes se quedan con un ancho máximo de 760 px para que se lean bien en una ventana tan ancha | `.cbot-msg`, `.cbot-input` |
| **Enlaces** | **Nunca** se ve una dirección: se convierten en enlaces con nombre, **en azul (`#1d4ed8`) y subrayados**. Ejemplo real verificado: `[la sección de anuncios](https://dechimbote.com/empleos)` se ve como **«la sección de anuncios»** subrayado | `chatbot_enlazar()` (PHP) + `etiquetaEnlace()` (JS) |
| **Opciones apiladas** | 12 opciones apiladas, y **en escritorio (≥900 px) en DOS COLUMNAS** (orden del jefe, 2026-09-14); en celular siguen en una sola columna. Las opciones **se van solas**: al primer mensaje y a los 20 segundos. Vuelven con el botón **💡** | `.cbot-chips` + `mostrarChips()` |
| **🧹 Limpiar el chat** | Deja la ventana **COMPLETAMENTE VACÍA**: ni la conversación, ni el saludo, ni las opciones apiladas (`accion:'limpiar'` → `limpiarChat()` + la bandera `vacio`, §2undecies) | `limpiarChat()` |
| **Saludo** | Por franjas horarias, sin la hora exacta | `chatbot_saludo_del_dia()` |
| **El cielo** | «el cielo está soleado / nublado / medio nublado»; de noche en pasado («hoy estuvo soleado»); **sin «en Chimbote»** y **sin temperaturas** | `chatbot_clima_resumen()` / `chatbot_clima_linea()` |
| **La noticia** | **UNA**: sale **en la pregunta 3 o 4**, en una línea discreta («📰 Si te interesa, hoy salió esto: <título de 4 palabras>»), con **el título corto como enlace** a `/noticia/<slug>`; **ya no** va en el saludo y **ya no** se ofrecen «10 noticias» | `chatbot_noticia_turno()` + `chatbot_diario_noticia_propia()` |

**¿De dónde sale la noticia de Chimbote?** 🔀 **ORDEN NUEVO (2026-09-13, con el módulo de noticias
`GUIA_NOTICIAS_DIARIAS.md`):** primero se busca **NUESTRA noticia** (`chatbot_diario_noticia_propia()`,
que lee la primera de `chatbot_diario_noticias_propias()`: las que publicó el robot esa mañana en
`/noticias`) y **el enlace lleva a `/noticia/<slug>`, aquí dentro del sitio** — que es lo que pidió el
jefe: *«que el bot inicie hablando de una noticia que el usuario vería dentro de la web»*. Si todavía no
hay nada publicado (o lo publicado tiene más de 2 días), se usa como **respaldo** la búsqueda de Google
Noticias por «chimbote» (RSS gratis, sin clave:
`news.google.com/rss/search?q=chimbote&hl=es-419&gl=PE&ceid=PE:es-419`), que hoy traía **51 titulares**;
se elige el primero que mencione Chimbote/Áncash/Santa/Coishco… y se le separa la fuente del titular
(«… - Radio RSD» → fuente *Radio RSD*). **De ese respaldo no se enlaza nada** (el modelo recibe la orden
de contar solo el titular): el enlace del saludo lo pone el sitio.
⛔ **LO QUE SE RETIRÓ (2026-09-13, misma orden):** la **lista de noticias nacionales** (Agencia
Andina/RPP por RSS) que el chat ofrecía con **sus enlaces de afuera** — tanto en la respuesta local
(«dame las noticias de hoy» → `chatbot_noticias_texto()`) como en el guion del modelo. Hoy la lista la
arma **`chatbot_noticias_propias_texto()`** con **nuestros** titulares y **nuestros** enlaces, y al
final la sección `/noticias`. Funciones retiradas: `chatbot_diario_noticias()`,
`chatbot_noticias_texto()` y la constante `CHATBOT_NOTICIAS_FUENTES`. **No volver a ponerlas.**
⚠️ **Cuando la noticia es NUESTRA no se cita ningún medio** (`'propia' => true`): el titular es el enlace
y no lleva «(Diario de Chimbote)» al lado. Y **la caché del día no la puede dejar vieja**: el archivo del
día se graba una sola vez, así que al leerlo de la caché **se refrescan siempre la noticia propia y la
lista** (es una consulta a la base, no gasta internet ni tokens).

**EL MAPA DE ETIQUETAS** (el corazón de «no escribas el link completo»), en `chatbot_enlazar()` y en su
gemelo de JS: `/registro` → «crear tu cuenta gratis» · `/login.php` → «entrar a tu cuenta» · `/perfil` →
«tu panel» · `/productos.php` → «tus productos» · `/empleos` → «la sección de anuncios» · **`/noticias`
→ «las noticias de hoy» y `/noticia/<slug>` → **el título CORTO de la noticia (4 palabras)**, leído de
la tabla `directorio_noticias`** · `/buscar.php` →
«el buscador» · `/caminante` → «publicar sin cuenta» · `/reclamar` → «reclamar tu negocio» ·
`/neg/<slug>` → «ver la tienda» · `/empleo/<slug>` → «ver el anuncio» · `wa.me` → «escribirle al
administrador». **Si algún día se añade una página nueva, se añade aquí (y en el JS)** — así el bot puede
enlazarla sin que se vea la dirección. ⚠️ Al cambiar `assets/js/chatbot.js` hay que **subir el número de
versión** (`includes/chatbot_widget.php`, `'version'`), o los navegadores siguen con el archivo viejo en
caché (el 2026-09-13 se subió de 13 a 14 por las páginas de noticias y **el 2026-09-14 a 16** por las
noticias discretas, las opciones en dos columnas y el «Limpiar» en blanco).

⚠️ **El etiquetador se aplica a TODO lo que sale del chat** (respuestas locales, las de la IA y los
avisos) en `api/chatbot.php`, y el JS hace de red de seguridad: por eso **también** se ven bien los
mensajes viejos que quedaron guardados en el navegador del visitante con la dirección a la vista.

---

## §2nonies. ⚡ RESPUESTAS RÁPIDAS (25 palabras), SIN JERGA Y CON LA UBICACIÓN DENTRO DEL CHAT

> **Pedido del jefe, 2026-09-13** (él lo escribió como *«antes de empezar dame la lógica»*, la aprobó ese
> mismo día y aquí está hecho). **Documento de la lógica:** `LOGICA_CHATBOT_RESPUESTAS_RAPIDAS.md`.

### a) Cómo habla ahora

| Regla | En una frase |
|---|---|
| **25 palabras** | Respuestas de **una o dos líneas**; 200 queda solo como techo de emergencia (regla 12-quater del prompt) |
| **Directo, sin guías** | **Primero la respuesta**, en seco; **nada de pasos ni tutoriales** salvo que el visitante los pida («¿cómo lo hago paso a paso?»). Si hace falta más, se **ofrece** en una pregunta (regla 12-quater-bis, §2undecies e) |
| **Una idea y UNA pregunta** | Nunca dos preguntas juntas; siempre termina en un paso concreto |
| **Cero jerga** | Prohibido «panel», «dashboard», «área de banner», «estadística», «catálogo», «CMS» y **cualquier ruta** (`/registro`, `productos.php`). Se dice lo que la persona hace o lo que gana: «donde administras tu tienda», «los espacios de publicidad de la portada», «cuánta gente vio tu tienda» |
| **El nombre del enlace es el de la cosa** | Y para publicar el jefe aprobó el texto **«Publicar mi negocio»** |

⚠️ El mapa de etiquetas cambió: **`/perfil` y `/panel.php` ya no se llaman «tu panel»** sino
**«donde administras tu tienda»** (en PHP y en el JS, los dos).

### b) Las respuestas aprobadas (texto exacto)

```
«¿Cómo me registro?»
  Es muy rápido: con tu cuenta de Google, o con tu correo. También puedes crearla con el asistente,
  que te va preguntando.
  [Crear mi cuenta gratis](https://dechimbote.com/registro)

«Quiero publicar mi negocio / mi tienda»
  Puedes publicarla tú mismo, sin cuenta y con la cámara del celular.
  [Publicar mi negocio](https://dechimbote.com/caminante)
```

⛔ **NUNCA se dice que la cuenta se puede crear con WhatsApp.** El jefe aclaró que cuando lo mencionó se
refería **al registro con el asistente**: las formas reales son **Google**, **correo** y el **asistente**
(comprobado en `/registro` el 2026-09-13).

### c) 🔎 La búsqueda: resultados personalizados (el ejemplo del «pollo» del jefe)

```
VISITANTE: «pollo»
BOT:  Sí, estos son los resultados de pollo en la ciudad:
      - [Pollería El Rústico](…) — Restaurantes
      - [K'umara Pollería](…) — Restaurantes
      - [Pollería Brasas & Leña](…) — Restaurantes
      + hasta 2 productos con su precio
      ¿Te muestro las más cercanas a ti? Necesitaré que actives tu ubicación. 📍
      [📍 Ver las más cercanas a mí]      ← la opción apilada (accion:'geo')

VISITANTE: (toca la opción y autoriza la ubicación)
BOT:  Estas son las más cercanas a ti:
      - [POLLOS ALFA MAS](…) — 📍 a 196 m · Restaurantes
      - [DELCÁ'S Pollería & Parrilladas](…) — 📍 a 201 m · Restaurantes
      - [Pollería Kikiriki](…) — 📍 a 286 m · Restaurantes
      (y ya NO le vuelve a pedir la ubicación)
```

**Cómo está hecho (y por qué así):**

1. `chatbot_buscar($termino, $limite, $lat, $lng)` (`includes/chatbot_buscar.php`): con coordenadas añade
   `ST_Distance_Sphere(POINT(n.lng,n.lat), POINT(?,?))` y **ordena por distancia** (sin coordenadas, como
   siempre: destacados y más vistos). Se reutiliza el mismo emparejador de palabras, así que «pollo»
   sigue encontrando las pollerías.
   ⚠️ Esos dos `?` van **en el SELECT**, así que sus valores van **primero** en los parámetros:
   `POINT(lng, lat)`.
   🛟 **Red de seguridad:** si con la ubicación no sale ninguna tienda (casi ninguna ficha tiene GPS
   guardado), se repite **sin** ubicación, se muestran igual y el texto lo dice con naturalidad.
2. `chatbot_busqueda_texto()`: el texto nuevo — «Sí, estos son los resultados de <rubro o término> en la
   ciudad:» / «Estas son las más cercanas a ti:» — con la **distancia** por línea (`distancia_txt()`:
   «a 850 m», «a 3,5 km») y **una sola pregunta** al final (la de la ubicación, y solo si no la ha dado).
3. `chatbot_accion_cerca()` (`includes/chatbot.php`): pone **primero** la opción
   `['texto' => '📍 Ver las más cercanas a mí', 'accion' => 'geo', 'q' => <el término>]`. No se ofrece si
   ya nos dio la ubicación (sería un bucle).
4. `assets/js/chatbot.js` → `pedirUbicacion(termino)`: pide el GPS con la misma pregunta que ya había
   hecho, pinta «📍 Ver las más cercanas a mí» como mensaje del visitante y **repite la búsqueda con
   `lat`/`lng`**. Si el visitante no autoriza, lo dice con naturalidad y sigue en el chat (**no** lo deja
   sin salida).
5. `api/chatbot.php`: recibe `lat`/`lng`, **valida el rango** (−90..90 y −180..180) y los pasa a
   `chatbot_responder()`; si no son válidos se ignoran (nunca se confía en el navegador).

### c-bis) 🏷️ «¿Quién vende clavos?» — LAS FRASES DE UNIÓN DEL RUBRO (2026-09-13, noche)

El jefe preguntó en el chat *«puedes buscar quién vende clavos acá en Chimbote»* y el bot le contestó
que **no había resultados** (en el registro del chat esa pregunta quedó como `fuente: ia`, o sea que el
buscador local devolvió **cero** y la pregunta se la pasó a la IA, que no tiene los datos del directorio).
El diagnóstico contra la base real dio **tres fallos**, y los tres quedaron arreglados el mismo día:

| # | El fallo | Por qué pasaba | Cómo quedó |
|---|---|---|---|
| **A** | **Una palabra basura anulaba la búsqueda** | «acá» **no** estaba en `chatbot_busqueda_ruido()` y, como el término se limpia de tildes antes, se quedaba como `aca` (3 letras: pasaba el filtro). El término salía **«clavo aca»** y la consulta exige que aparezcan **TODAS** las palabras → **0 resultados**. ⚠️ «aquí» sí estaba en la lista: solo faltaban «acá» y «allá» | «aca», «alla» y los rellenos de lugar están en la lista **y**, si un término de varias palabras no encuentra nada, se **reintenta palabra por palabra**: una palabra estorbosa ya no puede dejar la respuesta en cero |
| **B** | **Las «frases de unión» del sitio no se leían** | La tabla **`directorio_categoria_claves`** ya tenía **«clavos» → Ferreterías** (56 tiendas), pero el chat solo miraba el **nombre de la tienda**, el nombre del **rubro** y el **título del producto**: «clavos» nunca podía llegar a las ferreterías. El único «acierto» era una **chicha morada con clavo de olor** 🥤 | `chatbot_busqueda_rubro()` lee primero esa tabla (`chatbot_busqueda_claves()`) y de ahí sale el rubro de la respuesta |
| **C** | **El rubro encontrado no se usaba** | «alguien que parche llantas» **sí** encontraba el rubro «Reparación de llantas»… y contestaba 0 igual, porque el rubro nunca se convertía en resultados | Cuando el rubro se conoce, **sus tiendas se suman** a la respuesta y **sus productos son los que se muestran** |

**🏷️ Qué es una «frase de unión»:** la tabla **`directorio_categoria_claves`** (`categoria_id`, `clave`,
con `UNIQUE (categoria_id, clave)`; se administra por SQL). Es **el mismo diccionario que ya usaban El
caminante** (buscador predictivo de rubros) **y las noticias** (los enlaces suaves en gris): las palabras
**reales** que la gente escribe y que llevan a un rubro. La idea del jefe, textual: *«ferretería es igual a
clavos, lijas, martillos, cemento, ladrillos… y un largo etc»*; *«si preguntan quién vende cemento, obvio
son las ferreterías, y quien vende paracetamol, obvio las farmacias»*.

**🔑 LA REGLA QUE QUEDA (para no volver a escribir código por esto):** si un rubro **no encuentra algo que
vende**, casi siempre lo que falta es **la frase en esa tabla**, no código. Y las frases se escriben
**sin tildes**, **en minúsculas** y de **1 o 2 palabras** (así las compara el buscador).

**Cómo busca ahora, en orden** (todo en `includes/chatbot_buscar.php`):

1. `chatbot_busqueda_ruido()` — el relleno y el lugar. ⚠️ **Se mira DESPUÉS de quitar las tildes**: las
   palabras de esta lista van **sin tilde** («aca», no «acá»), o no se quitan.
2. `chatbot_busqueda_termino()` — saca el término («quién vende clavos acá en Chimbote» → `clavo`).
3. `chatbot_busqueda_rubro()` — **¿a qué rubro apuntan las palabras?** Puntaje: **4** si el nombre del
   rubro **empieza una palabra** (`[[:<:]]pollo` → «Pollerías»; `llanta` → «Reparación de llantas»),
   **3** si coincide exacto con una **frase de unión**, **2** si una contiene a la otra («clavo» ↔
   «clavos»). Empate: gana el rubro que **tiene tiendas activas**. ⚠️ El nombre del rubro se compara por
   **principio de palabra** a propósito: con un `LIKE '%aca%'` a secas, «acá» encontraba «Ac**aca**demias».
4. `chatbot_buscar()` — tiendas por texto (todos los tokens → de a uno) **+ las del rubro** (sin repetir);
   y los productos **del rubro si el rubro se conoce** (así nunca sale la chicha morada por «clavo»).
5. `chatbot_busqueda_texto()` — además, cuando la respuesta salió del rubro (`por_rubro`) se ofrece
   **«Mira el rubro [Ferreterías](…/categoria/ferreterias)»**: es el «ver más» natural (las 56).

**Y en la página del buscador** (`buscar.php`, la que abre el enlace «Hay más en el buscador»): si la
búsqueda **por texto no encuentra nada**, se repite por el **rubro de la frase de unión** y se avisa con
una nota visible («*«clavos» no está en el nombre de ninguna tienda, pero es una palabra del rubro
Ferreterías…*»). Motor compartido: `categoria_por_clave_texto()` y `sin_tildes_texto()` (`includes/helpers.php`).
⚠️ La lista de `NOTICIAS_ENLACE_NUNCA` (`config_noticias.php`) **ya veta «lima» y «cadena»**: al añadir
frases nuevas hay que mirar esa lista, porque en un texto periodístico significan otra cosa.

**📊 Los números de la carga (2026-09-13):** **Ferreterías 11 → 251** frases y **Farmacias / Boticas
6 → 146** (total del sitio: **1 057 → 1 437** en los 121 rubros). Las listas están en
**`__claves_ferreteria.json`** (240 nuevas) y **`__claves_farmacia.json`** (140 nuevas); el generador es
**`__gen_claves_seed.py`** (normaliza y arma el PHP) y la carga se hizo con la sonda temporal
**`__claves_seed.php`** (`python __ep_run.py __claves_seed.php salida.json "&go=1"`), que imprime la
orden de vuelta atrás (`DELETE FROM directorio_categoria_claves WHERE id > 1109;`).

> 🆕 **Y esa misma noche se completó TODO el directorio** (orden del jefe: *«con frases para el buscador
> en todos los aspectos»*): **87 rubros** repartidos en 6 lotes paralelos (encargos en
> `__claves_brief_1..6.json`, respuestas en `__claves_lote_1..6.json`) → **1 870 frases nuevas** y la tabla
> quedó en **3 320 claves en 122 rubros** (antes 1 437): Restaurantes 84, Tiendas de ropa 55, Mecánicos 51,
> Bodegas 50, Peluquerías 49… El generador/validador es **`__gen_claves_lotes.py`** y la sonda
> **`__claves_seed2.php`** (rollback: `WHERE id > 1507`). ⚠️ Tres lecciones de esa carga:
> (1) **una palabra no debe estar en el rubro genérico y en el dueño a la vez** («helado» estaba en Bodegas
> y Pastelerías, «pescado» en Restaurantes, «aspiradora» en Grifos): se quitaron del genérico;
> (2) **los electrodomésticos** («refrigeradora», «lavadora», «licuadora»…) van a **Informática /
> Celulares**, que es donde están sus negocios en esta base (y hay un **pendiente**: falta un rubro
> «Electrodomésticos» de verdad); (3) cuando dos rubros se pelean una palabra, **gana el que más negocios
> tiene** (`chatbot_busqueda_rubro()`), y el orden de búsqueda quedó: servicio del rubro → todas las
> palabras juntas → **el rubro** → palabra por palabra (ese orden arregló el «protector solar» que caía en
> un contador apellidado «Del Solar»). Prueba de humo: `python __ep_run.py __diag_claves_nuevas.php salida.json`.

**Cómo se prueba (todo sin gastar saldo):**

```
python __prueba_chat.py "puedes buscar quién vende clavos acá en Chimbote"   # fuente: busqueda
python __verif_buscar.py                                                    # la página del buscador (HTTP)
python __ep_run.py __diag_clavos3.php salida.json                           # 12 preguntas de una vez
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_kb.php                         # el guion, sin saldo
```

### c-ter) 🔧 LAS BÚSQUEDAS DE SERVICIO: «reparar mi celular» → los TÉCNICOS del rubro

**Pedido textual del jefe (2026-09-13, noche):** *«si alguien busca reparar su celular debe mostrarle a
los técnicos de la categoría/rubro celulares que tenemos en la base de datos»*.

**El problema:** el visitante escribe el **VERBO** («reparar», «formatear», «arreglar») y las fichas del
sitio están escritas con el **SUSTANTIVO** («Reparación de pantalla», «Servicio técnico de celulares»).
Además, dentro de un rubro como Informática / Celulares hay **34 negocios** y los que **reparan** son 13:
sin un orden especial salían primero tiendas de webcams o de cámaras de seguridad.

**Cómo quedó** (todo en `includes/chatbot_buscar.php`):

1. **`chatbot_busqueda_servicios()`** — traduce el verbo al sustantivo: `reparar/reparo/reparan/arreglar`
   → `reparacion` · `formatear` → `formateo` · `mantener` → `mantenimiento` · `instalar` → `instalacion`
   · `limpiar` → `limpieza` · `soldar` → `soldadura`, etc. También cuenta como servicio escribir un
   **oficio** («técnico», «service», «gasfitero», «mecánico», «cerrajero»…).
2. **`chatbot_busqueda_servicio($tokens)`** — dice si la búsqueda es de un servicio y devuelve: los
   sustantivos, **los trozos** con los que se reconoce a un negocio de servicios (`tecnic`, `service`,
   `servici`, `reparad`, `reparaci`, `arregl`, `mantenim`, `formate`, `instala`, `soporte`, `taller`…)
   y **las palabras que eran el verbo del servicio** (para poder quitarlas).
3. **En `chatbot_buscar()`:** si es un servicio **y** se conoce el rubro, se busca **primero dentro del
   rubro** y con la palabra del producto (**también en los TÍTULOS de los productos**, no solo en el
   nombre: así «reparar laptop» encuentra a los que reparan laptops aunque no se llamen «laptop»).
4. **El orden va en dos niveles** (es la clave de todo):
   **1.º** los negocios que lo dicen en su **NOMBRE** («Servicio técnico de celulares Julinho»,
   «Microtech Service») → esos son *los técnicos*; **2.º** los que tienen el **servicio publicado**
   («Reparación de pantalla»); **3.º** el resto del rubro.
5. Y los **servicios publicados** (`Reparación de laptops y computadoras`, `Mantenimiento de sistemas`…)
   se muestran **antes** que el resto de productos.

**Lo que se ve ahora (probado en el chat real):**

```
VISITANTE: «quiero reparar mi celular»
BOT:  Sí, estos son los resultados de informática / Celulares en la ciudad:
      - Servicio tecnico de celulares Julinho   - GSM Servicell   - Microtech Service
      - Reparación de pantalla (Innovacell)     - Reparación de tablet (Touch Screen Importaciones)
      Mira el rubro Informática / Celulares.   ¿Te muestro las más cercanas a ti? 📍

«reparar laptop» / «formatear mi laptop»  → Técnico Germán Computadoras, Técnico David Computadoras…
«un técnico de celulares»                 → Julinho, Servicio técnico de celulares, YoReparoVzla…
«comprar un celular» (control)            → NO cambia: sigue mostrando dónde venden celulares
«quién vende clavos» (control)            → NO cambia: sigue mostrando las ferreterías
```

⚠️ **Ojo con las palabras de servicio sueltas:** buscar `reparacion` a secas lleva a las **llanterías**
(su rubro se llama «Reparación de llantas»). Por eso los sustantivos **no** se buscan como tienda: solo
sirven para **ordenar** y para buscar los **productos de servicio** del rubro que ya se identificó.

**Cómo se prueba:** `python __ep_run.py __diag_reparar.php salida.json` (12 frases de reparación y dos
controles) · `python __prueba_chat.py "quiero reparar mi celular"` (el chat real) ·
`python __ep_run.py __diag_tecnicos.php salida.json` (dónde viven los técnicos en la base).

### c-quater) 🎨 EL COPY DEL BUSCADOR: bonito, amigable y sin repetirse

**Pedido textual del jefe (2026-09-13, noche):** *«mejora el copywriting, hazlo más bonito, amigable»*.

Antes el chat decía siempre lo mismo («Sí, estos son los resultados de X en la ciudad:» + «Mira el rubro
X.» + «¿Te muestro las más cercanas a ti? Necesitaré que actives tu ubicación.»). Ahora
**cada caso tiene sus frases y se sortea una** (`chatbot_busqueda_frase()`): un amigo no repite la misma
frasecita, y el visitante que pregunta dos veces ve algo distinto.

| Caso | Lo que dice ahora (una al azar) |
|---|---|
| 🔧 **Servicio** («reparar celular») | «Estos son los que te lo dejan como nuevo 🔧» · «Aquí sí te lo reparan 👇» · «Estos técnicos te pueden ayudar 🔧» · «Para eso, estos te sacan del apuro 👇» |
| 📍 **Con ubicación** | «Estas son las más cercanas a ti 📍» · «Estas te quedan cerquita 👇» · «Las que tienes más a la mano 📍» |
| 😃 **Rubro con más tiendas de las que se muestran** | «Tengo 56 opciones de ferreterías 😃 mira estas» · «Hay 56 opciones de ferreterías en la ciudad — aquí van algunas 👇» · «De ferreterías tengo 56 😃 te muestro las más movidas» |
| 🏷️ **Lo de siempre** | «Sí, estos son los resultados de «pollería» en la ciudad» · «¡Claro que sí! 🙌 Mira lo que tengo de pastelerías y tortas» · «Uy, sí 👀 aquí tienes heladerías y juguerías» · «Te dejo lo mejor de farmacias / boticas que tengo 👇» |
| 🛍️ **Solo productos** | «Mira lo que encontré 👇» · «Justo tengo esto 👀» · «Esto te puede servir 👇» · «Toma, mira esto 🔎» |
| 🏪 **El rubro (ver más)** | «Tienes más en el rubro [Ferreterías] 👀» · «Y si quieres verlas todas: [Ferreterías] 👈» · «Hay más en [Ferreterías] ✨» |
| 🔎 **El buscador** | «Y hay más en [el buscador] 👀» · «Si quieres ver todo lo que hay: [el buscador].» |
| 📍 **La despedida (UNA pregunta)** | «¿Te muestro las más cercanas a ti? Solo activa tu ubicación 📍» · «¿Quieres que te las ordene por cercanía? Activa tu ubicación 📍» · «¿Te digo cuáles te quedan más cerca? Activa tu ubicación 📍» |
| 😅 **Sin GPS de todas** | «(Todavía no todas me han dicho dónde están, así que van sin distancia 😅)» |

**Tres detalles que quedaron finos:**

1. **El número real de opciones:** «Tengo **56** opciones de ferreterías». Sale de `$res['rubro']['tiendas']`
   (lo cuenta `chatbot_busqueda_rubro()` sin gastar una consulta más) y **solo se dice cuando las tiendas
   salieron DEL RUBRO** (`por_rubro`): así el número siempre cuadra con lo que se muestra.
2. **El nombre del rubro va en minúsculas enteras** («Pastelerías y Tortas» → «pastelerías y tortas»):
   con minúscula solo en la primera letra quedaba una mayúscula rara en medio de la frase.
3. **A veces se nombra mejor con la palabra del visitante:** si escribió un **tipo de negocio** terminado
   en «-ería» («polleria») y el rubro es más ancho que él (el rubro se llama «Restaurantes»), el chat dice
   **«pollería»** (con su tilde devuelta: el término viene sin tildes). Con los **productos** NO se hace:
   decir «aquí tienes «helado»» sería peor que decir «heladerías y juguerías».
   La comprobación es `chatbot_busqueda_es_clave_de()` (misma regla que usa el anfitrión).

**Y en la página del buscador** (`buscar.php`), el copy también se humanizó: el vacío ya no dice
«Sin resultados» sino **«Uy, todavía no tengo nada de eso»** (con «No hay nada tan cerquita» en modo
cerca de ti) + *«Prueba con otra palabra, otro rubro u otro distrito. Y si quieres, pregúntale a
**El ninja** 🥷»*; la nota de las frases de unión empieza con 😉 («No vi ninguna tienda con «clavos» en
el nombre, pero en Ferreterías sí lo tienen — mira las que tengo»); y el separador dice
**«Y hay 20 más de «clavos» 👇»**.

**Cómo se prueba:** `python __ep_run.py __diag_copy.php salida.json` (imprime el copy de 8 casos, ya sin
las imágenes, para leerlo cómodo) · `python __prueba_chat.py "quién vende clavos"` (el chat real).

### d) Los recursos del celular (lo que hay y lo que falta)

| Recurso | Estado |
|---|---|
| 📷 Cámara (tomar foto · galería) | ✅ ya funciona (§2quinquies). **Desde el 2026-09-20 el botón abre una ventana modal con DOS botones en una fila** (§2duodecies punto 14) |
| 🖥️ Compartir pantalla | 🗄️ **RETIRADO el 2026-09-20** (el jefe pidió un modal con dos opciones y nada más) |
| 🎙️ Voz (dictado) | 🗄️ **ARCHIVADO / RETIRADO del guía el 2026-09-20** (orden del jefe: el guía es un chatbot normal; se escriben o se mandan fotos). Lo que había se cuenta en §2duodecies puntos 11-13, **archivados**. ⚠️ El **buscador** por voz sigue vivo y no se toca (`GUIA_BUSCADOR_VOZ.md`) |
| 📍 **GPS dentro del chat** | ✅ **nuevo el 2026-09-13** (esto de arriba) |
| 🔊 Nota de voz del visitante | ❌ pendiente (haría falta pasar audio a texto) |
| 🔔 Aviso al dueño cuando alguien busca lo que vende | ❌ pendiente |

---

## §2decies. 🎩 EL ANFITRIÓN: el chat que ofrece negocios en el momento justo (2026-09-13, noche)

**Pedido textual del jefe:** *«el chatbot es como un anfitrión que llama a la gente a visitar los
negocios, pero lo hace de manera inteligente e indirecta. Por ejemplo si alguien dice que hace mucho
calor o el día está muy soleado, el chatbot le debe mostrar los proveedores de helados o tiendas de
jugos, con frases como "es el momento perfecto para refrescarte con una bebida helada, mira estos
negocios"»*. Y con las fotos: *«tomé una foto de un letrero de un negocio llamado COMPUME que decía
reparación de laptops y formateos… el bot vio la imagen y dijo eso mismo, pero no ofreció ningún negocio
relacionado a productos de informática o técnicos»*.

**Qué es:** el chat ya no solo contesta: **detecta el momento o lo que se ve en la foto y ofrece los
negocios del sitio que le sirven**, con la foto enlazada a la ficha. Todo **local: no gasta ni un token
de DeepSeek** (reutiliza `chatbot_buscar()`).

**Los archivos:** `deploy/includes/chatbot_ofrecer.php` (todo el anfitrión) y **un envoltorio** al final
de `chatbot.php`:

```
chatbot_responder()            ← la llama api/chatbot.php (la puerta)
   ├─ chatbot_responder_base() ← el motor de siempre (renombrado, intacto)
   └─ chatbot_ofrecer_aplicar() ← 🎩 el anfitrión pega su bloque al final
```

⚠️ **Por qué un envoltorio y no un `if` dentro del motor:** el anfitrión tiene que valer para **todos**
los caminos de respuesta (la IA, el clima del día, el guion local…) sin tocar ninguno, y el motor tiene
~10 `return` distintos. Si algún día se toca `chatbot_responder_base()`, el anfitrión sigue funcionando.

### a) Los dos caminos del anfitrión

| Camino | Cuándo | Cómo saca el tema |
|---|---|---|
| **MOMENTO** | El visitante **cuenta** algo sin pedirlo: calor, frío, hambre, un cumpleaños, la llanta pinchada, la laptop que no prende… | `chatbot_ofrecer_momento()`: tabla escrita a mano (`chatbot_ofrecer_momentos()`) con **palabras sin tildes** que se buscan por **palabra completa** («el sol está fuerte» → calor; «**solo** quería saber» **no**) |
| **FOTO** | El visitante manda una imagen | `chatbot_ofrecer_palabras_de_texto()` saca de la **respuesta del modelo** las palabras que **son frases de unión del directorio** («laptop», «accesorio», «computadora») y `chatbot_ofrecer_rubro_de_texto()` resuelve el **rubro** (Informática / Celulares) |

**Cómo elige los negocios:**

1. Busca hasta **3 términos** (un momento) o **5** (una foto, que es rara y tiene cupo diario), **2 fichas
   de cada uno** — para que la lista no salga con un solo negocio.
2. En la foto, los términos se filtran a los que **son del rubro** que salió (`chatbot_ofrecer_es_clave_del_rubro()`).
3. Se completan con **los negocios del rubro** (máx. 4) y con **1 producto** (con su precio).
4. **Se puntúa cada ficha por lo que lleva su NOMBRE** y se muestran 3: así, con un letrero de
   «reparación de laptops», sale **Técnico Germán Computadoras** primero y no una tienda de webcams.
   Desempate: el orden en que los trajo el buscador y, luego, los más vistos.

### b) Cómo se ve de verdad (probado en producción el 2026-09-13)

```
VISITANTE: «hace mucho calor acá en Chimbote»
BOT:  Hoy estuvo nublado, así que ese calorcito es puro cariño del ambiente 😏…

      Es el momento perfecto para refrescarte con algo bien helado 🥤 Mira estos negocios:
      - HELADERIA UCV - RA            — Restaurantes
      - Jugueria restaurante Anita    — Restaurantes
      - Juguería y Licorería "El Pino" — Bodegas / Minimarkets
      - Hielo (bolsa) — S/ 3.00 · Licoreria San Luis
      [📍 Ver las más cercanas a mí]   ← la opción apilada (accion:'geo', q:'heladeria')

VISITANTE: (manda la foto del letrero «COMPUME — reparación de laptops y formateos»)
BOT:  (describe el letrero) …

      De paso: en Chimbote tenemos negocios de informática / celulares — mira 👇
      - Técnico germán computadoras   — Informática / Celulares
      - Técnico David computadoras    — Informática / Celulares
      - Brenda Beltran - Accesorios   — Ventas por Internet (Entregas)
      - Reparación de laptops y computadoras — S/ 90.00 · A & F Movil Perú
```

Y con el otro clima: *«está lloviendo»* → «Con este frío, algo calientito cae de maravilla ☕» +
cafeterías y panaderías · *«tengo harta hambre»* → pollerías · *«es el cumpleaños de mi hija»* → tortas,
globos y flores · *«se me pinchó una llanta»* → talleres y llanterías · *«mi perro está enfermo»* →
veterinarias · *«me duele la muela»* → farmacias · *«quiero lentes nuevos»* → ópticas · *«necesito
limpieza para mi casa»* → servicios de limpieza · *«se me perdió la llave»* → cerrajerías · *«busco un
hotel»* → hospedajes.

### c) ⛔ Los candados (un buen anfitrión no es un pesado)

1. **Nunca encima de una búsqueda:** si la respuesta ya trae negocios (`fuente: busqueda`) o el texto ya
   tiene un enlace `/neg/`, no se añade nada.
2. **Una vez por conversación:** si el historial que manda el navegador ya trae un `/neg/`, no se vuelve
   a ofrecer (`chatbot_ofrecer_ya_ofrecio()`).
3. **Nada cuando el chat está bloqueado** (cuota, límites, avisos) ni si hay que registrarse.
4. **Nada si no hay momento ni foto** («cuánto cuesta publicar» → respuesta limpia, sin ganchos).
5. **Nada si el rubro tiene menos de 2 negocios** (no se manda a una página vacía).
6. **El anfitrión nunca puede romper el chat:** todo va dentro de `try/catch` y cualquier fallo devuelve
   la respuesta tal como estaba.

### d) Cómo se prueba y cómo se añade un momento

```
python __prueba_chat.py "hace mucho calor acá en Chimbote"   # el chat real (gancho del calor)
python __prueba_chat.py "cuánto cuesta publicar"             # control: NO debe ofrecer nada
python __ep_run.py __diag_anfitrion.php salida.json          # momentos, foto de COMPUME y candados
python __ep_run.py __diag_tezminos.php  salida.json          # cuántos negocios encuentra cada término
```

**Para añadir un momento nuevo:** una fila más en `chatbot_ofrecer_momentos()` con `palabras`
(**⚠️ sin tildes**), `busca` (términos, **medidos antes** con `__diag_tezminos.php`: hay palabras que no
devuelven nada, como «jugo», «raspadilla» o «formateo») y `frase`. No hay que tocar nada más.

**⚠️ Trampa medida:** los términos se eligen mirando la base, no el diccionario. Ejemplos reales: existe
el rubro **«Heladerías y Juguerías» pero con 0 tiendas** (lo que hay son fichas de heladerías y
juguerías dentro de otros rubros), «jugo» en singular no encuentra nada y «jugos» sí, y «taller» lleva a
**Cursos y Talleres** en vez de a mecánicos (por eso el momento del carro usa «mecanico» y «llanta»).

---

## §2undecies. ✂️ NOTICIAS DISCRETAS (4 PALABRAS) · 🖥️ OPCIONES EN DOS COLUMNAS · 🧹 «LIMPIAR» EN BLANCO (2026-09-14)

> **Pedido textual del jefe (2026-09-14):** *«modifica el chatbot para que pueda poner las noticias en el
> chatbot pero no así como titulares con enlaces muy llamativos o titulares muy largos: el título de la
> noticia en el chatbot no puede ser más de cuatro palabras y no tratar de repetirlo cada rato, sino ser
> discreto para comentar noticias. También los botones de opciones deben apilarse en dos columnas cuando
> está en formato escritorio o formato PC. Y el botón limpiar debe limpiar todo el chat, no dejar nada,
> ni siquiera los mismos botones: el botón limpiar chat entrega limpio la ventana sin nada, ni siquiera
> los mismos botones de chat.»*

### a) 📰 Las noticias: título de **4 palabras como máximo** y tono discreto

| Qué | Cómo quedó | Dónde |
|---|---|---|
| **El recorte** | `chatbot_titular_corto($titulo, $max = 4)`: corta por palabra completa, **sin puntos suspensivos**; si el título arranca con un artículo suelto lo salta («El alcalde de Chimbote anunció…» → **«alcalde de Chimbote anunció»**) y quita las palabras colgadas del final (`de, la, en, con, sin, ni, según, contra…`) para que no quede un título mocho | `includes/chatbot.php` |
| **La línea del turno** | `📰 Si te interesa, hoy salió esto: [Nuevo Chimbote evalúa riesgo](…).` — **sin negritas, sin «¡…!» y sin «Es bueno estar informado»**. Antes era «📰 **Hoy**: <titular de 15 palabras>. Es bueno estar informado 🥷» | `chatbot_noticia_turno()` (`includes/chatbot_diario.php`) |
| **No se repite** | Sigue saliendo **solo en la pregunta 3 o 4** y **una sola vez** por conversación; y desde hoy, si **el tema ya salió** (en el historial viaja un `/noticia/` o el visitante ya pidió noticias), la función devuelve `''`: *«no tratar de repetirlo cada rato»* | idem |
| **La lista (si piden noticias)** | `chatbot_noticias_propias_texto()`: cabecera discreta («📰 Esto es lo de hoy:»), **5 noticias** por defecto (no 10) y **cada título corto como enlace** | idem |
| **El guion del modelo** | Al modelo se le da **ya cortado** el título (no el largo, para que no lo copie), y las reglas **15, 16 y 17** del prompt dicen: título de 4 palabras como máximo, nada de mayúsculas ni signos de admiración, **comentar una noticia como mucho una vez** y nunca convertir el chat en un boletín de titulares | `chatbot_diario_guion()` + `chatbot_prompt_sistema()` |
| **El nombre del enlace** | `/noticia/<slug>` **ya no se nombra con el titular completo**: `chatbot_etiqueta_enlace()` y su espejo en JS (`breve4()`) lo dejan en 4 palabras | `chatbot.php` + `assets/js/chatbot.js` |

**Comprobado en vivo (2026-09-14)**: la lista del chip «📅 Qué noticia hay hoy en Chimbote» salió
«Foro turístico en Chimbote · Atropello en San Pedro · Hallan un perro · Mujer denuncia robo · Dos
jóvenes heridas», y en la **pregunta 3** de una conversación nueva apareció la línea
«📰 Si te interesa, hoy salió esto: **Foro turístico en Chimbote**» (una sola vez en 4 preguntas).

### b) 🖥️ Las opciones, en DOS COLUMNAS en escritorio

```css
@media (min-width:900px){
  .cbot-chips{display:grid;grid-template-columns:1fr 1fr;gap:7px;align-content:start}
  .cbot-chip{max-width:none}
  .cbot-chip:last-child:nth-child(odd){grid-column:1/-1}   /* si son impares, la última ocupa todo */
}
```
Van en `includes/chatbot_widget.php`. **En celular siguen apiladas en UNA columna** (la orden del jefe
es para «formato escritorio o formato PC»): en una pantalla estrecha dos columnas dejarían los botones
ilegibles. Medido en pantalla de 1366 px: `grid-template-columns: 577px 577px` con las 12 opciones.

### c) 🧹 «Limpiar el chat» deja la ventana **sin nada**

En `assets/js/chatbot.js` (`limpiarChat()`): además de borrar la conversación y el `localStorage`, ahora
**no se repinta nada** — antes volvía a pintar el saludo y **las mismas opciones apiladas**, que era
exactamente lo que el jefe no quería. Se añadió la bandera **`vacio`**:

| Pieza | Qué hace |
|---|---|
| `vacio = true` | Lo pone `limpiarChat()`; la ventana queda en blanco (cuerpo sin mensajes, `pintarChips([])` y `mostrarChips(false)`, barra de cierre oculta, adjunto y menú del 📷 cerrados, cuadro vacío) |
| `refrescar()` | Con `vacio` **no pinta nada** (ni opciones, ni saludo del día, ni actividad, ni aviso de cierre): solo refresca la cuota, que es interna. La ventana sigue limpia aunque se cierre y se vuelva a abrir el chat |
| `enviarPregunta()` | Pone `vacio = false`: al escribir la primera pregunta el chat vuelve a la vida |
| 💡 | El botón de ideas **sigue sirviendo** para volver a ver las opciones cuando él quiera (es una acción suya, no automática) |

**Comprobado en vivo (2026-09-14)**, en pestaña nueva de escritorio: tras tocar «🧹 Limpiar el chat» →
`mensajes: 0`, `textoCuerpo: ""`, `chips: 0 y ocultos`, `localStorage: null`, y la captura de la ventana
**completamente vacía** (ni el saludo ni los botones).

### d) ⏳ EL «PENSANDO · CONSULTANDO · RESPONDIENDO» Y EL PISO DE ESPERA DE 1,5 s

**Pedido textual del jefe (2026-09-14):** *«no olvides siempre ganar al menos uno o dos segundos en el
periodo de respuesta con el texto "pensando"… escribirías "pensando", pero de texto un poco más pequeño,
que se note que es como una subrutina interna, y ese segundo te da tiempo para consultar en la base de
datos información más precisa; luego, después de consultar, puedes usar otra frase comodín que puede ser
"respondiendo": hasta eso ya ganaste dos segundos, que es tiempo valiosísimo para que puedas dar una
respuesta y no lanzarla de frente de golpe con lo primero que encuentre… eso de "pensando" y "escribiendo"
puede ser programación, para que no tengas cada vez que ejecutar ese texto, porque es el mismo, es
repetido»*.

**La idea del jefe es correcta y así quedó hecha: el texto es PROGRAMACIÓN, no una llamada al modelo.**
Gastar tokens (y tiempo) en generar siempre las mismas dos palabras sería absurdo: las frases viven en
`includes/config_chatbot.php` y se editan en el panel.

| Pieza | Qué hace | Dónde |
|---|---|---|
| `CHATBOT_PENSANDO_FRASES` | `'Pensando · Consultando · Respondiendo'` (máx. 4 frases; separadas por «·»). El navegador las **rota** mientras espera y se **queda en la última**: nunca se queda en blanco | `config_chatbot.php` + panel |
| `CHATBOT_PENSANDO_MS` | **700 ms** por frase: a los 0,7 s ya dice «Consultando…» y a los 1,4 s «Respondiendo…» | idem |
| `CHATBOT_ESPERA_MIN_MS` | **1500 ms de PISO**: la respuesta **no se pinta** antes. Si el servidor contestó en 300 ms, igual se ve el «pensando» entero (eso es el segundo que se gana) | idem |
| `chatbot_pensando_frases()` | Parte el texto en frases (tope 24 caracteres, 4 frases) y siempre devuelve al menos una. Se manda al navegador en `window.CHATBOT_CFG.pensando` | `includes/chatbot.php` |
| El dibujo | Tres puntitos + **texto de 13 px en gris** (`.cbot-pensando__txt`): pequeño a propósito, para que se note que es una nota interna del bot y no un mensaje suyo | `includes/chatbot_widget.php` |
| El piso en el JS | `pensando(true)` marca la hora, `faltaEspera()` calcula lo que falta y la respuesta sale con `respuesta(d)` cuando se cumple (nunca antes) | `assets/js/chatbot.js` |
| El prompt | Regla **12-quater-ter**: el modelo **no imita ni nombra** el «pensando» (nada de «déjame revisar», «estoy consultando»): el visitante ya lo vio | `chatbot_prompt_sistema()` |

**Medido en vivo (2026-09-14, pestaña nueva, cronómetro en la propia página):**

```
"Pensando…"      → t = 0 ms
"Consultando…"   → t = +702 ms
"Respondiendo…"  → t = +1403 ms
RESPUESTA pintada → t = +1505 ms      ← el piso exacto de 1500 ms
```
Y la captura lo confirma: la burbuja con los tres puntitos y **«Respondiendo…» en letra pequeña gris**
debajo de la pregunta. **No gasta ni un token**: es texto fijo del script.

### e) ✂️ PALABRAS CORTAS, RÁPIDAS Y DIRECTAS (sin guías si no las piden)

**Pedido textual del jefe (2026-09-14):** *«que el chatbot El ninja responda con palabras cortas, rápidas
y directas, procurando no decir muchas guías o pasos a menos que el visitante lo solicite; pero después
siempre el chatbot debe ser rápido para responder con respuestas cortas»*.

Se añadió la regla **12-quater-bis** al prompt (`chatbot_prompt_sistema()`), pegada a la del tope de 25
palabras:

1. **Primero LA RESPUESTA**, en una o dos líneas: nada de preámbulos («claro que sí», «con gusto te
   explico», «existen varias opciones»).
2. **Nada de pasos, listas ni tutoriales** cuando la pregunta es puntual: «¿quién vende lavadoras?» → las
   tiendas (y una pregunta). Ni cómo buscar, ni cómo publicar, ni los pasos de nada.
3. **Solo si lo PIDE** («¿cómo lo hago paso a paso?», «explícame», «dame los pasos», «guíame») se da la
   guía, y aun así la más corta posible (pasos numerados, una línea cada uno).
4. Si el bot cree que hace falta más, **lo ofrece en una pregunta** («¿te digo cómo?») en vez de soltarlo.


⚠️ Al tocar el JS hay que **subir la versión** del widget (`includes/chatbot_widget.php`, `'version'` →
**16**), o los navegadores siguen con el archivo viejo en caché.

---

## §2duodecies. 🧭 DE «EL NINJA» A «EL GUÍA»: EL BOT QUE ATIENDE CADA TIENDA (2026-09-17)

**Pedido textual del jefe (2026-09-17):** *«a partir de ahora el ninja se va a comportar de otra
manera; es más, ni siquiera se va a llamar El ninja: le vamos a cambiar el nombre. Ahora va a ser un
chatbot, un amigo, que cuando una persona entre a una tienda este botón automáticamente va a tener
precargada toda la información de esta tienda, como los horarios, la ubicación, qué cosas están cerca
de esta ubicación… también los productos, también los precios; por eso tiene inteligencia artificial.
Su función ahora es guiar referente al sitio web desde donde se está cargando la tienda, desde donde
se encuentra… Cuando cargue la página va a hacer un efecto de que el botón popa o modal, como que se
hubiese abierto y luego se hubiese cerrado, y permanecería ahí en un círculo futurista con algún halo
o algo bonito bien hecho indicando que quiere ser abierto, con pequeños movimientos persuasivos
buscando que le den clic… una vez que le den clic se va a presentar con un mensaje lo más humano o lo
más real posible, ya sea hablando del clima de la ciudad, con datos reales pero no extenso, sino
definir como una mañana abrigada, una mañana de lluvia, una mañana fría, una tarde caliente, una noche
con mucho viento… y se va a ofrecer como apoyo a preguntas: ¿tienes alguna pregunta?… también es
posible que sea entrevistado por los mismos dueños preguntando de quién es esta página… también puede
reclamar esta tienda, debido a que tal vez todavía no ha sido reclamada. ¿Cómo se sabe cuándo una
tienda no ha sido reclamada? Porque tiene el teléfono 955 041 690… el robot también debe seguir
ofreciendo la opción de crear tu propia tienda, pero mediante un enlace que lo lleve a la página de
El maestro»* (hoy el número del administrador es **908 785 164**). Y eligió el alcance y el nombre: **«El guía» 🧭** y **solo en las fichas de tienda**.

### 1) Qué es ahora, en una frase
**El anfitrión de la tienda que el visitante está mirando.** Vive **solo dentro de las fichas**
(`/neg/<slug>`) y llega con la tienda ya cargada: si alguien pregunta por el horario, la dirección, lo
que vende, los precios, qué hay cerca, cómo se pide o quién publicó la página, **contesta con los
datos reales de esa tienda y sin gastar un token**; para todo lo demás tiene el guion completo y la IA.

### 2) Los archivos
| Archivo | Qué hace |
|---|---|
| 🧭 `includes/chatbot_ficha.php` | **El módulo nuevo**: arma el contexto de la tienda, el saludo del día, las respuestas locales, las opciones y el bloque que recibe el modelo. Se puede cargar solo (no depende de `chatbot.php`). |
| `negocio.php` | Antes del `footer.php` llama a **`chatbot_ficha_poner($negocio, $extras)`** con productos, fotos, opiniones, pagos, cercanos y zonas. **Si esto faltara, el botón no se pinta.** |
| `api/chatbot.php` | Recibe el **`negocio` (slug)** en el POST y en el GET (`?negocio=<slug>`) y llama a **`chatbot_ficha_cargar($slug)`**: los datos se **vuelven a leer de nuestra base** (al navegador no se le cree nada). En una ficha **no añade la línea de la noticia** del turno 3-4. |
| `includes/chatbot.php` | `chatbot_sugerencias()`, `chatbot_saludo()` y `chatbot_saludo_dia()` tienen su rama de ficha; `chatbot_responder_base()` contesta **antes que nada** lo que sabe la ficha (`fuente: ficha`); `chatbot_prompt_sistema()` le añade el bloque de la tienda al final. |
| `includes/chatbot_widget.php` | **Solo se pinta si hay ficha** (`CHATBOT_SOLO_FICHAS`), con el **círculo + halo** y el nombre de la tienda debajo del nombre del bot. `?v=18`. |
| `assets/js/chatbot.js` | Manda el slug en cada petición y hace **el efecto de entrada** (`efectoPop()`), los movimientos persuasivos (la **brújula que se mueve cada 6,5 s**, `brujulaViva()`), el corte del efecto si el visitante toca el botón, el **modal de la cámara** y el envío. En el **JS** viven además las **tablas** (`formato()`) y el **tope de 3 opciones**. ⚠️ El texto está algo viejo: hoy va **`?v=22`** y **no queda grabadora ni voz** (ver el punto 14). |
| `includes/config_chatbot.php` | `CHATBOT_NOMBRE/TITULO/EMOJI` («El guía» / «El guía» / 🧭), `CHATBOT_SOLO_FICHAS`, `CHATBOT_POP_ACTIVO`, `CHATBOT_POP_MS` (1400), `CHATBOT_HALO`. |

### 3) El saludo: se presenta y habla del día en una frase corta
- **1.ª burbuja** (`chatbot_ficha_saludo`): «Buenos días! 🧭 Soy **El guía**. Te atiendo aquí en
  **Sport Center Gym** 👌» (con el nombre del visitante si tiene sesión).
- **2.ª burbuja** (`chatbot_ficha_saludo_dia`): «🌤️ Hoy está siendo **una mañana templada** por aquí.» +
  «¿Tienes alguna pregunta? Yo te ayudo con lo que venden y cómo comprarlo 🧭».
- **La frase sale del clima REAL** (`chatbot_ficha_frase_dia()`, datos de Open-Meteo del archivo del
  día) y **sin grados**: lluvia → «una tarde con lluvia» · viento ≥26 km/h → «una mañana con viento» o
  «una tarde fría con viento» (≥38 = «con mucho viento») · neblina · fría ≤15° · fresca ≤19° ·
  templada · calurosa ≥24° · caliente ≥28° · y cielo limpio y agradable = «soleada».

### 4) Lo que contesta la ficha SIN GASTAR TOKENS (`fuente: ficha`)
Pregunta por… → **horario** (o dice con honestidad que no está cargado y da el WhatsApp de la tienda) ·
**ubicación** (distrito + dirección + **referencia** + enlace al mapa) · **qué hay cerca** (los
negocios cercanos que ya calcula la ficha) · **productos y precios** (hasta 5 con su precio y unidad) ·
**cómo se compra** (❤️ Me interesa → un solo mensaje de WhatsApp) · **formas de pago** · **WhatsApp y
teléfono** · **entrega a domicilio y zonas** · **quién publicó la página / es mía / reclamarla** ·
**quién eres** · **de qué es la tienda** · **crear mi propia tienda** (enlace a **El maestro**,
`/crear-tienda`). Cualquier otra cosa sigue el camino normal (buscador vivo → IA con el contexto).

### 5) El reclamo: cómo se sabe que una ficha NO está reclamada
Dos señales (`chatbot_ficha_reclamable()`), la del jefe y la de la base:
1. **`dueno_id` vacío** (así lo mide el Súper Admin: «sin dueño»), y
2. **el teléfono/WhatsApp de la tienda es el del administrador** (`ADMIN_WHATSAPP` = **908 785 164** desde el **2026-09-19**; el `955 041 690` pasó a ser el número **personal** del jefe y quedó en `ADMIN_WHATSAPP_VIEJOS`, que es lo que mira `telefono_es_del_admin()`),
   que es exactamente la regla que dio el jefe (los mensajes de una ficha sin reclamar caen al jefe).
Si la ficha **no** está reclamada, el bot lo dice y ofrece el enlace
**`/reclamar_negocio.php?slug=<slug>`** («reclamar mi negocio»); si ya tiene dueño, lo dice y no ofrece
nada. 📊 **Medido el 2026-09-17: 1 527 de las 1 707 tiendas activas están sin dueño** (el 89 %), así
que esto sale casi siempre. ⚠️ La ficha de prueba del jefe (`sport-center-gym`, id 1049) **sí tiene
dueño** (`dueno_id = 153`): ahí el bot contesta la versión «ya tiene dueño registrado».

### 6) El botón: círculo futurista, halo y efecto de entrada
- **Círculo** de 62 px (68 en PC) con degradado azul y brillo interior; **halo** = dos anillos que
  laten (`cbot-fab--halo`, constante `CHATBOT_HALO`); **movimientos persuasivos** = un latido suave cada
  4,6 s (`cbot-fab--vivo`), que se apaga cuando el visitante ya abrió el chat.
  🔴 **Y desde el 2026-09-20 (apartado 13) la brújula 🧭 NO se queda quieta:** además del halo y el latido,
  **la aguja busca el norte cada 6,5 s** (`cbot-fab--brujula` + `@keyframes cbotBrujula`) mientras el guía
  esté cerrado; se apaga al abrirlo y **vuelve al cerrarlo** (`brujulaViva()`).
- **El efecto de entrada** (`efectoPop()` en `chatbot.js` + `cbot-panel--pop`): al cargar la ficha el
  chat **se abre desde el botón y se vuelve a cerrar solo** (`CHATBOT_POP_MS` = 1 400 ms), para que se
  vea que ahí hay un chat. Se hace **una vez por sesión del navegador** (`sessionStorage`) y se respeta
  «reducir movimiento». Si el visitante toca el botón mientras se presenta, el efecto se corta y el
  chat se abre de verdad. **No pinta mensajes ni llama a la API: no gasta cuota.**
- En la cabecera del chat, debajo de «El guía», va **el nombre de la tienda** (`.cbot-head__sub`).

### 7) 🔢 Los límites de hoy (esto es lo que reemplazó a los minutos)
**Preguntas por día y por persona** — 20 el visitante, 50 el registrado, 300 el ⭐ Premium (0 = sin
límite) —, contadas en el servidor en `cache/chatbot/cuenta/<clave>_<AAAAMMDD>.txt` (clave = `u<id>` o
`ip<hash>`; el día es el de **Lima**). Se gasta **una pregunta por respuesta** (también las locales,
incluidas las de la ficha); **no gastan** el saludo, los botones, la invitación ni el cierre.
Constantes: `CHATBOT_CUOTA_ACTIVA`, `CHATBOT_PREGUNTAS_VISITANTE/REGISTRADO/PREMIUM`,
`CHATBOT_CIERRE_SEG` (15 s antes de que la ventana se cierre sola). Al visitante **nunca** se le dicen
los números. Tope del sitio entero: `CHATBOT_LIMITE_GLOBAL_DIA` (600 mensajes).
Herramienta: **`python __chat_cuota.py ver | listar | borrar ip<hash>`** (⚠️ tenía la raíz vieja
`/public_html`; **arreglado el 2026-09-17**: ahora usa `/cache/chatbot`, trampa 33 de §8).

### 8) Lo que se RETIRÓ con este cambio (para no dejar botones muertos)
El bot dejó de existir fuera de las fichas, así que se quitaron sus puertas:
la **tarjeta «🥷 El ninja» del panel** (`panel.php`) y su sección «El ninja, tu asistente» · el **botón
💬 del Explorer** (`explorer.php`), su entrada en **«Tus herramientas»** y su historia
(`includes/explorer.php`, que abrían el chat y ya no habría widget detrás) · y el texto del **buscador
sin resultados** (`buscar.php`), que ahora dice la verdad (el guía vive dentro de cada tienda).
El **panel del Súper Admin sigue igual**, solo cambió de nombre: **🧭 El guía (chat)**.

### 9) Cómo se prueba (sin gastar saldo ni abrir el navegador)
```powershell
# 1) El módulo entero, con datos de mentira y sin tocar la base (56 comprobaciones)
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_ficha.php

# 2) El saludo real de una ficha por HTTP (GET: no gasta cuota)
curl.exe -s "https://dechimbote.com/api/chatbot.php?negocio=sport-center-gym"

# 3) Una pregunta real (POST: gasta UNA de las 20 del día; devolverla después)
#    {"mensaje":"¿qué vende y a cuánto?","historial":[],"negocio":"sport-center-gym"}
python __chat_cuota.py listar                 # ver los contadores del día
python __chat_cuota.py borrar ip<hash>        # devolver las preguntas (¡siempre al terminar!)

# 4) Y el registro, para ver con qué fuente se contestó (ficha / ia / local / dia)
python __chat_cuota.py ver
```
**Comprobado en producción el 2026-09-17:** el botón sale con halo y `?v=18`, el saludo dice «Te atiendo
aquí en **Sport Center Gym**» y «Hoy está siendo una mañana templada por aquí», y por POST contestó
`fuente: ficha` al horario («no lo tengo cargado» + su WhatsApp), a los precios (S/ 240 trimestral,
S/ 90 mensual, S/ 35 la hora, S/ 10 el pase diario, S/ 60 las clases), al delivery y a «¿de quién es
esta página?»; en `intifarma` (sin dueño) ofreció **reclamarla**. En la portada y las demás páginas el
botón ya no aparece.

### 10) Lo que NO cambió
El motor de DeepSeek y su clave, el guion del sitio (`chatbot_kb.php`), el **buscador vivo**, la
**visión** 📷 (fotos y capturas), el **anfitrión** 🎩 (que sigue ofreciendo negocios según el momento),
el panel de ajustes del Súper Admin, la **cuota**, el «Pensando · Consultando · Respondiendo» con su
piso de 1,5 s y el 🧹 Limpiar el chat (aunque cambió de sitio: ver el punto 11).

### 11) 🔧 SEGUNDA TANDA DEL MISMO DÍA (2026-09-17, lo que pidió el jefe al verlo)
**Pedido textual:** *«no me gusta el tamaño de la letra, muy grande; no me gustan los botones, son muy
anchos, muy grandes… ocupan mucha parte de la pantalla; máximo deben verse visibles tres botones,
exagerando, con texto pequeño, lo más compacto posible. Y también el tipo de comunicación: el clic en
el botón que dice "qué vende y a cuánto" y la guía respondió "Esto es lo que tiene publicado",
hablando en tercera persona como si no fuese de su interés… lo correcto debería ser "tenemos"; es
decir, la IA en sus respuestas debe involucrarse, parecer parte de esa sección, como que esa sección
solo hablara de esa tienda… en la parte de abajo dice "asistente automático" y "hablar con una
persona": ese texto bórralo, no agrega nada. El botón de escribir yo creo que también ya se debe
borrar porque la gente siempre va a preferir grabar un mensaje, al menos en el modo móvil: desactívalo
y en su defecto pon una especie de grabadora… el icono del micrófono no es muy entendible: usa los
mismos iconos que usa WhatsApp o Messenger… y si vas a ofrecer tablas, crea esas tablas y procura que
ocupen todo el ancho de la ventana modal… y siempre pon por ahí algún botón para limpiar el chat y
volver a empezar»*.

| Lo que pidió | Cómo quedó |
|---|---|
| 📏 **Letra más chica y bocadillos más grandes** | Mensajes a **14 px** (antes 18), cabecera a 13,5 px, opciones a 11,5 px, «pensando» a 11 px. Las respuestas del bot van **a todo el ancho** de la ventana (`max-width:100%`, alineadas al borde); las del visitante se quedan al 88 % |
| 🔘 **Tres botones y chiquitos** | El servidor manda **solo 3** (`chatbot_ficha_sugerencias()`); son **píldoras** compactas de 11,5 px que se envuelven solas. **La lista completa (10) sale con el 💡** (`sugerencias_todas` en `CHATBOT_CFG`), y el JS tiene el tope `max_chips = 3` como red |
| 🗣️ **Primera persona, como parte de la tienda** | Todas las respuestas de la ficha se reescribieron: «**Esto es lo que tenemos**», «**Atendemos así**», «**Estamos en**… **Nuestra referencia**», «**Aceptamos**», «**hacemos entregas**», «**escríbenos**», «**Somos** Sport Center Gym…». Y el guion del modelo lleva la **regla 1**: *hablas en nombre de la tienda, en primera persona del plural, nunca «esta tienda tiene…»* |
| 📊 **Tablas a todo el ancho** | `formato()` (JS) entiende tablas de markdown (`\| Producto \| Precio \|` + `\|---\|---\|`) y las pinta como `.cbot-tabla` **al 100 % del ancho**. Los productos y precios ya salen en tabla (6 filas y «se cotiza» cuando no hay precio) |
| 🗑️ **Fuera el pie «Asistente automático · Hablar con una persona»** | Se quitó el `<p class="cbot-legal">` y su CSS. El WhatsApp del administrador sigue accesible desde el 💡 |
| 🎤 **Grabadora en vez del cuadro de escribir (celular)** | 🗄️ **ARCHIVADO** (2026-09-20, orden posterior del jefe: *«quiero que borres todo lo que tenga que ver con la voz dentro de la brújula»*). La clase `cbot-graba` **se retiró** y **el guía no tiene voz**: el cuadro de escribir se ve siempre (también en el celular) y al lado está el **➤ enviar**. Ver el **punto 14** |
| 🧹 **Botón fijo para limpiar y volver a empezar** | Botón **🧹 en la cabecera**, siempre a mano. Ahora **sí vuelve a empezar** (saludo de la tienda + sus 3 opciones otra vez), que reemplaza la orden del 2026-09-14 («dejar la ventana sin nada»): aquella era para el botón de las opciones, esta es la orden de hoy |
| 🔢 **Versión** | `?v=19` (subir siempre al tocar `assets/js/chatbot.js`) |

**Probado el 2026-09-17:** la respuesta real de «¿qué tenemos y a cuánto?» sale así de producción
(`fuente: ficha`): *«Esto es lo que tenemos 👇»* + la tabla con Membresía trimestral S/ 240,
Entrenamiento personal S/ 35, Membresía mensual S/ 90, Pase diario S/ 10 y Clases grupales S/ 60.
Y `__test_chatbot_ficha.php` pasó a **69 comprobaciones, 0 fallos** (incluye la primera persona, la
tabla, las 3 opciones y el wa.me).

### 12) 🔧 TERCERA TANDA DEL MISMO DÍA (2026-09-17, lo que pidió después de probar la grabadora)
> 🗄️ **ARCHIVADO EN LO QUE TOCA A LA VOZ (2026-09-20).** El jefe ordenó después **borrar toda la voz del
> guía** (*«quiero que borres todo lo que tenga que ver con la voz dentro de la brújula»*): las filas 🎤
> de abajo (grabar/parar, el icono que cambia de forma, el texto repetido) **ya no describen el sitio** y
> se conservan solo como historia de lo que se probó. Lo que **sigue vigente** de esta tanda: **🎵 la
> música para al abrir el guía** y **👀 fuera el «andabas mirando tal»**. Estado de hoy: **punto 14**.
**Pedido textual:** *«cuando el usuario da clic en la guía, automáticamente la música debe parar, porque
como el usuario va a grabar, la música estorba. Luego también has ofrecido un mensaje que dice "por
cierto, no me digas que te olvidaste, andabas mirando tal": eso no es necesario; recuerda que este
chatbot solamente va a hablar de esta tienda, no de otras cosas que hayas visto anteriormente. En todo
caso eso debería poder salir al final. Cuando voy a grabar un mensaje de audio hay el problema de que el
texto se repite (puse "hola" y escribió "hola hola hola…"): me da la sensación de que el micrófono está
esperando algún silencio y no es necesario. Machucas para grabar, machucas para parar, así de simple; es
lo más básico y funciona. Sé que la opción de parar cuando escuchas un silencio es buena, pero a veces
trae más problemas que soluciones, porque cada persona tiene su celular de diferente gama. Y en la parte
baja de la guía procura que ocupe dentro de lo que se ve en la pantalla: que no deje muchos espacios
libres abajo»*.

| Lo que pidió | Cómo quedó |
|---|---|
| 🎵 **La música para al abrir el guía** | El reproductor de la ficha (`includes/cancion_player.php`) respeta la bandera **`window.DCH_MUSICA_PARADA`**: mientras el chat esté abierto **no arranca** (ni sola ni con el primer gesto del visitante), y el chat además **pausa el audio** que ya esté sonando (`musicaParar()`, en `abrir()` y también al empezar a grabar). Al cerrar el chat la música **no vuelve sola**: el visitante le da al ▶ si quiere (así no lo sorprende) |
| 👀 **Fuera el «andabas mirando tal»** | En una ficha **ya no se saluda con la actividad** del visitante: `api/chatbot.php` manda `actividad: ''` y el guion del modelo **no recibe** el bloque «LO QUE ESTE USUARIO HIZO EN EL SITIO». Si el visitante **lo pide** («¿qué he visto?»), ahí sí se le contesta (eso es «que salga al final») |
| 🎤 **Pulsar para grabar, pulsar para parar** | La grabadora graba **en continuo**: **los silencios no cortan nada** y no se envía nada solo (se quitó la dependencia del silencio, que era lo que fallaba en celulares de gama baja). El primer toque al micrófono **graba**, el segundo **termina y envía**; el ➤ verde envía sin esperar y el 🗑️ borra y cancela |
| 🎤 **El icono CAMBIA DE FORMA al grabar** (pedido del jefe, 2026-09-17: *«tal cual como hacen los botones de WhatsApp o de Messenger, que se muestran de una manera cuando están en reposo y al activarse cambian su forma a un modo que el usuario entienda que puede hablar y luego parar»*) | Dentro del mismo botón van **dos iconos** y el CSS muestra uno u otro con la clase `.grabando` que pone el JavaScript: en **reposo** el **micrófono** (círculo gris) y **grabando** un **CUADRO DE PARAR** blanco dentro del círculo rojo, con un **halo rojo que late** (`cbotGrabando`). Así «toca para parar» se entiende sin leer nada; al lado, la barra de grabación muestra 🔴, el tiempo, el 🗑️ y el ➤ |
| 🔁 **El texto ya no se repite** | Dos causas, las dos arregladas: (1) el navegador avisa **muchas veces el mismo interino** y el texto se iba pegando pedazo sobre pedazo → ahora **el tramo se rehace entero** en cada aviso (`recTramo`) y solo al cerrarse un tramo pasa a la lista cerrada (`recBloques`); (2) los trozos se unen con un **espacio** (sin él salía «holaqué venden»). **Prueba nueva: `node __test_chatbot_rec.js` → 9 comprobaciones, 0 fallos**, incluido el caso exacto del jefe (18 avisos del mismo «hola» ⇒ «hola» una sola vez) |
| 📐 **Sin hueco libre abajo** | ⚠️ **Superado el 2026-09-20** (apartado 13): la ventana ya no va de borde a borde, ahora **flota al 85 % × 85 %** |
| 🔢 **Versión** | `?v=20` |

**Comprobado en producción:** el `GET /api/chatbot.php?negocio=…` devuelve `actividad: ""`, la ficha
sirve el `?v=20`, el reproductor de la canción trae sus dos guardas de `DCH_MUSICA_PARADA` y las demás
páginas siguen sin el bot y sin errores.

---

### 13) 🗄️ ARCHIVADO — EL GUÍA FLOTA AL 85 %, CON ✕, BRÚJULA VIVA Y «VOZ DEL BUSCADOR» (2026-09-20, mañana)

> 🔴 **ESTE APARTADO QUEDÓ ARCHIVADO EL MISMO DÍA, POR LA TARDE.** El jefe volvió a mirarlo y ordenó
> **borrar toda la voz del guía** y dejarlo como un chatbot normal (ver el **punto 14**, que es el que
> manda hoy). De aquí **siguen vigentes**: la **✕ para cerrar** al lado de la 🧹 y la **brújula que se
> mueve cada 6,5 s**. De aquí **quedaron archivados y ya no existen en el código**: el **botón de tres
> caras 🎤🛑➤**, el **dictado con el motor del buscador** y la **marca de grabación 🔴**. Y de la
> posición: la ventana **ya no va centrada**, va **pegada arriba y a la derecha** (punto 14).

**Pedido textual de aquella mañana:** *«atención urgente: actualmente el guía que aparece dentro de los
negocios, al momento de abrirlo, ocupa toda la pantalla y no debe ocupar toda la pantalla: debe ocupar
solamente un 85 % tanto de ancho como de alto, para que dé la sensación de que está flotando por sobre el
sitio. Y al ladito de la escoba agrégale una ✕ para poder cerrar la guía. Actualmente se muestra en cada
ficha como una especie de brújula que hace un efecto de cargar al inicio y se cierra: mantén esa brújula en
cierto movimiento cada x segundos para que le llame la atención al usuario. … Y pon también un área de
escribir: tenemos un botón para tomar fotos, un botón para grabar o hablar… ese icono no me gusta: cámbialo
por un solo icono, un solo icono inteligente que sepa cuándo está grabando y cuándo está en stop. El código
de voz del buscador funciona perfecto… no estoy diciendo que metas el buscador adentro de la guía: estoy
diciendo que la tecnología de voz la copies en la guía… Y también agrega un cuadro de poder escribir texto
delgado, así como tiene TikTok con su botón de enviar en forma de triángulo»*.

| Lo que pidió | Cómo quedó (y qué pasó después) |
|---|---|
| 📐 **La ventana al 85 % × 85 %** | Pasó a **`85vw` × `85vh`** (antes ocupaba todo el alto y hasta el 90 % del ancho) con `85dvh` donde el navegador lo entiende. ⚠️ **Superado por el punto 14**: el tamaño se queda, la **posición cambia** (pegada arriba y a la derecha, ya no centrada) |
| ✕ **Cerrar al lado de la 🧹** | Botón **`#cbotCerrar`** en la cabecera, justo después de la 🧹, con su clase `.cbot-head__btn--x`. Vuelve la ✕ que se había quitado el 2026-09-13. ✅ **Sigue vigente hoy** (y también cierran el fondo oscuro y `Escape`) |
| 🧭 **La brújula se mueve cada X segundos** | ✅ **Sigue vigente hoy**: la clase **`cbot-fab--brujula`** gira el emoji cada **6,5 s** (`@keyframes cbotBrujula`), junto al latido (`--vivo`, 4,6 s) y el halo (`--halo`, 2,8 s). Lo enciende y lo apaga **`brujulaViva()`**: se apaga al abrir el guía y **vuelve al cerrarlo**. El efecto de entrada (`cbot-panel--pop`) se queda tal cual |
| ✍️ **Cuadro de escribir delgado** | ✅ **Sigue vigente hoy**: `.cbot-input` de **34 px**, fondo gris suave, sin borde a la vista y el anillo naranja solo al enfocarlo, **visible también en el celular** (se retiró la clase `cbot-graba` que lo escondía en el móvil) |
| 🎤🛑➤ **Un solo botón inteligente (tres caras)** | 🗄️ **ARCHIVADO**: existió ese mismo día y se **borró por la tarde**. Era un solo botón (`#cbotMicro`) con 🎤 micrófono → 🛑 cuadro de parar → ➤ enviar según lo que hubiera escrito. El jefe lo rechazó: *«olvídate ya de iconos compuestos»* |
| 🔴 **La marca de grabación** | 🗄️ **ARCHIVADA**: píldora roja con 🔴 · tiempo · 🗑️. Ya no existe |
| 🗣️ **La voz del buscador, copiada al guía** | 🗄️ **ARCHIVADA**: se copió el motor de `assets/js/buscador_voz.js` (reconocedor **nuevo** por dictado, el candado `instancia === recActual`, `es-PE` → `es-ES` → `es-MX`, los avisos de error del buscador y el texto escribiéndose en el cuadro). **Ya no está en el guía** ⛔. ⚠️ **El buscador NO se tocó**: `buscador_voz.js` y `GUIA_BUSCADOR_VOZ.md` siguen igual y funcionando |
| 🔢 **Versión de aquel momento** | `?v=21` (hoy `?v=22`) |

**Prueba de aquel momento:** `node __guia_test_dom.js` → 26 comprobaciones (incluida la voz). La prueba
**se reescribió** con el guía nuevo (**30 comprobaciones, 0 fallos**: sin voz, ventana pegada arriba-derecha,
brújula viva, ✕, modal de la cámara y envío). **Comprobado en producción aquella mañana:** la ficha servía
el `?v=21` con `IDIOMAS_VOZ` y `pintarBotonVoz()` en el JS.

---

### 14) 🔴 LO QUE MANDA HOY: EL GUÍA ES UN CHATBOT NORMAL — SIN VOZ, PEGADO ARRIBA A LA DERECHA (2026-09-20, tarde)
**Pedido textual:** *«a ver, entiéndeme por favor, entiéndeme: quiero que borres todo lo que tenga que ver
con la voz dentro de la brújula, dentro de la guía; quiero que borres todo, me comprendes. Quiero que lo
muestres simplemente como un chatbot normal, con su icono de cámara, su bloque para poder escribir y un
solo icono normal sencillo de enviar. …Y si hay por ahí alguna guía anterior de voz a texto relacionada a
la brújula, no vayas a valorar nada relacionado al buscador: el buscador está perfecto, su botón funciona
perfecto, eso no hay que tocarlo. …En la guía —repito, la guía—, que debe aparecer apegada a la derecha:
está apareciendo actualmente centrado; debe aparecer apegado a la derecha y arriba, ya no debe aparecer al
medio. …En esa guía vas a poner abajo solamente tres botones: uno, el botón de cámara, que abre un popup
—cuando digo popup me refiero a ventana modal— con dos opciones: una, la opción de poder tomar una foto con
un botoncito, y la otra, la opción de abrir la galería; son dos botoncitos pequeños en texto blanco y negro.
…Luego viene el área de texto donde se escribe y luego viene un botón para enviar lo que se ha escrito.
Algo tan sencillo ¿puedes hacer? Olvídate ya de iconos compuestos, iconos que contienen dos o tres
funciones: lo que te estoy pidiendo nada más es el icono de enviar, un botón que sirve para enviar. …Y si
hay alguna guía anterior que hable de cómo convertir voz a texto relacionado con esta guía, ignóralo,
márcalo como archivado, que no se usa; no lo borres, pero márcalo como archivado: ya no es la misma
tecnología que se está usando en el buscador»*.

| Lo que pidió | Cómo quedó |
|---|---|
| ⛔ **Borrar toda la voz del guía** | **BORRADA del código**, no escondida: se fueron el **micrófono** (`#cbotMicro`), el **botón compuesto** (🎤🛑➤), el **dictado** (todo el bloque del motor de voz: `Reconocimiento`, `IDIOMAS_VOZ`, `MENSAJES_VOZ`, `recEmpezar`, `recParar`, `recAgregar`, `pintarBotonVoz`) y la **marca de grabación** (`#cbotRec`, `#cbotRecT`, `#cbotRecX`). En el JS queda una **nota `⛔ ARCHIVADO`** que explica qué había y por qué no se repone. **Comprobado: el JavaScript del guía no usa la Web Speech API** (ni una línea de código; solo la nombra el comentario) |
| 🤖 **Un chatbot normal, con tres piezas** | El pie tiene **📷 cámara · cuadro de escribir · ➤ enviar**, en ese orden y nada más. ⛔ Sin iconos compuestos: el **➤ solo envía** y la **📷 solo abre su ventana**. El ➤ es ahora un botón **único y siempre visible** (desaparecieron las dos caras del botón inteligente y el ➤ que se escondía según el navegador) |
| 📷 **La cámara abre una ventana MODAL con DOS botones** | `#cbotCamModal`: una ventana con su ✕, el título *«¿Qué foto quieres mandarme?»* y **dos botones en UNA sola fila** (`.cbot-modal__fila`): **📷 Tomar foto con la cámara** (**blanco con letra negra**, su `input` lleva `capture="environment"`, así el celular abre la cámara) y **🖼️ Subir de la galería** (**negro con letra blanca**, su `input` no fuerza la cámara). Se cierra con la ✕, **tocando el fondo oscuro** y con **`Escape`** (y `Escape` cierra el modal, **no** todo el guía). ⚠️ Antes esto era un menú con «tomar foto / elegir foto» junto y **«Compartir mi pantalla»**: esa tercera opción **se retiró** con esta orden |
| ✍️ **El área de texto** | El cuadro delgado de siempre (34 px, fondo gris suave, anillo naranja al enfocar), **siempre visible** y también en el celular |
| ➤ **Un solo botón de enviar** | Un icono normal y sencillo: **solo envía** lo escrito (el formulario sigue mandando con **Enter**) |
| 📐 **Apegada a la derecha y arriba, ya no centrada** | `.cbot-panel` pasó de `top:7.5vh;left:7.5vw` (centrada) a **`top:8px;right:8px`**, con el **mismo 85 % × 85 %** de tamaño (`85vw` × `85vh`, y `85dvh` en el celular): el aire sobrante queda **a la izquierda y abajo** y la ventana se ve flotando sobre la página (el fondo sigue oscurecido y desenfocado). ⚠️ El anclaje va con **`top/right` y NO con `transform`**: `cbotEntra` y `cbotPop` ya usan `transform` |
| 🧭 **La brújula y la ✕ no se tocaron** | Siguen como quedaron en el punto 13: la brújula **se mueve cada 6,5 s** (y vuelve a moverse al cerrar el guía) y la **✕** sigue en la cabecera al lado de la 🧹 |
| 🗄️ **«Marca lo anterior como archivado, no lo borres»** | Se **archivaron** (sin borrar) los puntos **11, 12 y 13** de este apartado y las trampas **36 y 37**; la tabla de recursos del celular (§2nonies) marca la voz como **retirada**; y el JS del guía lleva su nota `⛔ ARCHIVADO` |
| ⚠️ **El buscador NO se tocó** | `assets/js/buscador_voz.js` y `assets/js/dictado_voz.js` quedaron **intactos** (mismos bytes, sin cambios; comprobado que el vivo sigue igual: `buscador_voz.js?v=3` → 200 y `es-PE`). `GUIA_BUSCADOR_VOZ.md` **no se modificó**: el buscador está bien como está |
| 🔢 **Versión** | **`?v=22`** |

**Prueba nueva (sin navegador y sin tocar el del jefe): `node __guia_test_dom.js` → 30 comprobaciones, 0 fallos.**
Levanta el `chatbot.js` de verdad sobre un DOM simulado y comprueba: que carga sin errores · que **no hay
voz** (ni `#cbotMicro`, ni `#cbotRec`, ni Web Speech en el código) · que la ventana va **pegada arriba y a
la derecha** (`top:8px;right:8px;85vw;85vh`, sin rastro del centrado viejo) · que el pie es **📷 → cuadro →
➤** en ese orden · el **efecto de entrada** y la **brújula que sigue moviéndose** (y se apaga al abrir y
vuelve al cerrar) · que **la ✕ cierra** · que **📷 abre el modal**, que dentro están los **dos botones**, que
la opción de la cámara usa `capture` y la de la galería no, y que el modal se cierra con la ✕, tocando el
fondo y con `Escape` · y que **escribir y enviar** manda la pregunta con su tienda (`negocio`).

**Comprobado en producción (2026-09-20):** `GET /neg/mercado-modelo-de-chimbote` → **200 · 741 840 bytes**
con `id="cbotCam"`, `id="cbotCamModal"` + los dos botones, `cbotArchivoCam … capture="environment"`,
`cbotArchivoGal` sin `capture`, `id="cbotTexto"`, `id="cbotEnviar"`, `id="cbotCerrar"` (después de la 🧹),
`cbot-fab--brujula`, `top:8px;right:8px;width:85vw;height:85vh` y **`chatbot.js?v=22`** — y **sin**
`cbotMicro`, **sin** `id="cbotRec"` y **sin** `cbotCamPantalla`. El JS vivo (`?v=22`, **62 630 bytes**) tiene
`camModal`/`cbotCamGaleria`, abre la cámara y la galería, y **no queda ni una línea de Web Speech** fuera de
la nota archivada. Y el buscador sigue igual: `GET /assets/js/buscador_voz.js?v=3` → **200 · 19 384 bytes**.

> ⚠️ **NOTA POSTERIOR DEL MISMO DÍA:** el **punto 15** devolvió el **micrófono al guía** (copiado del
> buscador, al lado de la 📷). Todo lo de este punto 14 sigue vigente **menos** la frase «no hay voz».

---

### 15) 🎤 VUELVE EL MICRÓFONO — COPIA LITERAL DEL BUSCADOR, PEGADO A LA CÁMARA (2026-09-20, la última orden)
**Pedido textual:** *«entiende bien lo que quiero: yo quiero que copies la tecnología que ya existe en el
buscador para convertir voz a texto, y esa misma tecnología la pongas al costado del icono de cámara… eso
estoy pidiendo: que lo copies tal cual, no estoy pidiendo que lo crees, lo modifiques ni nada, que lo copies
tal cual. Y cuando presione ese botón va a convertir mi voz en texto, y este texto… lo va a pegar en el input
que dice "escribe tu pregunta", que curiosamente es el input que está al costadito de la cámara»*. (Antes,
en la misma conversación: *«el buscador tiene un botón para poder grabar… me encanta cómo funciona, nunca se
equivoca; ¿lo puedo poner al costadito de la cámara, así igualito, con el mismo icono, el mismo color, la
misma forma, el mismo motor?»*.)

| Lo que pidió | Cómo quedó |
|---|---|
| 🎤 **El micrófono al lado de la 📷** | Botón **`#cbotVoz`** en el pie, **pegado a la cámara**: el orden queda **📷 → 🎤 → cuadro de escribir → ➤**. Y lo que se dicta **cae en el cuadro de al lado** (`#cbotTexto`, el que dice *«Escribe tu pregunta…»*), listo para mandar con el **➤** |
| 🎨 **Igualito: icono, color y forma** | El **mismo círculo naranja** del buscador (`--marca-naranja, #ea6a12`), el **mismo dibujo del micrófono** (el **SVG** de `buscador_voz.js`, 22 px, **no un emoji**) y **el mismo latido rojo** mientras escucha (`#c1121f`, `@keyframes` copiado de `voz-latido`, 1,1 s). Único ajuste: allá el botón va **flotando dentro del campo** (`position:absolute; right:5px`) y aquí es **un botón más del pie**; el dibujo y los colores son idénticos |
| ⚙️ **El mismo motor** | Copiado **tal cual** de `assets/js/buscador_voz.js`: **un reconocedor NUEVO en cada dictado** (reutilizar la instancia deja estados pegados en Chrome), el candado **`instancia === vozActual`** en cada manejador, **`es-PE` con caída a `es-ES` y `es-MX`** (`language-not-supported`), **`interimResults`** (lo que se oye se escribe al instante), **segundo toque = parar**, `ESPERA_FINAL` 260 ms y los **mismos avisos** (micrófono bloqueado, sin micrófono, sin conexión, no te escuché…) en **la misma caja blanca** del buscador (`.cbot-voz-aviso`, copia de `.voz-aviso`; aquí vive dentro de la ventana porque el guía no tiene la caja del buscador) |
| ✂️ **⚠️ Lo único que NO se copió (a propósito)** | **La limpieza del dictado** (`limpiarDictado()` → `ChimboteLimpieza`). Esa limpieza borra mandos **y conectores** porque el buscador busca **por palabras**: en una pregunta hablada **destrozaría la frase**. Medido con el motor común del sitio: «¿Cuánto cuesta la membresía mensual?» → **«membresía mensual»**; «Hola, ¿venden leche?» → **«leche»**; «quiero saber el horario de atención» → **«horario atención»**. Aquí el texto se escribe **como se dijo** (igual que el dictado del panel del dueño, `assets/js/dictado_voz.js`) |
| ⏎ **Nada de búsqueda automática** | En el buscador, a los 900 ms busca solo. Aquí **no**: el texto se queda en el cuadro y **manda el visitante con el ➤** (mandar solo sería del buscador, no del guía) |
| 🔒 **Candado contra el texto que volvía** | `yaDictado` (el **mismo** candado `yaBuscado` del buscador, con otro nombre): una vez escrito el texto, **no se vuelve a escribir**. Sin él, el temporizador de 260 ms podía **devolver la pregunta al cuadro justo después de mandarla** con el ➤ |
| 🦊 **Nunca un botón muerto** | Si el navegador **no sabe dictar** (Firefox, Safari), el micrófono **no se pinta**: quedan cámara, cuadro y ➤. Igual que en el buscador |
| 🎵 **La música se para al dictar** | Al empezar a escuchar se llama a `musicaParar()` (la música de la tienda se pausa), para que el micrófono no se confunda |
| ⚠️ **El buscador NO se tocó** | Esto es una **copia**, no un reemplazo: `assets/js/buscador_voz.js`, `assets/css/components.css` y `GUIA_BUSCADOR_VOZ.md` quedaron **intactos** (19 384 bytes de JS y 67 515 de CSS, sin cambios; el archivo local sigue con fecha del 2026-09-15) |
| 🔢 **Versión** | **`?v=23`** |

**Prueba (sin navegador y sin tocar el del jefe): `node __guia_test_dom.js` → 59 comprobaciones, 0 fallos.**
Incluye la sección **8) El dictado**: que al tocar el 🎤 arranca un reconocedor **nuevo** en **`es-PE`**, que el
botón **late en rojo**, que el cuadro avisa *«🎙️ Escuchando… habla ahora»*, que lo que se oye **se escribe al
instante** y **queda entero** («¿Cuánto cuesta la membresía mensual?» **con sus conectores**, no
«membresía mensual»), que el **segundo toque para**, que el **➤ manda lo dictado** y deja el cuadro vacío, que
el aviso de **micrófono bloqueado** sale con la caja del buscador y que, si el navegador no tiene el Perú,
**reintenta solo con `es-ES`**. Y la sección **9)**: en un navegador **sin voz**, el 🎤 **no se pinta** y
escribir/enviar sigue funcionando.

**Comprobado en producción (2026-09-20):** `GET /neg/mercado-modelo-de-chimbote` → **200 · 745 682 bytes** con
`id="cbotVoz"`, **el SVG del buscador**, `marca-naranja,#ea6a12`, `is-escuchando{background:#c1121f;animation:cbotVozLatido 1.1s}`,
`id="cbotVozAviso"`, el orden **📷 → 🎤 → cuadro → ➤**, la ventana pegada **arriba a la derecha** y
**`chatbot.js?v=23`** (ya sin `?v=22`). El JS vivo (**72 846 bytes**) trae `IDIOMAS_VOZ = ['es-PE','es-ES','es-MX']`,
`instancia !== vozActual`, `new Rec()` por dictado y escribe en `#cbotTexto`; y **`limpiarDictado` aparece UNA
sola vez, dentro de un comentario** (no hay una línea de código que limpie la pregunta). El buscador sigue
igual: `buscador_voz.js?v=3` → **200 · 19 384 bytes**.

---

### 16) ✨ LOS DOS DETALLES DE USABILIDAD QUE PIDIÓ AL PROBARLO (2026-09-20, última tanda)
**Pedido textual:** *«te ha quedado perfecto, la verdad muy perfecto. Solamente un pequeño detalle de
usabilidad: después de grabar el mensaje con el micrófono, y el micrófono se desactiva porque escucha
silencio, debería dar un segundo y enviarse el mensaje; de esa manera acostumbramos al usuario a dar
respuestas cortas y también le ahorramos dar clic en Enviar. No estoy diciendo que borres el botón de enviar,
déjalo, el botón de enviar está muy bien ahí en su sitio. [El botón de la cámara] no se ve muy armónico, se ve
muy llamativo, como un banner, carga muy grande: debe ser discreto nomás. Cuando hacen clic en la cámara,
automáticamente justo encimita aparecen los dos botones chicos: botón de "Abrir cámara" y botón de "Abrir
galería", pero así pequeños, discretos, no al medio grandote como que fuese un banner. El botón de la cámara
lo que debe hacer es abrir un modal pequeño, como un menú hamburguesa, prácticamente es un menú hamburguesa:
el icono de la cámara, solo que tiene un icono de cámara en lugar del icono de hamburguesa. Corrige el botón
de la cámara; después todo está perfecto»*.

| Lo que pidió | Cómo quedó |
|---|---|
| 🚀 **«Un segundo y enviarse el mensaje»** | Cuando el micrófono **se apaga porque escuchó silencio**, el texto ya está en el cuadro y **se manda solo al segundo** (`AUTO_ENVIO_MS = 1000`, `autoEnviarVoz()`). Da **1 segundo de gracia** y **se cancela** en cuanto el visitante hace algo: **escribe una tecla**, **toca el micrófono otra vez**, **abre la cámara** o **le da al ➤** (`autoEnvioCancelar()`; se escucha `keydown`, que solo llega cuando teclea una persona, no cuando escribe el dictado) |
| 🖐️ **Si lo para él, NO se manda** | Si el que apaga el micrófono es **su dedo** (segundo toque), **no hay envío automático**: el texto se queda escrito para que lo revise — eso es lo que él pidió («cuando el micrófono se desactiva porque escucha silencio»). Se distingue con la bandera `vozParoManual`, que se pone en el clic y se limpia en `onend` |
| ➤ **El botón de enviar se queda** | Tal cual, en su sitio y a la vista (lo dijo: *«no estoy diciendo que borres el botón de enviar»*). Y ahora tampoco se manda dos veces: el `submit` cancela el envío automático |
| 📷 **La cámara abre un MENÚ chiquito, no un banner** | Se fue la **ventana modal centrada** (cajita blanca con título y dos botones grandes: se veía como un cartel) y volvió un **menú discreto** (`#cbotCamMenu`) que sale **justo encima del botón de la cámara** (izquierda del pie, `bottom:56px`, 150 px de ancho, letra de **13 px**), con **dos renglones**: **📷 Abrir cámara** y **🖼️ Abrir galería**. Es «un menú hamburguesa con icono de cámara», como él lo describió. Se cierra tocándolo otra vez, **tocando cualquier otra parte del guía** o con **`Escape`** (y `Escape` cierra el menú, **no** el guía) |
| 🔢 **Versión** | **`?v=24`** |

**Prueba (sin navegador): `node __guia_test_dom.js` → 69 comprobaciones, 0 fallos.** La sección **8.b)** mide el
envío automático al detalle: **no se manda** mientras escucha · el texto queda en el cuadro · **al medio
segundo todavía no** sale nada · **al segundo se manda solo** · el cuadro queda vacío · **si teclea, no se
manda** · y **si lo para con el dedo, tampoco**. La sección **6)** comprueba que el menú nace cerrado, que la
📷 lo abre, que se llama «Abrir cámara» / «Abrir galería», que **ya no existe `cbot-modal`**, que es chiquito
(150 px, letra 13 px) y que se cierra al tocar otra parte. ⚠️ **Esa prueba cazó un error real:** el `Escape`
seguía llamando a la función vieja `modalAbierto()` (que ya no existía) → **`ReferenceError` al pulsar
Escape**; se cambió a `menuAbierto()`. **Lección: al renombrar una función hay que buscar sus llamadas en TODO
el archivo, no solo donde se escribió.**

**Comprobado en producción (2026-09-20):** `GET /neg/mercado-modelo-de-chimbote` → **200 · 746 598 bytes** con
`id="cbotCamMenu"`, **«Abrir cámara»** y **«Abrir galería»**, **sin** `cbot-modal`, con el 🎤 y su aviso, el ➤ en
su sitio y **`chatbot.js?v=24`**; y el JS vivo (**75 778 bytes**) con `AUTO_ENVIO_MS = 1000`,
`autoEnvioCancelar()` y `menuAbierto()` (sin rastro de `modalAbierto`). El buscador sigue intacto:
`buscador_voz.js?v=3` → **200 · 19 384 bytes**.

---

## §3. Qué sabe el bot (y de dónde sale cada dato)

Todo lo que el bot afirma vive en **`includes/chatbot_kb.php`** y está **comprobado contra el
código del sitio** (no se inventó nada). Los datos duros que usa:

| Tema | Dato real que da el bot |
|---|---|
| Registro | `https://dechimbote.com/registro` (chat paso a paso: nombre, correo, contraseña y tipo de cuenta 👤 cliente / 🏪 dueño). También **Ingresar con Google** en `/login.php`, «Recuérdame 30 días», y —desde el **2026-09-16**— **sí hay recuperación de contraseña**: el enlace **«¿Olvidaste tu contraseña?»** de `/login` lleva a **`/recuperar`**, donde el dueño pide con su número o su correo y el administrador le da una **clave nueva** por WhatsApp (módulo 🔑, detalle en `GUIA_CONSTRUCTOR_DE_TIENDAS.md` §3ter). |
| Productos | `/perfil` → franja roja «📸 Carga tu inventario con la cámara y la voz» → `/productos.php?n=ID#crear` (foto con vista previa, dictado 🎙️, precio, categoría; y ahí mismo editar/borrar). Las fotos se convierten solas a **WebP**. |
| Marketing | Los **10 consejos** de `chatbot_consejos_marketing()`, todos sobre herramientas que existen: fotos reales, rubro/subrubro + distrito + GPS, horario y entrega, **🎁 descuento por DeChimbote.com**, responder WhatsApp, **❤️ Me interesa**, publicar cada semana, compartir `/neg/<slug>`, **💼 ofrecer empleo**, y medir en `/perfil`. 🔒 **Solo con sesión.** ⚠️ Los consejos **ya no mencionan los tablones B2B** (se retiraron del sitio el 2026-09-13; antes el consejo decía «ofrecer empleo + tablones B2B»). |
| Borrar el sitio | **No hay autoborrado** (para que nadie borre por error): los productos los borra el dueño en `/productos.php?n=ID`; la tienda completa (con fotos, productos, opiniones y avisos) la borra el administrador por WhatsApp **+51 908 785 164** —el número del administrador desde el **2026-09-19**; el `955 041 690` es hoy el **personal** del jefe— o se **pausa**, y es **irreversible**. |
| Empleo | `https://dechimbote.com/empleos` con filtros por oficio/rubro/zona/tipo, ficha `/empleo/<slug>` con WhatsApp que lleva el mensaje listo, publicar «busco trabajo» (queda pendiente de aprobación) y **caducidad de 30 días**. |
| Tiendas cerca | Botón verde **📍 Ver tiendas cerca** (portada, fichas y páginas de rubro) → pide ubicación → abre `/buscar.php?lat=…&lng=…&radio=auto` (2 km → 5 → 10 km hasta juntar 20). Sin GPS: buscador de texto. |
| Publicar tienda | Gratis: `/registro` como 🏪 **dueño** → `/registrar_negocio.php`, o el asistente `/crear_negocio.php` (cámara + voz); **sin cuenta**: `/caminante`. Nace **pendiente** y el administrador la aprueba (normalmente el mismo día) → `dechimbote.com/neg/tu-negocio`. Si la tienda ya existe y no es suya: `/reclamar`. |
| 🌤️ Clima de hoy | Del archivo del día (Open-Meteo): "soleado / poco sol / nublado / lluvioso", grados de ahora, máxima y mínima, probabilidad de lluvia, humedad, viento, amanecer y atardecer. **Contestado localmente** con `"fuente":"dia"` (exacto y sin gastar saldo). |
| 📰 Noticias de hoy | **Nuestras noticias locales** (Chimbote · Nuevo Chimbote · Santa), las que publica el robot en `/noticias`: **el título corto (4 palabras) es el enlace** a `/noticia/<slug>` y al final va la sección. **Contestado localmente** con `"fuente":"dia"`. ⛔ Nunca una noticia ni un enlace de un medio de afuera. |
| 👁️ Imágenes | **Ve** las fotos y capturas que le manden (el 📷 del guía abre su **ventana modal: tomar foto con la cámara o subir de la galería**) y aconseja con lo que ve. No dice nunca que no puede ver imágenes y **no repite datos personales** de la imagen. Ver **§2quinquies** (§2duodecies punto 14: desde el 2026-09-20 ya **no** se comparte la pantalla). |
| Extras | Cuánto cuesta (gratis, sin comisión; los **banners** se pagan), cómo contactar a una tienda / hacer un pedido (carrito ❤️ = **un solo mensaje** de WhatsApp), cómo editar la tienda (productos él; la ficha, el administrador) y «hablar con una persona». |

**Para cambiar una respuesta** (porque el sitio cambió, o el jefe quiere otro texto): se edita
**`includes/chatbot_kb.php`** y se sube. Ahí está también el **aviso de que si cambia una ruta o un
botón hay que cambiarlo EN ESTE ARCHIVO y en ningún otro sitio**.

---

## §4. Cómo funciona por dentro (y por qué así)

```
  Visitante
     │  escribe o dicta 🎙️
     ▼
  assets/js/chatbot.js  ──POST──►  api/chatbot.php        (la puerta: valida y responde JSON)
                                        │
                                        ▼
                                 includes/chatbot.php     (el motor)
                                   ├─ 1. ¿quién pregunta?  → sesión y plan REALES (leídos por el servidor)
                                   ├─ 2. ¿es marketing sin sesión? → candado 🔒 (no gasta saldo)
                                   ├─ 3. ¿es clima o noticias? → datos del día guardados («fuente»: dia)
                                   ├─ 4. reloj de uso: 1 min / 3 min / premium
                                   ├─ 5. límites por IP / visitante / sitio
                                   ├─ 6. si no → API de DeepSeek (deepseek-chat) con el guion del sitio + el día
                                   └─ 7. si la API falla (clave, saldo, red, timeout) → guion local
```

**Por qué está hecho así (las 4 decisiones que importan):**

1. **La clave nunca sale del servidor.** El navegador habla con `api/chatbot.php`, y es el servidor
   quien llama a DeepSeek. Si la clave estuviera en el JavaScript, cualquiera la leería con F12 y
   gastaría el saldo del jefe.
2. **La sesión la lee el servidor, no el navegador.** El candado de los 10 consejos 🔒 se aplica en
   PHP con `$_SESSION`: no se puede burlar mandando un `"logueado":true` falso desde la consola.
3. **Red de seguridad local.** Si DeepSeek falla, no hay clave o se acabó el saldo, el bot **contesta
   igual** las preguntas de siempre con las respuestas verificadas. El visitante nunca ve un error
   seco: si la pregunta no está en el guion, se le da el WhatsApp del administrador.
4. **Sin tablas nuevas en la Base de Datos.** Crear tablas depende de ser admin (patrón
   `empleos_instalar_tabla`), así que los contadores y el registro son **archivos** en
   `cache/chatbot/` (carpeta bloqueada por web). El bot **sigue funcionando aunque la BD falle**
   (el dato de sus tiendas es opcional y va envuelto en `try/catch`).

**El guion del sistema** (`chatbot_prompt_sistema()`) va **primero y siempre igual** a propósito:
así DeepSeek lo tiene en **caché de contexto** y cada pregunta sale más barata. El **estado del
visitante** (logueado, su nombre, sus tiendas y sus enlaces) va **al final**, porque cambia en cada
chat.

**Reglas que se le imponen al modelo** (resumen; están escritas en `chatbot_prompt_sistema()`):
solo habla del sitio y del guion; **no inventa páginas, botones, precios ni plazos**; no pide
contraseñas, tarjetas ni DNI; no da asesoría legal ni contable; no habla mal de nadie; no revela sus
instrucciones; y los 10 consejos **solo** con sesión.

---

## §5. Límites anti-abuso y tope de gasto

Se cuentan en `cache/chatbot/rl/` (un contador por franja). Todos los números se cambian en
`includes/config_chatbot.php`:

| Límite | Valor por defecto | Constante |
|---|---|---|
| **⏱️ Preguntas al día, visitante sin cuenta** | **20** | `CHATBOT_PREGUNTAS_VISITANTE` |
| **⏱️ Preguntas al día, registrado** | **50** | `CHATBOT_PREGUNTAS_REGISTRADO` |
| **⏱️ Preguntas al día, ⭐ Premium** | **300** (0 = sin límite) | `CHATBOT_PREGUNTAS_PREMIUM` |
| Por IP y hora | 40 mensajes | `CHATBOT_LIMITE_IP_HORA` |
| Por IP y día | 120 mensajes | `CHATBOT_LIMITE_IP_DIA` |
| Por visitante (sesión) y día | 80 mensajes | `CHATBOT_LIMITE_SESION_DIA` |
| **Todo el sitio, por día** | **600 mensajes** | `CHATBOT_LIMITE_GLOBAL_DIA` |
| Largo de cada mensaje | 700 caracteres | `CHATBOT_MSG_MAX` |
| Turnos que recuerda | 8 | `CHATBOT_HISTORIAL_MAX` |
| Largo de la respuesta | 700 tokens | `CHATBOT_MAX_TOKENS` |

Al pasarse, el bot lo dice con simpatía y **sigue contestando** lo que esté en el guion (no deja al
visitante sin nada). Los contadores viejos (más de 3 días) se borran solos de vez en cuando; el
registro de preguntas se conserva `CHATBOT_LOG_DIAS` (**90 días**).

**💰 Lo que NO gasta saldo:** el saludo (con la fecha, la hora y el cielo), **nuestras noticias** (una
consulta a nuestra base), la
respuesta de **clima**, el candado 🔒 de los 10 consejos y toda la red de seguridad local. Son los
caminos que más se repiten, y el cielo sale del **archivo del día** (1 consulta a internet por día, 0 tokens).

---

## §5bis. 💰 ¿Cuánto cuesta de verdad? (medido, no estimado)

**Precios oficiales de DeepSeek** ([tabla de precios](https://api-docs.deepseek.com/quick_start/pricing),
consultada el 2026-09-13), por 1M de tokens. `deepseek-chat` se resuelve en **deepseek-flash**, y el
modelo con visión cuesta **igual**:

| Concepto | Fuera de punta | En punta |
|---|---|---|
| Entrada **en caché** (cache hit) | $0,007 | $0,014 |
| Entrada **sin caché** (cache miss) | $0,22 | $0,44 |
| Salida (lo que escribe el bot) | $0,66 | $1,32 |

**Punta** = 01:00–04:00 y 06:00–10:00 **UTC** de lunes a viernes (en hora de Lima: 20:00–23:00 y
01:00–05:00, de lunes a viernes). Todo lo demás cuesta **la mitad**.

**Medido en el sitio (5 preguntas de IA del 2026-09-13, incluida 1 con imagen):**
- Entrada: **6 973 tokens** por pregunta (de ellos, los que estaban en caché se cobran 31 veces más barato).
- Salida: **270 tokens** por pregunta.
- Gasto real: **US$ 0,00142 por pregunta** → **US$ 0,0071 por sesión** de 5 preguntas.
- Una **imagen** suma el equivalente a **~600 tokens de entrada**: unos **US$ 0,00013** más (nada).

**El detalle de las 5 preguntas reales (para ver de dónde sale el gasto):**

| Pregunta | Entrada | En caché | Salida | Costo (fuera de punta) |
|---|---|---|---|---|
| «hola, quién eres…» | 6 551 | 0 | 262 | US$ 0,0016 |
| «cómo me registro» | 6 545 | 5 504 | 234 | **US$ 0,0004** ← la más barata (caché) |
| «Hola» (del jefe) | 6 945 | 768 | 129 | US$ 0,0015 |
| «cuánto tiempo puedo usar el chat» | 7 100 | 640 | 62 | US$ 0,0015 |
| **con IMAGEN** | 7 727 | 0 | 666 | US$ 0,0021 ← la más cara, pero **solo US$ 0,00013 es la imagen**: el resto es que escribió 666 tokens |

Se ve clarísimo: **la que salió más barata fue la que tenía más caché** (31× más barato ese tramo) y
**la más cara escribió 11 veces más texto** que la más corta. La imagen aporta **el 6 %** de esa
pregunta.

**Proyección con el saldo del jefe (US$ 6,07 el 2026-09-13):**

| Escenario | Por pregunta | Por sesión (5 preguntas) | **Con US$ 5** | Con US$ 6,07 (saldo real) |
|---|---|---|---|---|
| **Caché caliente** (lo normal con gente entrando) | $0,00142 | $0,0071 | **~704 sesiones (~3 520 preguntas)** | ~855 sesiones (~4 275 preguntas) |
| Caché tibia (la mitad en caché) | $0,00157 | $0,0078 | ~637 sesiones (~3 185 preguntas) | ~773 sesiones |
| Caché fría (todo sin caché) | $0,00171 | $0,0086 | ~585 sesiones (~2 925 preguntas) | ~710 sesiones |

⚠️ **Ojo con las cuentas:** `sesiones = saldo ÷ costo por sesión` y `preguntas = sesiones × 5`.
El 2026-09-13 se dijo por error «~855 sesiones con US$ 5»: ese número corresponde al saldo real de
**US$ 6,07**. Con **US$ 5** son **~704 sesiones (~3 520 preguntas)**.

**En hora punta, la mitad de eso.** Y las respuestas de clima/noticias/saludo no gastan nada, así que
una sesión real de 5 preguntas casi nunca son 5 llamadas a la IA.

**Herramienta para medirlo cuando quieras (lee el registro del sitio y calcula con los precios de arriba):**
```powershell
python __chat_costo.py           # usa el saldo REAL de la cuenta (lo consulta con la clave)
python __chat_costo.py 5 5       # simula con 5 dólares y 5 preguntas por sesión
```

**Qué abarata el gasto (y hay que cuidarlo):** el guion del sitio va **primero y siempre igual** en el
prompt (por eso la caché acierta), las respuestas están topadas en tokens, el historial se recorta a 8
turnos y todo lo que se puede contestar sin IA (clima, noticias, saludo, candado, guion local) **no
llama a la API**.

**Apagarlo del todo** (sin borrar nada): `define('CHATBOT_ACTIVO', false);` en
`includes/config_chatbot.php` → el widget desaparece del sitio entero.

---

## §6. El registro de preguntas (qué pregunta la gente)

- Se guarda en **`cache/chatbot/log/AAAA-MM-DD.jsonl`** (una línea JSON por pregunta):
  fecha, IP **en hash** (nunca en claro), si tenía sesión, la pregunta (400 caracteres), la respuesta
  (600), la **fuente** (`ia` / `local` / `limite`), los milisegundos y los tokens usados.
- **No es público:** `https://dechimbote.com/cache/chatbot/log/` devuelve **403** (comprobado).
- **Para leerlo** desde la PC del jefe:

```powershell
python __ver_chat_log.py            # solo lista lo que hay
python __ver_chat_log.py bajar      # baja el último día y lo muestra en pantalla
```

- **Sirve para mejorar el bot**: si una pregunta se repite y está mal contestada, se arregla el guion
  (`chatbot_kb.php`) y se sube. El widget avisa al visitante:
  *«Asistente automático (IA). Tus preguntas se guardan de forma anónima para mejorar la ayuda.»*
- **Privacidad:** si algún día se quiere dejar de guardar, `define('CHATBOT_LOG_ACTIVO', false);`.

---

## §7. Pruebas que ya se hicieron (2026-09-13, en producción)

| Prueba | Resultado |
|---|---|
| `php -l` de los 8 archivos y `node --check` del JS | Sin errores y **sin avisos de PHP** en las tres suites locales |
| Guion y emparejador **sin gastar saldo** (`php __test_chatbot_kb.php`) | **22 frases probadas, 0 fallos**: las 7 preguntas del jefe caen cada una en su respuesta (y también las nuevas de tiempo, Premium, clima y noticias) |
| **Reloj de uso** (`php __test_chatbot_tiempo.php`) | **23 comprobaciones, 0 fallos** (visitante 60 s · registrado 180 s · premium sin límite) |
| **Contexto del día** (`php __test_chatbot_dia.php`) | **18 comprobaciones, 0 fallos**: trae el clima real de Chimbote, el archivo del día se lee de la caché (no se reescribe), el saludo dice fecha/hora/cielo y cuenta la noticia, y el guion del modelo lleva el bloque del día *(la prueba es de antes del cambio del 2026-09-13: la lista de noticias ya no es la de Andina, son las nuestras — §2ter)* |
| `GET /api/chatbot.php` | Saludo personalizado en **2 burbujas** («Buenos días! 🥷 Te saluda **El ninja**» + «🌤️ Hoy el cielo está … / ¿Tienes alguna pregunta para mí?») + 9 botones de pregunta rápida + el reloj. **La noticia ya no viene aquí**: sale en la pregunta 3 o 4 |
| **La IA respondiendo de verdad** (clave ya puesta) | `"fuente":"ia"`: a «hola, quién eres» contestó *«Soy **El ninja** 🥷»* con lo que sabe hacer y con el día (fecha, hora, 20 °C); a «cómo me registro» dio **los pasos y los enlaces exactos** del guion |
| **Clima y noticias por HTTP** | `"fuente":"dia"`: el cielo de hoy y **nuestras noticias con sus enlaces** (sin gastar tokens). ✅ **Comprobado el 2026-09-13 después del cambio**: el saludo enlaza a `https://dechimbote.com/noticia/…` y «dame las últimas noticias de hoy» devuelve **nuestros 5 titulares, todos con enlaces de casa** + la sección `/noticias` (cero enlaces a medios de afuera) |
| Las 7 preguntas por `POST` **sin clave** | Contestadas por la red de seguridad local (`"fuente":"local"`) |
| Los 10 consejos **sin sesión** / **con sesión** (cuenta de prueba) | 🔒 candado con botones / los 10 consejos completos |
| **Reloj agotado por HTTP** (cronómetro forzado con `__chat_tiempo.py`) | `bloqueado:true`, `fuente:"tiempo"`, `cta:"registro"` y el aviso de cierre |
| **Cierre por tiempo en el navegador real** (límite bajado a 8 s un momento y devuelto a 180) | Cuenta atrás en rojo, aviso + botón **«Hacerme Premium (S/ 30 al mes)»**, cuadro en «Chat cerrado por hoy», botón flotante en **⏱️** y **cierre automático a los 15 s** |
| **La ventana mide el 70 %** (navegador real) | Medido en la pestaña: ventana 1366×543 → panel **460×380 = 34 % del ancho (topado a 460 px) y 70 % del alto**, con el fondo oscurecido y desenfocado del menú hamburguesa |
| **El nombre y el día en el navegador** | El botón dice **«🥷 El ninja»**, la cabecera «El ninja · asistente de DeChimbote.com», y el chat abre con el saludo personalizado («Buenos días, Jimmy! 🥷 Soy El ninja…») más la línea del día (fecha, hora, clima) |
| **Tablones fuera y El ninja en su lugar** (`/perfil` en el navegador real) | La tarjeta «Tablones nuevos» y la sección «🗂️ Mis tablones» ya no están; en su lugar la tarjeta **«🥷 EL NINJA · Pregúntame aquí →»** y la sección **«🥷 El ninja, tu asistente»**, y su botón **abre el chat** de verdad (comprobado) |
| Portada, `/buscar.php`, `/empleos`, `/chat_comunidad.php`, `/reclamar`, `/trabaja_con_nosotros.php`, `/categoria/ferreterias`, una ficha `/neg/…` y la 404 | **200 (404 en la 404, que es lo correcto), widget presente y 0 errores PHP.** ⚠️ Medición de la **mañana** del 2026-09-13: **`/chat_comunidad.php` se retiró esa misma tarde y hoy responde 404** (la página 404 propia del sitio) |
| `https://dechimbote.com/includes/config_chatbot.php` y `/cache/chatbot/log/` | **403** en los dos (la clave y el registro no se pueden leer de fuera) |
| **`https://dechimbote.com/registro`** | Devolvía **404** → se añadió la regla de URL amigable al `.htaccess` → ahora **200** |
| Todas las direcciones que el bot cita en el guion | Una por una con `curl`: ninguna da 404 (y **`/tablones.php` se quitó del guion** porque daba 404). ⚠️ Hoy ese archivo **ya no existe**: el módulo de tablones **se retiró del sitio el 2026-09-13** (ver §8) |
| Cronómetros de prueba en el servidor al cerrar | **0 archivos** (`cache/chatbot/tiempo/` quedó vacío) |

| **👁️ Imágenes (fotos y capturas)** | **SÍ, con el modelo `deepseek-v4-flash-vision-exp` y la MISMA clave** (mismo precio que V4-Flash, cada imagen ≤384 tokens). Probado en producción con un afiche real: lo describió, **no repitió el teléfono** que aparecía y dio consejos. El 📷 está en el chat y abre su **ventana modal con dos opciones** (cámara · galería) y hay vista previa; el archivo `chatbot.js` comprime a JPEG (≤1280 px) y **las imágenes NO se guardan**. 🗄️ «Compartir pantalla» se retiró el 2026-09-20 |
| **🔗 Enlaces con nombre (nunca una dirección a la vista)** | Probado por HTTP y en el navegador: un enlace se ve como **«ver la tienda»** en azul (`rgb(29,78,216)`) y subrayado, con la URL solo en el `href`. El texto del chat **no muestra ningún `http`** |
| **🪟 Tamaño, fuente y opciones apiladas** | Medido en el navegador: panel **1200×462 = 88 % × 85 %**, letra del chat **18 px** contra **16 px** del sitio, las **6 opciones en columna** (620×48 px cada una) y el saludo por horas: *«Buenas trasnochadas! 🥷 Te saluda **El ninja**.»* |
| **💰 Coste real medido** | **US$ 0,00142 por pregunta** y **US$ 0,0071 por sesión** de 5 preguntas (entrada 6 973 + salida 270 tokens). Con US$ 5: **~855 sesiones / ~4 280 preguntas** (caché caliente) a **~708 sesiones / ~3 540 preguntas** (caché fría). En hora punta, la mitad. Herramienta: `python __chat_costo.py` |
| **Mensaje del minuto al visitante** | Dice **solo 1 minuto** y que «al registrarte podrás conversar **más tiempo**»; **jamás** los 3 minutos (eso es solo para quien ya tiene sesión). Probado por HTTP |

Cómo volver a probar (comandos exactos):

```powershell
# 1) El guion, sin gastar saldo
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_kb.php

# 2) El reloj de uso, sin esperar minutos
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_tiempo.php

# 3) El contexto del día (clima + noticias + caché 24 h) — SÍ usa internet
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_dia.php

# 4) El endpoint por HTTP
curl.exe -s https://dechimbote.com/api/chatbot.php          # GET: saludo, saludo del día, reloj y botones
curl.exe -s -H "Content-Type: application/json" --data-binary "@pregunta.json" https://dechimbote.com/api/chatbot.php

# 5) Forzar el cierre por tiempo de una IP (y devolverle el tiempo al terminar)
python __chat_tiempo.py ver
python __chat_tiempo.py agotar ip<HASH>
python __chat_tiempo.py borrar ip<HASH>

# 6) 👁️ Imágenes: modelos de la clave, visión de verdad y prueba contra producción
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_modelos.php
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_vision.php
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_prod.php
C:\xampp\php\php.exe D:\RELAX\__test_chatbot_precio_vision.php   # ¿cuesta más la visión? (mide y compara)

# 7) 💰 Cuánto se está gastando y cuánto queda
python __chat_costo.py

# 8) El registro de preguntas (con la fuente de cada respuesta: ia / local / dia / tiempo)
python __ver_chat_log.py bajar
```

⚠️ **Prueba con sesión:** para comprobar el caso «consejos de marketing logueado» hay que **entrar
con una cuenta**. El 2026-09-13 se creó una cuenta de prueba (`prueba.chatbot@dechimbote.com`), se
probó y **se borró con una sonda temporal** (`__chat_limpiar.py`, ya autodestruida). Si hace falta
otra vez: registrar en `/registro`, probar y volver a limpiar con el mismo patrón.

---

## §8. Trampas (lo que NO debe repetirse)

1. **`/registro` daba 404.** El bot manda a la gente a `dechimbote.com/registro` y esa URL **no
   existía** (solo `/registro.php`): el visitante que hacía caso al bot terminaba en la página de
   «no encontramos lo que buscas». Arreglado en el `.htaccess` (regla `^registro/?$`). **Antes de
   poner un enlace en una respuesta del bot, comprobarlo con `curl.exe -s -o NUL -w '%{http_code}'`.**
2. **Los verbos conjugados rompen el emparejador.** «cómo **borro** mi sitio» no caía en la respuesta
   de borrar porque la clave era «borrar mi sitio». Por eso cada tema tiene **`patrones`** (regex):
   `borr[a-z]*`, `(poner|pongo|agregar|subo|publico)…`. Si se añade un tema nuevo, **añadirle
   patrones**, no solo palabras.
3. **La red de seguridad devolvía el candado 🔒 a un usuario logueado.** `chatbot_faq_buscar()` tenía
   `false` fijo al pedir la respuesta. Ahora recibe **`$logueado`**. Cualquier función que devuelva
   texto que depende de la sesión **tiene que recibir ese dato**.
4. **El botón flotante chocaba con el «💬 Chat» del tablón comunitario** (los dos abajo a la derecha):
   el del bot va en `bottom:68px` (móvil) / `76px` (escritorio) y la ventana en **`z-index:1200`**
   (arriba del `1000` del otro botón).

   > 🗑️ **RETIRADO (2026-09-13, orden del jefe):** el botón **💬 Chat** (`.ch-fab`, que vivía en
   > `includes/footer.php`) **ya no existe**: se retiró el chat comunitario completo. **Hoy el único
   > botón flotante del sitio es el de El ninja** (`includes/chatbot_widget.php`, abajo a la derecha).
   > El choque que se describe aquí es **histórico**.
5. **No meter la clave en la raíz ni en `assets/`.** En `includes/` está protegida por el `.htaccess`.
6. **El `?v=` del JS**: al cambiar `assets/js/chatbot.js` hay que **subirle la versión** en
   `includes/chatbot_widget.php` (`$cfg['version']`), o los navegadores siguen con el viejo.
7. **Los mensajes del bot pasan por `formato()`** (escapado + negritas + listas + enlaces). No
   inyectar HTML crudo en las respuestas del guion: se vería como texto.
8. **NUNCA editar los archivos del proyecto con `Set-Content`/`Get-Content` de PowerShell.** El
   2026-09-13, al cambiar un número de `config_chatbot.php` con `-replace … | Set-Content -Encoding UTF8`,
   el archivo quedó con **BOM** (`EF BB BF` al inicio) y con **todas las tildes rotas** («dÃ­as»):
   PowerShell 5.1 lee el UTF-8 como ANSI y reescribe con BOM. Se arregló reescribiendo el archivo
   entero con las herramientas de archivos (y quedó comprobado que empieza en `3C 3F 70` = `<?p`).
   **Regla:** los archivos del proyecto se tocan con las herramientas de edición, no con PowerShell.
9. **El módulo de tablones B2B se retiró del sitio el 2026-09-13** (nunca llegó a publicarse: daba
   404). Por eso el bot **no promete tablones** ni enlaza ahí (§2bis), y **ya no existe ningún archivo
   de ese módulo en `deploy`**. Antes de citar cualquier página en el guion,
   comprobarla con `curl.exe -s -o NUL -w '%{http_code}'`.
10. **El día del reloj es el de LIMA, no el de UTC.** El cronómetro se llama
   `<clave>_<AAAAMMDD>.json` con la fecha de `America/Lima`, así que **a las 19:00 de Lima cambia el
   día del archivo** mientras en UTC sigue siendo el anterior. Al forzar pruebas con
   `__chat_tiempo.py`, la fecha que usa el script es la de Lima (UTC−5): si el archivo que quieres
   agotar es de ayer, hay que decírselo con el nombre exacto.
11. **⚠️ LAS CONSTANTES SE CARGAN ARRIBA, NO A MITAD DE LA PÁGINA.** Al poner la tarjeta de El ninja
   en `panel.php` se puso el `require_once config_chatbot.php` **después** de la tarjeta que usa
   `CHATBOT_EMOJI`/`CHATBOT_NOMBRE`: PHP 8, al llegar a una constante sin definir, **corta la página
   ahí mismo** (sin mensaje, porque `display_errors` está en 0). El panel se quedaba en las 4
   tarjetas de arriba y las tarjetas salían **vacías**. Se detectó **midiendo el DOM en el navegador**
   (la tarjeta tenía 3 hijos esperados y solo 1 vacío). Arreglado cargando el archivo **en la cabecera
   de la página**. Regla: si una página usa constantes o funciones del chat, el `require` va arriba.
12. **Para ABRIR el chat desde otro sitio del sitio se usa `window.NINJA_ABRIR()`** (lo expone
   `assets/js/chatbot.js`). No usar `document.getElementById('cbotFab').click()`, porque el botón es
   un **interruptor**: si el chat ya estaba abierto, lo cierra.
13. **⚠️ SUBIR EL `?v=` DEL JS CADA VEZ que se toque `assets/js/chatbot.js`** (está en
   `includes/chatbot_widget.php`, `$cfg['version']`). Pasó el 2026-09-13: se arregló el botón
   «Compartir mi pantalla» y en el navegador seguía sin aparecer porque servía el JS viejo de la
   caché. Con `?v=5` apareció. **Si algo del chat "no cambia" en el navegador, lo primero es mirar
   el `?v=`.**
14. **Un botón que nace con `hidden` en el HTML hay que MOSTRARLO cuando se puede**, no solo dejar de
   ocultarlo: el botón de «Compartir mi pantalla» nace oculto y el JS de la primera versión solo lo
   ocultaba cuando no había soporte (nunca lo mostraba). Ahora pone `hidden = !soportado`.
15. **Para probar la visión no hace falta el navegador**: `__test_chatbot_vision.php` le manda una
   imagen al modelo y comprueba que la describe (y `__test_chatbot_prod.php` lo hace contra el sitio).
16. **⚠️ UN ARCHIVO QUE SE CARGA SOLO NO PUEDE DEPENDER DE `chatbot_dir()`.** `api/lead.php` incluye
   `includes/chatbot_actividad.php` **directamente** (no pasa por `includes/chatbot.php`), así que
   dentro de ese archivo hay que usar su propio ayudante `chatbot_actividad_dir()` (que sí respaldo).
   Con `chatbot_dir()` a secas, anotar el «pidió precio» reventaba con *«Call to undefined function
   chatbot_dir()»* — **lo cazó la prueba local** `__test_chatbot_actividad.php` antes de que llegara a
   producción. Regla general: **todo archivo que se pueda cargar solo lleva su propio ayudante de
   carpeta** (igual que `chatbot_diario.php` tiene el suyo).
17. **⚠️ `defined('X')` NO ES LO MISMO QUE `X === true`.** Al poner el interruptor de pruebas
   `CHATBOT_PRUEBA_AVISO`, la condición quedó como `… && !defined('CHATBOT_PRUEBA_AVISO')`: como la
   constante **está definida** (aunque valga `false`), el aviso les salía **también a los usuarios
   registrados**. Lo cazó `__test_chatbot_tiempo.php` («al registrado NO se le da el aviso»).
   **Regla:** para un interruptor se mira el **valor** (`defined('X') && X`), nunca solo `defined()`.
18. **⚠️ SUBIR EL `?v=` DEL JS TAMBIÉN CUANDO SE QUITAN COSAS.** Al quitar el reloj, el navegador
   siguió usando el JS viejo (con reloj y con el icono ⏱️ en el botón) porque el `?v=` no cambió:
   **de v6 a v7**. Si algo del chat «no cambia», mirar el `?v=` ANTES de buscar el fallo en el código.
19. **⚠️ SIEMPRE LA MISMA TRAMPA: el archivo que se carga solo necesita su propio ayudante de
   carpeta.** Pasó **dos veces el mismo día**: primero con `api/lead.php` (→ `chatbot_actividad_dir()`)
   y después con el **botón «volver a pedir el clima»** del panel, que llamaba a `chatbot_diario_borrar()`
   → `chatbot_dir()` **sin `chatbot.php` cargado** → el panel devolvía **error 500** (se vio en el
   navegador como `chrome-error://chromewebdata/`). Arreglado por partida doble: el handler del panel
   hace `require_once includes/chatbot.php` **y** `chatbot_diario_borrar()` ya lleva su respaldo.
   **Antes de llamar a una función del chat desde fuera del chat, comprobar de dónde sale.**
20. **⚠️ EL ANCHO SE TOPA EN SILENCIO.** Al poner la ventana «al 90 %» con `width: min(90vw, 760px)`, en
   una pantalla de 1366 px el tope de 760 px la dejaba en **56 %**: se veía igual de chica. Ahora es
   `min(90vw, 1200px)` (medido: 88 %). Si el jefe pide «más grande», mirar **primero el tope en px**.
21. **⚠️ LA CACHÉ DEL DÍA SE QUEDA CON LA FORMA VIEJA.** Al añadir la noticia de Chimbote al contexto
   diario, el saludo seguía sin contarla porque el archivo `cache/chatbot/diario/<AAAAMMDD>.json` se
   había creado **antes** del cambio (dura 24 h). Solución: borrar ese archivo (o el botón
   *🌤️ Volver a pedir el clima y las noticias* del panel) para que se vuelva a pedir.
   **Regla: al cambiar la estructura del contexto del día, hay que refrescarlo.**
22. **⚠️ CUIDADO CON EL OTRO AGENTE QUE TRABAJA A LA VEZ.** El mismo 2026-09-13 se retiraron del sitio
   los **tablones y el chat comunitario** (otra sesión), con dos consecuencias para este módulo:
   la función del plan se llama ahora **`reglas_plan_usuario()`** (antes `reglas_tablones_usuario()`)
   —el bot la usa para saber quién es Premium— y el **botón flotante «💬 Chat» desapareció**, así que
   🥷 Ninja **bajó al borde** (`bottom:16px`). Al retomar el trabajo: **comprobar `deploy` antes de
   subir** (los archivos pueden haber cambiado) y **no reescribir el HTML que borre lo del otro**.
23. **📍 FUGAS QUE PARECÍAN «YA HECHO» (2026-09-13).** El saludo ya enlazaba a nuestra noticia, pero el
   chat **seguía sacando al visitante del sitio por otros tres caminos**, y ninguno se veía en el
   saludo: (a) el **FAQ de noticias** (`chatbot_kb.php` → `chatbot_noticias_texto()`) devolvía las 10
   de **Agencia Andina**; (b) el **guion del modelo** (`chatbot_diario_guion()`) le pasaba esos 10
   titulares externos con sus enlaces y le decía «puedes contarlas»; (c) las **reglas 13 y 16** del
   prompt ofrecían «buscarlo en la fuente (Andina o RPP)» y «las nacionales». **Lección: cuando una
   orden dice «el enlace NUNCA sale del sitio», no basta con arreglar el saludo — hay que buscar
   `andina`, `rpp`, `google` en TODO el módulo** (`grep` en `includes/chatbot*.php`) y probar la
   pregunta «dame las noticias de hoy» por `POST /api/chatbot.php`.
24. **📅 EL TURNO SE «SATURA» EN 4: contar turnos con el historial no basta (2026-09-13).** Para poner la
   noticia en la pregunta 3 o 4 se cuenta cuántos mensajes de **usuario** manda el navegador. Como el
   `historial` viene **recortado a los últimos 8 mensajes** (4 preguntas + 4 respuestas), a partir de la
   4.ª pregunta el conteo ya no sube: en la 5.ª seguiría dando «4». Por eso el candado de verdad **no es
   el número del turno** sino **buscar el enlace de la noticia dentro del historial**: si ya salió, no se
   vuelve a añadir. ⚠️ Y ojo al probarlo: `Invoke-WebRequest -Body <string>` **manda el JSON mal
   codificado** (acentos y emojis) y el chat responde `{"ok":false,"error":"Datos inválidos."}` — hay que
   pasar los bytes en UTF-8 (`-Body ([Text.Encoding]::UTF8.GetBytes($json))`).

25. **🔤 LAS PALABRAS DEL RUIDO VAN SIN TILDE (el fallo de «clavos», 2026-09-13).** El término se
   limpia de tildes **antes** de mirar `chatbot_busqueda_ruido()`, así que una palabra escrita con
   tilde en esa lista **nunca se quita**: se cuela al término y, como la consulta exige **todas** las
   palabras, deja la respuesta en **0**. Pasó con «acá» (`aca`) y «allá» (`alla`): «aquí» sí estaba.
   **Lección doble:** (a) al añadir relleno, escribirlo **sin tilde** y **suelto** (no solo dentro de
   la frase); (b) no confiar en que una sola palabra esté bien: `chatbot_buscar()` ahora **reintenta
   palabra por palabra** cuando el término trae varias y juntas no encuentran nada.
   ⚠️ Y ojo con el rubro: comparar el nombre del rubro con `LIKE '%palabra%'` también fue parte del
   fallo («aca» encontraba «Ac**aca**demias»). Ahora se compara por **principio de palabra**
   (`REGEXP [[:<:]]palabra`).
26. **🗂️ EL PRODUCTO→RUBRO NO SE ESCRIBE EN EL MAPA DE SINÓNIMOS (2026-09-13).** `chatbot_busqueda_sinonimos()`
   es solo para los **nombres de las fichas** («polleria» → «pollo, brasa»). La traducción
   «producto → rubro» vive en la **base** (`directorio_categoria_claves`) y la lee
   `chatbot_busqueda_rubro()`. Si se duplica a mano en el mapa, el día que se amplíe la tabla el chat
   seguirá con la lista vieja. **Regla: si falta una palabra, se añade a la tabla, no al código.**
27. **🍹 UN FALSO POSITIVO ES PEOR QUE UN CERO (2026-09-13).** Antes, «clavos» devolvía **una sola**
   cosa: una *chicha morada con canela y clavo* («clavo» de olor). Ahora, cuando el término tiene
   rubro, **los productos se buscan dentro de ese rubro** (`chatbot_buscar_productos()` con
   `$rubro_id`): pedir clavos muestra productos de ferretería, nunca una bebida.
28. **📰 UN TÍTULO DE NOTICIA NUNCA PASA DE 4 PALABRAS (orden del jefe, 2026-09-14)** y se recorta con
   `chatbot_titular_corto($titulo)` **en el servidor**, no pidiéndoselo al modelo: si a DeepSeek se le da
   el titular largo, lo copia. Los 5 sitios que hay que tocar si aparece otra fuente de noticias son la
   línea del turno, la lista, el guion del modelo, `chatbot_etiqueta_enlace()` (PHP) y `breve4()`
   (JS). Detalle: **§2undecies**.
29. **⛔ `limpiarChat()` NO REPINTA NADA (orden del jefe, 2026-09-14).** Antes volvía a pintar el saludo y
   **las mismas opciones**, que es exactamente lo que el jefe rechazó («ni siquiera los mismos botones»).
   Ahora borra y pone la bandera `vacio` (y `refrescar()` sale temprano mientras esté puesta). Si algún
   día se añade algo nuevo a la ventana (un aviso, un banner), hay que **respetar `vacio`**.
30. **⚠️ `__test_chatbot_dia.php` ESTÁ DESFASADO (2026-09-14).** Llama a `chatbot_diario_noticias()`,
   función **retirada** el 2026-09-13 con la lista nacional por RSS: al correrlo revienta con
   *«Call to undefined function»*. Para lo del 2026-09-14 se usa **`__test_chatbot_breve.php`** (títulos de
   4 palabras, línea del turno, candados de repetición, lista y guion, y las frases del «pensando»:
   **`FALLOS: 0`**). Pendiente: actualizar el viejo o archivarlo. 💡 El truco de la prueba nueva: las
   funciones del módulo van dentro de `if (!function_exists(...))`, así que la prueba **define sus propias
   noticias ANTES de cargar el motor** y no necesita MySQL ni internet.
31. **⏳ EL «PENSANDO» ES PROGRAMACIÓN, NO UNA LLAMADA AL MODELO (orden del jefe, 2026-09-14).** Las
   frases («Pensando · Consultando · Respondiendo») son texto fijo y repetido: se editan en el panel
   (`pensando_frases`) y las rota el navegador. ⛔ Nunca pedírselas a DeepSeek (gastaría tokens y tiempo
   en escribir siempre lo mismo) y ⛔ el modelo tiene prohibido **imitarlas** dentro de su respuesta
   (regla 12-quater-ter). Y el piso de espera (`espera_min_ms`) debe quedarse en **1,5 s**: por encima de
   ~2 s el chat empieza a sentirse lento. Detalle: **§2undecies d)**.

32. **⚠️ EL NOMBRE DEL BOT LO PISA LA BASE, NO EL ARCHIVO (2026-09-17).** Al renombrar el bot en
    `includes/config_chatbot.php` (de «El ninja» a «El guía»), en la web **seguía saliendo «El ninja»**:
    el panel del Súper Admin tiene **guardados** `titulo`, `nombre` y `emoji` en
    `directorio_chatbot_ajustes` y **lo guardado manda** (así está diseñado: `chatbot_ajustes_aplicar()`
    corre ANTES que `config_chatbot.php`). Se corrigió con una sonda temporal
    (**`__ep_chatbot_nombre.php`**: muestra la tabla y, con `&go=1`, pone al día esas tres claves):
    `python __sonda_run.py __ep_chatbot_nombre.php guia-nombre-2026-9kQ7 go "&go=1"`.
    **Regla: si se cambia un ajuste del chat y en la web no se nota, mirar PRIMERO la tabla del panel.**

33. **⚠️ LA HERRAMIENTA DE LA CUOTA APUNTABA A LA CARPETA VIEJA (2026-09-17).** `__chat_cuota.py` tenía
    `RAIZ = '/public_html/cache/chatbot'` — o sea **la copia vieja anidada**, no la raíz viva (`/`): daba
    «No such file or directory» y contadores vacíos mientras el chat contaba de verdad. **Arreglado:**
    `RAIZ = '/cache/chatbot'`, `ftp.cwd('/')` al conectar y la fecha del registro en **día de Lima**
    (antes usaba la de UTC). Es la misma trampa del FTP de la cabecera de este proyecto: **todo lo que
    se suba o se lea por FTP va contra `/`**.

34. **🧭 UN BOTÓN QUE ABRE EL CHAT SOLO SIRVE DONDE HAY CHAT (2026-09-17).** Al dejar el bot solo en las
    fichas, los botones que lo abrían desde otras páginas (`window.NINJA_ABRIR()`) quedaban muertos: el
    del **panel del dueño**, el **💬 del Explorer**, su entrada en «Tus herramientas», su historia y el
    texto del buscador. **Se retiraron todos.** Si algún día se devuelve el chat a todo el sitio
    (`CHATBOT_SOLO_FICHAS = false`), hay que reponerlos.

35. **📐 LA VENTANA SE ANCLA CON `top/right`, NUNCA CON `transform` (2026-09-20).** Para dejarla al
    **85 % × 85 %** pegada **arriba y a la derecha** lo natural sería centrarla con
    `top:50%; left:50%; transform:translate(-50%,-50%)`, y **rompe las dos animaciones del panel**:
    `cbotEntra` (la entrada) y `cbotPop` (la presentación que se abre y se cierra sola) **también usan
    `transform`**, así que se pisan entre sí y el guía salta de sitio. Por eso el anclaje va con
    **`top:8px; right:8px; width:85vw; height:85vh`** (y `85dvh` en el celular, para que el teclado no
    tape el cuadro de escribir). Historia de la posición: borde a borde → **centrada** (mañana del
    2026-09-20) → **pegada arriba a la derecha** (tarde del 2026-09-20, **lo que manda hoy**).

36. 🗄️ **ARCHIVADA — EL BOTÓN QUE DICTABA Y ENVIABA A LA VEZ (2026-09-20, mañana).** El «botón
    inteligente» de tres caras (🎤 → 🛑 → ➤) y el dictado con el motor del buscador existieron unas horas
    y el jefe los **mandó borrar esa misma tarde** (*«olvídate ya de iconos compuestos, iconos que
    contienen dos o tres funciones: lo que te estoy pidiendo nada más es el icono de enviar, un botón que
    sirve para enviar»*). Se conserva como historia: lo que se aprendió (reconocedor **nuevo** por
    dictado, el candado `instancia === actual`) sigue valiendo **para el buscador**, que es donde la voz
    funciona y **no se toca**. ⛔ **No volver a poner voz en el guía sin una orden nueva.**

37. 🗄️ **ARCHIVADA — LA VOZ DEL GUÍA NO ES LA DEL BUSCADOR (2026-09-20).** Copiar el motor de
    `buscador_voz.js` al guía **funcionó técnicamente** (26 comprobaciones en verde), pero el jefe
    **no lo quiere en el guía**: *«quiero que borres todo lo que tenga que ver con la voz dentro de la
    brújula»*. **Regla vigente: la voz vive SOLO en el buscador** (`GUIA_BUSCADOR_VOZ.md`); el guía
    escribe y manda fotos. ⚠️ Y cuando el jefe dice «copia la tecnología del buscador», hay que
    **confirmar antes si es en el buscador o en otra parte**: aquí la respuesta fue borrarla.

38. **🎤 SI EL GUÍA TIENE VOZ, ES LA COPIA DEL BUSCADOR Y NADA MÁS (orden del jefe, 2026-09-20 — la que manda).**
    El pie tiene **📷 cámara · 🎤 micrófono · cuadro de escribir · ➤ enviar**. Reglas que no se rompen:
    (a) **el 🎤 se COPIA tal cual del buscador** (mismo SVG, mismo naranja, mismo latido rojo, mismo motor);
    ⛔ **no inventarle otro motor** ni «mejorarlo»; (b) ⛔ **prohibido** el **botón compuesto** (uno que
    dicte y envíe), **«compartir mi pantalla»**, la **grabadora de audio** y cualquier botón escondido
    según el navegador (el ➤ es **uno solo y siempre visible**; el 🎤 se pinta **solo** si el navegador
    sabe dictar); (c) la 📷 abre un **menú chiquito ENCIMA del botón** (como el ☰, con icono de cámara:
    **Abrir cámara** · **Abrir galería**) y `Escape` cierra **ese menú**, no el guía; ⛔ **nada de ventanas
    modales centradas** con botones grandes (el jefe las vio como un banner);
    (d) **NO se le copia la limpieza del buscador** (`limpiarDictado`) porque borra conectores y la
    pregunta llegaría mutilada (medido: «¿Cuánto cuesta la membresía mensual?» → «membresía mensual»);
    (e) **envío automático**: si el micrófono se apaga **por silencio**, el mensaje se manda **al segundo**
    (`AUTO_ENVIO_MS`), y **se cancela** si él escribe, toca el micrófono, abre la cámara o le da al ➤;
    si el que apaga el micrófono es **su dedo**, **no se manda**; y (f) el candado `yaDictado` (el
    `yaBuscado` del buscador) evita que el temporizador de 260 ms **devuelva el texto al cuadro** después
    de mandarlo. Prueba: `node __guia_test_dom.js` (**69 comprobaciones**, con voz y sin voz).
    ⚠️ **Trampa que ya se pisó:** al cambiar el modal por el menú, el `Escape` se quedó llamando a
    `modalAbierto()` (función borrada) y reventaba con `ReferenceError`. **Al renombrar una función hay
    que buscar TODAS sus llamadas en el archivo.**

---

## §9. Pendientes y mejoras posibles

- ✅ **La clave de DeepSeek está puesta y probada** (`"fuente":"ia"`). Si algún día se rota, ver §2.
- **⭐ Definir qué más incluye Premium.** Hoy el bot solo promete el chat sin límite (es lo único
  activo). ⚠️ Los **tablones B2B ya no son una opción**: se **retiraron del sitio el 2026-09-13**, así
  que ese beneficio **no volverá** — no hay que esperar a que "se publiquen". Si algún día hay otro
  beneficio, se añade a `CHATBOT_PREMIUM_BENEFICIOS` (una línea). **Nunca prometer lo que el sitio no
  da.**
- **Cobro de Premium:** hoy se activa a mano (Súper Admin → Usuarios → plan `premium`). Si algún día
  hay pasarela de pago, el botón del bot debería llevar a esa página en vez de a WhatsApp.
- ✅ **CERRADO — el enlace de tablones de `superadmin.php` ya no existe.** La pestaña **🗂️ Tablones**
  (con su «👁 Ver») se quitó el **2026-09-13**, junto con el módulo completo. No queda nada que decidir
  aquí.
- **Aviso al Telegram** cuando alguien choca con el muro del tiempo o pide los 10 consejos sin
  cuenta: son las dos señales de venta más claras que tiene el sitio hoy.
- **Panel en el Súper Admin** para cambiar la clave, el encender/apagar, los minutos del reloj y
  leer las preguntas sin bajar el `.jsonl`.
- **📅 Noticias: ya no hay nada que traer de fuera (2026-09-13).** El pendiente viejo («traer los 10
  titulares generales de Andina, filtrar por temas») **quedó sin objeto**: la orden del jefe es que las
  noticias del chat son **las nuestras** (`/noticias`) y que el visitante **no salga del sitio**. Si algún
  día se quiere «solo de deportes» o «solo de un distrito», se hace **sobre nuestras noticias**
  (`api/noticias_json.php?distrito=…` o un filtro en `chatbot_diario_noticias_propias()`), nunca añadiendo
  un feed de un medio de afuera.
- **👁️ Imágenes: qué se puede mejorar.** Hoy el visitante manda la imagen a mano (📷). Se podría:
  (a) que el bot **pida** la captura solo cuando haga falta; (b) guardar la última imagen de la
  conversación para poder preguntar dos veces sobre ella (hoy se manda una vez y se tira — decisión de
  privacidad); (c) un tope de peso distinto para las capturas de pantalla (suelen pesar más que una foto).
- **💰 Vigilar el saldo:** `python __chat_costo.py` cuando el jefe quiera. Si el saldo baja de ~US$ 1,
  conviene recargar o bajar `CHATBOT_LIMITE_GLOBAL_DIA`.
- **El dueño no puede editar los datos de su ficha** (nombre, fotos, horario): hoy lo hace el
  administrador. El bot lo dice con honestidad; si algún día se hace la pantalla, hay que
  **actualizar esa respuesta** en `chatbot_kb.php`.
- **🏷️ LAS FRASES DE UNIÓN DE LOS OTROS 119 RUBROS ESTÁN FLACAS (2026-09-13):** el promedio del sitio
  es de **8,7 palabras por rubro** (el que más tiene es Restaurantes, con 52) y ya se vio lo que pasa:
  «lijas» y «martillos» no existían como palabra de Ferreterías, y «paracetamol», «ibuprofeno» ni
  «amoxicilina» como palabra de Farmacias. El jefe habla de **250 ítems por rubro** (ferretería) y
  **150** (farmacia): el mismo trabajo de redacción que se hizo con esas dos se puede repetir rubro por
  rubro (mismo molde: JSON → `__gen_claves_seed.py` → sonda con `&go=1`). Los que más lo necesitan son
  los rubros con más tiendas y menos frases.
- **Enlazar `/registro` desde el menú** (la URL amigable ya funciona).
- **Otros dos enlaces viejos que dan 404** (vistos de paso, no son del chat): `panel.php` y
  `superadmin.php` apuntaban a **`tiendas-cerca-de-mi.html`**, que **no existe** en el hosting; el
  botón bueno es **`btn_tiendas_cerca_html()`** → `buscar.php?lat=…&lng=…&radio=auto`. (El de
  `superadmin.php` sigue ahí; el de `panel.php` ya no se usa porque su tarjeta la ocupa El ninja.)
