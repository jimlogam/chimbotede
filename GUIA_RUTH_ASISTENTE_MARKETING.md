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


# GUÍA — RUTH, LA ASISTENTE DE MARKETING DE DECHIMBOTE.COM · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** si el jefe dice *"Ruth"*, *"el asistente de marketing"*, *"el widget"*,
> *"la página de prueba"* o pide cambiar/embellecer el asistente que habla con voz y fotos.
> **Estado:** ✅ **en producción como PÁGINA DE PRUEBA** (2026-09-10). Todavía **no** está enlazada
> desde el menú ni el pie, y **no** aparece en Google (manda `noindex` a propósito).
> **Última revisión: 2026-09-10.**

> 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni renombra nada a mano, así que **no se hace respaldo del archivo vivo** y **no se compara el local con el hosting** (ni md5, ni tamaños). Despliegue = `php -l` → subir solo lo modificado → verificar por HTTP. Las menciones a md5/respaldos que queden en este archivo son **históricas** y no son pasos del flujo.

---

## 1) QUÉ ES (EN 30 SEGUNDOS)

**Ruth** es una aplicación web **aparte** (vive en Google Cloud Run, no en el hosting de Hostinger) que
funciona como asistente de marketing de DeChimbote.com: recibe **voz 🎙️** y **fotos 📷** y responde con
ideas y el argumento de venta del sitio (vitrina 24/7, cotizaciones al WhatsApp/Telegram, S/10 al mes).

En el sitio hay **una sola página** que la muestra:

| Cosa | Dónde |
|------|-------|
| Página del sitio (la que ve la gente) | `deploy/ruth.php` |
| Enlace corto | **https://dechimbote.com/ruth** (regla `^ruth/?$` en `deploy/.htaccess`) |
| Enlace directo (sin regla) | **https://dechimbote.com/ruth.php** |
| URL de la app de Ruth | constante **`RUTH_WIDGET_URL`** dentro de `ruth.php` |
| Prueba de la variable de URL | `grep -n "RUTH_WIDGET_URL" D:\RELAX\deploy\ruth.php` |
| **Entrenamiento (el prompt que se pega en AI Studio Live)** | **`RUTH_ENTRENAMIENTO_AI_STUDIO.md`** (texto exacto, listo para copiar, con su versión y sus reglas) |

La página lleva **la cabecera real del sitio** (`includes/header.php`: banda de marca, buscador 🎙️,
marquesina de rubros, menú ☰, avisos de Telegram) y el **pie real** (`includes/footer.php`), y en el
centro el widget de Ruth dentro de un `<iframe>`.

---

## 2) LO ÚNICO QUE HAY QUE CAMBIAR CUANDO CAMBIA LO QUE SE EMBEBE

Todo (iframe, botón “abrir en grande” y enlaces) sale de **una sola constante**, la primera línea útil
de `deploy/ruth.php`:

```php
// const RUTH_WIDGET_URL = 'https://ais-dev-....us-west2.run.app?embed=true';   // widget propio de Ruth (comentado)
const RUTH_WIDGET_URL = 'https://aistudio.google.com/live?model=gemini-3.1-flash-live-preview';
```

**Estado a 2026-09-10 (tarde): apunta a AI Studio Live por orden expresa del jefe**, con la URL del
widget propio **comentada justo encima** para volver atrás en 5 segundos (se comenta una línea, se
descomenta la otra y `python __subir_uno.py ruth.php`).

Se cambia esa URL, se sube el archivo y listo. **No hay que buscar la dirección en más sitios.**

> ⚠️ **AI Studio dentro del iframe sale VACÍO** (`X-Frame-Options: DENY`, ver §7): está puesto porque el
> jefe lo pidió expresamente, no porque se vaya a ver.
> ⚠️ El nombre `ais-dev-…` es de una **app de desarrollo** de Google AI Studio / Cloud Run: puede
> apagarse o cambiar de dominio. Si algún día la página sale en blanco, **lo primero que se comprueba es
> que esa URL siga respondiendo** (§5).

---

## 3) CÓMO ESTÁ HECHA LA PÁGINA (DECISIONES TOMADAS)

1. **La cabecera es la del sitio, no una copia.** Se incluye `includes/header.php` y `includes/footer.php`
   (pedido expreso del jefe: *"ponle mi cabecera que uso en mi sitio web"*). Así Ruth se ve como una
   página más de DeChimbote.com y hereda buscador, rubros, menú y pie sin duplicar código.
2. **El `<iframe>` es el código que dio el jefe**, con dos cambios:
   - `src` sale de `RUTH_WIDGET_URL` (antes estaba escrito a mano) → un solo punto de cambio.
   - se le añadió `class="ruth-caja__embed"` para poder ajustarlo en móvil.
   `allow="microphone; camera"` se mantiene **tal cual**: es lo que habilita la voz y las fotos.
3. **El CSS vive dentro de `ruth.php`** (bloque `<style>`), no en `assets/css/`. Es una página nueva y de
   prueba: así no se toca ninguna hoja de estilos del sitio ni se sube el `?v=` de nadie.
4. **En móvil el widget mide `calc(100dvh - 200px)`** (CSS, con `!important` porque el `height="720"`
   del iframe manda). Sin eso, en un celular el chat se corta con la barra del navegador.
5. **`X-Robots-Tag: noindex, nofollow`** mientras sea prueba: no ensucia el SEO del sitio. **El día que
   Ruth sea oficial, se quita esa línea** y se la enlaza desde el menú ☰ o el pie.
6. **Botón “🚀 Abrir a Ruth en grande”** (pestaña nueva): plan B para el micrófono. Dentro de un iframe,
   algunos navegadores no conceden el micrófono aunque el iframe lo pida; abierta a pantalla completa
   **siempre** funciona. Por eso el botón está a la vista y no escondido.

---

## 4) CÓMO SE VERIFICA (SIN INVENTAR)

```powershell
# 1) Sintaxis antes de subir
& C:\xampp\php\php.exe -l D:\RELAX\deploy\ruth.php

# 2) Subir SOLO lo cambiado (sin respaldo del vivo y sin comparar local ↔ hosting)
python D:\RELAX\__subir_uno.py ruth.php      # y, si se tocó, .htaccess

# 3) Verificar de verdad (HTTP de la página y de las que dependen del .htaccess)
python D:\RELAX\__ruth_2_verificar.py
```

🔓 **Despliegue = 3 pasos: `php -l` → subir solo lo modificado → verificar por HTTP** (regla del
2026-09-12, el hosting es de uso exclusivo de la IA). **`__ruth_1_comparar.py`** (comparaba el local con el
hosting y respaldaba lo que se pisaba) queda como **HISTÓRICO (no se usa)**, igual que la carpeta
`_vivos_ruth\`.

Lo que **debe** salir tras un cambio correcto:

| Comprobación | Resultado esperado (2026-09-10) |
|---|---|
| ~~`ruth.php` md5 local = md5 subido~~ (**histórico**: ya no se comprueba) | `226e8a99b71dde3b98c47e857e4f6b33` (dato del 2026-09-10) |
| `https://dechimbote.com/ruth` | **HTTP 200** |
| `https://dechimbote.com/ruth.php` | **HTTP 200** (mismo contenido: 67.427 bytes) |
| `/`, `/buscar`, `/reclamar`, `/neg/<slug>`, `/sitemap.xml` | **HTTP 200** (el `.htaccess` no rompió nada) |
| `X-Robots-Tag` de `/ruth` | `noindex, nofollow` |
| Dentro del HTML de `/ruth` | `allow="microphone; camera"`, `marca-banda`, `topbar`, `buscador-fuzzy`, pie |

---

## 5) TRAMPAS YA PISADAS (NO VOLVER A CAER)

| Trampa | Qué pasa de verdad | Cómo se maneja |
|---|---|---|
| **La captura de pantalla del navegador no pinta los iframes de otro dominio.** | Se tomó captura de `/ruth` y el hueco del widget salía **gris uniforme**, como si la app estuviera vacía. Se puso `https://example.com` en el mismo iframe y la captura salió **idéntica** (mismo gris, 2,1 % de píxeles de diferencia): el gris **no era de Ruth, era de la captura**. | **Nunca** juzgar el embed por una captura. Se valida con: (a) que el `<iframe>` dispare su evento `load`, (b) las cabeceras de la app (§ siguiente) y (c) **los ojos del jefe** en su pantalla. |
| **`Invoke-WebRequest` a la app devuelve el título `Cookie check`, no a Ruth.** | Google Frontend responde un desafío de cookies cuando la petición llega **sin cookies**; un navegador real lo pasa y carga la app (título real: **“Ruth - Asistente de Ventas DeChimbote.com”**). | Un `Cookie check` por consola **no** significa que Ruth esté caída: se comprueba abriendo la URL en una pestaña. |
| **El micrófono dentro de un iframe.** | El navegador puede no conceder 🎙️ al iframe aunque lleve `allow="microphone"`. | Para probar la voz: botón **“Abrir a Ruth en grande”**, que la abre como página propia. |
| **Descargar el sitio entero para comparar.** | Prohibido por las Reglas de Oro: cuesta ~7 min y ya tumbó el sitio una vez. | **No se compara nada** (regla del 2026-09-12: el local es la verdad y el hosting es de uso exclusivo de la IA); se verifica por HTTP con `__ruth_2_verificar.py`. `__ruth_1_comparar.py` queda **HISTÓRICO (no se usa)**. |
| **Olvidar que `.htaccess` afecta a TODO el sitio.** | Una línea mala deja el dominio entero en 500. | Después de subir, comprobar `/`, `/buscar`, `/reclamar` y una ficha (**sin respaldo del vivo**: la carpeta `_vivos_ruth\` queda solo como archivo histórico — regla del 2026-09-12, el hosting es de uso exclusivo de la IA). |

---

## 6) PENDIENTES (CON SU SIGUIENTE PASO)

1. **Que el jefe confirme en su pantalla** que ve a Ruth dentro de `/ruth` y que el micro 🎙️ y las fotos 📷
   funcionan. → Siguiente paso: abrir **https://dechimbote.com/ruth** y decir qué se ve.
2. **Decidir si Ruth pasa a ser oficial.** → Siguiente paso: quitar `header('X-Robots-Tag: noindex, nofollow')`
   de `ruth.php` y añadir el enlace (menú ☰ en `includes/header.php` o pie en `includes/footer.php`).
3. **Si el iframe no cargara en el navegador del jefe**, revisar en este orden: (a) que `RUTH_WIDGET_URL`
   responda en una pestaña, (b) que la app siga viva en Cloud Run, (c) que la URL no haya cambiado de
   dominio. → Siguiente paso: cambiar la constante y volver a subir `ruth.php`.
4. **Nombre del título**: la app se llama a sí misma **“Asistente de Ventas”** y el `title` del iframe dice
   “Asistente de Marketing”. → Siguiente paso: unificar el texto cuando el jefe decida el oficial.

---

## 7) ⛔ AI STUDIO (`aistudio.google.com`) **NO SE PUEDE EMBEBER** (comprobado 2026-09-10)

El jefe pidió meter `https://aistudio.google.com/live?model=gemini-3.1-flash-live-preview` en un iframe y
“disimularlo” con capas encima. **Es imposible, y no es cuestión de CSS.**

**La prueba (30 segundos, repetible):**

```powershell
$r = Invoke-WebRequest "https://aistudio.google.com/live?model=gemini-3.1-flash-live-preview" -UseBasicParsing
$r.Headers['X-Frame-Options']      # -> DENY
$r.Headers['Content-Security-Policy']  # -> (la del login de Google: object-src 'none', etc.)
```

**`X-Frame-Options: DENY`** lo manda el servidor de Google y lo aplica **el navegador**, no la página que
embebe. Significa: *“este documento no se puede mostrar dentro de ningún iframe, de ningún sitio”*. El
iframe se queda **vacío** —no gris “por la captura”, vacío de verdad— y:

- **Ninguna capa, color, `z-index`, degradado o `overflow` lo arregla**: no hay nada que tapar, porque el
  navegador no llega a pintar el contenido. Un `<div>` encima de un hueco vacío sigue siendo un hueco vacío.
- **La sesión de Google no influye**: el bloqueo es una cabecera del servidor; da igual estar logueado.
- Lo mismo pasa con los trucos de “traer el HTML por PHP y pintarlo” (proxy inverso / `srcdoc`): rompe el
  **login OAuth** de Google, los **WebSocket** del modo Live y va contra los términos de uso. **Descartado.**

**Lo que SÍ se puede hacer** (ordenado de lo más “tuyo” a lo más rápido) — pendiente de que el jefe elija:

| Vía | Cómo se ve | Esfuerzo |
|---|---|---|
| **A. App propia con la UI de DeChimbote.com** (la definitiva): la voz en vivo se resuelve contra la **Gemini Live API** por WebSocket, con un pequeño backend (Cloud Run) que guarda la clave. Es lo que hace AI Studio por dentro, pero el visitante **nunca ve Google**. | 100 % marca propia; se puede embeber en `ruth.php` y taparle lo que sea | medio |
| **B. APK / WebView con la cabecera propia encima** (celular): dentro de un WebView, AI Studio carga como documento principal → **`X-Frame-Options` no aplica**; encima se pinta una barra propia (logo, colores, botón de micro). Es el “iframe con capas encima” que pidió el jefe, pero en una app. | parece app propia | bajo-medio |
| **C. Ventana tipo app**: acceso directo `chrome --app=<url>` o “Instalar como app”, más una portada de lanzamiento con los colores del sitio en `ruth.php` que la abre. | el primer contacto es propio; dentro sigue viéndose Google | mínimo |
| **D. Arreglar el widget propio** (`RUTH_WIDGET_URL`, la app de Cloud Run): **sí es embebible** (no manda `X-Frame-Options` ni CSP), así que ahí sí valen el marco y las capas de marca. | según lo que el jefe vea hoy | depende |

> ⚠️ **Antes de construir la vía D** hay que saber **qué ve el jefe** en `/ruth`: ¿recuadro vacío, la app
> pero sin voz, o la app lenta? Sin ese dato se decora un hueco.

### 7.1 Lo que se hizo igualmente (orden directa del jefe, 2026-09-10 tarde)

El jefe ordenó: *“mete esto al iframe: `https://aistudio.google.com/live?model=gemini-3.1-flash-live-preview`
y nada más”*. **Se hizo**: `RUTH_WIDGET_URL` apunta a AI Studio Live y la página está subida
(`ruth.php` md5 `48a92867acfde434f801ac7b11f53c7b`, 8.616 B — **dato histórico**: hoy no se compara el
local con el hosting, regla del 2026-09-12; HTTP 200 en `/ruth` y `/ruth.php`).

**Resultado esperado en pantalla: el recuadro del iframe sale VACÍO.** No es un fallo de la página ni del
despliegue: es el `X-Frame-Options: DENY` de Google que explica esta sección. La URL del widget propio de
Ruth quedó **comentada justo encima de la constante** para volver atrás en una línea.

