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


# GUÍA — BOT DE TELEGRAM, AVISOS AL JEFE Y MONITOREO DEL SERVIDOR · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** antes de tocar el bot, el motor de avisos (`includes/avisos.php`), el panel 📱 Telegram del Súper Admin o el cron que vigila el servidor.
> **Archivos que toca:** `D:\RELAX\deploy\includes\avisos.php`, `includes\config_avisos.php`, **`includes\informe_inteligente.php`** (🧠 el motor del informe, nuevo el 2026-09-15), `includes\config_monitoreo.php`, `includes\helpers.php`, `includes\metricas.php`, `includes\opiniones.php`, `includes\vista_records_admin.php`, `cron\monitoreo_sistema.php`, **`api\llamada.php`** (📞 el contador del botón Llamar, nuevo el 2026-09-15), `api\telegram_bot.php`, `api\reportar.php`, **`assets\js\llamadas.js`**, `superadmin.php`, `negocio.php`.
> **Estado:** EN PRODUCCIÓN. El cron del monitoreo ya quedó configurado en hPanel (ver §13) y desde el **2026-09-15** manda además el **🧠 informe inteligente** del día y de la semana (§13 y §18).

## 0) LO ESENCIAL EN 30 SEGUNDOS

- **Bot:** `@Jimmychimbote_bot` ("Alertas chimbote"). **Chat ID del jefe (Jimmy):** `8333560284`.
- **Costo $0 de por vida.** Se decidió NO usar Zapier: era "overkill" (exageración y costo innecesario) porque Jimmy tiene control total del PHP de su hosting. Se construyó un **"Zapier nativo" en PHP** sobre la API de Telegram: alertas ilimitadas, en tiempo real, sin servicios de pago y con los datos dentro del ecosistema de dechimbote.com.
- **Dos formas de avisar, y no son lo mismo:**
  - `aviso('tipo', [...])` → motor completo de avisos (`includes/avisos.php`). Lo usa casi todo el sitio.
  - `notificar_jefe($mensaje, $nivel)` → mensaje suelto y directo (`includes/helpers.php`). Lo usan el monitoreo del servidor y los reportes de contenido.
- **Panel de control del jefe:** https://dechimbote.com/superadmin.php?seccion=avisos (Súper Admin → 📱 Telegram): casillas para encender/apagar cada aviso.
- **Regla clave del motor:** si Telegram falla, la web sigue igual. Todo va en try/catch y el envío se hace **después** de responderle al visitante (`litespeed_finish_request()`), para que la web nunca se frene por Telegram.
- **🧠 EL INFORME INTELIGENTE (2026-09-15 — lo que el jefe pidió):** además del resumen de cada hora, el motor manda **todos los días** un parte que **cruza las tablas** (personas de verdad, la tienda que más destaca, quién pidió llamada o WhatsApp, lo que la gente busca y no encuentra y lo que espera decisión) y **los domingos** el mismo parte con los **7 días comparados con la semana anterior**. El del **día** va del **00:00 a la hora del envío** (día natural) y se compara con **ayer a la misma hora**. Lo arma **`includes/informe_inteligente.php`** y lo envía el cron (§13 y el detalle completo en §18). El viejo «📅 Resumen del día» de contadores sueltos **nunca se envió** (error de interruptores ya arreglado) y ahora ese interruptor manda este informe.
- **📞 El botón «Llamar» ya se mide (2026-09-15):** era un `<a href="tel:">` puro que **no pasaba por el servidor**, así que el dato **no existía**. Ahora `assets/js/llamadas.js` manda un *beacon* a `api/llamada.php`, el clic queda en `directorio_pedidos` con `tipo='llamada'` y avisa con el tipo nuevo `llamada_tienda` (§18).
- 🚫 **LOS FALSOS POSITIVOS «🔥 PIDIERON PRECIO» Y «📞 TOCARON LLAMAR» ESTÁN ARREGLADOS (2026-09-16 — queja del jefe):** llegaban avisos de clics que **nadie hizo** (solo entrar a la ficha). Medido en producción: de **371 avisos** en 7 días, **306 venían de IPs que aparecen UNA SOLA VEZ** en todo el registro, sin visita a la ficha y con **modelos de móvil falsos** («Pixel 9», «iPhone 18_4»…) = la granja y los rastreadores **siguiendo el enlace del botón** (`api/lead.php`, que está escrito en el HTML de la ficha) y, en el caso de la llamada, el JS contando el **`pointerdown`** (bastaba con **tocar** el botón para empezar a deslizar la pantalla). Ahora **solo cuenta un clic de verdad** (`&c=1` que pega el JS al enlace + `Sec-Fetch-User: ?1` como respaldo, y `click` con `pointerup` sin moverse más de 12 px). **Todo el detalle, con las cifras y la verificación: §19.**
- **El catálogo tiene 27 tipos de aviso** (eran **23** antes del 2026-09-15). La tabla vieja de esta guía solo listaba **21** porque nunca documentó `empleo` ni `noticias_dia`; los **4 nuevos** son `llamada_tienda`, `opinion_nueva`, `informe_dia` e `informe_semana`. Todos se encienden/apagan desde el panel (§4), que además trae una tarjeta nueva **👤 Personas frente a 🤖 robots (24 h)** con los clics de pedir/llamar y dos enlaces para **ver el informe sin enviarlo** (§10).
- **Código real:** `D:\RELAX\deploy` (editar y desplegar solo desde ahí).

## 1) QUÉ ES EL BOT Y PARA QUÉ SIRVE

Un sistema de alertas en tiempo real escrito en PHP, sin pagar Zapier ni ningún SaaS (decisión del jefe, con ese motivo: el código es propio y el hosting ya corre PHP).

Se usa para tres cosas:

1. **Avisos de lo que pasa en el sitio** (búsquedas, visitas, leads, altas, tiendas, reclamos, postulaciones, chat, 404, errores, resúmenes).
2. **Comandos desde el chat del bot**, para consultar sin abrir el panel (`/buscar`).
3. **Monitoreo del hosting**: avisar si el disco, la CPU o la memoria se acercan a sus límites (cron cada hora).

## 2) `notificar_jefe()` FRENTE A `aviso()` — CUÁL USAR

| | `notificar_jefe($mensaje, $nivel)` | `aviso($tipo, $datos)` |
| --- | --- | --- |
| Dónde vive | `includes/helpers.php` | `includes/avisos.php` |
| Qué hace | Manda el texto tal cual al chat del jefe | Decide si el aviso está encendido, evita repetir, aplica topes, separa personas de robots, da formato y encola el envío |
| Se apaga desde el panel | **No** (no es un tipo del catálogo) | **Sí**, cada tipo con su casilla en 📱 Telegram |
| Cuándo usarlo | Avisos del sistema que el jefe debe recibir siempre (monitoreo del servidor, reportes de contenido) | Cualquier evento del sitio que se quiera poder encender/apagar |

**Cómo envía `notificar_jefe()`** (formato conservado de la primera guía):

- Prefijos por nivel: `info` → ℹ️ · `exito` → ✅ · `urgente` → 🔥 · desconocido → 📢.
- Texto final: `*{emoji} DECHIMBOTE.COM - ALERTA*` + salto + mensaje + `_Enviado desde el servidor_`.
- `@param string $nivel` acepta `'info'`, `'exito'` o `'urgente'`.
- Falla en silencio: si no hay token o chat ID, devuelve `false` sin romper la web.
- (verificar) La primera versión de la guía daba otros prefijos (`'urgente' => ''` y respaldo `'📢'`); el valor vigente es ℹ️/✅/🔥 con respaldo 📢.

```php
// Formato exacto del mensaje
$texto_formateado = "*{$emoji} DECHIMBOTE.COM - ALERTA*\n\n" . $mensaje . "\n\n_Enviado desde el servidor_";
return telegram_enviar($chat_id, $texto_formateado, 'Markdown');
```

**`telegram_enviar($chat_id, $texto, $parse_mode)`** es el único punto de envío: todo el sistema pasa por ahí.

- Envío único por cURL, **timeout 5 s** (nunca retrasa la web del usuario). (verificar: la guía vieja decía 3 s.)
- Si el texto pide Markdown y Telegram lo rechaza (un nombre de negocio con `_` o `*` lo rompe y responde 400), **reintenta en texto plano** para no perder el aviso.
- `$parse_mode` acepta `''` (texto plano), `'Markdown'` o `'HTML'`.
- Devuelve `true` solo si Telegram respondió **HTTP 200**.

**Constantes** (definidas en `includes/helpers.php`, se pueden sobrescribir desde `config.php`):

```php
TELEGRAM_BOT_TOKEN      = '<el token real: está en deploy/config.php o includes/helpers.php>'
TELEGRAM_CHAT_JEFE      = '8333560284'          // chat del jefe (no es secreto)
TELEGRAM_WEBHOOK_SECRET = '<el secreto real: en deploy/config.php>'
```

🔐 **Los valores reales NO se escriben en las guías** (esta carpeta no es un repositorio privado). Están
en `deploy/config.php` y `deploy/includes/helpers.php` del sitio. El token se obtuvo con
`https://api.telegram.org/bot<TOKEN>/getUpdates` después de escribirle al bot; el bot se creó con
`@BotFather`.

## 3) EL MOTOR `includes/avisos.php` — QUÉ HACE POR TI

Se llama desde el resto del sitio con **una sola línea**:

```php
aviso('busqueda', ['termino' => 'zapatillas', 'resultados' => 12]);
```

El motor resuelve todo lo difícil:

1. **Sabe si ese aviso está encendido** (`directorio_avisos_config`, lo maneja el panel).
2. **No repite el mismo evento** (clave de dedupe por tipo + IP + negocio).
3. **No satura el chat** (topes por hora, ver §9).
4. **Separa personas de robots** (user-agent + señales de persona, ver §7 y §8).
5. **Da formato** al mensaje (catálogo de plantillas por tipo).
6. **Envía después de responder al visitante**, con `litespeed_finish_request()`.
7. **Registra todo** en `directorio_avisos_log` (tipo, estado, clave de dedupe, IP, user-agent, robot, negocio, usuario, fecha).
8. **Vigila los errores fatales**: `register_shutdown_function` dentro de `avisos.php` (se carga desde `helpers.php`), así un fallo de PHP llega al Telegram una sola vez por error distinto.
9. **Agrupa lo que se pasa del tope** y lo entrega en el resumen de la hora.
10. **Limpia el historial** de más de **30 días** (lo hace el monitor).

## 4) LISTA COMPLETA DE LOS 27 AVISOS (TIPO INTERNO, CUÁNDO LLEGA Y ESTADO)

El panel los agrupa por tema: *Visitas y búsquedas · Tiendas y productos · Personas · Publicidad · Sistema y errores*. **Esta tabla es `avisos_catalogo()` de `includes/avisos.php`, copiada tal cual el 2026-09-15: 27 tipos.** (El panel se pinta del catálogo, así que un tipo nuevo aparece solo en la vista — no hay que tocar `superadmin.php`.)

| # | Grupo | Aviso | `tipo` interno | Cuándo llega | Por defecto |
| --- | --- | --- | --- | --- | --- |
| 1 | Visitas y búsquedas | 🔍 **Búsquedas (con término)** | `busqueda` | Alguien busca con un término (persona o anónimo) | ENCENDIDO |
| 2 | Visitas y búsquedas | 🕳️ **Búsquedas sin resultados** | `busqueda_vacia` | Buscan algo que no existe en el directorio (oportunidad de captar ese negocio) | ENCENDIDO |
| 3 | Visitas y búsquedas | 👁️ **Visitas a una tienda (solo personas)** | `visita_tienda` | Abren la ficha de un negocio **y hay alguna señal de persona** (§7) | ENCENDIDO |
| 4 | Visitas y búsquedas | 📦 **Visitas a un producto (solo personas)** | `visita_producto` | Abren un producto dentro de una ficha (mismo filtro que las visitas a tienda) | ENCENDIDO |
| 5 | Visitas y búsquedas | 🤖 **Resumen de robots (por hora)** | `visita_robot` | Cada hora, si hubo tráfico automático (§8) | ENCENDIDO |
| 6 | Tiendas y productos | 🏪 **Tienda nueva creada** | `tienda_nueva` | Negocio creado (formulario, asistente o Caminante) | ENCENDIDO |
| 7 | Tiendas y productos | 📦 **Producto nuevo o editado** | `producto_nuevo` | Un negocio publica o cambia un producto de su ficha | ENCENDIDO |
| 8 | Tiendas y productos | 🗑️ **Producto eliminado** | `producto_borrado` | Un negocio borra un producto | ENCENDIDO |
| 9 | Tiendas y productos | 📷 **Fotos nuevas en una ficha** | `foto_nueva` | Un negocio sube fotos (de la ficha o de un producto) | ENCENDIDO |
| 10 | Tiendas y productos | 📵 **Caminante: subida incompleta** | `caminante_incompleto` | Una captura en la calle llega con **menos fotos de las que el celular envió**, con fotos rechazadas o sin ninguna. Trae carpeta, enviadas/guardadas/rechazadas, ubicación y el motivo; el detalle completo queda en `caminante/_registro_subidas.log` | ENCENDIDO |
| 11 | Personas | 👤 **Usuario nuevo registrado** | `usuario_nuevo` | Alta con correo o con Google | ENCENDIDO |
| 12 | Personas | 🔥 **Pidieron precio (WhatsApp)** | `lead_precio` | Clic en el botón 💬 WhatsApp de una ficha (`api/lead.php`) **o** en un WhatsApp del copy que enlaza directo a `wa.me` (§18). ⚠️ **Desde el 2026-09-16 solo cuenta con prueba de clic** (`&c=1` / `Sec-Fetch-User`): los robots que seguían el enlace del botón ya no inventan pedidos (§19) | ENCENDIDO |
| 13 | 🆕 Personas | 📞 **Tocaron «Llamar» en una ficha** | `llamada_tienda` | Toque en un enlace `tel:` de una ficha, medido por `assets/js/llamadas.js` → `api/llamada.php`. ⚠️ **Desde el 2026-09-16 solo cuenta el `click`** (con respaldo de `pointerup` sin moverse): tocar el botón para deslizar la pantalla ya no avisa (§19). Dedupe **10 min** por IP + tienda. El texto sale como «📞 TOCARON «LLAMAR» EN UNA FICHA» | ENCENDIDO |
| 14 | 🆕 Personas | 💬 **Opinión nueva en una ficha** | `opinion_nueva` | Alguien deja su opinión anónima (`opinion_crear()` en `includes/opiniones.php`). Lleva apodo, estrellas y el texto; si la nota es de **1 a 3 ⭐** el mensaje lo resalta | ENCENDIDO |
| 15 | Personas | 🙋 **Reclamo de negocio** | `reclamo` | Alguien dice "este negocio es mío" | ENCENDIDO |
| 16 | Personas | 🚩 **Reportes de contenido** | `reporte_contenido` | Un usuario reporta un negocio o producto (fraude, contenido inapropiado…) | ENCENDIDO |
| 17 | Personas | 💼 **Postulación (Trabaja con nosotros)** | `postulante` | Nuevo candidato | ENCENDIDO |
| 18 | Personas | 💼 **Aviso de empleo nuevo** | `empleo` | Aviso nuevo, o uno enviado por un visitante que espera aprobación: el mensaje trae **✅ Aprobar** y **🗑️ Rechazar** | ENCENDIDO |
| 19 | Publicidad | 🖱️ **Clic en un banner** | `banner_clic` | Alguien hace clic en la publicidad (los banners rotan mucho) | **apagado** |
| 20 | Sistema y errores | 🔗 **Enlaces rotos (404)** | `pagina_404` | Alguien llega a una página que no existe | ENCENDIDO |
| 21 | Sistema y errores | 💥 **Errores del sitio** | `error_sitio` | Fallo de PHP (pantalla en blanco) o caída de la base de datos. Una vez por error distinto | ENCENDIDO |
| 22 | Sistema y errores | 📊 **Resumen de la última hora** | `resumen_hora` | Cada hora, si hubo movimiento (§5.3 y §18) | ENCENDIDO |
| 23 | Sistema y errores | 📅 **Resumen del día (22:00)** | `resumen_dia` | **Hoy es un alias del informe del día**: si este está encendido, el cron manda el 🧠 informe aunque `informe_dia` esté apagado (§13). El mensaje viejo de contadores ya no se usa | **apagado** |
| 24 | 🆕 Sistema y errores | 🧠 **Informe inteligente del día (22:00)** | `informe_dia` | Todos los días a la hora de `AVISOS_INFORME_HORA` (de fábrica **22:00**): el **día natural** (00:00 → la hora del envío), comparado con **ayer a la misma hora** | ENCENDIDO |
| 25 | 🆕 Sistema y errores | 🏆 **Informe de la semana (domingo 22:00)** | `informe_semana` | El día `AVISOS_INFORME_SEMANA_DIA` (de fábrica **0 = domingo**), con los 7 días corridos y la comparación con los 7 anteriores | ENCENDIDO |
| 26 | Sistema y errores | ⚠️ **Alertas del servidor (disco/CPU/memoria)** | `alerta_sistema` | El hosting se acerca a sus límites (lo dispara el cron del monitoreo) | ENCENDIDO |
| 27 | Sistema y errores | 📰 **Noticias del día publicadas** | `noticias_dia` | El robot de noticias (06:00) ya publicó las noticias locales en `/noticias`: un mensaje al día con los titulares | ENCENDIDO |
| 28 | 🆕 Personas | 🔑 **Olvidó su contraseña (pide ayuda)** | `clave_olvidada` | Un dueño pide recuperar su contraseña desde **`/recuperar`** (módulo del 2026-09-16). Trae su nombre, su usuario (su WhatsApp o su correo), su tienda, la IP y el enlace a **Súper Admin → 👥 Usuarios**, donde el botón **`🔑 Restablecer contraseña`** le da una clave nueva para mandársela por WhatsApp. Dedupe **60 min** por cuenta; si ese número **no tiene cuenta**, el aviso lo dice para que el jefe lo busque. Detalle: `GUIA_CONSTRUCTOR_DE_TIENDAS.md` §3ter | ENCENDIDO |

> ⚠️ **Cuenta real = 28 (eran 27).** Aquí está todo lo que el panel ofrece hoy. La tabla vieja de esta guía decía
> «21 avisos» y **no listaba** `empleo` (18) ni `noticias_dia` (27), que ya existían en el código: por eso se
> documentaban **21 de los 23 reales**. Con los **4 nuevos del 2026-09-15** (`llamada_tienda`, `opinion_nueva`,
> `informe_dia`, `informe_semana`) el catálogo quedó en **27**, y con **`clave_olvidada` (28, 2026-09-16)** está
> en **28**. Comprobación: `avisos_catalogo()` en
> `includes/avisos.php` (una línea `'grupo' =>` por tipo).
> ⚠️ **Lo «Por defecto» es el valor de fábrica, no lo que hay hoy en la base:** lo que manda es lo guardado en
> `directorio_avisos_config` desde el panel. La visita a tienda la tiene encendida el jefe (el informe del 14/09 ya
> contó visitas de gente): el filtro de señales de persona (§7) es lo que quitó el ruido, no el interruptor.
>
> 🏷️ **LA HORA DEL RÓTULO SALE DE LA CONSTANTE (2026-09-15):** los títulos de `resumen_dia`, `informe_dia` e
> `informe_semana` ya **no son texto fijo**: se arman con `AVISOS_RESUMEN_DIA_HORA` · `AVISOS_INFORME_HORA` y
> `avisos_dia_semana_nombre(AVISOS_INFORME_SEMANA_DIA)` (ayudante nuevo, **0 = domingo**). Es decir: **si se cambia
> la hora o el día en `includes/config_avisos.php`, el rótulo del panel cambia solo** y no hay que tocar la vista.
> (Antes decían «21:00» en duro mientras el envío era a las 22:00: desfase encontrado y corregido el 2026-09-15.)

> 🗑️ **RETIRADO (2026-09-13, orden del jefe): el aviso `chat_comunidad` ya no existe.** El **chat
> comunitario se quitó del sitio** (página, API, banda, botón flotante y migración borrados), así que su
> aviso se eliminó del catálogo. En `avisos_catalogo()` quedó solo el comentario de que estaba ahí.

## 5) FORMATOS REALES DE LOS MENSAJES

### 5.1 Visitas y búsquedas

```
🔍 BÚSQUEDA NUEVA

"zapatillas"
👤 Anónimo · 📱 Móvil
🌐 190.108.93.134 · desde Google
📄 12 resultado(s)
🔗 https://dechimbote.com/buscar.php?q=zapatillas
🕒 10/09 00:12
```

```
🕳️ BÚSQUEDA SIN RESULTADOS

"tablas de snowboard usadas"
👤 Anónimo · 🌐 190.108.93.134
💡 Nadie ofrece eso en el directorio: oportunidad para captar ese negocio.
🔗 https://dechimbote.com/buscar.php?q=tablas+de+snowboard+usadas
🕒 10/09 00:09
```

```
👁️ VISITA A UNA TIENDA

🏪 DON MORILLAS Pollos a la Brasa
👤 Anónimo · 📱 Móvil
✅ Persona real: llegó desde Google · esa IP ya había entrado hoy
🌐 190.108.93.134 · desde Google
🔗 https://dechimbote.com/neg/don-morillas-pollos-a-la-brasa
🕒 10/09 00:12
```

> ⚠️ **La línea `✅ Persona real:` la añadió el 2026-09-15** (`avisos_linea_quien()`): dice **por qué** el motor
> consideró que era gente (hasta 3 señales, §7). Antes el mensaje no lo explicaba y de ahí venía la queja del
> jefe. En el panel, el registro de esa visita guarda lo mismo dentro de `resumen` (`· persona porque …`).

```
📦 VISITA A UN PRODUCTO

📦 "Alquiler de cancha de vóley" · S/ 80.00
🏪 COMPLEJO DEPORTIVO MIRAMAR BAJO
👤 Anónimo · 🌐 190.108.93.134
🔗 https://dechimbote.com/neg/complejo-deportivo-miramar-bajo
🕒 10/09 00:11
```

```
🔥 PIDIERON PRECIO POR WHATSAPP

📦 "Alquiler de cancha de vóley" · S/ 80.00
🏪 COMPLEJO DEPORTIVO MIRAMAR BAJO
📞 943111222
👤 Anónimo · 📱 Móvil
🌐 190.108.93.134 · desde Google

💡 Lead caliente: te lo acaban de pedir.
🔗 https://dechimbote.com/neg/complejo-deportivo-miramar-bajo
🕒 10/09 00:11
```

### 5.2 Personas

```
👤 USUARIO NUEVO

👤 Ana Torres
📧 ana.torres@ejemplo.com
🔑 se registró con correo (dueno)
👥 Ya son 10 usuarios registrados
🌐 190.108.93.134
🕒 10/09 00:11
```

```
🏪 TIENDA NUEVA CREADA

🏪 Bodega Doña Rosa
📂 Bodegas / Minimarkets
📍 Nuevo Chimbote
👤 Anónimo · 🖥️ Computadora
⏳ Estado: pendiente de aprobación
🔗 https://dechimbote.com/neg/bodega-dona-rosa
🛠️ Revisar: https://dechimbote.com/superadmin.php?seccion=tiendas
🕒 10/09 00:11
```

```
🙋 RECLAMO DE NEGOCIO

🏪 Pollos a la Brasa Suárez
👤 Rosa Medina
📧 rosa@ejemplo.com
📞 943 111 222

📝 Soy la dueña y quiero actualizar el teléfono y las fotos.
🛠️ Revisar: https://dechimbote.com/superadmin.php?seccion=reclamos
🕒 10/09 00:11
```

```
💼 POSTULACIÓN NUEVA

👤 Luis Chávez
📧 luis@ejemplo.com
📞 944 333 444
💰 Sueldo esperado: S/ 1,200
🕒 Jornada: Tiempo completo
🛠️ Revisar: https://dechimbote.com/superadmin.php?seccion=postulantes
🕒 10/09 00:11
```

```
🗑️ EJEMPLO HISTÓRICO — el aviso «MENSAJE EN EL CHAT COMUNITARIO» se retiró el 2026-09-13
   (el chat comunitario ya no existe; `/chat_comunidad.php` da 404). Se conserva como muestra del formato.

💬 MENSAJE EN EL CHAT COMUNITARIO

👤 PRUEBA
📝 ¡Hola Chimbote! Este es un ejemplo de aviso del chat comunitario.
🔗 https://dechimbote.com/chat_comunidad.php
🕒 10/09 00:11
```

### 5.3 Sistema, errores y resúmenes

```
🔗 ENLACE ROTO (404)

🚫 /pollo-a-la-brasa-chimbote-2026
↩️ desde Google
🌐 190.108.93.134 · 🖥️ Computadora
🕒 10/09 00:09
```

```
💥 ERROR EN EL SITIO

💥 Call to undefined function ejemplo()
📄 /ejemplo.php:42
🌐 /ejemplo.php

🛠️ Este aviso se manda una sola vez por error distinto.
🕒 10/09 00:11
```

```
📊 MOVIMIENTO DE LA ÚLTIMA HORA

👤 PERSONAS: 3 visita(s) a 2 tienda(s)
  🏆 Ópticas Angelina (2)
  🏆 Ola Cafe Periodista (1)

📞 PIDIERON: 4 (1 📞 llamada · 3 💬 WhatsApp)
  · Policlínico Arribasplata (2)
  · Ópticas Angelina (1)

🔍 BUSCARON: cerveza (5) · clavos (3)

🚩 1 reporte(s) por revisar
💬 Opiniones nuevas: 1
👤 Usuarios nuevos: 1

🤖 Robots: 41 (3 buscadores · 38 automáticas sin señales de persona)
🕒 10/09 01:00
```

> ⚠️ **Este resumen se REHIZO EL 2026-09-15** (`avisos_resumen_hora()`, §18): el ejemplo de arriba es
> **la forma real del mensaje** (las líneas y su orden salen del código), con **cifras de muestra** — antes
> eran contadores sueltos («Visitas a tiendas: 1 · 🔥 Pidieron precio: 2…») y el jefe no sabía si el sitio se
> movía. Ahora **cruza tablas** y el orden es: **👤 PERSONAS** (+ 🏆 las más visitadas) → 📞 **PIDIERON**
> (llamadas/WhatsApp/consultas/carrito + top de tiendas, de `directorio_pedidos`) → 🔍 **BUSCARON** (`directorio_busquedas`)
> → 🚩 **reportes pendientes** y lo nuevo (opiniones, usuarios, tiendas) → 🤖 **robots en una línea, al final**.
> Si no hay nada que contar **no se envía nada** (salvo con `$forzar`).

```
🤖 TRÁFICO AUTOMÁTICO (última hora)

  · 41 visita(s) de robots — NO son personas.
    • Googlebot: 9
    • Automáticas (granja de IPs): 30
    • AhrefsBot: 2

🔎 11 con nombre de robot (Googlebot, Applebot, IA…): esos tratan el sitio como contenido.
👥 30 automáticas sin señales de persona (la granja de IPs que raspa el sitio).

👤 Y en esa misma hora entraron 3 PERSONA(S) de verdad a las tiendas (van en el resumen de movimiento).
🕒 10/09 01:00
```

> ⚠️ **También rehecho el 2026-09-15.** El mensaje viejo («En la última hora: 66 visita(s) de robots») es el
> que provocó la queja del jefe: parecía que nadie entraba. Ahora **separa los dos tráficos** (con nombre vs
> granja de IPs) y —lo importante— **dice cuántas PERSONAS entraron en esa misma hora** (§8). Si no entró
> nadie, lo dice igual: *«👤 En esa misma hora no entró ninguna persona a una tienda (la granja tapa el sitio,
> pero no es gente)»*.

```
⚠️ ALERTA DE SISTEMA

💾 Disco: 87% usado (2.3 GB libres de 16.0 GB)
⚡ CPU: 82% de carga (carga 52.48 en 64 núcleos del servidor)
🧠 Memoria: 91% usada (47.0 GB libres de 527.2 GB)
📁 Carpeta del sitio: 10.4 GB

Acción recomendada: Limpiar archivos de backup o logs antiguos.

Revisar en hPanel → Estadísticas → Uso de recursos.
Hora de la medición: 09/09/2026 23:28 (America/Lima)
```

### 5.4 🧠 EL INFORME INTELIGENTE (`informe_dia` / `informe_semana`) — formato REAL

Este es el mensaje **real del 2026-09-15 a las 00:21** (prueba del motor 🧠 `informe_dia` después del arreglo del
**día natural**). El del día se manda **todos los días** a `AVISOS_INFORME_HORA` (**22:00** de fábrica) y los
domingos va además el de la semana (`🏆 INFORME INTELIGENTE DE LA SEMANA`, mismas secciones y «LA QUE MÁS DESTACA
ESTA SEMANA»). Es un parte de **madrugada**, así que casi todo sale a cero — sirve justo de ejemplo de **cómo se ve
un día flojo** (lo que el jefe verá si no pasa nada):

```
🧠 INFORME INTELIGENTE DEL DÍA
📅 hoy (15/09, 00:00 → 00:21)

👤 PERSONAS (señales de persona real, no robots)
  · Ninguna visita a tienda con señales de persona en este periodo.
  · 🔍 3 búsqueda(s) de personas
  · 💬 1 clic(s) de pedir: 1 WhatsApp · 0 consulta de producto · 0 📞 llamada · 0 carrito armado

🤖 Robots: 7 (3 buscadores/IA · 4 automáticas sin señales)
  ℹ️ No son personas. Los buscadores traen visitas buenas (indexan el sitio).

🏆 LA QUE MÁS DESTACA HOY
  Baterias&Ferreteria Progress
  · 0 visita(s) de personas · 1 clic(s) de pedir = 3 punto(s)
  · ▲ nuevo frente al periodo anterior
  🔗 https://dechimbote.com/neg/baterias-ferreteria-progress

📞 LAS QUE MÁS PIDEN (llamada y WhatsApp)
  1. Baterias&Ferreteria Progress — 1 (1 💬)

🔍 BUSCAN Y NO ENCUENTRAN (oportunidad de captar esos negocios)
  · xyzzy (1)
🔥 LO MÁS BUSCADO: pollo (1) · tiendecita d mam lu (1)

🚩 REPORTES
  · Pendientes de tu decisión: 1 reporte(s) de contenido · 1 opinión(es) reportada(s)
  👉 Súper Admin → 🚩 Reportes · Reclamos de opiniones

🏪 EL SITIO EN NÚMEROS
  · 2 aviso(s) de empleo
  · 1 pedido(s)
  · = igual frente al periodo anterior

🕒 15/09 00:21
```

> ⚠️ **EL PARTE DEL DÍA ES EL DÍA NATURAL, NO «ÚLTIMAS 24 H» (2026-09-15).** La línea de fecha dice
> **`📅 hoy (15/09, 00:00 → 00:21)`** porque `informe_lineas('dia')` usa **`informe_rango_hoy()`**: desde las
> **00:00 de hoy** hasta la hora del envío. Y la comparación **`▲/▼ frente al periodo anterior`** va contra
> **AYER a la misma hora** (`informe_rango_ayer()`), no contra el día entero: comparar un día a medias con un día
> completo daba siempre un **-50 % falso** que asustaba al jefe sin motivo. El parte de la **semana** sigue siendo
> **7 días corridos contra los 7 anteriores** (`informe_rango()`), y ahí no se usa el calendario.
> En el motor eso es el parámetro **`informe_datos($dias, $calendario)`** (`$calendario = true` solo para `'dia'`).

**Cómo se lee (y por qué está en este orden):**

| Sección | Qué contesta | De dónde sale el dato |
| --- | --- | --- |
| 👤 **PERSONAS** | *«¿entró gente o solo robots?»* — lo primero que preguntó el jefe | `directorio_avisos_log` con `tipo IN ('visita_tienda','visita_producto')` y **`estado <> 'robot'`** (todo lo que **pasó el filtro de persona** de §7, aunque el mensaje se callara por dedupe, por tope de la hora, por silencio o por estar apagado el aviso) + `directorio_stats_sesiones` para los navegadores de verdad |
| 🤖 **Robots** | que quede claro que **no se están contando como personas** | `directorio_avisos_log` con `es_bot=1`, **separando por prefijo** `Visita automática%` (§8) |
| 🏆 **LA QUE MÁS DESTACA** | *«qué tienda esta semana está destacando más que las demás»* | cruz de **visitas de personas + 3 × clics de pedir/llamar** (`directorio_avisos_log` + `directorio_pedidos`), comparada con el periodo anterior |
| 📞 **LAS QUE MÁS PIDEN** | *«las tiendas que reciben más clics en el botón de llamada»* | `directorio_pedidos` del periodo, agrupado por tienda, con el desglose **📞 llamada** / 💬 mensaje |
| 🔍 **BUSCAN Y NO ENCUENTRAN** | oportunidades: lo que la gente pide y el directorio no tiene | `directorio_busquedas` con `resultados = 0` · y 🔥 lo más buscado con `resultados > 0` |
| 🚩 **REPORTES** | *«si se ha reportado alguna tienda»* | `directorio_reportes` + `directorio_opiniones_reportes` + `directorio_reclamos`, **solo los `pendiente`** |
| 💬 **OPINIONES NUEVAS** (aparece si hay) | termómetro de las tiendas | `directorio_opiniones` del periodo, con el promedio de ⭐ |
| 🏪 **EL SITIO EN NÚMEROS** | el tamaño del movimiento | `directorio_avisos_log` (`tienda_nueva`, `producto_nuevo`, `usuario_nuevo` — esos tres **sí** con `estado='enviado'`), `directorio_noticias`, `directorio_empleos` y `directorio_pedidos` (+ los S/ del carrito) |

> ⚠️ **LAS 5 DECISIONES QUE HAY QUE ENTENDER ANTES DE TOCARLO** (todas en `includes/informe_inteligente.php`):
> 1. **El día es el día natural** y se compara con **ayer a la misma hora** (arriba, en el aviso grande). El de la semana, 7 días corridos.
> 2. **Los pendientes de reportes NO se filtran por fecha.** Lo que espera la decisión del jefe se avisa **siempre**, aunque el reporte sea de hace un mes — el filtro por fecha es para el movimiento, no para lo que está parado esperando.
> 3. **Las personas se cuentan con `estado <> 'robot'`, NO con `estado='enviado'`.** El filtro escribe `robot` cuando dice que **no** es persona; cualquier otro estado (`enviado`, `duplicado`, `agrupado` —el tope por hora—, `silencio`, `apagado`) significa que **sí pasó el filtro**. Contar solo `enviado` dejaba fuera visitas de gente real y el informe salía **más pobre de lo que es la realidad**.
> 4. **«Navegadores de verdad» NO son todas las sesiones.** Cuenta solo las de `directorio_stats_sesiones` con **`paginas > 1 OR segundos > 0`**. Contar las **2 350 sesiones del 14/09** habría dado un número grande y **falso**: la granja crea su cookie igual que una persona, pero no ejecuta JavaScript.
> 5. **Nada puede romper el cron:** toda consulta va en `informe_q()` (try/catch) y las tablas se comprueban antes con `informe_tabla()` / `informe_columna()`; si falta una tabla, esa línea **no sale** y el informe sigue. Y el **peso 3** de la tienda que destaca está a propósito: un clic de llamar o WhatsApp vale mucho más que una visita (así lo pidió el jefe).

### 5.5 📞 «Tocaron Llamar» y 💬 «Opinión nueva» (los 2 avisos nuevos de personas)

```
📞 TOCARON «LLAMAR» EN UNA FICHA

🏪 Ópticas Angelina
📞 943111222
👤 Anónimo · 📱 Móvil
🌐 190.108.93.134 · desde Google

💡 Lead caliente: van a llamar (o acaban de llamar) a la tienda.
🔗 https://dechimbote.com/neg/opticas-angelina
🕒 15/09 00:12
```

```
💬 OPINIÓN NUEVA EN UNA FICHA

🏪 Bodega Doña Rosa
👤 Cliente satisfecho · ⭐⭐⭐⭐⭐

📝 «Atienden rápido y siempre tienen lo que busco.»

👍 Se publicó al instante (las opiniones son anónimas).
🔗 https://dechimbote.com/neg/bodega-dona-rosa
🕒 15/09 00:12
```

> Con nota de **1 a 3 ⭐** el cierre cambia: *«⚠️ Puntuación baja: si es un ataque o un falso, se decide en Súper Admin → 🚩 Reclamos de opiniones.»* — es el caso que hay que atender rápido.
> El de `llamada_tienda` lleva dedupe de **10 min** por IP + tienda (el mismo toque repetido no vuelve a sonar); el de `opinion_nueva` lleva clave `opinion:<id>`, así que **una opinión = un aviso**.

### 5.6 Avisos sin ejemplo de mensaje capturado en las guías

Estos existen y funcionan, pero **no hay formato textual registrado**; el texto sale de la plantilla del motor `avisos_formato()`:

| `tipo` | Qué lleva el mensaje |
| --- | --- |
| `producto_nuevo` | "Producto nuevo o editado": el negocio agrega o cambia un producto de su ficha |
| `producto_borrado` | "Producto eliminado": el negocio borra un producto |
| `foto_nueva` | "Fotos nuevas en una ficha": fotos de la ficha o de un producto |
| `banner_clic` | "Clic en un banner": cada clic en la publicidad (apagado por defecto) |
| `reporte_contenido` | "Reportes de contenido": alguien reporta un negocio o producto (ver §11) |
| `resumen_dia` | "Resumen del día (22:00)": plantilla que **hoy ya no se usa** — ese interruptor manda el 🧠 informe del día (§13 y §18) |
| `caminante_incompleto` | "Caminante: subida incompleta": la captura de campo llegó con menos fotos de las que el celular envió (o con rechazadas / sin ninguna). Cuerpo: 📵 + 🏪 nombre + 📁 carpeta + 📷 enviadas/guardadas/rechazadas + 📍 ubicación + 🔍 detalle por archivo + ⚠️ motivo + 🗂️ ruta del registro |
| `empleo` | "Aviso de empleo nuevo" (o "POR APROBAR"): 💼 título + 🏢 entidad + 🔧 oficio + 📍 zona + 📞 teléfono + 💰 sueldo + 📝 resumen + quién lo publicó y, si está pendiente, los enlaces **✅ APROBAR** / **🗑️ RECHAZAR** |
| `noticias_dia` | "Noticias del día publicadas": 📰 + cuántas noticias locales nuevas + 📍 distritos + los titulares (máx. 6) + 🔗 `/noticias` |

> (verificar) El reporte de contenido se manda, según la guía vieja, con `notificar_jefe()` en nivel `urgente` y, a la vez, existe el tipo `reporte_contenido` en el catálogo; no se ha comprobado en esta sesión cuál de los dos caminos usa `api/reportar.php` desplegado. Lo mismo con las **opiniones reportadas**: `opinion_reportar()` usa `notificar_jefe()` (no hay tipo de catálogo para eso; en el resumen de la hora sí aparece la etiqueta «🚩 Opiniones reportadas»).

## 6) QUÉ **NO** LLEGA A TELEGRAM (Y POR QUÉ)

La verdad completa, para que no haya sorpresas:

| Flujo | Estado real |
| --- | --- |
| 🗑️ ~~**Tablones B2B (mensajes entre negocios)**~~ **RETIRADO (2026-09-13)** | El módulo **se retiró del sitio** (nunca se desplegó: las tablas `directorio_tablones` / `directorio_tablon_mensajes` **nunca existieron**) y su función `tablon_enviar_mensaje()` **se borró de `includes/helpers.php`**. Ya **no hay nada que enganchar**: este pendiente queda **cerrado y descartado**. |
| **Opiniones / reseñas de usuarios** | ✅ **YA SÍ AVISA (arreglado el 2026-09-15).** El módulo de **opiniones anónimas** se estrenó el **2026-09-14** y `opinion_crear()` **no avisaba de nada** (solo avisaba el botón 🚩 Reportar, con `notificar_jefe()`). Ahora cada opinión nueva dispara el aviso **`opinion_nueva`** (§4, §16 y §18). |
| **Pagos y Premium** | No hay pasarela de pago ni flujo de cobro en el código; los pagos se registran a mano. |
| **Lo que hace el propio jefe en el Súper Admin** (aprobar/ocultar tiendas, crear banners, dar Premium) | No se avisa a sí mismo, sería ruido. Se puede activar si lo pide. |
| **Impresiones de banners** | Solo se avisan los **clics** (y apagados por defecto). Las impresiones quedan en las estadísticas del panel de banners. |
| **Mensajes al negocio** (`directorio_mensajes`) | La función existe pero **nadie la usa**: la tabla está vacía. |
| **Robots uno por uno** | Apagado **a propósito**: van en el resumen por hora. Se cambia con `AVISOS_INCLUIR_ROBOTS_SUELTOS` (no recomendado). |
| **Errores de la base de datos** | ✅ **Sí avisa**: si la BD no responde, llega "LA BASE DE DATOS NO RESPONDE" (dentro de `error_sitio`). |

## 7) VISITAS: FILTRO DE PERSONA DE VERDAD (`solo_humanas`) — REHECHO EL 2026-09-15

**El problema, medido.** En este hosting hay una **granja de robots** que entra a una ficha distinta cada ~25
segundos, cada una desde una **IP de servidor distinta** (149.19.255.249, 155.94.177.89, 45.41.190.93…) y con
**modelos de móvil falsos** («Pixel 8 Pro», «iPhone 18_2»). Medido el 2026-09-10 a las 08:20: en 3 horas se habían
enviado **62 avisos de «visita a tienda»** y eran **puro ruido**; avisar de todas serían **~500 mensajes al día**.
Medido de nuevo el 2026-09-15 en **7 días**: **2 914 visitas a ficha** marcadas como «automáticas» con **2 509 IPs
distintas** — la granja es real y sigue.

**El otro lado del problema (la queja del jefe).** El filtro viejo tenía solo **3 señales** (sesión iniciada, llegar
de Google/redes o IP repetida), así que **se perdían visitas de gente de verdad**: el que **escribe la dirección a
mano** o **llega de un enlace sin referencia** (un WhatsApp reenviado, un cartel, un QR) no cumplía ninguna y se
contaba como robot. De ahí venía el *«siempre dicen que son robots, nunca dice que son personas»*.

**La solución: `avisos_senales_persona(array $d): array`** (`includes/avisos.php`). Devuelve **la lista de señales
encontradas** (vacía = no hay ninguna prueba de persona) y **sustituye a la lógica vieja** de
`avisos_visita_confiable()`, que hoy es solo un envoltorio de una línea sobre la función nueva:

| # | Señal | Cómo se comprueba | Por qué vale |
| --- | --- | --- | --- |
| 1 | 🔑 **Tiene sesión iniciada** | `$d['usuario_id']` | Un usuario logueado **nunca** es robot |
| 2 | 🌐 **Llega de Google / Bing / Instagram / Facebook / WhatsApp / TikTok** | regex sobre el `origen` (el `referer` traducido por `avisos_origen()`) | Llegada intencionada |
| 3 | 🔁 **Esa IP ya había entrado hoy** | `SELECT COUNT(*) FROM directorio_avisos_log WHERE ip = ? AND creado_en > (ahora − 1440 min)` | Visitante que vuelve |
| 4 | 🧠 **LA HUELLA DEL NAVEGADOR** (la nueva, y la más fuerte) | la cookie **`cz_stats`** del visitante consultada en **`directorio_stats_sesiones`** | La granja **no ejecuta JavaScript**: no deja rastro de navegador |

**La señal 4 (`🧠`) es la que arregla el falso «robot».** Se busca la cookie `cz_stats` (32 hex) en
`directorio_stats_sesiones` y basta **cualquiera** de estas cuatro cosas:

| Comprobación | Señal que se escribe en el mensaje |
| --- | --- |
| `COUNT(*) > 1` (más de una sesión con esa cookie = **dispositivo conocido**) | *es un dispositivo que ya había entrado antes* |
| `MAX(paginas) >= 2` (abrió otra página en esta visita) | *abrió más de una página en esta visita* |
| `SUM(segundos) > 0` (los **latidos JS** de tiempo en página) | *estuvo leyendo la ficha (N s)* |
| única sesión y `MAX(inicio)` de **hace más de 30 minutos** | *ya había pasado por el sitio hoy* |

**Datos reales que lo justifican (medidos el 14/09, 2026-09-15 en producción):** hubo **2 350 sesiones** ese día pero
**solo 12 abrieron más de una página** y **27 mandaron latidos de tiempo**. Es decir: la granja **crea la cookie
igual que una persona** pero **no ejecuta JavaScript** (no hay navegador de verdad detrás). Eso convierte a la
huella en la prueba más fiable de que hay gente — y también es la razón de **no** contar «todas las sesiones» como
personas en el informe (§5.4 y §18).

**Qué pasa con lo que no cumple ninguna señal:** se **cuenta como robot** y **no** genera mensaje. Se guarda en
`directorio_avisos_log` con `estado='robot'`, `es_bot=1` y `bot='Visita automática (sin señales de persona)'`
(por eso el resumen de robots puede separarlas por **prefijo**, §8). Y si **sí** hay señales:

- El mensaje lleva la línea **`✅ Persona real: …`** (`avisos_linea_quien()`, hasta 3 señales, §5.1).
- El registro guarda las señales en `senales_persona` y el `resumen` termina en **`· persona porque …`** (hasta 2).
- Todo va en **try/catch**: si el módulo de estadísticas no está o falta la tabla, se decide con las demás señales y **la web nunca se rompe**.

**Interruptor:** `AVISOS_VISITAS_MODO` en `includes/config_avisos.php` (el comentario de arriba de la constante
**ya lista las 4 señales**, incluida la 🧠 huella del navegador y el porqué: es la lectura rápida desde el archivo).

- `solo_humanas` → **valor actual**, recomendado (avisa solo con al menos una señal).
- `todas` → avisa de absolutamente todas. **NO recomendado** (fue justo el problema de arriba).

Y un usuario **con sesión iniciada nunca** se considera robot.

> 📊 **DE QUÉ CIFRAS HABLA EL INFORME (2026-09-15):** las «visitas de personas» del informe y del resumen de la
> hora **no** se cuentan con `estado='enviado'` sino con **`estado <> 'robot'`** (`avisos_personas()`,
> `avisos_top_negocios()` y las consultas de personas/ranking de `informe_inteligente.php`). El razonamiento es el
> del propio filtro: **cuando NO es persona, el motor escribe `robot`**; por lo tanto **cualquier otro estado**
> (`enviado`, `duplicado`, `agrupado` —el tope por hora—, `silencio`, `apagado`) significa que **sí pasó el filtro
> de persona**. Contar solo `enviado` dejaba fuera visitas de gente real (por ejemplo: la misma IP entrando dos
> veces a la misma ficha en 12 h → `duplicado`, o un pico que se pasó del tope → `agrupado`) y el informe salía
> **más pobre que la realidad**. ⚠️ **Consecuencia práctica: al comparar partes de antes y de después de este
> cambio, los números de PERSONAS SUBEN** (el parte del 14/09 decía 39 visitas contando solo `enviado`; con el
> criterio nuevo son más). No es que haya más gente: es que antes se estaba contando de menos.
> ℹ️ **`avisos_visita_confiable()`** sigue viva y **no es basura**: es la **versión CORTA** (sí/no) de
> `avisos_senales_persona()` —la pregunta directa para cualquier otro módulo—, mientras que **el motor usa la
> versión larga** porque además necesita saber **POR QUÉ**, que es lo que le permite escribir la línea
> **`✅ Persona real: …`**. Si se toca una, se tocan las dos (la corta solo devuelve `true/false`).

> 🔎 **Bonus del propio sistema:** el aviso de enlaces rotos descubrió que **cada visita al sitio pedía un JS que entonces no existía** (`assets/js/estadisticas.js`). Arreglado en `includes/footer.php`: la etiqueta solo se imprime si el archivo existe, así se activaba sola al publicar el módulo. **Ya se publicó**: el módulo de Estadísticas está desplegado y midiendo (2026-09-10).

## 8) ROBOTS: POR QUÉ NO LLEGAN UNO POR UNO (Y LOS DOS TRÁFICOS QUE HAY)

Los robots (Googlebot, AhrefsBot, scrapers con navegador headless, etc.) **no se avisan uno por uno**: se cuentan y salen en **un solo resumen por hora**. Se detectan por su *user-agent* (`avisos_robot()`) y sus visitas quedan registradas en el panel con su nombre, para ver quién está rastreando el sitio.

> 📈 **Dato real de este hosting (medido el 2026-09-10):** el sitio recibe **~1.150 visitas al día** desde **~1.100 IPs distintas** (entre 35 y 80 por hora, sin parar de día y de noche, casi todas de una sola vez y de países que no son Perú). Son **robots**, no personas: en un directorio local sin campañas no puede haber 1.100 visitantes distintos por día. Si esos avisos se mandaran uno por uno serían **más de mil mensajes diarios** (y Telegram acabaría bloqueando el envío). Por eso van en un resumen por hora.

### 8.1 🆕 HAY DOS TRÁFICOS AUTOMÁTICOS, Y NO VALEN LO MISMO (2026-09-15)

`avisos_robots($minutos)` ya no devuelve solo el total: devuelve **`total`, `top`, `conocidos`, `automaticas` y `personas`**, y el mensaje «🤖 TRÁFICO AUTOMÁTICO (última hora)» los separa con nombre (§5.3):

| Grupo | Qué es | Qué se hace con él |
| --- | --- | --- |
| 🔎 **`conocidos`** | Robots **con nombre**: Googlebot, Applebot, los de IA… | **Son buenos**: indexan el sitio y tratan el contenido. Se listan en el `top` con su nombre |
| 👥 **`automaticas`** | **La granja de IPs**: visitas a ficha que **no pasaron ninguna señal de persona** | Es el ruido que se cuenta, no se avisa uno por uno |
| 👤 **`personas`** | Las visitas a tienda **con señales de persona** de esa misma hora (`avisos_personas()`) | Va **justo debajo del total**, para que el mensaje no deje la duda de «¿y no entró nadie?» |

**⚠️ LA TRAMPA DEL NOMBRE TRUNCADO (costó un 0 en el primer intento).** La columna **`bot` es `VARCHAR(40)`**, así que el nombre que el motor escribe (`'Visita automática (sin señales de persona)'`) se guarda **cortado**:

```
Visita automática (sin señales de person
```

Por eso **NO se puede comparar por igualdad** (`bot = 'Visita automática…'` nunca coincidiría: el valor guardado es más corto). La comparación es **por PREFIJO**, igual en los dos sitios donde se usa:

```sql
SUM(bot LIKE 'Visita automática%')      AS automaticas,   -- la granja
SUM(bot NOT LIKE 'Visita automática%')  AS conocidos      -- Googlebot, Applebot, IA…
```

- En `includes/informe_inteligente.php` (`informe_datos()`), con ese mismo `LIKE`.
- En `includes/avisos.php` (`avisos_robots()`), con `strpos($bruto, 'Visita automática') === 0`, y además el nombre de la granja se **reescribe** a **«Automáticas (granja de IPs)»** para que el mensaje del jefe se lea bien.

> ✅ **El `total` es EXACTO (corregido el 2026-09-15):** `avisos_robots()` hace **un `COUNT(*)` aparte** sobre todas
> las filas con `es_bot=1` de la ventana (`SELECT COUNT(*) … WHERE es_bot = 1 AND creado_en > ?`), con **respaldo
> en la suma de los grupos** (`array_sum($top)`) solo si esa consulta falla. Antes el total era la **suma del
> `GROUP BY bot ... LIMIT 8`** y habría salido **corto** en cuanto aparecieran más de 8 nombres de robot distintos
> (encontrado al revisar el módulo). El informe del día usa `SUM`/`COUNT(*)` sobre todas las filas, así que ahí
> también es exacto.
> ⚠️ **Lo único que sigue saliendo del TOP 8 son `conocidos` y `automaticas`** (se suman recorriendo los grupos de
> la lista, que está limitada a 8): con los nombres que hay hoy no se llega al límite, pero si un día aparecen más
> de 8 nombres distintos, esas dos cifras del mensaje por hora pueden quedar algo por debajo — el **total** no.

Si algún día se quieren ver uno por uno: `AVISOS_INCLUIR_ROBOTS_SUELTOS = true` en `includes/config_avisos.php` (no se recomienda).

## 9) TOPES ANTI-SATURACIÓN Y DEDUPE

Para que el chat siga siendo útil:

| Control | Valor | Constante (`includes/config_avisos.php`) |
| --- | --- | --- |
| Máximo de avisos por hora, en total | **40** | `AVISOS_TOPE_HORA_TOTAL` |
| Tope de visitas a tiendas/productos por hora | **25** | `AVISOS_TOPE_HORA_VISTAS` |
| Tope de búsquedas por hora | **25** | `AVISOS_TOPE_HORA_BUSQUEDAS` |
| Tope de 404 por hora | **5** | `AVISOS_TOPE_HORA_404` |
| Tope de errores por hora | **5** | `AVISOS_TOPE_HORA_ERRORES` |

- Lo que se pasa del tope **no se pierde del todo**: se registra con `estado='agrupado'` en `directorio_avisos_log` y se ve en el panel. ⚠️ **Corrección del 2026-09-15:** el resumen de la hora **ya NO cuenta lo agrupado** — `avisos_resumen_hora()` pasa siempre `'agrupados' => 0` (el comentario del código dice que ahora sale sumado en su propia línea de personas/pedidos/búsquedas). Es decir: **el jefe ve menos avisos de los que hay y ese resto solo se ve en la base**, no en el mensaje.
- **No se repite lo mismo** (dedupe): la misma visita (misma IP + misma tienda) no se avisa dos veces en **12 h** (`AVISOS_DEDUPE_VISTA_MIN` = 720, la usa `registrar_vista()`); la misma búsqueda, **5 min** (`AVISOS_DEDUPE_BUSQUEDA_MIN`); el mismo error, **1 hora** (`AVISOS_DEDUPE_ERROR_MIN`); el **mismo 404, 12 h** ⚠️ pero ese plazo **no tiene constante**: va escrito en la llamada (`404.php` → `'dedupe_min' => 720`). Otros dedupes también viven **en la llamada**, no en el config: `llamada_tienda` y el `lead_precio` del copy, **10 min** (`api/llamada.php`, igual que `api/lead.php`); el 🧠 informe del día, **1 200 min**, y el de la semana, **10 080 min** (§13); la opinión nueva lleva clave **`opinion:<id>`**, así que **una opinión = un aviso** (pero **no** evita el aviso de las demás opiniones: si se insertan 5 opiniones de prueba, llegan 5 mensajes).
- ⚠️ **TRAMPA AL PROBAR LAS OPINIONES (2026-09-15):** **cualquier sonda o script que llame a `opinion_crear()` manda mensaje al Telegram del jefe** (el aviso `opinion_nueva` no distingue de dónde viene). Para probar hay que **apagar antes la fila `opinion_nueva` de `directorio_avisos_config` (`activo = 0`)** o **insertar la fila a mano** en `directorio_opiniones`. Se descubrió con el estreno real: ver §18.6.
- **Horario de silencio** (apagado por defecto): si algún día se quiere, se activa con `AVISOS_SILENCIO_ACTIVO` (por defecto `false`, franja `AVISOS_SILENCIO_DESDE` 1:00 am → `AVISOS_SILENCIO_HASTA` 7:00 am) y todo lo de la madrugada llega agrupado en el resumen de la mañana. ⚠️ **El 🧠 informe se salta el silencio a propósito** (`'ignorar_silencio' => true`): es el parte del día del jefe.
- **Resumen del día / informe:** `AVISOS_RESUMEN_DIA` (2026-09-15 quedó en **`true`**, pero **hoy no lo lee ningún archivo**: se conserva como marca del arreglo — quien decide es `aviso_activo('resumen_dia')`, o sea **el interruptor del panel**) · `AVISOS_RESUMEN_DIA_HORA` = **22** · `AVISOS_INFORME_HORA` = `AVISOS_RESUMEN_DIA_HORA` (**22:00**) · `AVISOS_INFORME_SEMANA_DIA` = **0** (domingo). Esas constantes también **pintan el rótulo del panel** (§4). Detalle: §13 y §18.

### 9.1 CÓMO DIAGNOSTICAR UN 404 O UN 500 MIRANDO LA BITÁCORA (técnica del 2026-09-10)

Cuando el jefe dice *"me están llegando muchos enlaces rotos"*, **el dato está en la BD**: la tabla
**`directorio_avisos_log`** guarda TODO aviso, incluso los que **no** llegaron al Telegram. Columnas:
`tipo`, `estado`, `clave`, `resumen` (aquí va la **ruta** en los 404), `ip`, `user_agent`, `es_bot`,
`bot`, `negocio_id`, `usuario_id`, `creado_en` (hora de Perú; el MySQL del hosting va en UTC).

**Estados del `estado` — leerlos bien es la mitad del diagnóstico:**

| estado | Qué significa |
|---|---|
| `enviado` | Salió al Telegram del jefe |
| `duplicado` | Se calló por dedupe (mismo 404: 12 h) |
| `agrupado` | **Se calló por el TOPE POR HORA** (`AVISOS_TOPE_HORA_404` = 5/h) — ojo: **el jefe ve menos avisos de los que hay** |
| `apagado` | Ese tipo está desmarcado en Súper Admin → 📱 Telegram |
| `silencio` | Horario de silencio |
| `robot` | Es un robot/crawler: se cuenta, no se avisa |

```sql
-- Los 404 agrupados por RUTA (sin el ?query), con cuántos son humanos y cuándo fue el último
SELECT SUBSTRING_INDEX(resumen,'?',1) AS ruta, COUNT(*) n, SUM(es_bot) bots,
       MIN(creado_en) desde, MAX(creado_en) hasta
  FROM directorio_avisos_log WHERE tipo='pagina_404'
 GROUP BY ruta ORDER BY n DESC LIMIT 40;

-- 404 que mencionan algo concreto (ej. reclamos): el patrón que delató el problema del jefe
SELECT resumen, estado, COUNT(*) n, MAX(creado_en) ultimo
  FROM directorio_avisos_log WHERE tipo='pagina_404' AND resumen LIKE '%reclam%'
 GROUP BY resumen, estado ORDER BY n DESC;
```

**Caso real (10/09/2026):** así se descubrió que **96 visitas HUMANAS** (`bots = 0`) iban a `/reclamar` y a
`/reclamar?q=…&rubro=…`, el buscador de reclamos viejo: su `reclamar.php` devolvía **ERROR 500** y la URL
sin `.php` no tenía regla en `.htaccess` → **404**. El botón de la ficha del negocio, que era lo que el
jefe señalaba, **funcionaba perfectamente**. *Lección:* **medir antes de arreglar** y no fiarse del enlace
que señala el jefe.

**⚠️ Los ERROR 500 NO llegan como "enlaces rotos", llegan como 💥 ERROR EN EL SITIO** (`error_sitio`).
Si el jefe se queja de errores, mirar también:

```sql
SELECT mensaje, archivo, COUNT(*) n, MAX(creado_en) FROM directorio_avisos_log
 WHERE tipo='error_sitio' GROUP BY mensaje, archivo ORDER BY n DESC LIMIT 20;
```

Páginas **viejas** que hoy devuelven **500** (medido el 2026-09-10 con
`python __reclamar_14_paginas_viejas.py`): `mercado.php`, `noticias.php`, `categorias.php`,
`distritos.php`, `ultimos.php`, `ranking.php`, `api.php`, `cron_noticias.php`, `opiniones.php`,
`track.php`. Son de la arquitectura anterior y **están pendientes de borrar o reescribir**. *No* se les
puso regla en `.htaccess` a propósito.

**Cómo se consulta esto sin phpMyAdmin:** con una **sonda temporal** (patrón de
`GUIA_DESPLIEGUE_Y_ENTORNO.md` §3.1) — el MySQL remoto **no** está abierto.

## 10) PANEL DE CONTROL DEL JEFE — `superadmin.php?seccion=avisos`

Súper Admin → **📱 Telegram** → https://dechimbote.com/superadmin.php?seccion=avisos

- **La lista completa de los 28 avisos**, agrupada por tema (*Visitas y búsquedas · Tiendas y productos · Personas · Publicidad · Sistema y errores*), cada una con su explicación. **La vista se pinta del catálogo** (`avisos_catalogo()` en `superadmin.php`): un tipo nuevo aparece solo, sin tocar el HTML del panel.
- Cada ítem tiene su **cuadrito de chequeo**: **marcado = la recibes**, desmarcado = no la recibes. Debajo de cada uno dice **"✅ Notificaciones activadas"** o **"🔕 Notificaciones desactivadas"** y cuántas se enviaron en 24 h / 7 días.
- Se cambia lo que se quiera y se pulsa **💾 Guardar cambios** (abajo, siempre visible). También hay **Marcar todas** y **Desmarcar todas**. Los cambios se aplican al instante.
- Arriba, 4 números: notificaciones activadas, desactivadas, enviadas en 24 h y robots en 24 h.
- 🆕 **Tarjeta «👤 Personas frente a 🤖 robots (24 h)» (2026-09-15)** — la respuesta directa a la queja del jefe
  («¿acaso no he recibido ninguna visita de persona?»), porque antes el único número que veía era el de robots:
  - 👤 **N** visita(s) de PERSONAS a tiendas (con **tiendas distintas** y **visitantes/IPs**), de `avisos_personas(1440)`
    — el mismo criterio del informe: **`estado <> 'robot'`** (§7).
  - 🤖 **N** visita(s) de robots, con el desglose **buscadores e IA** vs **automáticas sin señales**.
  - 📞/💬 **N** clic(s) de pedir en 24 h (📞 llamada · 💬 WhatsApp · 📦 consulta · 🛒 carrito), de `avisos_pedidos(1440)`.
  - Si hay robots y **cero** personas, lo dice con todas las letras: *«La granja de robots tapa el sitio, pero **no son personas**: cuando entra alguien de verdad, se avisa.»*
  - Y el recordatorio del informe con **dos enlaces para verlo en pantalla sin enviarlo**:
    `cron/monitoreo_sistema.php?k=<CLAVE>&solo-mostrar=1&informe=dia` · `…&informe=semana` (§13.1).
  - 🐛 **Trampa que costó un «Undefined constant»:** la tarjeta usa `MONITOREO_CLAVE_WEB`, que hasta entonces solo
    cargaba el cron; ahora `superadmin.php` hace un `require_once` defensivo de `includes/config_monitoreo.php` antes
    de pintarla. Sin eso, abrir 📱 Telegram **moría con un error fatal** (que además el propio sistema habría avisado
    al Telegram como 💥 ERROR).
- Los **robots más activos** de las últimas 24 h.
- La lista de los **últimos 25 avisos enviados** (con hora, IP y si era robot).

> 🕗 **Ejemplo real (2026-09-10):** el jefe desmarcó *"👁️ Visitas a una tienda"* porque le llegaban ~20 por hora de una granja de robots. 🗑️ **Nota (2026-09-13):** con el aviso del chat comunitario retirado el catálogo quedó uno menos. **Actualización (2026-09-15):** el catálogo quedó en **27 tipos** (los 4 nuevos: `llamada_tienda`, `opinion_nueva`, `informe_dia`, `informe_semana`) y desde el filtro nuevo de señales de persona (§7) la visita a tienda **ya está encendida y avisando de gente de verdad** (el informe del 14/09 contó visitas de personas).

## 11) REPORTES DE CONTENIDO 🚩 (AVISO + GESTIÓN EN EL SÚPER ADMIN)

El aviso `reporte_contenido` nace de aquí:

- **Tabla `directorio_reportes`** (la creó `migrar_reportes.php`, ya ejecutada y **autodestruida**): `usuario_id`, `negocio_id`, `producto_id`, `motivo`, `descripcion`, `ip`, `estado` (`pendiente`/`revisado`/`resuelto`/`ignorado`), `atendido_por`, `atendido_en`, `fecha`.
- **`api/reportar.php`**: valida CSRF, comprueba que el negocio exista y que el producto sea de ese negocio, limita a **3 reportes por usuario (o por IP si es anónimo) al día**, guarda y **avisa al jefe por Telegram** (nivel `urgente`). Responde JSON `{"ok":true,"mensaje":"Reporte enviado"}`.
- Motivos en lista blanca: `Fraude`, `Contenido inapropiado`, `Información falsa`, `Otro`. Descripción sin HTML.
- **`negocio.php`**: botón discreto **🚩 Reportar** cerca del final de la ficha y un modal (móvil-primero, campos de 16 px) con select de motivos, select de producto (si el negocio tiene productos) y descripción. Lo puede usar cualquiera: sin sesión, el reporte llega como "Anónimo".
- **`superadmin.php?seccion=reportes`**: sección **🚩 Reportes** en el menú (con globito de pendientes) que lista fecha, quién reportó, negocio, producto, motivo y descripción, con los botones **✓ Marcar como revisado**, **🚫 Suspender negocio** (lo oculta del directorio y cierra el reporte) y **🙈 Ignorar**, más un historial de los ya atendidos.
- Respuestas del endpoint: sin CSRF → **419**; motivo inventado → **422**; negocio inexistente → **404**; 4.º reporte del día → **429**.

## 12) WEBHOOK, COMANDOS DEL BOT Y SEGURIDAD

### 12.1 Webhook (configurado CON token secreto)

```
https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://dechimbote.com/api/telegram_bot.php&secret_token=<SECRETO>
```
(Sustituir `<TOKEN>` y `<SECRETO>` por los valores reales de `deploy/config.php`; **no se escriben aquí**.)

> El `secret_token` es **obligatorio**: Telegram lo manda en la cabecera `X-Telegram-Bot-Api-Secret-Token` y el bot la compara con `hash_equals`; sin ella responde **403**. Si algún día se vuelve a configurar el webhook **sin** `secret_token`, el bot dejará de responder.

- Estado del webhook: `https://api.telegram.org/bot<TOKEN>/getWebhookInfo`.
- Comprobar que el archivo está arriba: abrir https://dechimbote.com/api/telegram_bot.php en el navegador (GET) → muestra "Bot de DeChimbote.com activo".

### 12.2 Comandos del bot (`api/telegram_bot.php`)

| Comando | Qué hace |
| --- | --- |
| `/buscar <texto>` | Responde con los **3 negocios que más coinciden**, con su enlace `https://dechimbote.com/neg/<slug>` |
| `/buscar` sin texto | Pide el término: "🔍 ¿Qué buscas?" |
| `/start`, `/ayuda`, `/help` | Ayuda corta con el ejemplo `/buscar pollo` |
| Cualquier otro | "🤖 No conozco ese comando" + recordatorio de `/buscar` |
| `/estado` | **NO implementado todavía** (era la idea nº 9 del catálogo: "dashboard de bolsillo" con las métricas en vivo). No lo busques en el código: no existe. |

Cómo busca:

- Lee el JSON del POST de Telegram, saca `message.text` y `message.chat.id`.
- Responde **solo** al Chat ID del jefe (`8333560284`); cualquier otro chat se ignora y queda en el log.
- Admite también la forma `/buscar@Jimmychimbote_bot ...` (la que usa Telegram en grupos).
- Consulta `directorio_negocios` **con prepared statements** (nunca se concatena el término al SQL), filtrando `estado='activo'` y ordenando por **relevancia**: 1) coincide en el **nombre**, 2) en el **rubro** (categoría), 3) en la **descripción**, 4) tiene un **producto** con ese texto; dentro de cada grupo, los más vistos.
- Si no hay resultados con el término tal cual, prueba su variante singular/plural (`zapatillas` → `zapatilla`). Las tildes no importan (`pollería` encuentra "Polleria").
- Máximo **3 resultados**.

**Formato real de la respuesta:**

```
🔍 RESULTADOS PARA "zapatillas":

1️⃣ Coll zapatillas Chimbote
👟 Calzado · Chimbote
🔗 https://dechimbote.com/neg/coll-zapatillas-chimbote

2️⃣ ZAPATILLAS NIKE
👟 Calzado · Chimbote
🔗 https://dechimbote.com/neg/zapatillas-nike

3️⃣ Zapatillas Adidas
⚽ Deportes / Recreación · Chimbote
🔗 https://dechimbote.com/neg/zapatillas-adidas
```

Sin resultados: `❌ No encontré negocios con 'xyzabcnoexiste'. Intenta con otro término.`

### 12.3 Seguridad

| Punto | Cómo queda |
| --- | --- |
| Webhook | Token secreto en la cabecera `X-Telegram-Bot-Api-Secret-Token`, comparado con `hash_equals`. Sin ella → **403** |
| Autorización del bot | Solo responde al chat `8333560284`; otros chats se registran y se ignoran |
| Inyección SQL | **Prepared statements** en todas las consultas (el término nunca se concatena) |
| Cron | Sin la clave responde "Acceso denegado" y no ejecuta nada (sirve también para el navegador) |
| Reportes | CSRF obligatorio, motivos en lista blanca, descripción sin HTML y máximo 3/día |
| Migración | Clave secreta + se **autodestruye** al terminar (ya no existe en el servidor) |

> ⚠️ **Corrección importante:** `e()` **no** protege contra inyección SQL (escapa HTML). Para SQL se usan *prepared statements*; `e()` se usa solo al pintar datos en la web.

## 13) CRON JOB DEL MONITOREO DEL SERVIDOR (hPanel)

**Estado actual: ya configurado.** hPanel → Avanzado → Cron Jobs → tipo **Personalizado**, frecuencia cada hora (`0 * * * *`):

```
0 * * * *  /usr/bin/php /home/u196269909/domains/dechimbote.com/public_html/cron/monitoreo_sistema.php
```

- ⚠️ La ruta `/home/u196269909/public_html/cron/...` **no existe** en este hosting: la correcta lleva `domains/dechimbote.com` (comprobado en el servidor con `realpath`).
- 🐛 **Error conocido:** si el trabajo se crea con tipo **PHP**, hPanel intenta ejecutar el archivo directamente y falla con `timeout: failed to run command '.../monitoreo_sistema.php': Permission denied`. El archivo tiene permisos `-rw-r--r--` (644): no es ejecutable y no lleva intérprete. **Solución:** tipo **Personalizado** llamando a `/usr/bin/php` como arriba.

**Qué vigila `cron/monitoreo_sistema.php`:**

- **Disco** (`disk_free_space`/`disk_total_space`), **CPU** (`sys_getloadavg` → `/proc/loadavg` → `uptime`, traducido a % según los núcleos) y **memoria** (`/proc/meminfo`, `MemAvailable`).
- **Tamaño real de la carpeta del sitio** (`du`), extra propio de este hosting: el disco que reporta Hostinger (21 TB) es el de **todo** el servidor compartido, no el de la cuenta.
- **Si todo está bajo los umbrales no envía nada** (silencio = todo OK); solo escribe una línea en el log del cron.
- Guarda su estado **fuera de la web**: `/home/u196269909/domains/dechimbote.com/.monitoreo_chimbote.json`.
- También dispara desde aquí el **resumen de la hora** (§6.1), el **resumen de robots** (§6.2), el **🧠 informe inteligente del día y de la semana** (§6.3, nuevo el 2026-09-15) y la **limpieza** del historial de avisos (más de 30 días).

### 13.1 🧠 §6.3 — EL INFORME INTELIGENTE EN EL CRON (NUEVO, 2026-09-15) Y EL ARREGLO DEL «RESUMEN DEL DÍA»

**🐛 EL ARREGLO PRIMERO (por qué el «📅 Resumen del día» NUNCA se envió):** el panel lo **encendía** en la tabla
`directorio_avisos_config`, pero `cron/monitoreo_sistema.php` **exigía además la constante `AVISOS_RESUMEN_DIA`**,
que estaba en **`false`** → la condición **no se cumplía nunca** y ese mensaje **jamás salió** (ni un solo día).
Arreglado así:

1. `AVISOS_RESUMEN_DIA` quedó en **`true`** en `includes/config_avisos.php` (con el comentario de la trampa). ⚠️ Ojo: **hoy esa constante no la lee ningún archivo** — se conserva como marca del arreglo; la decisión la toma **`aviso_activo('resumen_dia')`**, es decir **el panel**.
2. **Manda el interruptor del panel**: `$informe_activo = aviso_activo('informe_dia') || aviso_activo('informe_semana') || aviso_activo('resumen_dia')`.
3. Ese resumen del día **ES el informe inteligente**: se retiró el mensaje viejo de contadores sueltos (la plantilla `resumen_dia` sigue en `avisos_formato()`, pero **hoy no la usa nadie**). Quien tenga marcado **📅 Resumen del día (22:00)** en el panel recibe el 🧠 informe aunque `informe_dia` esté desmarcado.

**Qué hace el bloque §6.3** (el número de sección con el que se busca en el archivo: `// 6.3 🧠 EL INFORME INTELIGENTE`):

- Calcula la **hora** (`AVISOS_INFORME_HORA`) y el **día de la semana** (`date('w')`, **0 = domingo**) contra `AVISOS_INFORME_SEMANA_DIA`.
- **Día** (`informe_dia`): todos los días, si la hora actual (`date('G')`) coincide con `AVISOS_INFORME_HORA`.
- **Semana** (`informe_semana`): solo si además el día coincide con `AVISOS_INFORME_SEMANA_DIA` (**0 = domingo**). Usa `informe_semana_lineas()` (7 días corridos comparados con los 7 anteriores).
- **El parte del día es el DÍA NATURAL:** de **00:00 de hoy** a la hora del envío, y el `▲/▼ frente al periodo anterior` compara contra **AYER a la misma hora** (`informe_rango_hoy()` / `informe_rango_ayer()` en `includes/informe_inteligente.php`, activados por el parámetro **`$calendario`** de `informe_datos()`). Antes eran «últimas 24 h» contra las 24 h anteriores y el parte del cierre salía siempre con un **-50 % falso**. Detalle: §5.4.
- Los dos salen por el **motor** `aviso()` (§3), con **clave de dedupe**:
  - `informe:dia:YYYY-MM-DD` con `dedupe_min` = **1 200** (20 h)
  - `informe:semana:oW` (semana ISO) con `dedupe_min` = **10 080** (7 días)
  → **aunque el cron corra dos veces en la misma hora, el informe NO se repite.**
- Va con **`'ignorar_silencio' => true`** y queda registrado en `directorio_avisos_log` (se ve en el panel y en Súper Admin).
- Todo el bloque está dentro del **try/catch** del §6: un fallo aquí **no rompe** el monitoreo del servidor.

**Qué NO se puede tocar sin pensarlo:**

| Cosa | Por qué |
| --- | --- |
| `require_once includes/informe_inteligente.php` | El informe **no manda nada por su cuenta**: si no se carga, no hay líneas |
| La **clave de dedupe** | Es lo único que impide que el jefe reciba el mismo parte dos veces |
| El **`$calendario`** del día | Es lo que hace que el parte del cierre sea el **día natural** y que se compare con **ayer a la misma hora**; si se quita, vuelve el **-50 % falso** |
| El orden de los `if` de `$toca_dia` / `$toca_semana` | Fuera de la prueba a mano, el parte de la semana **sustituye** al del día ese día (los dos pueden salir el domingo a esa hora si ambos interruptores están encendidos: es lo previsto) |

**Horas configurables** (`includes/config_avisos.php`, se pueden sobrescribir desde `config.php`):

| Ajuste | Valor de fábrica | Constante | Qué manda |
| --- | --- | --- | --- |
| Hora del informe del día | **22** (22:00 de Lima) | `AVISOS_INFORME_HORA` | A qué hora sale el 🧠 informe diario (y **lo que dice su rótulo en el panel**) |
| Hora del viejo resumen del día | **22** | `AVISOS_RESUMEN_DIA_HORA` | Es de donde sale `AVISOS_INFORME_HORA` (una sola verdad) |
| Día de la semana del informe semanal | **0 = domingo** | `AVISOS_INFORME_SEMANA_DIA` | Qué día sale el 🏆 informe de los 7 días (`date('w')`: 0=dom, 1=lun…), también en el rótulo |
| Días que se conservan en el registro | **30** | `AVISOS_DIAS_HISTORIAL` | Hasta dónde llega el informe hacia atrás y qué borra la limpieza del §6.4 |

> 🏷️ **EL RÓTULO DEL PANEL SALE DE LA CONSTANTE (ya no miente):** los títulos de `informe_dia`, `informe_semana` y `resumen_dia` se construyen **en el catálogo** con `AVISOS_INFORME_HORA`, `AVISOS_RESUMEN_DIA_HORA` y **`avisos_dia_semana_nombre()`** (ayudante nuevo en `includes/avisos.php`, **0 = domingo**). Hoy el panel dice
> «🧠 Informe inteligente del día (**22**:00)» y «🏆 Informe de la semana (**domingo 22**:00)», que es exactamente
> cuándo se envían. **Si el jefe cambia la hora (o el día) en `config_avisos.php`, el rótulo cambia solo**: no hay
> que tocar la vista ni el catálogo. (Antes decían «21:00» escrito a mano mientras el envío era a las 22:00:
> desfase encontrado y corregido el 2026-09-15.)

**Prueba a mano desde el navegador** (la clave `k` es obligatoria; ver §14):

```
https://dechimbote.com/cron/monitoreo_sistema.php?k=ChimboteCron2026%23Jimmy&solo-mostrar=1&informe=dia
https://dechimbote.com/cron/monitoreo_sistema.php?k=ChimboteCron2026%23Jimmy&solo-mostrar=1&informe=semana
```

- Con **`&solo-mostrar=1`** el informe se **imprime en pantalla** y **NO se envía** al Telegram.
- **Sin `solo-mostrar` se envía de verdad** (y gasta la clave de dedupe del día/semana: si ya se envió, el motor lo calla).
- **`&informe=dia`** (o `&informe=1`) manda **solo el del día**; **`&informe=semana`** manda **solo el de la semana**. ✅ Corregido el 2026-09-15: antes `&informe=semana` encolaba **también** el del día y llegaban **dos mensajes seguidos**.
- La prueba a mano **se salta los interruptores del panel** (el `|| $pide_informe !== ''` del §6.3): sirve justo para **ver el informe** aunque esté apagado en Súper Admin → 📱 Telegram.
- 👀 **Los dos enlaces están también en el panel** (tarjeta «👤 Personas frente a 🤖 robots (24 h)», §10): «🧠 el del día» y «🏆 el de la semana», los dos con `solo-mostrar=1` — **no envían nada**, solo lo muestran en pantalla.

**Umbrales** (`includes/config_monitoreo.php`, se pueden sobrescribir desde `config.php`):

| Ajuste | Valor | Constante |
| --- | --- | --- |
| Disco | **85 %** | `ALERTA_DISCO_PORCENTAJE` |
| CPU | **80 %** | `ALERTA_CPU_PORCENTAJE` |
| Memoria | **90 %** | `ALERTA_MEMORIA_PORCENTAJE` |
| Carpeta del sitio | **10 GB** | `MONITOREO_CARPETA_ALERTA_GB` |
| Máximo un aviso del mismo problema cada | **6 h** | `MONITOREO_ESPERA_HORAS` |
| Lecturas seguidas para CPU | **2** | `MONITOREO_CONFIRMAR_CPU` |
| Lecturas seguidas para memoria | **2** | `MONITOREO_CONFIRMAR_MEMORIA` |
| Lecturas seguidas para disco | **1** (avisa de inmediato) | `MONITOREO_CONFIRMAR_DISCO` |
| Avisar cuando el problema desaparece | **No** (silencio = todo OK) | `MONITOREO_AVISAR_RECUPERACION` |
| Clave de la prueba web | *(valor real en `deploy/includes/config_monitoreo.php`)* | `MONITOREO_CLAVE_WEB` |

> **Anti falsas alarmas:** en un servidor compartido la CPU y la memoria son de todos los clientes, así que un aviso de CPU/memoria exige **2 lecturas seguidas** por encima del umbral. El disco avisa de inmediato, porque eso sí es de la cuenta.

> 💡 El PHP de línea de comandos de este hosting tiene `shell_exec`, `exec` y `system` **deshabilitados** (el de la web no). Por eso el monitor mide el tamaño de la carpeta con PHP puro cuando corre por cron.

## 14) COMPROBACIONES ÚTILES

| Qué | Cómo |
| --- | --- |
| Ver el monitor | `https://dechimbote.com/cron/monitoreo_sistema.php?k=<CLAVE>&estado=1` → muestra "Última ejecución" y "Ejecuciones": si el número sube cada hora, el cron funciona |
| Ver métricas sin enviar nada | `...&solo-mostrar=1` |
| 🧠 **Ver el informe inteligente SIN enviarlo** | `...&solo-mostrar=1&informe=dia` (o `&informe=semana`) → lo imprime en pantalla (§13.1) |
| 🧠 **Enviar el informe a mano** | `...&informe=dia` (sin `solo-mostrar`) — respeta el interruptor del panel y la clave de dedupe del día. `&informe=semana` manda **solo** el de la semana (§13.1) |
| 👀 **Ver el informe sin enviarlo** | Los dos enlaces de la tarjeta «👤 Personas frente a 🤖 robots» del panel 📱 Telegram (§10), o `...&solo-mostrar=1&informe=dia` |
| Enviar una alerta de prueba | `...&probar=1` (llega "🧪 PRUEBA TÉCNICA") |
| Ver los avisos del panel | https://dechimbote.com/superadmin.php?seccion=avisos |
| Ver los reportes | https://dechimbote.com/superadmin.php?seccion=reportes |
| Comprobar el bot | https://dechimbote.com/api/telegram_bot.php |
| Estado del webhook | `https://api.telegram.org/bot<TOKEN>/getWebhookInfo` (`pending_update_count` en 0 = no hay mensajes sin procesar) |
| Cron sin clave | Responde "Acceso denegado" / **403** y no ejecuta nada |

Desde línea de comandos el monitor entiende los mismos modificadores: `--solo-mostrar`, `--probar`, `--estado`.

## 15) TABLAS, ARCHIVOS Y AJUSTES IMPLICADOS

| Qué | Detalle |
| --- | --- |
| `includes/avisos.php` | Motor: catálogo de tipos (**27**, con los rótulos de hora/día armados con las constantes y el ayudante **`avisos_dia_semana_nombre()`**), formatos, dedupe, topes, robots, errores fatales, `aviso()`, **`avisos_senales_persona()`** (§7), **`avisos_personas()`** (cuenta **`estado <> 'robot'`**), `avisos_robots()` con `conocidos`/`automaticas` y **total por `COUNT(*)`**, `avisos_pedidos()` y `avisos_resumen_hora()` rehecho |
| 🧠 **`includes/informe_inteligente.php`** | **NUEVO (2026-09-15).** El motor del informe que el jefe pidió: `informe_datos($dias, $calendario)`, `informe_lineas()`, `informe_dia_lineas()`, `informe_semana_lineas()`, **`informe_rango_hoy()`** y **`informe_rango_ayer()`** (el **día natural** y su comparación a la misma hora), `informe_rango()`, `informe_variacion()`, `informe_tienda()`, `informe_q()`, `informe_tabla()`, `informe_columna()`, `informe_n()`. **No manda nada**: devuelve las líneas y el cron las envía por `aviso()` (§18). Consulta `directorio_avisos_log`, `directorio_stats_sesiones`, `directorio_pedidos`, `directorio_busquedas`, `directorio_opiniones`, `directorio_reportes`, `directorio_opiniones_reportes`, `directorio_reclamos`, `directorio_noticias` y `directorio_empleos` |
| `includes/config_avisos.php` | Topes, dedupe, robots, modo de visitas, silencio y la **hora del informe**: `AVISOS_RESUMEN_DIA` (**`true`** desde el 2026-09-15), `AVISOS_RESUMEN_DIA_HORA` (**22**), **`AVISOS_INFORME_HORA`** (= la anterior) y **`AVISOS_INFORME_SEMANA_DIA`** (**0 = domingo**) |
| `includes/config_monitoreo.php` | Umbrales del monitoreo y clave de la prueba web |
| `includes/helpers.php` | `telegram_enviar()`, `notificar_jefe()`, constantes `TELEGRAM_*`, `registrar_vista()`, `crear_reclamo()`, `crear_postulante()` · **`descripcion_negocio_html()`**: los botones del copy salen con **`data-negocio="<id>"`** (el `cz-btn cz-wa` → `<a class="cz-btn cz-btn--wa">` y el `cz-btn cz-tel` → `<a class="cz-btn cz-btn--tel">`), que es lo que permite al JS saber de qué tienda es el clic (§18) (🗑️ `tablon_enviar_mensaje()` **ya no existe**: se borró el 2026-09-13 con el módulo de tablones) |
| `includes/opiniones.php` | `opinion_crear()` → **`aviso('opinion_nueva', …)`** (nuevo el 2026-09-15, §6 y §18); `opinion_reportar()` sigue avisando con `notificar_jefe()` |
| `includes/metricas.php` | Récords. **2026-09-15:** el `CREATE TABLE` de `directorio_pedidos` lleva el ENUM **`('clic','consulta','pedido','llamada')`**, `metrica_pedido()` acepta `'llamada'` con la comprobación defensiva **`metricas_tipo_llamada_ok()`** (si la base no lo aceptara, guarda `'clic'` en vez de perder el clic), `metricas_top_pedidos()` devuelve la columna **`llamadas`** y `metricas_pedido_tipo_txt()` traduce «📞 Tocó «Llamar»» |
| `includes/vista_records_admin.php` | La tabla **🔥 Tiendas con más pedidos** ganó la columna **📞** (llamadas) y el subtítulo dice «… + llamada 📞» |
| `cron/monitoreo_sistema.php` | Monitoreo del servidor + §6.1 resumen de la hora, §6.2 robots, **§6.3 informe inteligente (día/semana)** y §6.4 limpieza |
| 📞 **`api/llamada.php`** | **NUEVO (2026-09-15).** Recibe el *beacon* del botón Llamar y de los WhatsApp del copy: guarda en `directorio_pedidos` (`tipo='llamada'` o `'clic'`) y avisa (`llamada_tienda` / `lead_precio`). Responde **204** y trabaja **después** de responder (`litespeed_finish_request`). Sin CSRF: es un contador anónimo como `api/lead.php`. **Se salta a propósito el filtro de robots** (`'ignorar_filtro_robot' => true` y `'es_bot' => 0`): el beacon solo lo manda un navegador real y el UA marcaría como robot a quien navega dentro de WhatsApp/Telegram (§18.7) |
| 📞 **`assets/js/llamadas.js`** | **NUEVO (2026-09-15).** Mide el toque en `tel:` y en los `wa.me` del copy con `navigator.sendBeacon` (respaldo `Image()`) y **deja seguir el enlace**. **2026-09-16 (`?v=2`, arreglo de los falsos positivos, §19):** YA NO cuenta el `pointerdown`; cuenta el **`click`** (con respaldo de **`pointerup`** y solo si el dedo **no se movió más de 12 px** ni estuvo más de 1,5 s) y pega el marcador **`&c=1`** en los enlaces de `api/lead.php` **en el momento del clic**. Dedupe por enlace y por carga de página. Cargado en `includes/footer.php` como `assets/js/llamadas.js?v=2` |
| `api/telegram_bot.php` | Webhook y comandos del bot |
| `api/reportar.php` | Endpoint de reportes de contenido |
| `api/lead.php` | Clic a WhatsApp de la ficha: avisa el lead, guarda el récord y redirige a WhatsApp |
| 🗑️ ~~`api/chat_comunidad.php`~~ **BORRADO (2026-09-13)** | Avisaba de cada mensaje del tablón. El archivo **ya no existe** (el chat comunitario se retiró del sitio) |
| `superadmin.php` | Panel 📱 Telegram (`?seccion=avisos`, **se pinta del catálogo**) y 🚩 Reportes (`?seccion=reportes`). **2026-09-15:** tarjeta **👤 Personas frente a 🤖 robots (24 h)** con `avisos_personas()` + `avisos_pedidos()` y los 2 enlaces de «ver el informe sin enviarlo»; hace un `require_once` defensivo de `includes/config_monitoreo.php` para tener `MONITOREO_CLAVE_WEB` (§10) |
| `directorio_avisos_config` | Encendido/apagado de cada aviso (lo maneja el panel) |
| `directorio_avisos_log` | Registro de todo (tipo, estado, clave de dedupe, IP, user-agent, robot, negocio, usuario, fecha). Se borra lo que tenga más de **30 días**. **2026-09-15: se le añadió el índice `idx_ip_fecha (ip, creado_en)`** — lo usa la señal 3 de persona (§7) y los conteos del informe |
| `directorio_pedidos` | **Cambio de estructura del 2026-09-15:** `tipo` pasó a **`ENUM('clic','consulta','pedido','llamada') NOT NULL DEFAULT 'clic'`** (`ALTER TABLE … MODIFY tipo …`, aplicado en producción por sonda temporal, ya borrada) para que quepa el clic del botón 📞 Llamar |
| 🏆 `directorio_busquedas` · `directorio_pedidos` | **Tablas del módulo 🏆 Récords (2026-09-13)**: **cada búsqueda** (con sus resultados) y **cada botón de pedido**. Existen porque este registro de avisos **no guardaba el término ni el pedido de forma consultable** (solo un `resumen` de texto) y además **se borra a los 30 días**. Guía: `GUIA_RECORDS_DEL_SITIO.md` |
| `directorio_stats_sesiones` | Sesiones con cookie `cz_stats` (`paginas`, `segundos`, `inicio`). Es la tabla del **módulo de Estadísticas** que da la **🧠 huella del navegador** (§7) y los «navegadores de verdad» del informe (§18). Nada se escribe aquí desde los avisos: solo se **lee** |
| 🏆 `includes/metricas.php` | Motor de los récords. **⚠️ `metricas_sembrar()` LEE este registro**: saca el término buscado del campo **`resumen`** de `directorio_avisos_log`, que el motor escribe **siempre** como `«<término> (<N> res.)»` (y el total del carrito como `«Total referencial: S/ …»`). **Si se cambia ese formato en `avisos_formato()`, hay que cambiar también el regex de `metricas_sembrar()`** (no rompe nada: se salta las filas) |
| `directorio_reportes` | Reportes de contenido (ver §11) |
| `migrar_avisos.php` | Creó las tablas de avisos (ya ejecutado y **autodestruido**) |
| `migrar_reportes.php` | Creó `directorio_reportes` (ya ejecutado y **autodestruido**) |

## 16) DÓNDE ESTÁ CADA ENGANCHE (ARCHIVO → AVISO)

| Evento | Archivo | Punto exacto |
| --- | --- | --- |
| Búsqueda | `buscar.php` | tras calcular `$resultados` |
| 🏆 **Búsqueda (guárdala para los récords)** | `buscar.php` | **justo después del `aviso()`** → `metrica_busqueda($termino, count($resultados), ['origen'=>'web'])`. **Los dos van juntos: si se mueve uno, mover el otro** |
| 🏆 **Búsqueda del chat** | `includes/chatbot.php` | bloque 3-bis (buscador vivo), tras `chatbot_buscar()` → `origen='chat'` |
| 🏆 **Pedido / clic de WhatsApp** | `api/lead.php` | **después de `header('Location: …')`** (el visitante no espera) → `metrica_pedido([...])` |
| Visita a tienda / producto | `includes/helpers.php` | dentro de `registrar_vista()` — y el **filtro de persona** (`avisos_senales_persona()`) se aplica después, dentro de `aviso()` |
| Clic a WhatsApp (lead) | `api/lead.php` + botones en `negocio.php` | el botón pasa por el contador y luego va a WhatsApp |
| 📞 **Toque en «Llamar»** | **`assets/js/llamadas.js` → `api/llamada.php`** | el JS escucha el **`click`** (y un `pointerup` de respaldo que exige no haberse movido) en cualquier `href^="tel:"` y manda el beacon; el endpoint guarda el récord (`tipo='llamada'`, con **`ignorar_filtro_robot`**) y dispara **`aviso('llamada_tienda', …)` con `'es_bot' => 0`** (por qué: §18.7). Los enlaces llevan **`data-negocio="<id>"`** (`negocio.php` y los botones del copy) o el JS lee el `<div data-negocio-id>` de la ficha |
| 💬 **WhatsApp del copy (salta `api/lead.php`)** | **`assets/js/llamadas.js` → `api/llamada.php`** | el JS detecta los `wa.me` / `api.whatsapp.com` / `web.whatsapp.com` que **no** pasan por `api/lead.php` ni por `/carrito` → guarda `tipo='clic'` y dispara **`aviso('lead_precio', …)`**. Y a los que **sí** pasan por `api/lead.php` les pega **`&c=1`** (la prueba de clic que ese endpoint exige desde el 2026-09-16, §19) |
| 💬 **Opinión nueva** | **`includes/opiniones.php`** | dentro de `opinion_crear()`, **después del INSERT** y de `opiniones_recalcular_rating()` → `aviso('opinion_nueva', ['negocio_id','autor','rating','texto','clave'=>'opinion:<id>'])`. Va en try/catch: **nunca rompe la publicación de la opinión** |
| Reclamo | `includes/helpers.php` | dentro de `crear_reclamo()` |
| Postulación | `includes/helpers.php` | dentro de `crear_postulante()` |
| 🗑️ ~~Chat comunitario~~ **RETIRADO (2026-09-13)** | ~~`api/chat_comunidad.php`~~ | tras el INSERT — el archivo **ya no existe** (módulo retirado del sitio) |
| Usuario nuevo | `registro.php` y `google_callback.php` | tras el INSERT |
| Tienda nueva | `registrar_negocio.php`, `guardar_asistente.php`, `caminante/subir.php` | tras el INSERT |
| Enlace roto | `404.php` | al inicio |
| Error fatal | `includes/avisos.php` | `register_shutdown_function` (se carga desde `helpers.php`) |
| Reporte de contenido | `api/reportar.php` | al guardar el reporte |
| 🧠 **Informe del día y de la semana** | **`cron/monitoreo_sistema.php` (§6.3) → `includes/informe_inteligente.php`** | el cron arma las líneas con `informe_dia_lineas()` / `informe_semana_lineas()` y las manda con **`aviso('informe_dia'/'informe_semana', …)`** (clave `informe:dia:YYYY-MM-DD` · `informe:semana:oW`) |
| Resumen de la hora, robots y limpieza | `cron/monitoreo_sistema.php` | §6.1 `avisos_resumen_hora(60)` · §6.2 `avisos_robots(60)` + `avisos_formato('visita_robot', …)` · §6.4 borra el registro de más de 30 días |
| Alertas de disco/CPU/memoria | `cron/monitoreo_sistema.php` | tras medir, solo si se pasa el umbral |

## 17) PENDIENTES DEL BOT Y DEL MONITOREO

✅ **Lo que dejó de ser pendiente el 2026-09-15:**

- [x] **El cron del monitoreo en hPanel ya funciona** (§13): el trabajo horario corre y desde ahí salen el resumen de la hora, los robots y el **🧠 informe del día y de la semana**.
- [x] **El «📅 Resumen del día» ya se envía**: el bug de los dos interruptores está arreglado y ese interruptor manda el informe inteligente (§13.1).
- [x] **Las opiniones nuevas ya avisan** (`opinion_nueva`): antes el jefe solo se enteraba entrando al panel (§6 y §18).
- [x] **El dato que el jefe pidió ya existe**: los **clics del botón 📞 Llamar** (y los WhatsApp del copy) se miden y salen en el informe, en el resumen de la hora y en la pestaña 🏆 Récords — columna 📞 (§18).

⏳ **Lo que de verdad queda:**

- [ ] **El Cron Job de las NOTICIAS sigue pendiente de poner en hPanel** (11:00 UTC = 06:00 de Chimbote). Mientras no esté, **la tarea horaria de este mismo cron la llama ella sola** a la hora que toca (guarda `NOTICIAS_CRON_HORA_LIMA` + el registro del día). Detalle: `GUIA_NOTICIAS_DIARIAS.md`.
- [ ] **`/estado` (dashboard de bolsillo)**: comando que responda con las métricas en vivo del sitio. Idea del catálogo, con base lista, todavía sin implementar.
- [ ] **Recordatorios de vencimiento** de Premium y banners (avisar 3 días antes). Usarían `notificar_jefe()`.
- [ ] **Aviso al jefe cuando un negocio cruza los 3 pedidos** en una hora (lead scoring). El resto del «Dashboard de Oportunidades» ya está hecho con la pestaña **🏆 Récords del sitio** (`GUIA_RECORDS_DEL_SITIO.md`): las tiendas ordenadas por **pedidos** (🛒 carrito / ❓ consulta / 💬 WhatsApp / 📞 llamada y los soles), las que mejor convierten y las que **nadie mira**. ⚠️ **Ya hay resumen por Telegram** desde el 2026-09-15: es el 🧠 informe del día (§18).
- [ ] **`cron_reporte_ventas.php` semanal** (los lunes a las 9:00): el 🏆 informe de la semana del 2026-09-15 **lo cubre en gran parte** (sale los domingos con los 7 días comparados). Si se quiere el lunes, se cambia `AVISOS_INFORME_SEMANA_DIA` (1 = lunes) y `AVISOS_INFORME_HORA` — es **dato**, no código nuevo.
- [ ] Otras ideas del catálogo aún sin hacer: alerta con botones inline `[Aprobar]` `[Rechazar]` para tiendas del Caminante, aviso de 5 intentos fallidos de login en el Súper Admin, récords de Caminante, modo mantenimiento `/mantenimiento`, aviso de carrito abandonado y confirmación de pagos (esta última depende de que exista pasarela de pago).

## 18) 🧠 EL INFORME INTELIGENTE Y EL BOTÓN 📞 LLAMAR (2026-09-15)

### 18.0 POR QUÉ SE REHIZO TODO ESTO — LA QUEJA DEL JEFE (2026-09-14, textual)

> *«los mensajes que me llegan por Telegram siempre dicen que son robots, nunca dice que son personas… revisa ese
> sistema de Telegram, mejóralo, CRUZA INFORMACIÓN y dame resultados que sean para mí de interés, como por ejemplo
> las tiendas que están recibiendo más clics en el botón de llamada, o qué tienda esta semana está destacando más
> que las demás, si se ha reportado alguna tienda y todo eso. NECESITO DATOS MÁS INTELIGENTES.»*
> — Jimmy López, 2026-09-14

Se leyeron **las cuatro cosas que pidió** y cada una tiene su arreglo, ya desplegado y verificado en producción:

| Lo que pidió | Lo que se hizo |
| --- | --- |
| *«nunca dice que son personas»* | **Clasificación persona/robot rehecha** (`avisos_senales_persona()`, §7) + la línea **`✅ Persona real: …`** en cada visita + el informe abre con **👤 PERSONAS** |
| *«las tiendas que reciben más clics en el botón de llamada»* | **El botón 📞 Llamar ya se mide** (`api/llamada.php` + `assets/js/llamadas.js`, §18.6) y sale en 📞 **LAS QUE MÁS PIDEN** del informe y en el resumen de la hora |
| *«qué tienda esta semana está destacando más que las demás»* | **🏆 LA QUE MÁS DESTACA**: `visitas de personas + 3 × clics`, comparada con el periodo anterior (§18.5) |
| *«si se ha reportado alguna tienda»* | **🚩 REPORTES**: reportes de contenido + opiniones reportadas + reclamos, **siempre los pendientes** (§18.5) |
| *«CRUZA INFORMACIÓN» / «DATOS MÁS INTELIGENTES»* | **`includes/informe_inteligente.php`** (archivo nuevo): un parte que cruza 10 tablas y **el resumen de la hora rehecho** para que también cruce (§18.4 y §18.5) |

### 18.1 LO QUE CAMBIÓ, DE UN GOLPE

| # | Cambio | Dónde | Detalle |
| --- | --- | --- | --- |
| 1 | **Clasificación persona/robot rehecha** | `includes/avisos.php` → `avisos_senales_persona()` | **4 señales**, la nueva es la **🧠 huella del navegador** (cookie `cz_stats` en `directorio_stats_sesiones`). `avisos_contexto()` devuelve también **`cookie`**. Ayudante nuevo **`avisos_personas($minutos)`**. Todo el detalle: **§7** |
| 2 | **Dos tipos de tráfico automático** | `avisos_robots()` + `avisos_formato('visita_robot')` | Devuelve `total`, `top`, `conocidos`, `automaticas` y `personas`; el mensaje los separa y termina diciendo cuántas **PERSONAS** entraron esa hora. Detalle: **§8** |
| 3 | **Resumen de la hora rehecho** | `avisos_resumen_hora()` + `avisos_top_negocios()` + `avisos_pedidos()` (nueva) | Cruza tablas y va en orden: 👤 PERSONAS → 🏆 más visitadas → 📞 PIDIERON → 🔍 BUSCARON → 🚩/lo nuevo → 🤖 robots en una línea. Detalle: **§18.4** |
| 4 | **🧠 Informe inteligente** | **`includes/informe_inteligente.php`** (NUEVO) + tipos `informe_dia` / `informe_semana` + §6.3 del cron | El parte diario y el semanal. El del **día** va del **00:00 a la hora del envío** y se compara con **ayer a la misma hora**; el de la semana, 7 días corridos. Detalle: **§18.5**, §5.4 (formato) y §13.1 (cron) |
| 5 | **El «Resumen del día» que nunca se envió** | `config_avisos.php` + §6.3 del cron | El panel lo encendía y el código exigía una constante en `false`. Ahora manda el panel y ese resumen **es** el informe. Detalle: **§13.1** |
| 6 | **Las opiniones nuevas ya avisan** | `includes/opiniones.php` → `opinion_crear()` | Tipo nuevo `opinion_nueva` (apodo, estrellas y texto; 1-3 ⭐ resaltado). Estrenado de verdad el 15/09 a las 00:18:43. Detalle: **§18.6** |
| 7 | **📞 El botón «Llamar» ya se mide** | `api/llamada.php` + `assets/js/llamadas.js` (NUEVOS) | Y también los WhatsApp del copy que se saltaban `api/lead.php`. Detalle: **§18.7** |
| 8 | **Catálogo: 21 → 27 avisos** | `avisos_catalogo()` | 4 nuevos (`llamada_tienda`, `opinion_nueva`, `informe_dia`, `informe_semana`) + los 2 que nunca se documentaron (`empleo`, `noticias_dia`). **Los rótulos de hora/día se arman con las constantes** (`avisos_dia_semana_nombre()`). Detalle: **§4** |
| 9 | **Las cifras de PERSONAS ya no se quedan cortas** | `avisos_personas()` · `avisos_top_negocios()` · informe | Cuentan **`estado <> 'robot'`**, no `estado='enviado'`: una visita de persona que no generó mensaje (duplicado, tope de la hora, silencio, aviso apagado) **también cuenta**. Los números de personas **suben** respecto a los partes anteriores |
| 10 | **Tarjeta nueva en el panel: 👤 personas frente a 🤖 robots (24 h)** | `superadmin.php` (pestaña 📱 Telegram) | Los dos números juntos + los clics de pedir/llamar + los enlaces para **ver el informe sin enviarlo**. Detalle: **§10** |

### 18.2 LA CRUZADA QUE HACE EL INFORME (tabla por tabla)

| Dato del informe | Tabla(s) | Filtro exacto |
| --- | --- | --- |
| 👤 Visitas de personas y cuántas tiendas distintas | `directorio_avisos_log` | `tipo IN ('visita_tienda','visita_producto') AND estado <> 'robot'` |
| 📈 Comparación «▲/▼ frente al periodo anterior» | `directorio_avisos_log` | el mismo filtro, con la ventana anterior (día → **ayer a la misma hora**; semana → los 7 días anteriores) |
| 🔁 **Visitantes que VOLVIERON** | `directorio_avisos_log` | `estado <> 'robot'`, `GROUP BY ip HAVING COUNT(DISTINCT DATE(creado_en)) > 1` |
| 🖥️ **Navegadores de verdad** | `directorio_stats_sesiones` | `paginas > 1 OR segundos > 0` (**no** todas las sesiones: §18.5) |
| 🤖 Robots (buscadores vs granja) | `directorio_avisos_log` | `es_bot = 1` + `SUM(bot LIKE 'Visita automática%')` (§8) |
| 💬 Clics de pedir: WhatsApp / consulta / 📞 llamada / carrito | `directorio_pedidos` | `fecha` dentro del periodo, por `tipo` |
| 📞 Las tiendas que más piden | `directorio_pedidos` | `GROUP BY negocio_id`, con desglose `llamadas` / mensajes |
| 📦 El producto más pedido | `directorio_pedidos` + `directorio_servicios` | `producto_id IS NOT NULL` |
| 🏆 Puntos de la tienda que destaca | `directorio_avisos_log` + `directorio_pedidos` | `vistas + 3 × clics` por `negocio_id`, y lo mismo en el periodo anterior |
| 🔍 Búsquedas, lo más buscado y **lo que no se encuentra** | `directorio_busquedas` | `resultados > 0` para el top · **`resultados = 0`** para las oportunidades |
| 💬 Opiniones nuevas y su promedio | `directorio_opiniones` | `fecha` dentro del periodo (con `informe_columna()` por si la columna cambia) |
| 🚩 Pendientes (**sin filtro de fecha**) | `directorio_reportes` · `directorio_opiniones_reportes` · `directorio_reclamos` | `estado = 'pendiente'` |
| 🏪 Lo nuevo del sitio | `directorio_avisos_log` (`tienda_nueva`, `producto_nuevo`, `usuario_nuevo`), `directorio_noticias`, `directorio_empleos` | `estado='enviado'` / `creada_en` / `creado_en` del periodo |
| 💰 Dinero referencial | `directorio_pedidos` | `SUM(total)` de los carritos armados |

**Las funciones, una por una** (`includes/informe_inteligente.php`):

| Función | Para qué sirve |
| --- | --- |
| `informe_datos($dias, $calendario)` | **Todo** lo que sabe el informe de un periodo (el array `$d` con cada bloque). **`$calendario = true`** (el que usa el parte del **día**) cambia las ventanas: hoy 00:00 → ahora contra **ayer a la misma hora** |
| `informe_lineas($modo)` | El texto: `'dia'` (**día natural**, con `$calendario = true`) o `'semana'` (7 días corridos). Devuelve `titulo`, `rango_txt`, `filas` y `datos` |
| `informe_dia_lineas()` · `informe_semana_lineas()` | Atajos que usa el cron §6.3 |
| **`informe_rango_hoy()`** | El día natural: `hoy 00:00 → ahora`, con su etiqueta `«hoy (15/09, 00:00 → 00:21)»` |
| **`informe_rango_ayer()`** | El mismo tramo de **AYER cortado a la misma hora**: es contra eso que se compara el parte del día (así no sale el **-50 % falso** de comparar un día a medias con uno entero) |
| `informe_rango($dias, $offset)` | Las fechas de los periodos corridos (7 días, 30 días…), **en hora de Lima**; lo usa el parte de la semana |
| `informe_variacion($ahora, $antes)` | «▲ +180 %» · «▼ -20 %» · «= igual» · «▲ nuevo» · «= sin datos» |
| `informe_tienda($id)` | Nombre y slug de la tienda (con caché, para no repetir consultas) |
| `informe_q()` · `informe_tabla()` · `informe_columna()` | La red de seguridad: consulta en try/catch, ¿existe la tabla?, ¿existe la columna? |
| `informe_n()` | Número en formato peruano corto: `1 234` · `12,5 mil` |

### 18.3 LOS DOS AVISOS NUEVOS DEL INFORME Y SU DEDUPE

| Aviso | Cuándo | Clave de dedupe | Minutos |
| --- | --- | --- | --- |
| 🧠 `informe_dia` | Todos los días a `AVISOS_INFORME_HORA` (**22:00** de fábrica) · ventana = **día natural** (00:00 → ahora) contra **ayer a la misma hora** | `informe:dia:YYYY-MM-DD` | **1 200** (20 h) |
| 🏆 `informe_semana` | El día `AVISOS_INFORME_SEMANA_DIA` (**0 = domingo**), a la misma hora | `informe:semana:oW` (semana ISO) | **10 080** (7 días) |

- Los dos van por el **motor `aviso()`**, así que **el jefe los apaga y enciende desde el panel** y quedan registrados en `directorio_avisos_log` (se ven en «últimos 25 avisos»).
- **Aunque el cron corra dos veces en la misma hora, NO se repiten** (esa es justo la función de la clave).
- Se envían con **`'ignorar_silencio' => true`**: el parte del día no se calla por el horario de silencio.
- El interruptor **📅 Resumen del día (22:00)** también los manda (§13.1): es el puente con el aviso viejo.
- **Prueba a mano** (copiar y pegar en el navegador, la clave es obligatoria):

```
https://dechimbote.com/cron/monitoreo_sistema.php?k=ChimboteCron2026%23Jimmy&solo-mostrar=1&informe=dia
https://dechimbote.com/cron/monitoreo_sistema.php?k=ChimboteCron2026%23Jimmy&solo-mostrar=1&informe=semana
```

- Con **`&solo-mostrar=1`** se imprime en pantalla y **no se envía**; **sin** ese parámetro **se envía de verdad** (y consume la clave de dedupe del día o de la semana).
- ✅ **Cada uno va por su lado:** `&informe=dia` manda **solo** el del día y `&informe=semana` **solo** el de la semana (corregido el 2026-09-15: antes `&informe=semana` encolaba también el del día y llegaban dos mensajes seguidos).
- La prueba a mano **ignora los interruptores del panel** (`|| $pide_informe !== ''`): sirve justo para **ver** el informe aunque esté apagado en 📱 Telegram.

### 18.4 EL RESUMEN DE LA HORA REHECHO (`avisos_resumen_hora()`) — POR QUÉ CRUZA TABLAS

Antes eran contadores sueltos de avisos («🔥 Pidieron precio: 2») y **cada dato salía de una tabla distinta sin
relación entre ellos**. Ahora el mensaje **cruza** y va en este orden (el formato real está en §5.3):

| Orden | Bloque | De dónde sale | Por qué va ahí |
| --- | --- | --- | --- |
| 1 | 👤 **PERSONAS** + 🏆 las más visitadas | `avisos_personas()` y `avisos_top_negocios()` (`directorio_avisos_log`, **`estado <> 'robot'`**) | Es lo que el jefe preguntó: primero **gente**, no robots |
| 2 | 📞 **PIDIERON** (llamada/WhatsApp/consulta/carrito) + top 3 de tiendas | **`avisos_pedidos()`** (nueva, sobre `directorio_pedidos`) | Es el dinero en la puerta: quien pide es quien compra |
| 3 | 🔍 **BUSCARON** (top 4 términos) | `directorio_busquedas` | Lo que la gente quiere, dicho con sus palabras |
| 4 | 🚩 **reportes pendientes** y lo nuevo (opiniones, usuarios, tiendas, productos, empleos, noticias) | `directorio_reportes` + `directorio_opiniones_reportes` + `directorio_avisos_log` | Lo que espera decisión + el tamaño del movimiento |
| 5 | 🤖 **Robots en UNA línea** | `avisos_robots()` | Al final y resumido: no debe tapar a las personas |

- **🐛 Arreglo de un dato falso:** `avisos_top_negocios()` **no filtraba por tipo de aviso**: entraba cualquier fila con `negocio_id`, y como los avisos de pedir precio también lo traen, el «🏆 Más visitadas» **contaba clics de WhatsApp como si fueran visitas**. Ahora se limita a **`tipo IN ('visita_tienda','visita_producto')`**.
- **Regla:** si no hay nada que contar (ni personas, ni pedidos, ni búsquedas, ni novedades, ni robots) el resumen **no se envía** (salvo `$forzar = true`).
- ⚠️ **El bloque de «se agruparon N avisos» ya no se cuenta** (`'agrupados' => 0`): lo que se pasa del tope por hora queda registrado como `agrupado` y **solo se ve en la base** (§7 y §9).

### 18.5 LAS DECISIONES DEL INFORME (lo que hay que entender antes de tocarlo)

1. **La tienda que más destaca = visitas de personas + 3 × clics de pedir/llamar**, comparada con el periodo anterior.
   *Por qué ×3:* un clic de llamar o de WhatsApp vale mucho más que una visita (lleva a una llamada real). El peso 3 es
   la forma de que una tienda que recibe clics suba por encima de una que solo mira.
2. **El parte del DÍA es el día natural y se compara con AYER a la misma hora.** `informe_rango_hoy()` va de
   **00:00 a la hora del envío** y `informe_rango_ayer()` cierra ayer **a esa misma hora**: así el `▲/▼` compara
   **manzanas con manzanas**. Con «últimas 24 h» contra las 24 h anteriores, el parte del cierre salía siempre con un
   **-50 % falso** (un día a medio andar contra un día entero) y asustaba al jefe sin motivo. La **semana** sigue
   siendo 7 días corridos contra los 7 anteriores. Todo eso se activa con el parámetro **`$calendario`** de
   `informe_datos()` (lo pone `informe_lineas()` solo para `'dia'`).
3. **Las personas se cuentan con `estado <> 'robot'`** (`avisos_personas()`, `avisos_top_negocios()` y las consultas
   de personas/ranking del informe), **no** con `estado='enviado'`: el filtro escribe `robot` cuando dice que **no**
   es persona, así que cualquier otro estado (`enviado`, `duplicado`, `agrupado`, `silencio`, `apagado`) significa
   que **sí pasó el filtro**. Contar solo `enviado` dejaba fuera visitas de gente real y el informe salía **más pobre
   que la realidad**: **al comparar partes de antes y de después de este cambio, los números de PERSONAS SUBEN.**
4. **Los pendientes de reportes NO se filtran por fecha.** Lo que espera la decisión del jefe se avisa **siempre**
   (el filtro por fechas es para el movimiento, no para lo que está parado esperando a alguien).
5. **«Navegadores de verdad» NO son todas las sesiones.** Cuenta solo las de `directorio_stats_sesiones` con
   **`paginas > 1 OR segundos > 0`**. Contar las **2 350 sesiones del 14/09** habría dado **un número grande y
   falso**: la granja crea su cookie igual que una persona, pero **no ejecuta JavaScript**.
6. **Nada puede romper el cron.** Todo va en try/catch (`informe_q()`) y cada tabla se comprueba antes
   (`informe_tabla()` / `informe_columna()`): si una tabla no existe, **esa línea no sale** y el informe sigue. El
   bloque del cron está además dentro del try/catch general del §6.
7. **Ninguna cifra se inventa.** Si no hay dato, la línea **no se pinta** (y si no hay nada de nada, sale
   «· Sin movimiento propio en este periodo.»).

**El ejemplo REAL del informe**, tal cual llegó al Telegram del jefe el **2026-09-15 a las 00:21** —ya con el día
natural— (el mismo bloque está en §5.4, con la explicación de cada sección). Es un parte de **madrugada**, así que
sirve de ejemplo de **día flojo**:

```
🧠 INFORME INTELIGENTE DEL DÍA
📅 hoy (15/09, 00:00 → 00:21)

👤 PERSONAS (señales de persona real, no robots)
  · Ninguna visita a tienda con señales de persona en este periodo.
  · 🔍 3 búsqueda(s) de personas
  · 💬 1 clic(s) de pedir: 1 WhatsApp · 0 consulta de producto · 0 📞 llamada · 0 carrito armado

🤖 Robots: 7 (3 buscadores/IA · 4 automáticas sin señales)
  ℹ️ No son personas. Los buscadores traen visitas buenas (indexan el sitio).

🏆 LA QUE MÁS DESTACA HOY
  Baterias&Ferreteria Progress
  · 0 visita(s) de personas · 1 clic(s) de pedir = 3 punto(s)
  · ▲ nuevo frente al periodo anterior
  🔗 https://dechimbote.com/neg/baterias-ferreteria-progress

📞 LAS QUE MÁS PIDEN (llamada y WhatsApp)
  1. Baterias&Ferreteria Progress — 1 (1 💬)

🔍 BUSCAN Y NO ENCUENTRAN (oportunidad de captar esos negocios)
  · xyzzy (1)
🔥 LO MÁS BUSCADO: pollo (1) · tiendecita d mam lu (1)

🚩 REPORTES
  · Pendientes de tu decisión: 1 reporte(s) de contenido · 1 opinión(es) reportada(s)
  👉 Súper Admin → 🚩 Reportes · Reclamos de opiniones

🏪 EL SITIO EN NÚMEROS
  · 2 aviso(s) de empleo
  · 1 pedido(s)
  · = igual frente al periodo anterior

🕒 15/09 00:21
```

**Cómo se lee ese ejemplo (y por qué demuestra que el arreglo funciona):**

- **`📅 hoy (15/09, 00:00 → 00:21)`**: es el **día natural** de las 00:21 de la madrugada, no «últimas 24 h». Por
  eso sale casi todo a cero y **no hay que asustarse**: es lo que el jefe verá si mira un parte recién nacido.
- **`▲ nuevo frente al periodo anterior`**: ayer a esa misma hora la tienda **no tenía puntos**, así que la
  variación se dice «nuevo» en vez de inventar un porcentaje (`informe_variacion()`).
- **«Ninguna visita a tienda con señales de persona en este periodo.»**: el parte **lo dice con todas las letras**
  y aun así sigue contando búsquedas, clics y robots — nunca deja la sensación de «no entró nadie» sin explicar.
- **🏆 Baterias&Ferreteria Progress con 0 visitas y 1 clic = 3 puntos**: el **×3** funcionando — **pidió gente**, y
  eso vale más que una visita.
- **`🤖 Robots: 7 (3 buscadores/IA · 4 automáticas sin señales)`**: los dos tráficos bien separados, con el total
  **contado aparte** (§8) — 7 y no «muchos», que es lo que asusta.
- **🔍 «xyzzy (1)» entre las que no se encuentran**: es una **oportunidad de captar** (sale de
  `directorio_busquedas` con `resultados = 0`); si aparece una palabra rara de una prueba, es que la prueba
  también se midió.
- **`💬 1 clic(s) de pedir: 1 WhatsApp`**: ese clic es del botón de la ficha o del copy, y es lo que alimenta el
  **📞 LAS QUE MÁS PIDEN** justo debajo: los dos bloques salen de `directorio_pedidos`.

### 18.6 💬 EL ARREGLO DE LAS OPINIONES NUEVAS (`opinion_nueva`)

- El **módulo de opiniones anónimas se estrenó el 2026-09-14** y tenía un agujero: `opinion_reportar()` **sí**
  avisaba (con `notificar_jefe()` y el interruptor `reporte_contenido`), pero **`opinion_crear()` no avisaba de
  nada**. Si alguien escribía una opinión —y sobre todo si era de **1 o 2 ⭐**, que es lo que hay que atender
  rápido— el jefe **no se enteraba hasta entrar al panel**.
- **Arreglado:** `includes/opiniones.php` → `opinion_crear()` dispara **`aviso('opinion_nueva', …)`** con
  `negocio_id`, `autor` (apodo), `rating` y `texto`, y clave **`opinion:<id>`** (una opinión = un aviso).
- **Formato:** §5.5. Con nota de **1 a 3 ⭐** el mensaje resalta el caso y manda a **Súper Admin → 🚩 Reclamos de
  opiniones**; con 4-5 ⭐ dice que se publicó al instante.
- Va **después del INSERT y de recalcular el rating**, y dentro de un try/catch: **una avería de Telegram nunca
  impide publicar una opinión**.
- ✅ **ESTRENO REAL (verificado en producción):** el aviso se disparó por primera vez el **15/09 a las 00:18:43** —
  una opinión de **PRUEBA** de otra IA («**Prueba Interna** (5⭐) en la tienda **#1736**», sonda con user-agent
  `Mozilla/5.0 (sonda interna)`). O sea: **el aviso funciona de punta a punta** (INSERT → motor → Telegram → registro
  en el panel).
- ⚠️ **TRAMPA AL PROBARLO:** **cualquier sonda o script que use `opinion_crear()` manda mensaje al Telegram del
  jefe** (el aviso no distingue si la opinión es de prueba). Para probar hay que **apagar antes el interruptor**:
  fila `opinion_nueva` de `directorio_avisos_config` con **`activo = 0`** —o **insertar la fila a mano** en
  `directorio_opiniones`— y volver a encenderlo al terminar (§9).

### 18.7 📞 EL BOTÓN «LLAMAR» YA SE MIDE (el dato que no existía)

**El problema (por qué el jefe no tenía ese dato):** el botón 📞 Llamar de la ficha era un **`<a href="tel:…">`
puro**: el navegador abre el marcador y **no pasa por ningún sitio del servidor**, así que el clic **no existía en
ninguna tabla**. Lo mismo con los **botones de WhatsApp que pinta el copy** de una tienda
(`<p class="cz-btn cz-wa">` → `<a class="cz-btn cz-btn--wa">` de `descripcion_negocio_html()`): enlazan **directo a
`wa.me`** y se saltaban `api/lead.php`. Es decir: **los dos botones más calientes de las fichas de los «amigos de
Jimmy» eran invisibles para las estadísticas.**

**La solución, sin tocar lo que siente el visitante:**

| Pieza | Qué hace |
| --- | --- |
| **`assets/js/llamadas.js`** (cargado en `includes/footer.php` como `llamadas.js?v=2`) | Escucha el **`click`** (con un `pointerup` de respaldo que exige que el dedo no se haya movido), sube hasta el `<a>`, y según el `href`: `tel:` → *llamada*; `wa.me` / `api.whatsapp.com` / `web.whatsapp.com` → *whatsapp*… **ignorando los que ya se cuentan solos** (`api/lead.php` y `/carrito`, a los que solo les pega la marca `&c=1`). ⚠️ **Hasta el 2026-09-16 contaba el `pointerdown`** y eso inventaba clics que nadie hacía (§19) |
| El envío | `navigator.sendBeacon()` a `/api/llamada.php` (respaldo con `new Image()`), **asíncrono** y **sin bloquear**: el teléfono se marca igual y el WhatsApp se abre igual |
| **Dedupe en el navegador** | Una clave por tipo + tienda + `href` **y por carga de página**: en móvil el mismo dedo dispara `pointerdown` **y** `click`, y sin esto **un toque contaría como dos clics** |
| **De dónde sale la tienda** | Del atributo **`data-negocio`** del enlace (añadido en `negocio.php` y en los botones del copy de `descripcion_negocio_html()`) o, si no está, del bloque **`<div data-negocio-id="…">`** que pinta la ficha. **Sin id no se manda nada** (nunca un dato falso) |
| **`api/llamada.php`** (nuevo) | Comprueba que la tienda exista, responde **204** y **trabaja después de responder** (`litespeed_finish_request`). Guarda el récord (`metrica_pedido`) y avisa |
| **Avisos** | `tipo='llamada'` → **`llamada_tienda`** («📞 TOCARON «LLAMAR» EN UNA FICHA», dedupe **10 min** por IP + tienda) · `tipo='clic'` (WhatsApp del copy) → **`lead_precio`** (dedupe **10 min**, clave `wa-copy:<ip>:<negocio>`) |
| **El filtro de robots se SALTA a propósito (2 sitios)** | `metrica_pedido([... 'ignorar_filtro_robot' => true])` y `aviso(…, ['es_bot' => 0])`. **Por qué es necesario:** un *beacon* **solo lo puede mandar un navegador que está ejecutando la página** (un robot que lee el HTML no lo dispara), mientras que el filtro por *user-agent* se lleva por delante a **gente de verdad**: los navegadores que van **DENTRO de WhatsApp o Telegram** llevan el nombre de la app en su UA y `stats_es_bot()` los marca como robot. **Sin esa excepción se perderían justo los clics más calientes** (los que llegan de un chat) — los mismos que alimentan 📞 LAS QUE MÁS PIDEN del informe. La protección real aquí no es el UA: son el **dedupe de 10 min** y el **tope por hora** del motor |

**Cambio de ESTRUCTURA aplicado en producción el 2026-09-15** (por sonda temporal, ya borrada — no se toca la base
desde el navegador ni con phpMyAdmin):

```sql
ALTER TABLE directorio_pedidos   MODIFY tipo ENUM('clic','consulta','pedido','llamada') NOT NULL DEFAULT 'clic';
ALTER TABLE directorio_avisos_log ADD KEY idx_ip_fecha (ip, creado_en);
```

- El **ENUM** hacía falta porque `'llamada'` **no existía**: MySQL habría guardado **vacío** (sin dar error) y el
  clic se perdería. Se añadió también al `CREATE TABLE` de `metricas_instalar()` (**`includes/metricas.php`**) para
  que una instalación nueva nazca con el tipo.
- El **índice `idx_ip_fecha`** es para la **señal 3 de persona** (§7: «esa IP ya había entrado hoy») y para los
  conteos por IP del informe.

**Lo que cambió en el código de los récords:**

| Archivo | Cambio |
| --- | --- |
| `includes/metricas.php` | ENUM del `CREATE TABLE` · `metrica_pedido()` acepta `'llamada'` con la **comprobación defensiva `metricas_tipo_llamada_ok()`** (si la base no lo aceptara, guarda `'clic'` en vez de perder el clic) · `metricas_top_pedidos()` devuelve la columna **`llamadas`** · `metricas_pedido_tipo_txt()` traduce «📞 Tocó «Llamar»» |
| `includes/vista_records_admin.php` | La tabla **🔥 Tiendas con más pedidos** ganó la columna **📞** y el subtítulo dice «… + llamada 📞» |
| `negocio.php` · `includes/helpers.php` | **`data-negocio="<id>"`** en los botones de llamar y en los del copy (sin ese atributo el informe no podría decir **de qué tienda** son los clics) |

> ✅ **AVISO IMPORTANTE — POR QUÉ `api/llamada.php` SE SALTA EL FILTRO DE ROBOTS (y por qué no hay que quitarlo).**
> El navegador *dentro* de **WhatsApp o Telegram** lleva el nombre de la app en su *user-agent*, y `stats_es_bot()`
> lo marca como robot; si el clic pasara por ese filtro, **se perderían justo los clics más calientes** (los de
> quien llega desde un chat), que son la mayoría. Por eso el endpoint pasa **`'ignorar_filtro_robot' => true`** a
> `metrica_pedido()` y **`'es_bot' => 0`** al `aviso()`: **un *beacon* solo lo puede mandar un navegador que está
> ejecutando la página** (un robot que solo lee el HTML no lo dispara), así que ese es el dato de verdad. Lo que
> protege aquí no es el UA, sino el **dedupe de 10 minutos** (por IP + tienda) y el **tope por hora** del motor.
> ⚠️ Si algún día se quitan esas dos líneas, el informe empezará a decir **menos clics de los que hay** y el
> 📞 LAS QUE MÁS PIDEN se quedará corto. **No tocar.**

### 18.8 LAS TRAMPAS QUE COSTARON TIEMPO (2026-09-15)

| ⚠️ Trampa | Qué pasó / qué hacer |
| --- | --- |
| **`bot` es `VARCHAR(40)`** | El nombre «Visita automática (sin señales de persona)» se guarda **truncado**, así que comparar por igualdad daba **0 automáticas**. Se compara **por prefijo** `LIKE 'Visita automática%'` (§8) |
| **El «Resumen del día» nunca salió** | El panel lo encendía en la base pero el cron exigía además `AVISOS_RESUMEN_DIA` (en `false`): **dos interruptores para lo mismo y uno apagado**. Hoy manda **el del panel** (§13.1) |
| **El parte del día comparaba mal** | Con «últimas 24 h» contra las 24 h anteriores el cierre salía con un **-50 % falso** (un día a medio andar contra uno entero). Ahora es el **día natural** contra **ayer a la misma hora** (§5.4 y §18.5) |
| **Las personas se contaban de menos** | Se contaban solo con `estado='enviado'`: se perdían las de estado `duplicado`, `agrupado` (tope de la hora), `silencio` o `apagado`, que **sí pasaron el filtro de persona**. Ahora es **`estado <> 'robot'`** y **los números de personas suben** (§7 y §18.5) |
| **`avisos_top_negocios()` contaba clics como visitas** | Cualquier fila con `negocio_id` entraba (los avisos de pedir precio también lo traen). Ahora solo `visita_tienda` / `visita_producto` (§18.4) |
| **Contar las sesiones como personas** | Las **2 350 sesiones** del 14/09 habrían sido un número grande y **falso**: solo cuentan las de **`paginas > 1 OR segundos > 0`** (§18.5) |
| **El `total` de robots salía corto** | Era la suma del `GROUP BY bot ... LIMIT 8`; ahora hay un **`COUNT(*)` aparte** (con respaldo en la suma de los grupos si falla) (§8) |
| **Probar opiniones despierta al jefe** | Cualquier sonda que use `opinion_crear()` **manda mensaje al Telegram**: apagar antes `opinion_nueva` (`activo = 0`) o insertar la fila a mano (§9 y §18.6) |
| **Los rótulos del panel decían 21:00** | Estaban escritos a mano mientras el envío era a las 22:00. Ya se arman con las **constantes** (`avisos_dia_semana_nombre()`): si el jefe cambia la hora, el rótulo cambia solo (§4 y §13.1) |
| **`metricas_sembrar()` lee el formato de `resumen`** | El sembrador histórico saca el término buscado del `resumen` de `directorio_avisos_log` (`«<término> (N res.)»`): si se cambia ese formato en `avisos_formato()`, hay que cambiar su regex (§15) |

---

## 19) 🚫 LOS FALSOS POSITIVOS: «PIDIERON PRECIO» Y «TOCARON LLAMAR» SIN QUE NADIE TOCARA NADA (2026-09-16)

### 19.0 LA QUEJA (textual del jefe)

> *«revisa que está dando falsos positivos: cuando alguien carga la página automáticamente me manda el botón
> de que han dado clic en preguntar precio, y eso no es verdad; o también me dice que tocaron el botón de
> llamar teléfono cuando lo único que pasó es que entraron a dicha tienda y cuando cargó el botón marcó como
> una acción.»*
> — Jimmy López, 2026-09-16

Tenía razón, y eran **dos fallos distintos** en los dos avisos «calientes» (`lead_precio` = 🔥 Pidieron precio
y `llamada_tienda` = 📞 Tocaron Llamar). Ninguno era del motor de avisos: los dos nacían **antes** de llegar a
él (`api/lead.php` y `assets/js/llamadas.js`).

### 19.1 LO QUE SE MIDIÓ ANTES DE TOCAR NADA (producción, 7 días)

Se hizo con **sondas temporales de solo lectura** (se suben con `python __sonda_run.py <sonda>.php <clave>` y
se borran del hosting solas). El diagnóstico vive en **`__sonda_falsos.php`** (+ `__sonda_falsos2.php` y
`__sonda_falsos3.php`, que clasificaron los avisos) y sus respuestas quedaron en los
`__sonda_falsos*_resultado.json` de `D:\RELAX`.

| Dato medido (7 días) | Valor |
| --- | --- |
| Visitas a ficha registradas | **4 253** |
| Avisos `lead_precio` («pidieron precio») | **1 140** |
| De esos, **rastreadores con nombre** (ClaudeBot 749, Applebot, MJ12bot, `curl`…) | **764** |
| De esos, **enviados al jefe** (`estado='enviado'`) | **371** |
| De los 371, **IPs que aparecen UNA SOLA VEZ en todo el registro** (sin visita a ficha, sin sesión) | **306** |
| De los 371, IPs con visitas de la **granja** marcadas robot | **48** |
| De los 371, IPs con visitas de persona | **19** |
| Avisos `llamada_tienda` | **3** |
| Usuarios-agente de esos «leads» fantasma | `Pixel 8 Pro`, `Pixel 9`, `iPhone 18_2`…`18_7`, `SM-S938B`, `CPH2653` (los **móviles falsos de la granja**, §7) |
| Por día (los enviados) | 10/09: 7 · 11/09: 19 · 12/09: 10 · **13/09: 122** · **14/09: 127** · 15/09: 82 · 16/09: 6 |
| Negocios distintos afectados | **213** |

**La prueba que lo cerró:** el rastreador **ClaudeBot** hizo **384 peticiones** a `api/lead.php` en 7 días (una
por ficha: 140 y 112 en otras dos IP suyas), y las IP fantasma pedían **una sola vez** un
`api/lead.php?n=<id>` y desaparecían. Es decir: **alguien estaba pidiendo la URL del botón**, no el botón.

### 19.2 LAS DOS CAUSAS

| # | Aviso | Causa real |
| --- | --- | --- |
| 1 | 🔥 **PIDIERON PRECIO** (`api/lead.php`) | El botón 💬 WhatsApp de la ficha es **un enlace a `api/lead.php?n=<id>`**, así que **la URL está escrita en el HTML de la ficha** y el endpoint contaba **cualquier petición** como un clic. Los robots que siguen enlaces (Googlebot, ClaudeBot, MJ12bot, `curl`) y la **granja de IPs** la pedían una y otra vez → aviso al jefe sin nadie delante. ⚠️ **No es el navegador de un visitante**: comprobado en el navegador real el 2026-09-16, una carga normal de la ficha **no pide** `api/lead.php` ni `api/llamada.php` (solo `api/negocios_json.php`, que es del propio sitio) |
| 2 | 📞 **TOCARON LLAMAR** (`assets/js/llamadas.js`) | El JS contaba el **`pointerdown`**: bastaba con que el dedo **TOCARA** el botón —para empezar a deslizar la pantalla, para mirar, o en un toque que el navegador acaba cancelando— y ya salía el aviso. Es exactamente el «entré a la tienda y me marcó una acción» del jefe |

> ⚠️ **Por qué el filtro de robots del motor (§7 y §8) no los paraba:** los dos caminos iban con
> **`es_bot => 0`** a propósito (`api/llamada.php` lo hace para no perder a quien navega dentro de WhatsApp,
> §18.7) y **la granja usa móviles falsos** (`Pixel 9`, `iPhone 18_4`…), que no llevan nombre de robot en el
> *user-agent*: `stats_es_bot()` no tenía por dónde reconocerlos. **El UA nunca fue la prueba**: la prueba es
> que **alguien toque el botón**.

### 19.3 EL ARREGLO — AHORA HACE FALTA PRUEBA DE CLIC

| Archivo | Qué cambió |
| --- | --- |
| **`api/lead.php`** | Calcula **`$clic_real`** = `&c=1` (lo pega el JS al enlace en el momento del clic) **o** `Sec-Fetch-User: ?1` (la cabecera que manda el navegador cuando la navegación la activó el usuario; es el respaldo para quien navega sin JavaScript). **Sin ninguna de las dos**, el visitante **va igual a WhatsApp** (el `302` con su mensaje de contexto no cambia) pero el hecho se anota como **🤖 robot** (`bot = 'Enlace seguido sin clic (robot)'`), **no avisa** al jefe y **no entra en los récords** (`metrica_pedido`), ni en HubSpot, ni en la memoria del chat |
| **`assets/js/llamadas.js`** (`?v=2`) | Ya **no** cuenta el `pointerdown`. Cuenta el **`click`** (toque de verdad) y, como respaldo para los navegadores que no sueltan `click` al abrir el marcador, un **`pointerup`** que exige **no haberse movido más de 12 px** y **menos de 1,5 s** (así un **arrastre** o una **pulsación larga** no cuentan). Además, al enlace del contador (`api/lead.php`) le pega **`&c=1`** en el clic |
| **`assets/js/carrito.js`** (`?v=8`) | El pedido del carrito y la consulta de la ficha rápida llevan **`&c=1`** (los abre `window.open` dentro del clic) |
| **`includes/footer.php`** | Sube los `?v=` de los dos JS (si no, el navegador del jefe sigue usando el viejo de la caché) |

**Lo que NO cambió, a propósito:** el mensaje al cliente, el `302` a WhatsApp, el `204` del beacon, el dedupe
de 10 minutos, el `es_bot => 0` del beacon (§18.7) y el interruptor del panel. El arreglo **quita el ruido, no
la medida**: un clic de verdad se sigue midiendo igual que desde el 2026-09-15.

### 19.4 LA VERIFICACIÓN (2026-09-16, en producción)

Con **`__sonda_clic.php`** (apaga los dos interruptores mientras dura la prueba y los deja como estaban, para
no mandarle mensajes al jefe) y con el **navegador real** (pestaña de pruebas, ya cerrada):

| Prueba | Resultado |
| --- | --- |
| `GET /api/lead.php?n=1681` **sin** prueba de clic (como la de un robot) | **`302` a WhatsApp** (el visitante no nota nada) y en el registro una fila **`estado='robot'`, `bot='Enlace seguido sin clic (robot)'`** → **sin Telegram** ✅ |
| `GET /api/lead.php?n=1681&c=1` (como un clic de verdad) | Entra al motor y queda registrado como lead ✅ (en la prueba salió `apagado` porque el interruptor estaba apagado a propósito: **el camino bueno sigue vivo**) |
| `GET /api/llamada.php?n=1681&tipo=llamada` (el beacon) | `204` y aviso enviado ✅ (el beacon sigue funcionando) |
| Navegador: **`pointerdown`** sobre el botón 📞 Llamar (el viejo falso positivo) | **0 beacons** ✅ (antes contaba) |
| Navegador: **toque real** (`pointerup` sin moverse) | **1 beacon** ✅ |
| Navegador: **arrastre** de 200 px empezando en el botón | **0 beacons** ✅ |
| Navegador: **clic** en el botón 💬 WhatsApp de la ficha | el enlace queda con **`&c=1`** y **0 beacons** (lo cuenta el servidor: no se duplica) ✅ |
| Navegador: carga de la ficha | **ninguna** petición a `api/lead.php` ni a `api/llamada.php` ✅ |

Las filas de récords que creó la prueba se borraron (`directorio_pedidos`: 2 filas de la prueba) para no
ensuciar los récords del jefe.

### 19.5 LO QUE HAY QUE RESPETAR DE AQUÍ EN ADELANTE (trampas)

1. **Si se añade un botón nuevo de WhatsApp, tiene que ser un `<a>` que se toque** (o llevar `c=1` si lo abre
   JavaScript): si se le ocurre a alguien **enlazar `api/lead.php` desde un `<img>`, un `<iframe>`, un
   `rel=prefetch` o un `<link>`**, volverá el falso positivo — porque eso **no es un clic**.
2. **No quitar la marca `&c=1` de `llamadas.js` ni de `carrito.js`**, ni el `Sec-Fetch-User` de
   `api/lead.php`: sin ellos **no llega ni un pedido**. Si algún día se cambia el JS del carrito o de los
   botones, hay que volver a comprobarlo con `__sonda_clic.php`.
3. **Los enlaces del panel de administración** (la ficha rápida que se pinta ahí) no cargan
   `llamadas.js`: si el jefe o la IA tocan ese botón desde el panel, **no se cuenta** (que es lo correcto:
   no es un cliente).
4. **El `pointerdown` NO se vuelve a poner** en `llamadas.js` «para no perder clics»: es justo lo que
   inventaba los clics (§19.2). El respaldo es el `pointerup` con sus dos guardas.
5. **La fila `llamada_tienda` puede no existir en `directorio_avisos_config`** (comprobado el 2026-09-16:
   el tipo se estrena con el valor de fábrica del catálogo). Al probar algo que «apague» avisos, hay que
   **mirar si la fila existe** o el `UPDATE` no apaga nada (lección de esta sesión: la prueba de la llamada
   sí mandó un mensaje).
6. **Herramientas reutilizables:** `__sonda_falsos.php` (radiografía de los avisos de pedir/llamar: quién
   los dispara, de qué IP y con qué UA) y `__sonda_clic.php` (verifica el arreglo: sin clic / con clic /
   beacon). Las dos se suben con **`python __sonda_run.py <sonda>.php <clave>`** y se borran solas.
---

> **Guías base del proyecto:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (qué es el sitio, mapa de archivos,
> estado real de cada módulo y qué hacer después) · `REGLAS_DE_ORO_PROYECTO.md` (reglas del jefe,
> canónicas) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar sin romper el sitio).
> **Guías hermanas de este módulo:** `GUIA_RECORDS_DEL_SITIO.md` (la pestaña 🏆 Récords, que es donde se cruzan
> los mismos datos: columna **📞** de llamadas) · `GUIA_OPINIONES_ANONIMAS.md` (el módulo que ahora avisa con
> `opinion_nueva`) · `GUIA_ESTADISTICAS_DEL_SITIO.md` (la tabla `directorio_stats_sesiones`, que es la 🧠 huella
> del navegador del §7) · `GUIA_NOTICIAS_DIARIAS.md` (el aviso `noticias_dia` y su Cron Job pendiente).


_Última revisión: **2026-09-16 — §19: LOS FALSOS POSITIVOS «🔥 PIDIERON PRECIO» Y «📞 TOCARON LLAMAR»** (la queja
del jefe, la medición en producción —371 avisos en 7 días, **306 de IPs que aparecen una sola vez**—, las dos causas
-`api/lead.php` contaba cualquier petición del enlace y el JS contaba el `pointerdown`-, el arreglo con **prueba de
clic** (`&c=1` + `Sec-Fetch-User: ?1` y solo `click`/`pointerup` sin moverse) y la verificación en producción y en el
navegador). También se corrigieron §0, §9, §15, §16 y §18.7, que describían el `pointerdown` y el `?v=1`._
_2026-09-15 (segunda pasada, tras los arreglos del código):_ §0: el informe inteligente y el
conteo real de avisos · §4: tabla completa de los **27** tipos con los 4 nuevos y los **rótulos del panel armados con
las constantes** · §5: formatos reales del informe (**5.4**, ya con el **día natural** y el parte del 15/09 00:21), de
«Tocaron Llamar» y «Opinión nueva» (5.5) y los dos mensajes rehechos (resumen de la hora y robots) · §6: las
opiniones ya avisan · §7: el filtro de persona REHECHO con la 🧠 huella del navegador, las 4 señales del comentario
de `config_avisos.php` y el criterio **`estado <> 'robot'`** · §8: los dos tráficos automáticos, la trampa del
`VARCHAR(40)` y el **total exacto** con `COUNT(*)` · §9: dedupes nuevos, la corrección de «agrupados» y la trampa de
probar opiniones · §10: 27 notificaciones en el panel · §13.1: el §6.3 del cron (🧠 informe diario y semanal, **día
natural contra ayer a la misma hora**), el arreglo del «Resumen del día» que nunca se envió y la prueba a mano que
manda **solo** el informe pedido · §15: archivos, constantes y las dos estructuras tocadas (`ENUM` de
`directorio_pedidos` e índice `idx_ip_fecha`) · §16: los enganches nuevos · §17: lo que dejó de ser pendiente ·
**§18: 🧠 EL INFORME INTELIGENTE Y EL BOTÓN 📞 LLAMAR** (§18.6 con el estreno real del aviso y §18.7 con el porqué de
que el botón Llamar se salte el filtro de robots)._
_2026-09-13: §15 las tablas del módulo 🏆 Récords y la dependencia del formato de `resumen` que lee `metricas_sembrar()`._
_2026-09-10: consolidación de las guías de Telegram._
