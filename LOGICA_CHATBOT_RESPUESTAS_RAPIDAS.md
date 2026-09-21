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


# 🧠 LÓGICA DEL CHATBOT — RESPUESTAS RÁPIDAS, PERSONALIZADAS Y CON LOS RECURSOS DEL CELULAR

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Estado: ✅ APROBADA E IMPLEMENTADA el 2026-09-13 (mismo día).** Las 4 decisiones del §7 las tomó el
> jefe así: **1)** el registro se nombra con **Google / correo / el asistente** (él aclaró que cuando dijo
> «WhatsApp» se refería **al registro con el asistente**; con WhatsApp **no** se crea cuenta);
> **2)** el texto del enlace es **PUBLICAR MI NEGOCIO**; **3)** respuestas de **25 palabras**;
> **4)** la ubicación se pide **dentro del chat** (construido).
> **Quién lo pidió:** el jefe, **2026-09-13**.
> **Guía viva:**
> `GUIA_CHATBOT_DEEPSEEK.md` (§2nonies).
> **Qué es:** las reglas de cómo debe **contestar** «El ninja» a partir de ahora. No cambia el motor ni la
> clave: cambia **cómo habla, qué dice primero y qué le ofrece al visitante**.
> **Se tocó en:** `includes/chatbot_kb.php` (las respuestas y los consejos), `includes/chatbot.php` (las
> reglas del prompt + el botón de ubicación), `includes/chatbot_buscar.php` (la respuesta del buscador y el
> orden por distancia), `api/chatbot.php` (recibe la ubicación) y `assets/js/chatbot.js` +
> `includes/chatbot_widget.php` (**los botones/acciones del celular**).

---

## §1. El principio (una frase)

**El bot es un amigo que atiende rápido, no un manual.** Informa en **pocas palabras**, da **resultados
personalizados** y **ofrece el siguiente paso** usando lo que el celular ya tiene (cámara, voz, GPS).

Textual del jefe:

> *«actúa como una inteligencia y responde de manera siempre fluida, rápida, sin tanto detalle técnico y sin
> tampoco tratar de nombrar rutas… recuerda que puedes usar todos los recursos que te pueda dar el celular,
> como por ejemplo cámara, grabadora de voz, GPS, y si se te ocurre algún uso que le puede dar también al
> celular, incluye. La función del bot es informar de manera rápida y en pocas palabras, resultados
> personalizados.»*

---

## §2. Las 6 reglas de la respuesta corta

| # | Regla | Cómo se mide |
|---|---|---|
| 1 | **Una idea por respuesta** | Si se contesta con una línea, se contesta **una línea** (como ya manda la regla 12-quater) |
| 2 | **Tope corto de verdad** | Respuestas normales: **máximo ~25 palabras** (hoy el tope técnico es 200: queda como red de seguridad, no como meta) |
| 3 | **Nunca dos preguntas juntas** | Una sola pregunta al final, siempre |
| 4 | **Cero tecnicismos** | Prohibido: «panel», «dashboard», «área de banner», «estadística», «catálogo», «CMS», `productos.php`, `/neg/…`, «clic aquí» |
| 5 | **Termina en acción o en pregunta** | Un enlace con nombre **o** una pregunta de una línea |
| 6 | **Cuando la respuesta es un resultado, se muestra el resultado** | No se explica cómo buscar: se le dan 2-3 resultados reales y listos |

### Traductor de jerga (lo interno → lo que lee el visitante)

| ❌ No se dice | ✅ Se dice |
|---|---|
| «panel» / «dashboard» | «donde administras tu tienda» · «tu cuenta» |
| «área de banner» | «los espacios de publicidad de la portada» |
| «estadística» / «métricas» | «cuánta gente vio tu tienda» |
| «registrar negocio» | «publicar tu tienda» |
| «catálogo / gestión de productos» | «tus productos» |
| «validar / cargar ficha» | «revisar tu tienda» |
| `/registro`, `/caminante`, `/productos.php` | **el nombre de la cosa**, nunca la ruta |

⚠️ **RESUELTO (2026-09-13, decisión 2 del jefe):** la orden del 2026-09-13 decía que el texto del enlace
es **el nombre de la cosa** («nada de *ver tienda*»). El jefe aprobó que, para publicar, el texto sea
**«Publicar mi negocio»**: es a la vez frase corta y nombre de la acción, así que se cumplen las dos
reglas. **Así quedó implementado** en las dos burbujas y en el guion.

---

## §3. Las salidas rápidas (las preguntas de siempre)

### 3.1 «¿Cómo me registro?» — así debe sonar

```
Es muy rápido: entra con tu cuenta de Google y ya estás dentro.

👉 [Crear mi cuenta gratis](https://dechimbote.com/registro)
```

Y si prefiere sin Google, en la misma respuesta (sin alargar):

```
Es muy rápido: con tu cuenta de Google, o con tu correo y una contraseña.

👉 [Crear mi cuenta gratis](https://dechimbote.com/registro)
```

**Lo que existe hoy en `/registro` (comprobado en vivo el 2026-09-13):**
1. **Continuar con Google** (es la primera opción, la más rápida) ✅
2. **Crear cuenta con el Asistente** (te va preguntando paso a paso) ✅
3. **Formulario clásico**: nombre, correo, contraseña y tipo de cuenta ✅
4. **Con el número de WhatsApp** ❌ **NO EXISTE** hoy (ver decisión 1)

### 3.2 «Quiero publicar mi negocio / mi tienda» — así debe sonar

```
Puedes publicarla tú mismo, sin cuenta y con la cámara del celular.

👉 Ingresa aquí para publicar tu tienda
```

(El destino es **`/caminante`**, el asistente que va armando la tienda con fotos y voz. **El enlace se
presenta con palabras cortas y humanas**, como pidió el jefe.)

### 3.3 El patrón para todas las demás (ya verificado, solo se acorta)

| Pregunta | Primera línea (corta) | Enlace con nombre |
|---|---|---|
| ¿Cómo busco trabajo? | «Mira los avisos de hoy, con el WhatsApp listo para postular.» | la página de empleos |
| ¿Cuánto cuesta publicar? | «Publicar es **gratis** y no cobramos comisión.» | registrar tu tienda |
| ¿Cómo veo tiendas cerca? | «Te las ordeno por distancia.» | Ver tiendas cerca |
| ¿Cómo contacto a una tienda? | «Toca su WhatsApp y se abre con el mensaje ya escrito.» | — |
| ¿Cómo pongo un producto? | «Desde tu cuenta: foto, nombre y precio.» | tus productos |

---

## §4. La búsqueda: **resultados personalizados** (el ejemplo del jefe)

**Lo que pidió, textual:** *«si el usuario busca "pollo", el robot responderá: "sí, estos son los resultados
de pollerías en la ciudad. ¿Te muestro las más cercanas a ti? Necesitaré que actives tu ubicación."»*

### La lógica, paso a paso

```
1. El visitante escribe «pollo» (o «pollería», «donde venden pollo», con errores de tipeo).
2. El bot busca EN NUESTRA BASE (ya lo hace hoy: `chatbot_buscar()`), sin gastar tokens.
3. Contesta con el RESULTADO, no con instrucciones:
      «Sí, estos son los resultados de pollerías en la ciudad:»
      + 2 o 3 tiendas (foto + nombre enlazado) y, si hay, un producto con su precio.
4. Y cierra con UNA sola pregunta, la de la ubicación:
      «¿Te muestro las más cercanas a ti? Necesitaré que actives tu ubicación.»
5. Si dice «sí» → se le pide el permiso de ubicación del celular (GPS) y se le
   ordenan los resultados por distancia, SIN salir del chat.
6. Si dice «no» → se queda con la lista y se le ofrece otra cosa corta
   («¿quieres ver solo los que están abiertos ahora?» / «¿te muestro los precios?»).
7. Si NO hay resultados → nunca se inventa: «Todavía no tengo pollerías publicadas en Chimbote.
   ¿Te muestro los restaurantes que sí tengo?»
```

**Lo que hay que construir aquí (hoy NO existe):** que la respuesta del buscador **ofrezca la ubicación** y
que, aceptando, **se ordene por distancia dentro del chat** (hoy el GPS vive en el botón «📍 Ver tiendas
cerca», que **cierra el chat y abre el buscador**). Es un añadido de JavaScript + una acción nueva del
widget, no un cambio del motor.

**Cómo se cuenta el resultado (formato del jefe):** corto, con el nombre de la cosa y sin adornos:

```
Sí, estos son los resultados de pollerías en la ciudad:

- 🍗 [Pollería Don Pollo](https://dechimbote.com/neg/polleria-don-pollo) — Chimbote
- 🍗 [Pollo a la Brasa El Fogón](https://dechimbote.com/neg/…)

¿Te muestro las más cercanas a ti? Necesitaré que actives tu ubicación. 📍
```

---

## §5. Los recursos del celular (regla: se usan antes de pedir que alguien teclee)

| Recurso | ¿Existe hoy? | Para qué lo usa el bot |
|---|---|---|
| 📷 **Cámara** | ✅ Sí · **desde el 2026-09-20 el botón abre un MENÚ chiquito encima (como el ☰) con dos renglones discretos: «📷 Abrir cámara» y «🖼️ Abrir galería»** (`GUIA_CHATBOT_DEEPSEEK.md` §2duodecies punto 16) | Foto del producto o de la tienda → el bot la mira y aconseja; también para una captura de un problema |
| 🎙️ **Voz (dictado)** | ✅ Sí · **desde el 2026-09-20 es una COPIA LITERAL del micrófono del buscador**, pegada a la 📷 en el guía: mismo icono, mismo naranja, misma forma, mismo motor (`es-PE` → `es-ES` → `es-MX`). Lo que dictas **se escribe en el cuadro de al lado**; y si el micrófono **se apaga porque escuchó silencio**, **el mensaje se manda solo al segundo** (si lo parás con el dedo, no: lo revisás y le das al ➤) (`GUIA_CHATBOT_DEEPSEEK.md` §2duodecies puntos 15 y 16). ⚠️ **NO se le aplica la limpieza del buscador** (esa borra conectores y mutilaría la pregunta) y ⚠️ el buscador **no se toca**: sigue con su propio botón (`GUIA_BUSCADOR_VOZ.md`) | Dictar la pregunta en vez de teclearla |
| 📍 **GPS** | ⚠️ Solo como botón que **abre** el buscador | «¿Activas tu ubicación?» → resultados ordenados por distancia |
| 🖥️ **Compartir pantalla** | 🗄️ **RETIRADO del guía el 2026-09-20** (el jefe pidió un modal con dos opciones y nada más) | — |
| 🔊 **Nota de voz del visitante** | ❌ No | Mandar un audio y que el bot lo entienda (haría falta pasar voz a texto) |
| 🔔 **Avisos al celular del dueño** | ❌ No en el chat | Que le llegue un aviso cuando alguien busca lo que él vende |
| 📞 **Llamar por WhatsApp** | ✅ Sí (botones con mensaje listo) | Cerrar la venta sin escribir de cero |

**Ideas nuevas que se pueden incluir (aprovechando el celular):**
1. **La tienda se arma sola (ya existe en `/caminante`):** foto → nombre → precio dictado, y el bot le
   avisa al dueño qué le falta («te falta el horario»).
2. **«Esto queda cerca de ti»** en cada resultado de la búsqueda (distancia en minutos caminando).
3. **Foto del producto y precio por voz** desde el chat, sin entrar a ninguna página.
4. **El bot avisa cuando haya novedades:** «¿te aviso cuando llegue stock de X?» (necesita aviso al
   Telegram/WhatsApp del visitante: trabajo nuevo).
5. **Dictado para el nombre del negocio y la descripción** (ya está en el panel; se puede traer al chat).

---

## §6. Lo que NO se toca (sigue vigente)

1. **Las noticias son nuestras**: el titular enlaza a `/noticia/<slug>`; jamás a un medio de afuera.
2. **La noticia del día sale en la pregunta 3 o 4** (orden del mismo día) — no en el saludo.
3. **El saludo es personalizado**: cielo de hoy + «te saluda El ninja» + «¿tienes alguna pregunta para mí?».
4. **No se inventa nada**: si no está en nuestra base, se dice; no se promete lo que el sitio no hace.
5. **Los enlaces nunca se ven como dirección** (`http`, `www`) y el nombre del enlace es el de la cosa.
6. **El bot puede moverse libre por dechimbote.com** (llevar al visitante a cualquier página, abrirle el
   buscador, los empleos, una ficha, el registro…), pero **no sale del sitio**.

---

## §7. Las 4 decisiones — ✅ YA TOMADAS POR EL JEFE (2026-09-13)

| # | Decisión | Lo que eligió | Cómo quedó hecho |
|---|---|---|---|
| **1** | **Registro** | **El asistente** (aclaró: *«whatsapp me refiero al registro con el asistente»*) | El bot dice: «Es muy rápido: con tu cuenta de Google, o con tu correo. También puedes crearla con el asistente, que te va preguntando.» ⛔ Prohibido decir que se registra con WhatsApp |
| **2** | **El texto del enlace de publicar** | **«PUBLICAR MI NEGOCIO»** | `[Publicar mi negocio](https://dechimbote.com/caminante)` (una línea antes: «Puedes publicarla tú mismo, sin cuenta y con la cámara del celular.») |
| **3** | **Largo de las respuestas** | **25 palabras** | Regla 12-quater del prompt + las respuestas del guion reescritas a 1-2 líneas (200 quedó solo como techo de emergencia) |
| **4** | **La ubicación** | **Dentro del chat** | Construido: la opción **📍 Ver las más cercanas a mí** (`accion:'geo'`) pide el GPS, repite la misma búsqueda con las coordenadas y el servidor la contesta **ordenada por distancia** (`chatbot_buscar($termino, $limite, $lat, $lng)` + `distancia_txt()`), sin salir del chat |

---

## §8. Cómo se sabrá que quedó bien (prueba)

| Prueba | Qué tiene que pasar |
|---|---|
| «¿cómo me registro?» | 1 línea + el enlace al registro, sin nombrar rutas ni pasos técnicos |
| «quiero publicar mi tienda» | 1 línea + el enlace para publicar (caminante), con palabras cortas |
| «pollo» | «Sí, estos son los resultados de pollerías en la ciudad…» + 2-3 resultados reales + **«¿te muestro las más cercanas? Necesitaré tu ubicación»** |
| «estadísticas de mi tienda» | Responde «cuánta gente vio tu tienda», **nunca** «área de estadísticas» ni «panel» |
| Cualquier respuesta | Ninguna palabra interna (`panel`, `banner`, `estadística`, rutas) y **una sola** pregunta |
| «busco X» que no existe | Lo dice sin inventar y ofrece lo más parecido |

---

*Propuesta escrita el 2026-09-13. Cuando el jefe decida los 4 puntos del §7, se implementa y se pasa a
`GUIA_CHATBOT_DEEPSEEK.md` (guía viva del módulo).*
