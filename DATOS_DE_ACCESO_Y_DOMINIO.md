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


# 🔑 DATOS DE ACCESO Y DOMINIO DEL PROYECTO — dechimbote.com

> **Qué es este archivo:** la hoja única con **el dominio actual** y **los accesos** (FTP, base de
> datos, panel, bot de Telegram y claves del sitio). Si necesitas entrar al hosting, empezar por aquí.
> **Actualizado el 2026-09-15** (el día de la mudanza de dominio, orden del jefe).
> ⚠️ Este archivo **no se despliega**: es de trabajo local (vive en la raíz de `D:\RELAX`).

---

## 1) EL SITIO Y SU DIRECCIÓN

| Dato | Valor |
|------|-------|
| **Dirección oficial** | **`https://dechimbote.com`** ← **sin www** |
| Con www | `www.dechimbote.com` → **redirige (301)** a `https://dechimbote.com` (para no partir visitas ni perder la sesión de quien entra) |
| **Marca visible en el sitio** | **`DeChimbote.com`** (pie, menú, chat 🥷, mensajes de WhatsApp y descripciones de las tiendas) |
| Dominio viejo | `chimbote.xyz` — **ya no existe** (el sitio se mudó el 2026-09-15; no queda ninguna referencia suya ni en el sitio, ni en la base de datos, ni en las guías) |
| Hosting | **Hostinger**, cuenta `u196269909`, panel **hPanel** |
| Carpeta de la web en el servidor | `/home/u196269909/domains/dechimbote.com/public_html` |
| Cómo saber que estás en la **raíz viva** por FTP | existe **`assets/css/carrito.css`** (16 967 bytes) — ver la trampa del FTP en §2 |

## 2) FTP (cuenta nueva del dominio nuevo, 2026-09-15)

| Dato | Valor |
|------|-------|
| **Host** | **`ftp.dechimbote.com`** (puerto **21**) · la IP `82.25.67.35` es la misma máquina y también sirve |
| **Usuario** | **`u196269909.dechimboteftp`** |
| **Contraseña** | **`PON_AQUI_LA_CLAVE_DEL_FTP`** |
| **Carpeta inicial** | `public_html` ⚠️ **pero la raíz viva del sitio es `/`** |
| **Archivo que leen los scripts** | `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` |

🔴 **LA TRAMPA DEL FTP (vale para todo el mundo, no la olvides):** al conectarse, el FTP deja al usuario
en **`/public_html`**, y esa carpeta **NO es la web**: es una **COPIA VIEJA y completa del sitio anidada
dentro de la raíz viva**. Subir ahí **no da error** (los bytes quedan idénticos) pero **la web no cambia
nunca**. ✅ Todo script de subida hace **`cwd('/')`** y **comprueba la marca `assets/css/carrito.css`**
antes de escribir un solo byte (`__subir_uno.py`, `__sonda_run.py`, `__pub_productos.py`).
📖 El detalle y cómo detectarlo en 10 segundos: **`GUIA_DESPLIEGUE_Y_ENTORNO.md` §11.1**.

⚠️ **Cuentas viejas:** `u196269909.chimboteftp` y `u196269909.chimbote.xyz` **ya no existen** (dan **530**).

## 3) BASE DE DATOS

| Dato | Valor |
|------|-------|
| Base | MySQL **`u196269909_CHIMBOTEALDIA`** |
| Usuario de la web | `u196269909_ALDIACHIMBOTE@localhost` (⚠️ **nunca** tocar sus permisos: rompe el sitio) |
| Solo lectura (BI) | `u196269909_metabase` (pendiente de habilitar por el jefe) |
| Zona horaria | El sitio usa **`America/Lima`**; **el MySQL del hosting va en UTC** → las fechas se guardan **desde PHP**, nunca con `NOW()` del SQL |
| Cómo se lee sin phpMyAdmin | **sonda temporal por FTP** (`__sonda404.php` + `python __sonda404.py`), se borra del servidor al terminar |
| Los internos NO cambian | `u196269909_CHIMBOTEALDIA`, el usuario, la cookie `CHIMBOTE_SID`: son técnicos y renombrarlos **rompería el sitio** (decisión consciente) |

## 4) BOT DE TELEGRAM (avisos al jefe)

| Dato | Valor |
|------|-------|
| Bot | **`@Jimmychimbote_bot`** (nombre: «Alertas chimbote») |
| Chat del jefe | `8333560284` |
| **Webhook** | **`https://dechimbote.com/api/telegram_bot.php`** — re-registrado el 2026-09-15 al mudar el dominio (si el bot deja de responder comandos, es esto lo primero que hay que mirar) |
| Panel de avisos | Súper Admin → **📱 Telegram** |

## 5) OTROS ACCESOS Y DÓNDE ESTÁN LAS CLAVES

| Qué | Dónde |
|-----|-------|
| Claves del sitio (BD, secretos, HubSpot, Google, DeepSeek) | `deploy/config.php` · `deploy/config_hubspot.php` · `deploy/includes/google_config.php` · `deploy/includes/config_chatbot.php` · `deploy/includes/config_tienda_ia.php` · `deploy/includes/config_monitoreo.php` · `deploy/includes/config_noticias.php` (**no** copiar sus valores dentro de una guía) |
| Panel del sitio | `https://dechimbote.com/superadmin.php` (Súper Admin) |
| Google (entrar con Google) | la URI del dominio nuevo (`https://dechimbote.com/google_callback.php`) ya está en el código; **también hay que añadirla en Google Cloud Console** (lo hace el jefe) |
| PHP para validar sintaxis | `C:\xampp\php\php.exe -l <archivo>` |

## 6) LO QUE HAY QUE SUBIR (3 PASOS, SIEMPRE IGUAL)

```powershell
# 1) validar sintaxis
C:\xampp\php\php.exe -l D:\RELAX\deploy\includes\helpers.php
# 2) subir SOLO lo modificado (lee las credenciales de ftp_config.json y entra en la raíz viva)
python __subir_uno.py includes/helpers.php
# 3) verificar por HTTP
curl.exe -s -o NUL -w "%{http_code}" "https://dechimbote.com/?cb=1"
```

## 7) PENDIENTES DE LA MUDANZA (lo que NO puede hacer la IA)

| Pendiente | Quién | Qué hay que hacer |
|-----------|-------|-------------------|
| **Cron Job del monitoreo** ⚠️ **URGENTE: dejó de disparar** | **el jefe, en hPanel** | el 2026-09-15, después de la mudanza, se quedó en la ejecución **11:00** (140 en total) y a las **12:13** ya no había corrido: la ruta vieja murió con el dominio. Poner **`/usr/bin/php /home/u196269909/domains/dechimbote.com/public_html/cron/monitoreo_sistema.php`** (tipo **Personalizado**, `0 * * * *`) y comprobar que «Ejecuciones» sube |
| **Cron de noticias** | **el jefe, en hPanel** | `0 11 * * *` → `/usr/bin/php /home/u196269909/domains/dechimbote.com/public_html/cron/noticias_diarias.php` |
| **URI de Google — ⛔ ESTO ES LO ÚNICO QUE ROMPE «ENTRAR CON GOOGLE»** | **el jefe, en Google Cloud Console** | **Diagnóstico confirmado el 2026-09-15 14:48:** el código ya está bien y desplegado (el sitio en vivo manda `redirect_uri=https://dechimbote.com/google_callback.php`, comprobado con `curl.exe -s -D - -o NUL "https://dechimbote.com/google_login.php"`); lo que falta es **autorizar esa URI en el cliente OAuth**, que todavía tiene la vieja de `chimbote.xyz` → por eso el botón responde **`Error 400: redirect_uri_mismatch`**. **Pasos:** ① `https://console.cloud.google.com/apis/credentials?project=bot-marketplace-492512` · ② abrir el cliente OAuth cuyo ID empieza por **`240770495440-`** (cliente `chimbote-web`) · ③ en **«URIs de redireccionamiento autorizados»** → **AÑADIR URI** → pegar **`https://dechimbote.com/google_callback.php`** (exacto: `https`, **sin `www`**, **sin barra final**) · ④ **GUARDAR** (y borrar la línea vieja `https://chimbote.xyz/google_callback.php`) · ⑤ esperar de **5 min a un par de horas** (Google tarda en propagar) y probar **https://dechimbote.com/login.php** → *Ingresar con Google*. **Si el cliente no aparece** (borrado o proyecto perdido): crear un cliente OAuth nuevo (tipo *Aplicación web* con esa misma URI) y pasarle al agente el **Client ID** y el **Client Secret** nuevos → el agente cambia las 2 líneas de `deploy/includes/google_config.php` y lo sube (`python __subir_uno.py includes/google_config.php`). ⚠️ **TRAMPA EN LA QUE YA SE CAYÓ (2026-09-15, el jefe):** la página **«Cuentas de servicio»** (`IAM y administración → Cuentas de servicio → robot-vendedor`, ID `109378909204434623197`) **NO es el sitio donde se arregla esto** — ahí no hay ninguna URI que autorizar. Las URIs viven **solo** en **API y servicios → Credenciales** = **`/apis/credentials`**, dentro del **cliente OAuth**; el proyecto se ve arriba con el nombre **«Bot-Marketplace»** (el ID es `bot-marketplace-492512`). ⚠️ Aparte: si el botón diera *acceso denegado* en vez de este error, el asunto es la **pantalla de consentimiento** en modo **Prueba** (hay que poner el correo como *usuario de prueba*). El **login normal** (correo o teléfono + contraseña) **no depende de Google** |
| **Copia vieja anidada `/public_html/`** | el jefe decide | es de antes, se ve por HTTP en `dechimbote.com/public_html/…`; borrarla es limpieza, **nunca** se le hacen cambios |
| **Banners con la dirección vieja impresa** | ✅ **hecho el 2026-09-15** | 9 banners repintados con `www.dechimbote.com`; los originales quedaron en `D:\RELAX\__banners_originales_dominio_viejo_2026-09-15\` |
