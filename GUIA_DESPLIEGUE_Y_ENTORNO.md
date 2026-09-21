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


# GUÍA — DESPLIEGUE, ENTORNO Y TRAMPAS DEL HOSTING · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** SIEMPRE que vayas a **editar, subir o verificar** algo en el sitio.
> **Archivos que toca:** los de `D:\RELAX\deploy` (nunca se edita en el servidor).
> **Estado:** ✅ vigente · **Última revisión: 2026-09-16** (🔓 **sin respaldos del vivo y sin comparaciones
> local ↔ hosting** · 🆕 **§6bis: la velocidad de entrega medida — el hosting va 8 veces más lento que la
> línea del jefe, y qué haría Cloudflare**)

---

## 0) LO ESENCIAL EN 30 SEGUNDOS

1. 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe **nunca** sube,
   edita, renombra ni borra nada a mano (ni por FTP, ni por el hPanel, ni por phpMyAdmin). Todo lo que
   hay en el hosting lo puso la IA.
2. Por eso: **NO se hace respaldo del archivo vivo** antes de subir y **NO se compara nada entre local y
   hosting** (ni md5, ni tamaños, ni fechas, ni bajadas de comprobación). **Ya no son pasos del flujo.**
3. **El local ES la verdad:** se edita en **`D:\RELAX\deploy`** y se sube a **Hostinger**. Si el hosting
   tuviera algo distinto, es porque una sesión anterior lo subió: se arregla **subiendo el local**.
4. **`php -l` antes de subir.**
5. **Subir solo lo modificado**, un archivo por vez: `python __subir_uno.py <ruta relativa>`
   (ese script hace **`cwd('/')`** y comprueba la marca de la raíz viva: ver **§11.1**). 🔴 **Si subes y
   la web no cambia, lo primero que hay que mirar es EN QUÉ CARPETA cayó el archivo** — el FTP deja al
   usuario en `/public_html`, que es una **copia vieja anidada**, no la web.
6. **Verificar por HTTP después** (y con el navegador en pestaña nueva si es visual).
7. Si un **PHP nuevo** da **404**, puede ser la **cuarentena del escáner del hosting** (§7)… **o** que se
   subió a la carpeta equivocada (§11.1). Comprobar la carpeta **antes** de culpar al antivirus.

> 🚫 **Lo que NO se hace nunca más:** respaldar el vivo, bajar el vivo para comparar, comparar md5/tamaños,
> ni "abortar el despliegue si el vivo cambió". Las carpetas `backup\`, `_backup_*`, `_vivos_*`,
> `_live\`, `_espejo_vivo_*` y los scripts de espejo/comparación **se quedan como archivo histórico y
> nadie tiene que correrlos**.

---

## 1) EL ENTORNO, DE UN VISTAZO

| Dato | Valor |
|------|-------|
| Sitio | **`https://dechimbote.com`** · la dirección oficial es **SIN www** (`www.dechimbote.com` → **301** a la de sin www). Delante del `www` está el **CDN de Hostinger** (`hcdn`); la de sin www pega directo al origen |
| Hosting | Hostinger, cuenta `u196269909`, hPanel |
| Código real | **`D:\RELAX\deploy`** (único punto de edición) |
| Documento raíz | `/home/u196269909/domains/dechimbote.com/public_html/` |
| Base de datos | MySQL `u196269909_CHIMBOTEALDIA` |
| Usuario de la web | `u196269909_ALDIACHIMBOTE@localhost` |
| PHP de producción | **8.3.33** |
| PHP local (para `php -l`) | XAMPP 8.2.12 → `C:\xampp\php\php.exe` |
| Zona horaria | El sitio usa `America/Lima`; **el MySQL del hosting va en UTC** (guardar fechas desde PHP) |

⚠️ **La ruta del hosting NO es `/home/u196269909/public_html/...`** (esa no existe). Siempre lleva
`domains/dechimbote.com` en medio. Ejemplo válido:
`/home/u196269909/domains/dechimbote.com/public_html/cron/monitoreo_sistema.php`.

⚠️ Existe una **copia VIEJA del proyecto** (otra arquitectura: otras constantes, otro `header.php`) en
`D:\desorden\chimboteweb\` (y su `_deploy_obsoleto_20260909_1931`). **Nunca desplegar archivos de ahí**:
fue la causa de la vez que el sitio se cayó con 500.

## 2) CREDENCIALES Y ACCESOS (DÓNDE ESTÁN, NO LOS VALORES)

- **FTP (cuenta NUEVA del 2026-09-15, la del dominio nuevo; la creó y la verificó el jefe al mudar el
  sitio). Los datos del jefe son estos:**
  - **Host:** `ftp.dechimbote.com` · **puerto** 21 (la IP `82.25.67.35` es la misma máquina y también sirve).
  - **Usuario:** `u196269909.dechimboteftp` · **contraseña:** `PON_AQUI_LA_CLAVE_DEL_FTP`.
  - **Carpeta inicial:** `public_html` ⚠️ pero **la raíz viva del sitio es `/`** (trampa del FTP, §11.1):
    el script **siempre** hace `cwd('/')` y comprueba la marca antes de escribir.
  - **Config que leen los scripts:** `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json`
    (ese archivo manda; dentro están estos mismos valores).
  - ⚠️ **Las cuentas viejas ya no existen** (`u196269909.chimboteftp`, `u196269909.chimbote.xyz`):
    no usarlas, dan **530**.
- **BD (phpMyAdmin):** hPanel → Sitios web → dechimbote.com → Administrar → **Bases de datos**.
- **Credenciales del sitio:** `deploy/config.php` · `deploy/config_hubspot.php` ·
  `deploy/includes/google_config.php`. **Nunca copiar sus valores dentro de una guía.**
- **Acceso remoto a la BD:** no está abierto por defecto (el usuario es `@localhost`). Para conectar
  una herramienta externa hay que añadir la **IP pública del jefe** (`190.108.93.134`) en
  **hPanel → Remote MySQL → Create** — si reinicia el router, hay que actualizarla.
- **IP pública del jefe:** `190.108.93.134` (sirve también para `curl --resolve`).

## 3) 🔓 EL HOSTING ES DE USO EXCLUSIVO DE LA IA — SIN RESPALDOS Y SIN COMPARACIONES (2026-09-12)

> **Orden del jefe (2026-09-11):** *"vamos a saltarnos el paso de verificar si los archivos locales son
> exactamente iguales que los del hosting… yo en el hosting no toco nada, no subo nada, solo trabajo en
> local."*
> **Orden del jefe (2026-09-12, la que manda):** *"elimina de las guías la regla de hacer respaldo del
> vivo o de tener que comprobar si lo que está en local coincide con lo que está en el hosting, debido a
> que yo nunca subo nada al hosting de modo manual ni edito archivos ni renombro nada… en pocas palabras
> el hosting es de uso exclusivo de la IA que crea el sitio."*

- 🔓 **El jefe no toca el hosting jamás:** no sube, no edita, no renombra y no borra nada a mano (ni por
  FTP, ni por el hPanel, ni por el Administrador de archivos, ni por phpMyAdmin). **Todo lo que hay en el
  hosting lo puso la IA.**
- 🚫 **PROHIBIDO respaldar el archivo vivo antes de subir.** Ya no es un paso del flujo (§4).
- 🚫 **PROHIBIDO comparar local ↔ hosting** de cualquier forma: **md5, tamaños, fechas o bajadas de
  comprobación**. Tampoco "guardas" que aborten la subida porque el vivo cambió.
- ✅ **El local ES la verdad.** Si el hosting tuviera algo distinto, es porque **una sesión anterior lo
  subió**: se arregla **subiendo el local**, no comparando.
- ✅ **El despliegue queda en 3 pasos:** `php -l` → subir solo lo cambiado → verificar por HTTP.
- ℹ️ **Lo que ya existe se queda como archivo histórico y NO se usa:** las carpetas `backup\`,
  `_backup_*`, `_vivos_*`, `_live\`, `_espejo_vivo_*` y los scripts de espejo/comparación
  (`espejo_vivo.py`, `espejo_verificar_deploy.py`, `__reco_md5.py`, `__reco_diff_vivo.py`,
  `__bajar_vivos_*.py`, `__*_comparar.py`, `__*_deploy*.py` con respaldo). **Nadie tiene que correrlos:**
  son el registro de cómo se trabajaba antes del 2026-09-12.
- 📏 **Bajar el sitio completo NO es parte del flujo** ni como respaldo: `espejo_vivo.py` (~521 archivos /
  ~50 MB / ~7 min) queda como herramienta histórica (§18).

### 3.1 LEER DATOS DE PRODUCCIÓN SIN phpMyAdmin: LA SONDA (patrón del 2026-09-10)

A veces la respuesta no está en el código sino **en la base de datos** (ej. *"¿a qué rutas van los 404 que
me llegan?"*). **El MySQL remoto NO está disponible aunque el puerto 3306 esté abierto:** el usuario del
sitio es `u196269909_ALDIACHIMBOTE@localhost` y el de Metabase rechaza la IP del jefe
(`Access denied for user 'u196269909_metabase'@'190.108.93.134'`). Comprobado el 2026-09-10. Así que:

1. Se escribe un **PHP de solo lectura, protegido por clave**, que hace las consultas y devuelve **JSON**
   (`key` obligatoria; si no coincide → `403`). **Nunca imprime credenciales** (usa `db()` del
   `config.php` del servidor).
2. Se sube por FTP, se lee por HTTPS y **se borra del servidor en la misma ejecución** (bloque `finally`).
3. **La plantilla NO vive en `deploy/`** — así ningún `subir_deploy.py` puede publicarla por descuido:
   `D:\RELAX\__sonda404.php` + `D:\RELAX\__sonda404.py` (patrón listo para copiar y cambiar las consultas).
   Ejemplo reciente: `D:\RELAX\__sonda_tiendas.php` + `.py` (columnas reales y conteos de las 1 593 tiendas,
   usados el 2026-09-12 para la vista previa del Súper Admin).

```powershell
python __sonda404.py     # sube la sonda, guarda el JSON en __sonda404_resultado.json y la BORRA
```

⚠️ **Verificación obligatoria al terminar:** pedir la URL de la sonda y comprobar que responde **404**
(`https://dechimbote.com/__sonda404.php?key=…`). Si sigue viva, es una puerta abierta con datos del sitio.

> ℹ️ **La sonda NO es un respaldo:** es la forma de **leer datos** del sitio (columnas, tablas, conteos).
> Lo que se borra del hosting al terminar son las **sondas temporales** (por seguridad), no "respaldos".

### 3.2 LAS GUÍAS SE PUEDEN MOVER MIENTRAS TRABAJAS

Otra sesión puede estar editando **la misma guía** (pasó el 2026-09-10 con
`GUIA_MAESTRA_CHIMBOTE_XYZ.md`: el editor avisó *"file changed since it was read"*). **Qué hacer:**
**releer** el archivo, **anexar al final** (no reordenar el medio) y usar como ancla la **última línea
real** del archivo, no un texto del medio que otra sesión pudo mover.

## 4) EL DESPLIEGUE, PASO A PASO (3 PASOS, SIN RESPALDO)

1. **Editar en `D:\RELAX\deploy`** — es la fuente de verdad y **no hay paso previo** de comprobación ni de
   copia de seguridad (Regla de Oro n.º 3).
2. **Validar sintaxis** de cada archivo tocado:
   `C:\xampp\php\php.exe -l <archivo>` → debe decir *"No syntax errors detected"*.
   ⚠️ El local es PHP 8.2 y producción 8.3: que pase en local **no** garantiza el hosting.
3. **Subir SOLO lo modificado** (nunca el `deploy/` completo sin revisar), **un archivo por vez**:
   - `python __subir_uno.py <ruta relativa>` → ej. `python __subir_uno.py includes/header.php`
     (lee las credenciales de `ftp_config.json`).
   - `python subir_deploy.py` sube **todo** `deploy/` (122 archivos, ~4,7 MB) → **peligroso**: arrastra
     el trabajo local sin desplegar (§9). Y tiene **la trampa del FTP dentro**: hace `cwd('/public_html')`,
     que es la **copia vieja anidada**, así que subiría «sin error» a la carpeta muerta. Lo correcto es
     `__subir_uno.py` (§11.1).
4. **Verificar por HTTP** (§5) y, si es visual, **en el navegador en pestaña nueva** (cerrarla al terminar).
5. **Actualizar la guía del módulo** (🚫 sin crónica de sesión: orden del jefe, 2026-09-14).

> 🧪 **Opción útil (no obligatoria): probar antes de pisar.** Para una sección o página muy tocada se
> puede subir un **archivo de prueba temporal** (p. ej. `__pv_<tema>.php` en la raíz, **fuera de
> `deploy/`**) que haga `require` del código nuevo, abrirlo en el navegador con la sesión ya iniciada del
> jefe, y **borrarlo del servidor** al terminar (comprobando el 404). Ejemplo real: la vista previa de
> tiendas del Súper Admin (2026-09-12). Esto **no** es un respaldo: es una página desechable de prueba.

🚫 **Scripts prohibidos (hay que saber por qué):**

- `verificar_y_subir.py`: sube **todo** el deploy sin revisar y lleva la contraseña escrita dentro.
- `preparar_deploy.py`: ⚠️ **borra la carpeta `deploy` entera** (`shutil.rmtree`) y la reemplaza con una
  copia descargada de `C:\Users\Usuario\Downloads\...`. Es el candidato más probable de la vez que se
  subió la versión VIEJA encima del sitio real y **el sitio se cayó con 500**. No ejecutarlo nunca.
- `deploy_files.py`: **ya no existe** en `D:\RELAX` (guías viejas lo citaban). Si lo ves citado en algún
  lado, el comando vigente es `__subir_uno.py`.

## 5) VERIFICAR POR HTTP DESPUÉS DE SUBIR

**Cómo leer los códigos:**

- **200** → correcto.
- **302** → correcto en páginas protegidas (ej. `/productos.php` → `login.php?redirect=…`).
- **403** al abrir un archivo de `/includes/` **directo** → **normal** (el hosting bloquea el acceso
  directo a esa carpeta) y **no afecta** a los `include` de PHP.
- **500** → **nunca debe pasar**: significa que se subió código equivocado o viejo.
- Un **404** en un PHP **nuevo** → sospechar del escáner del hosting (§7).
- Un **404 con `Set-Cookie: CHIMBOTE_SID`** → lo genera `404.php` (ErrorDocument) y **no** tu código.

**Tabla de referencia (esperado tras un despliegue sano):**

| URL | Esperado |
|-----|----------|
| `https://dechimbote.com/index.php` | 200 |
| `https://dechimbote.com/registrar_negocio.php` | 302 (pide login) |
| `https://dechimbote.com/guardar_asistente.php` | 401 (pide sesión) |
| `https://dechimbote.com/caminante/subir.php` | 405 |
| `https://dechimbote.com/api/lead.php?n=0` | 302 |
| `https://dechimbote.com/includes/helpers_hubspot.php` | 403 (bloqueado) |
| `https://dechimbote.com/config_hubspot.php` | **403 (bloqueado)** desde el 2026-09-10 (antes 200 con 0 bytes) — lo bloquea el `.htaccess` |
| `https://dechimbote.com/config.php` · `/cron_reset_vistas.php` | 403 (bloqueados por el `.htaccess`) |
| `https://dechimbote.com/api/telegram_bot.php` | 200 · texto "Bot de DeChimbote.com activo" |

⚠️ **Nunca** probar `api/lead.php?n=<id real>`: dispara un **aviso real** al Telegram del jefe y suma un
clic al `lead_score`.

**Técnicas que funcionan (todas usadas ya en este proyecto):**

| Técnica | Para qué |
|---------|----------|
| `?cb=<epoch>` (cache-busting) | Ver el HTML nuevo aunque Cloudflare cachee |
| `curl --resolve dechimbote.com:443:82.25.67.35 https://…` | Hablar con el **origen**, saltando Cloudflare |
| SHA256 local vs remoto (FTP `RETR` en memoria) | Confirmar que el archivo subió íntegro |
| `Invoke-WebRequest` con User-Agent de navegador | Probar sin que el WAF moleste |
| `php -l` local | Sintaxis antes de subir |

## 6) CACHÉ: `?v=N` EN CSS Y JS

- Al cambiar **CSS o JS compartido** hay que **subir la versión** de la URL (`?v=N`): Cloudflare lo
  cachea y, además, el hosting manda `Cache-Control: public, max-age=604800` (**7 días**). Sin subir el
  número, el jefe sigue viendo la versión vieja una semana.
  Versiones **vivas hoy (2026-09-10)**: `banners-v2.css?v=2` y `banners.js?v=2`.
  Dónde se sube el número: CSS en `includes/header.php`, JS en `includes/footer.php`
  (y **hay que subir esos dos archivos** después de cambiarlo).
- Si el componente añade su CSS/JS **inline** en la propia página, **no** hay que tocar `?v=N`.
  Regla práctica: **componente puntual → CSS inline; CSS compartido → subir `?v=N`.**
- ⚠️ Trampa: el sitio **NO** carga `assets/css/banners.css` (variante vieja; solo la usa
  `prueba_diseno.html`). La hoja viva de banners es **`banners-v2.css`**.

### 🔴 MEDIDO EL 2026-09-15: LA CACHÉ ES **POR URL COMPLETA** (y te deja creyendo que el despliegue falló)

Subiendo el CSS del constructor de tiendas (`assets/css/tienda_ia.css`, **16 733 bytes**) el servidor
siguió entregando **el de antes (16 266 bytes)** porque la página lo pedía con **`?v=3`** y esa URL ya
estaba cacheada. Lo mismo con **cualquier otro `?v=`**: en cuanto cambia la cadena, el servidor devuelve
el archivo **nuevo** al instante.

| URL pedida | Lo que devolvió |
|---|---|
| `…/tienda_ia.css?v=3` (la que pedía la página) | **16 266 bytes** ← el VIEJO |
| `…/tienda_ia.css?v=4` | 16 733 bytes ← el nuevo |
| `…/tienda_ia.css?x=abc` | 16 733 bytes ← el nuevo |

**Qué hacer:** al cambiar un `.css` o un `.js`, **subir el número en la página que lo pide** (`?v=3` →
`?v=4`) **y volver a subir ese PHP**. Y comprobarlo en 10 segundos, comparando lo que sirve el servidor
con el archivo local (**tienen que pesar lo mismo**):

```powershell
(Invoke-WebRequest 'https://dechimbote.com/assets/css/tienda_ia.css?v=4' -UseBasicParsing).RawContentLength
(Get-Item D:\RELAX\deploy\assets\css\tienda_ia.css).Length
```

⚠️ **Cómo se detecta que te está pasando:** el archivo está subido (el FTP lo confirmó) y el cambio
«no se ve» **ni recargando**. Antes de buscar el problema en el navegador, en el `.htaccess` o en un
error de PHP, **pregúntale al servidor cuánto pesa lo que sirve**. Pasó el 2026-09-15: se creyó durante
un rato que el CSS nuevo estaba mal escrito, cuando en realidad el navegador nunca lo había recibido
(lo delató la medición del diseño: las reglas recién subidas no aparecían).

## 6bis) 🔴 LA VELOCIDAD DE ENTREGA: EL HOSTING VA **8 VECES MÁS LENTO** QUE LA LÍNEA DEL JEFE (medido el 2026-09-16)

> Pregunta del jefe (textual): *«¿cómo es posible que pueda ver un video de YouTube en HD y mi web, que no
> pesa más de 7 MB, tarde tanto en cargar? ¿Puede ser el servidor? ¿La CDN? ¿Será buena opción usar
> Cloudflare?»*

**La respuesta corta: no es tu celular ni tu línea, y tampoco el peso de la página. Es la velocidad a la que
el hosting/CDN entrega los archivos.** Medido desde la propia PC del jefe, el mismo día:

| Qué se midió | Velocidad |
|---|---|
| **La línea del jefe** contra Cloudflare (3 MB) | **3,83 MB/s** (≈ 31 Mbps) — la línea está perfecta |
| El **CDN del sitio** (`hcdn`) sirviendo una foto cacheada de 59 KB | **0,18 MB/s** |
| El **CDN del sitio** sirviendo una foto de 200 KB | **0,47 MB/s** |
| El **HTML de la portada** (317 KB) | **88 KB/s** → **3,7 s** solo para el HTML; TTFB **629 ms** |
| Un CSS del sitio (63 KB) | 390 KB/s · TTFB **209 ms** |

O sea: **el mismo cable baja 3,83 MB/s de un lado y 0,2-0,5 MB/s del otro.** Con 1,5 MB de portada, eso es
la diferencia entre **0,4 s** y **4-7 s**. (`x-hcdn-cache-status: HIT` en las fotos: el CDN **sí** las tenía
en caché y aun así las entregaba a esa velocidad.)

**Lo que NO es el problema** (comprobado, para no perder el tiempo en ello):

- **El protocolo:** el servidor **SÍ soporta HTTP/2** (TLS 1.3, `ALPN = h2`) y el `trust` de cifrado sale en
  **78 ms**. No hay que «activar HTTP/2».
- **El peso:** ya se bajó de 2,19 MB a 1,51 MB al abrir (ver `GUIA_IMAGENES_Y_OPTIMIZACION.md` §11) y sigue
  siendo lento, porque el cuello de botella es el caudal, no los bytes.
- **El 403 que aparece a veces** midiendo seguido: es el CDN **limitando** a quien hace muchas peticiones
  seguidas (con las cabeceras normales de un navegador responde **200** siempre). No es un fallo del sitio.

**Por qué un video de YouTube en HD se siente instantáneo y esto no:** YouTube tiene **miles de servidores
repartidos por el mundo** (y uno cerca de Chimbote), sirve por **HTTP/3**, y **empieza a reproducir con los
primeros cientos de KB** mientras sigue bajando. Aquí, en cambio, el navegador tiene que **esperar el HTML**
(629 ms de TTFB) para saber qué fotos pedir, y luego bajar ~50 fotos por un camino que entrega a 0,2-0,5 MB/s.

### ¿Cloudflare sería buena opción? Sí, es la palanca más grande que queda (y es gratis)

Está **sin hacer**: nadie ha tocado el DNS ni el hosting por esto. Lo que haría:

| Qué mejora | Por qué |
|---|---|
| **Fotos, CSS y JS servidos desde una punta de Cloudflare cerca del Perú** | Es el **~1 MB de los 1,5 MB** de la portada. En la misma medición, Cloudflare entregó a **3,83 MB/s** contra los 0,2-0,5 MB/s del CDN actual. |
| **HTTP/3 y TLS 1.3** de serie | Menos idas y vueltas por cada archivo. |
| **Menos costo del hosting** | Las fotos dejarían de salir del hosting en cada visita. |
| **Ya está preparado para el cambio de caché** | Las fotos van con **`?v=75`** (ver §11.4 de la guía de imágenes): cuando cambian los bytes, se sube el número y el borde las vuelve a pedir. |

**Lo que hay que saber antes de decidir (no es solo «activar un botón»):**

1. **Hay que cambiar los servidores de nombres (DNS) del dominio a Cloudflare** — se hace desde el
   **registrador/hPanel**, y eso **solo lo puede hacer el jefe** (la IA no tiene esa llave). Después, en
   Cloudflare: plan **gratis**, proxied (nube naranja), y reglas de caché para `fotos/`, `assets/`.
2. **El HTML seguiría saliendo del hosting** (TTFB ~0,6 s): la portada es dinámica y lleva sesión. Se puede
   cachear en el borde para visitantes anónimos, pero **hay que hacerlo con cuidado** (el sitio manda cookie
   de sesión en todas las páginas). Proyecto aparte.
3. **`hcdn` (el CDN de Hostinger) quedaría detrás de Cloudflare.** Conviene **desactivarlo** en hPanel para
   no encadenar dos cachés (dos capas = el doble de tiempo hasta que un cambio se ve).
4. ⚠️ **Si se migra, el `?v=` sigue siendo obligatorio** (Cloudflare también cachea por URL completa): lo que
   aprende el §6 de esta guía no se pierde, se refuerza.

**Expectativa medida y prudente:** con el borde cerca, la portada pasaría de **4-7 s a ~1 s** en el celular
del jefe (0,4 s de fotos + ~0,6 s del HTML). **Mientras el DNS no se mueva, lo que queda por hacer es bajar
peso**, que ya está documentado en la guía de imágenes §11.5.

**Herramienta para volver a medirlo:** `__red_diagnostico.py` (mide protocolo, compresión, velocidad
contra el sitio y contra Cloudflare, y compara) y **`__cf_estado.py`** (el semáforo de la activación:
dice quién maneja el DNS, quién sirve la web, a qué velocidad y si el correo conserva sus MX).

### 6ter) 🔵 CLOUDFLARE: EL BLINDAJE YA ESTÁ HECHO Y FALTA SOLO EL DNS (2026-09-16)

**Estado medido ese día:** el DNS sigue en **Hostinger** (`byte/pixel.dns-parking.com`), la web la sirve
**`hcdn`** (`x-hcdn-cache-status: DYNAMIC`, TTFB ~0,6 s) y las **fotos salen a 0,2-0,5 MB/s** mientras
**Cloudflare entrega a ~3 MB/s** por la misma línea (`__cf_estado.py`). El **origen real es la IP
`82.25.67.35`** (comprobado con `curl --resolve`: responde el sitio con `Server: LiteSpeed` y sin
cabeceras de `hcdn`) — ⚠️ **ojo: los A actuales del dominio (77.37.85.228 / 185.249.224.69) NO son el
origen, son puntas del CDN de Hostinger**: al armar la zona en Cloudflare hay que apuntar al origen.

**🔴 LA TRAMPA QUE HABÍA QUE DESACTIVAR ANTES DE ACTIVAR LA CDN (hecho):** el sitio guarda la **IP del
visitante** en **24 sitios** (informes inteligentes persona/robot, antispam por IP, claves de dedupe de
los avisos, visitas, clics de 📞/💬, muro, empleos, Caminante). Si Cloudflare se ponía delante y el
servidor empezaba a ver la **IP de la punta de Cloudflare**, **todos los visitantes habrían parecido la
misma persona** y los informes del jefe se volvían basura (el mismo problema de los falsos positivos del
2026-09-16, pero peor). **Ya está resuelto:** nació el motor **`deploy/includes/ip_real.php`**
(`ip_real()` · `ip_es_de_cloudflare()` · `ip_en_rango()`), que devuelve la IP del visitante **con o sin
CDN delante**: se cree `CF-Connecting-IP` **solo si** quien nos habla es de verdad una punta de
Cloudflare (lista oficial de rangos, bajada el 2026-09-16) y lo que trae es una IP válida — así **una
cabecera inventada no sirve para falsear la IP ni para saltarse los límites**. Se cambiaron los 24
puntos en **16 archivos** (`config.php` la carga **antes** de los helpers, con red de seguridad si el
archivo faltara; `includes/helpers.php`, `avisos.php`, `metricas.php`, `explorer.php`, `banners.php`,
`telegram_subs.php`, `pedidos_sin_vendedor.php`, `chatbot.php`, `api/{reportar,llamada,lead,muro_accion,telegram_bot}.php`
y `caminante/subir.php`, que además **dejó de leer `X-Forwarded-For` a pelo**). **Subido y verificado por
HTTP el 2026-09-16** (portada, buscador, empleos, sitemap y APIs en 200; `caminante/subir.php` en 405 =
solo POST; y la IP que guarda el sitio es la real, idéntica a antes). **Sin Cloudflare activado el
cambio NO altera nada.**

**Lo que falta (y es lo único que no puede hacer la IA: no tiene la llave del registrador):**
1. Cuenta de Cloudflare (gratis) + *Add a site* `dechimbote.com`.
2. Cambiar los **servidores de nombres** en **hPanel → Dominios → dechimbote.com → Nameservers**.
   ⚠️ **Antes de tocar los nameservers** la zona de Cloudflare tiene que tener ya: los **2 MX**
   (`mx1/mx2.hostinger.es`), el **TXT de SPF** (`v=spf1 include:_spf.mail.hostinger.com ~all`), el
   **TXT de verificación de Google**, el **A del origen** (`82.25.67.35`), el **www**, el **A de `ftp`
   en gris (DNS only)** —porque el FTP no pasa por el proxy— y los CNAME de `autodiscover`/`autoconfig`:
   **si falta un MX, se cae el correo del dominio.**
3. Después, en Cloudflare: SSL **Full (strict)**, caché para `/fotos/*` y `/assets/*` (siguen
   obligatorios los `?v=N`), **Bot Fight Mode apagado** (si no, la pelea con bots puede frenar el
   webhook del bot de Telegram y las subidas de Caminante) y **desactivar `hcdn`** en hPanel para no
   encadenar dos cachés.

📖 Herramientas de esta tarea: **`__cf_estado.py`** (semáforo), **`__ep_cdn_entorno.php`** (sonda que
dice qué IP ve el servidor y con qué cabeceras — se corre con `__sonda_run.py`) y
**`__cf_ip_real.py`** (el script que hizo los 24 cambios, con simulacro).

### ✅ 6quater) CLOUDFLARE QUEDÓ ACTIVADO EL 2026-09-16 (lo que hay que saber para el futuro)

**Cómo quedó (verificado leyendo la zona por la API, no de oídas):** la zona `dechimbote.com`
(id `849a8d7a75f95937418790f4a47c4e30`) está **activa** con los nameservers
**`bryce.ns.cloudflare.com`** y **`paige.ns.cloudflare.com`** (el cambio lo hizo el agente de
Hostinger; snapshot del DNS anterior: **`180820359`**). La zona la dejó el script
**`__cf_arreglar_zona.py`** así:

| Registro | Cómo está |
|---|---|
| **A raíz** | **`82.25.67.35`** (el ORIGEN) con nube 🟠 — 🔴 **los A que importa el robot de Cloudflare por su cuenta son del CDN de Hostinger y NO sirven**: hay que ponerlos a mano |
| **AAAA raíz** | borrados (Cloudflare entrega IPv6 al visitante por su cuenta) |
| **CNAME `www`** | → `dechimbote.com`, 🟠 |
| **`ftp` · `autoconfig` · `autodiscover`** | ⚪ **gris (DNS only)** — 🔴 **con nube naranja el FTP deja de funcionar** (el proxy no pasa el puerto 21) y se rompe la configuración automática del correo |
| **MX ×2 y TXT ×2** | intactos |

Ajustes: **SSL = `strict`** (Full strict), Always Use HTTPS, TLS mín. 1.2, Brotli, HTTP/3 · **Bot Fight
Mode apagado** (si no, la pelea con bots puede frenar el webhook del bot de Telegram y las subidas del
Caminante) · **regla de caché**: `starts_with(/fotos/) or starts_with(/assets/)` → **Edge 30 días,
Browser 7 días**. El **HTML y `/api/` NO se cachean** (son dinámicos y llevan sesión: el sitio manda
`Cache-Control: no-store`) — comprobado: la portada responde `cf-cache-status: DYNAMIC`.

**🔴 LO QUE HAY QUE SABER SÍ O SÍ:**

1. **El `?v=` es MÁS obligatorio que nunca.** Todo lo que pase por `/fotos/` y `/assets/` queda
   cacheado 30 días **por URL completa**. Al cambiar un CSS/JS/FOTO **en el mismo nombre de archivo**
   hay que **subir el número** (`?v=75` → `?v=76`), o el cambio no se verá hasta 30 días. Las portadas
   nuevas **no tienen problema** porque el publicador escribe **nombre de archivo nuevo**.
2. **El hcdn (CDN de Hostinger) quedó FUERA del camino solo**: como el A apunta directo al origen, el
   tráfico ya no pasa por él. No hay que apagarlo en hPanel.
3. **La IP real del visitante se conserva** (medido con sonda el 2026-09-16, ya con Cloudflare delante):
   `REMOTE_ADDR = 181.176.116.44` (la del visitante) y `CF_CONNECTING_IP` igual; el sitio guarda la IP
   **real**. El motor `includes/ip_real.php` cubre el caso contrario (si algún día el hosting dejara de
   restaurarla). La zona manda también `CF-IPCOUNTRY` (país del visitante), por si algún día se quiere
   usar.
4. **Medición del antes/después** (mismos archivos, misma línea, `__cf_velocidad.py`):

   | | Antes (hcdn) | Después (Cloudflare) |
   |---|---|---|
   | HTML de la portada | **1,98 s** | **0,48 s** (4×) |
   | Cada foto (62 KB) | 0,44 s · 0,14 MB/s | **0,15 s · 0,41 MB/s** (3×) |
   | Foto más lenta de la muestra | 0,96 s | **0,13 s** (7×) |
   | Los 9 archivos de la muestra | **5,56 s · 0,06 MB/s** | **2,12 s · 0,15 MB/s** |

   📌 **Lo que sigue pesando:** la portada pide **138 archivos / 3,5 MB** y **rota** cada rato, así que
   una foto que **nadie ha pedido todavía** el borde la tiene que ir a buscar al hosting, que entrega a
   ~0,2 MB/s (`cf-cache-status: MISS`). Ahí queda margen: **precalentar el borde** (recorrer el sitio
   una vez para que Cloudflare guarde todas las fotos) y **bajar el peso de la portada**
   (`GUIA_IMAGENES_Y_OPTIMIZACION.md` §11).
5. **Cómo se revierte todo:** en hPanel → Dominios → dechimbote.com → Nameservers → volver a poner
   `byte.dns-parking.com` y `pixel.dns-parking.com` (y, si hiciera falta, restaurar el snapshot
   `180820359` del DNS de Hostinger).

📖 Herramientas de esta tarea (todas en `D:\RELAX`): **`__cf_estado.py`** (semáforo: DNS, quién sirve,
velocidad y MX), **`__cf_velocidad.py`** (el antes/después con los mismos archivos),
**`__cf_pagina.py`** (la portada completa como la pide un navegador, en paralelo),
**`__cf_arreglar_zona.py`** (deja la zona correcta; simulacro por defecto),
**`__cf_ver_zona.py`** (lee la zona y los ajustes), **`__hs_nameservers.py`** (nameservers por la API de
Hostinger **con freno de seguridad**: no cambia nada si la zona de Cloudflare no tiene el A al origen,
el `ftp` en gris y los MX), **`__cf_esperar.py`** (vigía de la activación) y **`__ep_cdn_entorno.php`**
(la sonda de la IP real). Los tokens viven en `C:\Users\Usuario\Documents\apk\seguridad\`
(**`cloudflare.json`** — el de Hostinger, `hostinger.json`, se borró al terminar la mudanza; por eso
hoy **no** se puede tocar el DNS ni los nameservers por API: eso es del jefe o de soporte de Hostinger),
igual que el del FTP.

### ⚠️ 6quinquies) EL SUSTO DEL IPv6 VIEJO (2026-09-17): «ESTE SITIO NO PUEDE PROPORCIONAR UNA CONEXIÓN SEGURA»

**Síntoma (lo trajo el jefe: captura del celular a las 6:58):** en el celular (5G) `dechimbote.com`
daba **`ERR_SSL_PROTOCOL_ERROR`** («Este sitio no puede proporcionar una conexión segura»), mientras que
en la PC el sitio cargaba perfecto en el navegador.

**No era el sitio.** El día anterior se mudaron los nameservers a Cloudflare; mientras los resolutores
(el del router y el de la operadora) seguían contestando con la **zona vieja**, mandaban **A → puntas del
`hcdn`** (esas **sí** funcionan) y **AAAA → `2a02:4780:…`**, y esas direcciones IPv6 **aceptan TCP en el
443 pero NO hacen TLS** (comprobado con SSL Labs: *Failed to communicate with the secure server*). El
celular prefiere IPv6, conecta, **el TLS se cae antes de la web** y Chrome no vuelve a probar IPv4 → ese
error. La PC no tiene IPv6 (solo link-local): por eso ella nunca lo vio. Ojo: en un celular de red
**IPv6-only (464XLAT)** esto no deja ni siquiera la opción de caer a IPv4: el sitio queda caído.

**Qué se hizo: NADA** (no había nada que arreglar en el sitio ni en hPanel). Se esperó el TTL: los
registros de Cloudflare son de **TTL 300** y a los pocos minutos **las dos vías** (el DNS de la red y
hasta los nameservers viejos `byte/pixel.dns-parking.com`) ya devolvían Cloudflare → el celular quedó bien.

**Cómo se comprueba en 10 segundos (receta para la próxima mudanza):**

| Comprobación | Comando | Qué tiene que dar |
|---|---|---|
| **AAAA** | `Resolve-DnsName dechimbote.com -Type AAAA -Server 192.168.1.1 -DnsOnly` | **`2606:4700:…`** (Cloudflare). Si da `2a02:4780:…` es la zona vieja → **esperar, no tocar nada** |
| **A / quién sirve** | `curl.exe -sSI https://dechimbote.com/` | `Server: cloudflare` + `CF-RAY:` (si dice `Server: hcdn` es la zona vieja) |
| **IPv6 de verdad** (esta PC **no** tiene IPv6) | codificar la IPv6 en `sslip.io` y preguntarle a SSL Labs: `https://api.ssllabs.com/api/v3/analyze?host=2a02-4780-…-….sslip.io&publish=off&all=done` | la buena: `Ready` + nota **A** · la rota: **Failed to communicate with the secure server** |

⚠️ **Regla nueva:** cualquier queja de **celulares** con error de conexión segura después de mover DNS =
**mirar primero la AAAA**, no el sitio ni los archivos. Y si el jefe tiene el celular con el error todavía
en pantalla, se le pide **volver a cargar** (el TTL es de 5 minutos) o apagar/encender los datos.



## 7) ERROR CONOCIDO: EL ESCÁNER DEL HOSTING BLOQUEABA LOS PHP NUEVOS (404) — ✅ RESUELTO

- **Síntoma:** archivos PHP **nuevos** subidos por FTP respondían **404** aunque existían en
  `/public_html` con el contenido correcto (**SHA256 idéntico al local**). Los que **sobrescribían** un
  archivo existente sí corrían.
- **Diagnóstico:** el 404 aparecía también con cache-busting y contra el origen (no era Cloudflare). Un
  PHP de **una línea** recién subido también daba 404; **renombrar no cambiaba nada**.
  Conclusión: **cuarentena del antivirus del hosting** sobre las subidas FTP recientes.
- **Archivos afectados entonces:** `tablones.php`, `tablon.php`, `api/tablon_enviar.php`,
  `api/tablon_estado.php`, `migrar_tablones.php`, `api/banners.php`, `migrar_banners.php`, `migrar_chat.php`.

  > 🗑️ **RETIRADO (2026-09-13, orden del jefe):** de esa lista, los **5 archivos de tablones** y
  > `migrar_chat.php` **ya no existen** (el módulo de tablones se quitó del sitio: ver el bloque 🗑️ más
  > abajo, en este mismo apartado). Hoy solo siguen vivos los de **banners**.
- **Soluciones:** 1) hPanel → **Seguridad → Imunify360** → revisar cuarentena/lista blanca y liberar;
  2) esperar el escaneo automático; 3) si urge, incrustar el flujo en un archivo ya permitido.
- **Estado actual: RESUELTO.** Los módulos están en producción. ~~El tablón comunitario dejó de depender
  de un script de migración que el antivirus bloqueaba: la propia página **auto-instala** su tabla y su
  columna cuando entra un **admin**.~~
  🗑️ **RETIRADO (2026-09-13, orden del jefe):** el **chat comunitario se quitó del sitio** (su página, su
  API, su banda, su botón flotante y su migración se **borraron** del hosting y de `deploy`), así que hoy
  no queda ningún tablón que auto-instalar. Lo que **sí sigue vigente** es el **patrón de
  auto-instalación defensiva** que nació aquí: lo usan banners, los prompts de productos y el módulo de
  empleos.

## 8) ERROR CONOCIDO: `ALTER … AFTER plan_hasta` → HTTP 500

- La columna `directorio_usuarios.plan_hasta` **no existe** en producción (la migración de tablones
  nunca corrió). Cualquier `ALTER` encadenado a `plan_hasta` revienta con **500**.
- Síntoma real: un `POST` devolvía **500 vacío** en el ~~tablón comunitario~~ (🗑️ módulo **retirado** el
  2026-09-13; el caso queda como ejemplo clásico); se arregló quitando el
  `AFTER plan_hasta` (ahora devuelve **400 JSON**, que es lo correcto).
- **Regla:** nunca encadenar un `ALTER` a `plan_hasta`; y ante un **500 vacío** en un `POST`,
  sospechar primero de esto.

## 9) DESFASE LOCAL ↔ HOSTING (DRIFT) Y TRABAJO EN PARALELO

- **🔓 El hosting es de uso exclusivo de la IA (2026-09-12): NO se compara nada y NO se respalda nada.**
  Se edita en local y se sube (Regla de Oro n.º 3). Todo lo que sigue en esta sección es **histórico**
  (sesiones del 09-09/09-10, cuando el hosting se tocaba a mano y existía el riesgo de divergencia).
  **Hoy ese riesgo no existe:** si el hosting tuviera algo distinto, lo subió una sesión anterior de la IA.
- **(Histórico) Trabajo local que podía estar sin desplegar:**
  - El **rediseño de la ficha** a dos columnas.
  - Diferencias conocidas en `buscar.php`, `categoria.php`, `negocio.php` y `superadmin.php`.
  - ⚠️ El **módulo de Estadísticas SÍ está desplegado y midiendo** (verificado en vivo el 2026-09-10).
    No tratarlo como pendiente.
- Ejecutar `subir_deploy.py` (que sube TODO) publicaría cualquier trabajo local sin desplegar.
  **Subir archivo por archivo.**
- **Regla de trabajo en paralelo:** dos sesiones de agente pueden estar sobre la **misma** carpeta →
  **re-leer el archivo antes de cada edición** e insertar cambios en bloques pequeños. (Esto sí sigue
  vigente: no es una comparación con el hosting, es no pisar el trabajo de otra sesión.)

## 10) TRAMPAS DE POWERSHELL EN LA PC DEL JEFE

| Trampa | Qué hacer |
|--------|-----------|
| La ejecución de `.ps1` está **deshabilitada** (`& script.ps1` falla con *"la ejecución de scripts está deshabilitada"*) | `Invoke-Expression (Get-Content 'ruta.ps1' -Raw)` |
| **`pwsh` no está en el PATH** de los procesos hijos | No usar `pwsh -File …`; usar PowerShell 5.1 |
| `curl.exe -d '{"json":"…"}'` con comillas simples **rompe el JSON** (*"Invalid input JSON"*) | `Invoke-RestMethod` + `ConvertTo-Json` (con `-Depth` si hay objetos anidados) |
| `Add-Content` / `Out-File` **sin `-Encoding UTF8` dañan los emojis** de los `.md` | Editar con las herramientas de archivo del agente, no con `Add-Content` |
| Python multilínea dentro de `python -c "…"` rompe el escapado (*SyntaxError / invalid escape sequence*) | Escribir el script en un `.py` y ejecutarlo |
| `Invoke-WebRequest -MaximumRedirection 0` falla con *"No se puede indizar en una matriz nula"* | Para ver redirecciones y códigos, usar Python |
| Los emojis salen como `�` **solo en la consola** | Es la consola, no el archivo |
| Rastreos largos desde el evaluador del navegador **cortan las promesas a ~100 ms** | Hacerlos por HTTP con Python |
| Este PowerShell es **5.1**: `Invoke-WebRequest` **no** tiene `-SkipHttpErrorCheck` | Usar `curl.exe -s -o NUL -w "%{http_code}" <url>` |
| **`php` no está en el PATH de Windows** | Llamarlo por ruta completa: `& C:\xampp\php\php.exe -l deploy\archivo.php` |

⚠️ **Pruebas de páginas con login:** abrir una **pestaña nueva** al mismo dominio **reutiliza las cookies
del jefe** (sirve para ver el panel como él). **NO iniciar sesión como `admin@dechimbote.com`**: eso
**saca al jefe de su sesión**.

## 11) TRAMPAS DE FTP

### 11.1 🔴 LA TRAMPA GORDA (2026-09-14): EL FTP ATERRIZA EN UNA COPIA VIEJA, NO EN LA WEB

> **Lo que pasó:** al recrear la cuenta FTP (`u196269909.chimboteftp`, y **otra vez** con la cuenta nueva
> del dominio nuevo `u196269909.dechimboteftp`, el 2026-09-15), el servidor deja al usuario
> en **`/public_html`**. Esa carpeta **NO es la web**: es una **COPIA VIEJA Y COMPLETA del sitio
> anidada DENTRO de la raíz viva** (43 archivos: `index.php` de 10 499 bytes, `chat_comunidad.php`,
> `tablones.php`, `migrar_tablones.php`… cosas que ya no existen en el sitio). La raíz que sirve el
> sitio es **`/`** (164 archivos, ahí vive `assets/css/carrito.css`).
>
> **Comprobado otra vez el 2026-09-15 con la cuenta nueva** (`__mudanza_sonda.php`, una sonda que imprime
> `__DIR__`, `DOCUMENT_ROOT` y la marca): la web viva de `dechimbote.com` es
> **`/home/u196269909/domains/dechimbote.com/public_html`**, el FTP entra en `/public_html` (la copia
> vieja: `SIZE index.php` = 10 499 bytes) y **su raíz `/` es la web viva** (`SIZE assets/css/carrito.css`
> = 16 967 bytes). **La trampa es idéntica con las dos cuentas.**
>
> **Síntoma que engaña:** `__subir_uno.py` decía **«SUBIDO negocio.php 47873 bytes»** sin un solo
> error, el archivo **estaba** por FTP (`RETR` devolvía exactamente esos bytes, md5 idéntico al
> local)… y **la web seguía mostrando el código viejo**. Parecía **cuarentena del antivirus**
> (GUIA §7) y no lo era: **era la carpeta equivocada**. La copia anidada **sí es visible por HTTP**
> (`https://dechimbote.com/public_html/prueba-20260909.txt` → 200), lo que confunde todavía más.
>
> **La prueba que lo resuelve en 10 segundos:** subir un **archivo estático** (un `.txt`, que no pasa
> por ningún antivirus) y pedirlo por HTTP; y comparar un **estático que sirve la web**
> (`assets/css/carrito.css`) con el mismo archivo leído por FTP en `/` y en `/public_html`.
> El que coincide **por bytes** es la raíz viva. Hoy: **`/`**.

- ✅ **LA RAÍZ VIVA ES `/`.** Los scripts `__subir_uno.py` y `__sonda_run.py` ya hacen **`cwd('/')`** y
  **comprueban la marca** antes de subir (si no ven `assets/css/carrito.css`, **se detienen** en vez de
  escribir en el vacío). **No cambiar eso.**
- 🧭 **Comprobación manual rápida:** por FTP, `LIST assets/css/carrito.css` tiene que responder. Si da
  *«No such file or directory»*, estás en la copia vieja.
- ⚠️ **La copia vieja se queda ahí** (no es nuestra y el jefe decide si se borra): lo que **nunca** hay
  que hacer es **subir a `public_html/`** ni editar sus archivos. Si alguna sesión antigua dejó algo
  suyo ahí (p. ej. `__empleo_aceros.php`), es basura: **no se copia «por si acaso»**.
- ⚠️ **No confundir con la cuarentena de verdad (§7):** aquella era el **antivirus** bloqueando un PHP
  **nuevo**; esta es **subir al directorio equivocado**. Ante un «subí y no cambió», **primero**
  comprobar la carpeta, **después** sospechar del antivirus.

- 📌 **Lo que NO cambió con la mudanza (y que nadie tiene que «arreglar»):** los **internos técnicos**
  siguen igual a propósito porque **romperían el sitio**: la base de datos `u196269909_CHIMBOTEALDIA`, el
  usuario `u196269909_ALDIACHIMBOTE`, la cookie de sesión `CHIMBOTE_SID` y la carpeta de fotos. Lo que
  cambió es **lo visible**: el dominio, la marca (**`DeChimbote.com`**) y los correos internos de las
  cuentas (`<numero>@dechimbote.com`).

### 11.2 LA MUDANZA DE DOMINIO (2026-09-15): QUÉ SE CAMBIÓ Y QUÉ FALTA

> El jefe mudó el sitio de **`chimbote.xyz`** a **`dechimbote.com`** (la dirección oficial es **sin www**;
> `www` redirige con 301). Se hizo un **barrido completo**: el dominio viejo no debe aparecer **en ninguna
> parte** (código, base de datos, guías, scripts ni datos guardados).

| Qué | Dónde quedó |
|-----|-------------|
| Dominio y marca | `SITE_URL` = `https://dechimbote.com` · `SITE_NAME` = **`DeChimbote.com`** (`deploy/config.php`) |
| URL vieja en el código | **0 apariciones** (se cambió todo: enlaces, correos internos, rutas del servidor y la marca visible) |
| .htaccess | fuerza **HTTPS** y **`www` → sin www** (la regla del `www` ya apunta a `dechimbote.com`) |
| Base de datos | las descripciones de las tiendas, las noticias y los correos internos de las cuentas se migraron con una sonda |
| Acceso FTP | cuenta nueva `u196269909.dechimboteftp` (§2) |
| Cron del monitoreo | ⚠️ **HAY QUE REVISARLO EN hPanel — lo hace el jefe:** después de la mudanza **dejó de disparar** (el 2026-09-15 la última ejecución quedó en **11:00** con **140** ejecuciones, y a las **12:13** seguía igual). La ruta buena es `/usr/bin/php /home/u196269909/domains/dechimbote.com/public_html/cron/monitoreo_sistema.php` (tipo **Personalizado**, `0 * * * *`). Se comprueba con `cron/monitoreo_sistema.php?k=ChimboteCron2026%23Jimmy&estado=1`: si «Ejecuciones» **sube cada hora**, funciona |
| Telegram | el **webhook** del bot se volvió a registrar contra `dechimbote.com` (si el bot deja de responder comandos, es esto) |
| Google (entrar con Google) | El **código está bien y está desplegado**: el 2026-09-15 se comprobó **por HTTP** que el sitio en vivo ya manda el dominio nuevo (`curl.exe -s -D - -o NUL "https://dechimbote.com/google_login.php"` → `redirect_uri=https%3A%2F%2Fdechimbote.com%2Fgoogle_callback.php`). ⛔ **FALTA UN PASO Y SOLO ESE ROMPE EL BOTÓN: la URI no está autorizada en Google Cloud Console**, así que Google contesta **`Error 400: redirect_uri_mismatch`** («Acceso bloqueado: la solicitud de esta app no es válida»). El cliente OAuth es del proyecto **`bot-marketplace-492512`** (ID `240770495440-…`, cliente `chimbote-web`) y todavía tiene la URI **vieja** de `chimbote.xyz`. **Lo hace el jefe** (pasos exactos: §7 de `DATOS_DE_ACCESO_Y_DOMINIO.md`). ⚠️ **No hay arreglo por código**: Google exige que el `redirect_uri` coincida **exacto** con uno autorizado (mismo esquema `https`, **sin `www`**, **sin barra final**) |

⚠️ **La copia vieja anidada `/public_html/` sigue ahí** (se ve por HTTP en `dechimbote.com/public_html/…`):
es de antes, no es nuestra y **el jefe decide si se borra** (GUIA_OPINIONES_ANONIMAS.md §11 lo tiene anotado
como pendiente). **Nunca** se le hacen cambios.

### 11.3 Otras trampas de FTP

- **Los scripts suben con rutas relativas** desde la raíz viva (`__subir_uno.py` hace `cwd('/')`).
  - **Bug histórico:** con rutas **absolutas** (`STOR /archivo`), el script decía *"OK 122 archivos"*
    pero la web **no cambiaba**: los archivos caían en otra carpeta. El `pwd` inicial engañaba.
  - **Comprobación:** `LIST` + `cwd` por FTP para ver en qué carpeta se está subiendo de verdad.
- **`SIZE` falla con "not allowed in ASCII mode"**: ejecutar `ftp.voidcmd('TYPE I')` **antes** de
  consultar tamaños. Ese error **no** significa que el archivo no exista.
- **La conexión se corta a los 60 s de inactividad** (*421 Idle timeout*): si una sonda tarda mucho en
  responder, **reconectar** antes de borrar el archivo temporal.

## 12) TRAMPAS DE PHP / SERVIDOR / BASE DE DATOS

- **La BD es `localhost`**: no se llega desde fuera. Todo lo que toque la BD debe ser un script **en el
  servidor** (subirlo, usarlo, **borrarlo**).
- **`display_errors` está en `0`**: un error fatal **no se ve**, y un `try/catch` puede esconder un fallo
  durante semanas (así estuvo **muerto** el enganche de negocio nuevo de HubSpot). Envolver en
  `try/catch` **y devolver mensajes propios**.
- **El PHP de línea de comandos** (el del cron) tiene `shell_exec`, `exec` y `system`
  **deshabilitados**; el PHP de la web no. Por eso el monitor mide el tamaño de la carpeta con PHP puro
  cuando corre por cron.
- **El disco que reporta Hostinger (21 TB) es el del servidor compartido**, no el de la cuenta: hay que
  vigilar aparte el tamaño real de `public_html`.
- **Permisos de la BD:** el usuario de la web **solo manda dentro de su propia base**. `CREATE USER`
  → error **1227**; `GRANT` → **1142**; `FLUSH PRIVILEGES` → **1227**; `SHOW GRANTS` → **1044**.
  Crear usuarios y repartir permisos es cosa de **hPanel o soporte**.
  ⚠️ **Nunca** tocar los permisos de `u196269909_ALDIACHIMBOTE`: **romperías la web**.
- **Scripts temporales con claves dentro**: se suben, se usan y se **borran** del servidor (verificar
  que dan 404). Nunca dejarlos en `deploy`.
- Horas: el MySQL va en **UTC**. Para mostrar hora de Perú: `CONVERT_TZ(x,'UTC','America/Lima')`.

## 13) SCRIPTS DE DESPLIEGUE Y VERIFICACIÓN (EN `D:\RELAX`)

> 🔓 Desde el **2026-09-12** los scripts que **respaldan el vivo** o **comparan local ↔ hosting (md5)**
> **no se usan**: quedan como archivo histórico (Regla de Oro n.º 3). Los de la tabla van marcados.

| Script | Qué hace |
|--------|----------|
| `__subir_uno.py <ruta relativa>` | ✅ **El comando estándar**: sube **un** archivo de `deploy`. Hace **`cwd('/')`** y **comprueba la marca** `assets/css/carrito.css` antes de subir (§11.1) |
| `__sonda_run.py <archivo.php> <clave> [go]` | ✅ Sube una **sonda** a la raíz viva, la ejecuta por HTTP con su clave y **la borra**. También comprueba la marca (§11.1) |
| `__bajar_uno.py <ruta>` | Baja un archivo del hosting y lo compara con el local (tamaño + md5) |
| `__comparar_vivos.py` | Compara los archivos publicados (local ↔ hosting) y avisa si alguno quedó distinto |
| `__radar_despliegue.py` | Mira si algún archivo de `deploy` **falta** en la raíz viva (trabajo sin desplegar) |
| **`espejo_vivo.py [--sincronizar]`** | 🕰️ **HISTÓRICO — no usar.** Bajaba el sitio vivo a local con manifiesto md5 (§18) |
| `espejo_verificar_deploy.py [--sincronizar]` | 🕰️ **HISTÓRICO — no usar.** Comparaba `deploy` contra el vivo **por md5** |
| `espejo_bajar_carpeta.py <carpeta> [hilos]` | 🕰️ Histórico: bajaba una carpeta pesada del hosting (p. ej. `fichas_tiendas`, `fotos`) |
| `espejo_bajar_bd.py` | Baja los dumps de la BD de `__backups/` a `_BASE_DE_DATOS\` — **sirve para LEER datos** (p. ej. sacar los ids reales de los rubros), no para desplegar |
| `__bajar_vivos_boton_cerca.py` | 🕰️ **HISTÓRICO — no usar** (bajaba los vivos y comparaba md5) |
| `__sa_bajar_vivo.py` | 🕰️ **HISTÓRICO — no usar** (comparaba md5 de `superadmin.php`) |
| `__deploy_btn_tiendas_cerca.py` | 🕰️ Histórico de ese módulo (respaldaba y subía) |
| `__verificar_btn_tiendas_cerca.py` | Verificación **solo lectura** por HTTP (debe decir **TODO OK**) |
| `__deploy_cerca_mi.py` · `__deploy_pagina_cerca_mi.py` · `__deploy_paneles_cerca.py` | 🕰️ Despliegues puntuales de ese módulo (con respaldo) |
| `subir_deploy.py` | Sube **todo** `deploy/` (122 archivos) — ⚠️ cautela (§9) y hoy apunta a un host que no resuelve |
| `verificar_y_subir.py` · `preparar_deploy.py` · `deploy_files.py` | 🚫 **NO USAR** (ver §4: el segundo **borra** la carpeta `deploy`) |

- ✅ **Lo que sí se usa hoy:** `__subir_uno.py <ruta>` para subir y **`curl.exe`** (o el navegador en
  pestaña nueva) para verificar por HTTP.
- Los `__deploy_*.py` (no `.ps1`) fueron el patrón de despliegue con respaldo: ahora son **modelo
  histórico**. Muchos `__*.py`/`__*.mjs` de la raíz son **herramientas de una sola sesión**.

## 14) MIGRACIONES E INSTALADORES (SE EJECUTAN UNA VEZ)

Los `migrar_*.php` y los `*_instalar.php` crean tablas, columnas o datos iniciales. **Se ejecutan una
vez y se borran del servidor** (comprobar luego que dan 404).

- **Patrón con clave:** `https://dechimbote.com/migrar_<modulo>.php?key=<CLAVE>` → el script se
  autodestruye al terminar. La clave de cada migrador está **dentro del propio archivo** en `deploy`
  (no se copia a las guías). Ejemplo ya consumido: `migrar_estadisticas.php` **hoy da 404** porque ya se
  ejecutó y se borró.
- **Vía recomendada cuando existe:** hacerlo desde el propio panel
  (ej. **Súper Admin → 📈 Estadísticas → "⚙️ Crear tablas de estadísticas"**), sin subir archivos.
- **Vía manual:** pegar el SQL en phpMyAdmin (hPanel).
- ⚠️ **Nunca encadenar un `ALTER` a `plan_hasta`** (§8) y **nunca** dejar en `deploy` un script temporal
  con credenciales dentro.

## 15) CÓMO REVERTIR UN CAMBIO

> 🔓 **Desde el 2026-09-12 ya NO se hacen respaldos del vivo.** Para revertir un cambio **se revierte el
> código local** (`D:\RELAX\deploy`) — que es la verdad — y se sube. Las carpetas `backup\` /
> `_backup_*` / `_vivos_*` que existan son **archivo histórico** de sesiones viejas: se pueden consultar
> como referencia de cómo estaba un archivo aquel día, pero **no son el camino** para deshacer algo.

1. **Revertir el archivo en local** (`D:\RELAX\deploy`): quitar el bloque que se añadió o reponer la
   versión anterior del archivo si la sesión la guardó.
2. **`php -l`** del archivo.
3. **Subirlo** con `python __subir_uno.py <ruta relativa>`.
4. **Verificar por HTTP** que el sitio responde (nunca 500).
5. Ejemplo con detalle: `GUIA_CERCA_DE_MI_Y_GEOBUSQUEDA.md` §14 (botón "Ver tiendas cerca").
   Rollback de HubSpot: `GUIA_HUBSPOT_CRM.md` §15.

## 16) EL CRON DEL MONITOREO (hPanel)

- **Ruta:** hPanel → Avanzado → **Cron Jobs** · tipo **Personalizado**.
- **Comando:** `0 * * * *` →
  `/usr/bin/php /home/u196269909/domains/dechimbote.com/public_html/cron/monitoreo_sistema.php`
- **Error conocido:** con el tipo **PHP** falla con
  `timeout: failed to run command '…monitoreo_sistema.php': Permission denied` (el archivo es 644, no
  ejecutable y sin intérprete). Por eso va como **Personalizado** con `/usr/bin/php`.
- El estado del monitor se guarda **fuera de la web**:
  `/home/u196269909/domains/dechimbote.com/.monitoreo_chimbote.json`.
- Comprobación manual desde el navegador:
  `…/cron/monitoreo_sistema.php?k=<CLAVE>&estado=1` (con `&solo-mostrar=1` para ver sin
  enviar y `&probar=1` para una alerta de prueba).

## 17) HERRAMIENTAS EXTERNAS: DÓNDE CORREN

- **Metabase (BI) NO va en el hosting: corre en la PC del jefe** → `http://localhost:3000`, v0.53.4.1,
  con `metabase.jar` + **JRE 21 portátil** (`D:\RELAX\metabase\jre21`; no hay Docker ni Java del sistema).
  **Nunca exponer el puerto 3000 a internet.** Detalle: `GUIA_METABASE_BI.md`.
- **HubSpot:** es un servicio en la nube; el sitio le envía webhooks. Detalle: `GUIA_HUBSPOT_CRM.md`.
- **Telegram:** el webhook vive en `api/telegram_bot.php` (con token secreto). Detalle:
  `GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md`.
- **El arnés (la aplicación del agente) NO va en el hosting: corre en la PC del jefe** →
  `http://127.0.0.1:3080`, con todo su estado en `C:\Users\Usuario\.dsh\`
  (`profiles\` = el perfil del arnés con sus plugins, `plugins\` = los plugins externos,
  `sessions\` = el historial de las conversaciones). **Al arnés se le puede agregar un plugin para que
  avise con un sonido cuando termina una tarea** (2026-09-19: `dsh-sounds`). Detalle y receta:
  `GUIA_ARNES_SONIDOS_Y_PLUGINS.md`.

## 18) ESPEJO DEL SITIO EN LOCAL — 🕰️ HERRAMIENTA HISTÓRICA (YA NO SE USA)

> 🔓 **2026-09-12:** con el hosting en uso exclusivo de la IA, **este espejo dejó de ser parte del flujo**
> (era un respaldo del sitio + una comparación local ↔ hosting, justo lo que el jefe mandó eliminar).
> Se conserva documentado por si algún día se quiere **bajar una carpeta concreta** (p. ej. `fotos/`),
> que sigue siendo útil como **descarga puntual**, nunca como paso previo a un despliegue.

**Los comandos (histórico):**

```text
python espejo_vivo.py                 # baja el respaldo y AVISA de las diferencias (no toca deploy)
python espejo_vivo.py --sincronizar   # además reemplaza en deploy lo que difiera del vivo
```

**Qué baja:** los archivos de la raíz + `api/`, `assets/`, `includes/`, `cron/`, `caminante/`, `img/`,
`galeria/`, `sms/`, `pyhub/` → a `_espejo_vivo_sitio_<fecha>\`, con `_MANIFIESTO_ESPEJO.json` (tamaño +
**md5** de cada archivo) y un `_LEEME_NO_DESPLEGAR.txt`.

**Reglas del espejo (histórico, aprendidas el 2026-09-10):**

1. **Nunca borra archivos de `deploy`.** Un archivo que existe en local y **no** en el hosting puede ser
   trabajo pendiente **irrecuperable**: así se salvó el módulo **Tablones B2B**
   (`tablones.php`, `tablon.php`, `api/tablon_*.php`), que **no está en el hosting** (404) y cuya única
   copia vivía en `deploy`. El espejo solo informa de esos archivos.
   🗑️ **RETIRADO (2026-09-13, orden del jefe):** ese módulo **se quitó del sitio** y sus archivos ya **no**
   están en `deploy` (quedaron guardados en `D:\RELAX\_ARCHIVO_RETIRADO_CHAT_Y_TABLONES_2026-09-13\`);
   el ejemplo sigue explicando **por qué** el espejo nunca borra nada.
2. **⚠️ NUNCA usar `--sincronizar` sin leer esto:** reemplaza `deploy` con lo del hosting y **puede pisar
   trabajo local sin desplegar**. Con la regla del 2026-09-12 (**el local es la verdad**) sincronizar
   desde el hosting **va en contra del flujo**: no hacerlo.
3. **Cuidado con las sesiones en paralelo:** si otra sesión está desplegando mientras bajas, el espejo se
   queda a medias y puede traer una versión **más vieja** que la local. (Otra razón para no usarlo.)
4. **Peso:** el código son ~521 archivos / ~50 MB (≈7 min por FTP). También entran `fichas_tiendas/`
   (1 531 archivos, 2,5 MB) con `python espejo_bajar_carpeta.py fichas_tiendas 4` (4 conexiones en
   paralelo; multiplica la velocidad ×4). **No** entran las **fotos de los usuarios** (`fotos/`,
   5 299 archivos, **481 MB**): se bajan igual con el mismo script el día que haga falta
   (`python espejo_bajar_carpeta.py fotos 4`).

**Base de datos (esto sí es un respaldo, y lo hace el hosting solo):** `cron/tasks/backup.php`
(escribe `__backups/backup_<fecha>.sql.gz`, rotación de 14) y se dispara con:

```text
https://dechimbote.com/cron_runner.php?tarea=backup&k=<CLAVE_DE_CRON_RUNNER>
```

Los dumps se pueden bajar a `_espejo_vivo_sitio_<fecha>\_BASE_DE_DATOS\` con `python espejo_bajar_bd.py`
(útil para **leer datos** viejos, p. ej. los ids reales de los rubros), o leerse en vivo con una **sonda**
(§3.1). **Estado el 2026-09-10:** el runner estaba **roto** (daba **HTTP 500** porque `cron_runner.php`
usaba `$pdo` y `config.php` solo define la función `db()`): el último dump era del **2026-09-03**.
Corregido con `$pdo = db();` y ya genera dumps (2 311 KB). ⚠️ **Pendiente del jefe:** añadir en hPanel el
Cron Job que lo ejecute solo (ver §16).


---

_Última revisión: **2026-09-12** (🔓 **el hosting es de uso exclusivo de la IA: se eliminan el respaldo
del archivo vivo y toda comparación local ↔ hosting** — órdenes del jefe del 2026-09-11 y 2026-09-12; el
despliegue queda en 3 pasos: `php -l` → subir solo lo modificado → verificar por HTTP)._
_2026-09-10 (creación: consolida las lecciones de despliegue que estaban repartidas en las guías de
Telegram, Tablones, Banners, Cerca de mí, HubSpot y Metabase)._
_2026-09-10 (SUPERADO): §3 aclaraba el **alcance exacto del md5** (2-5 archivos que se van a pisar,
unos segundos) y enumeraba lo que **NO** se hacía antes de cada tarea (bajar el sitio completo o
verificar todo `deploy`). Desde el 2026-09-12 **el md5 y el respaldo del vivo ya no existen en el flujo**._
