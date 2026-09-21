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


# GUÍA — CÓMO SE TRABAJA EN ESTE PROYECTO · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** al **empezar** cualquier sesión (junto con `REGLAS_DE_ORO_PROYECTO.md` y
> `GUIA_MAESTRA_CHIMBOTE_XYZ.md`).
> **Para qué sirve:** que ninguna sesión futura repita errores, repita preguntas ya respondidas ni pierda
> tiempo **descargando el sitio entero** para comprobar un archivo.
> **Estado:** ✅ vigente · **Última revisión: 2026-09-14** (🚫 **fin de las crónicas por sesión y de las
> guías por cliente** — orden del jefe; ver §2 · 🔓 **el hosting es de uso exclusivo de la IA: sin
> respaldos del vivo y sin comparaciones local ↔ hosting**).
> ⚠️ **El nombre del archivo es histórico** (antes decía «cronica»): hoy esta guía es el **flujo de trabajo**,
> no la plantilla de ninguna crónica.

---

## 0) LO ESENCIAL EN 30 SEGUNDOS

1. **La única documentación es LA GUÍA DEL MÓDULO** (el conocimiento vivo, una por tema; índice en
   `GUIA_MAESTRA_CHIMBOTE_XYZ.md`). 🚫 **No hay crónicas por sesión ni guías por cliente** (orden del
   jefe, 2026-09-14, §2): publicar una tienda **no crea ningún archivo nuevo**.
2. 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe **nunca** sube,
   edita, renombra ni borra nada a mano. Por eso **no se respalda el archivo vivo** y **no se compara el
   local con el hosting** (ni md5, ni tamaños, ni bajadas de comprobación).
3. **El local ES la verdad:** se edita en `D:\RELAX\deploy` y se sube. El despliegue son **3 pasos**:
   `php -l` → subir solo lo cambiado → **verificar por HTTP**.
4. **Nunca bajes el sitio completo.** La verificación es **por HTTP** (~0,7 s por página).
5. **Antes de creer a una guía, comprueba el sitio.** En la sesión del 2026-09-10 dos guías decían
   "sin implementar" un módulo que **llevaba días en producción**.
6. **Otra sesión puede estar trabajando en la misma carpeta**: si un archivo "cambió desde que lo leí",
   **releerlo** antes de editar e insertar bloques pequeños.

---

## 1) EL FLUJO DE TRABAJO (SIEMPRE EL MISMO)

| # | Paso | Herramienta | Tiempo típico |
|---|------|-------------|---------------|
| 1 | Leer `REGLAS_DE_ORO_PROYECTO.md` + `GUIA_MAESTRA_CHIMBOTE_XYZ.md` + la guía del módulo | `read` | 1–2 min |
| 2 | Comprobar el **estado real** de lo que se va a tocar (HTTP en vivo, no suposiciones) | `curl.exe -s -o NUL -w "%{http_code}"` | segundos |
| 3 | ~~md5~~ · ~~respaldo del vivo~~ **ELIMINADOS (2026-09-11 y 2026-09-12)** — no hay paso previo | — | 0 s |
| 4 | Si otra sesión pudo tocar lo mismo: **releer** el archivo antes de editar | `read` | segundos |
| 5 | Editar **en `D:\RELAX\deploy`** | herramientas de archivo del agente | — |
| 6 | `php -l` del archivo | `C:\xampp\php\php.exe -l deploy\<ruta>` | 1 s |
| 7 | **Subir solo lo cambiado** (un archivo por vez) | `python __subir_uno.py <ruta relativa>` | ~5 s |
| 8 | Verificar por HTTP (+ navegador en **pestaña nueva** si es visual) | `curl.exe`, Browser Control | segundos |
| 9 | **Actualizar LA GUÍA DEL MÓDULO** (lo nuevo, las trampas, los comandos y el registro de lo hecho) | `edit` | 1–3 min |

---

## 2) LA DOCUMENTACIÓN: SOLO LA GUÍA DEL MÓDULO (no hay crónicas)

> **Orden del jefe (2026-09-14, textual):** *«veo que creas guías por cada cliente y eso no es necesario;
> la única guía que sirve es "publicando a los amigos de Jimmy". No tienes por qué documentar cada sesión…
> Yo nunca pedí que cada sesión sea documentada, porque son más de 1500 negocios: en teoría son 1500
> sesiones, serían demasiadas guías, nadie lo va a leer, se convertiría en basura.»*
> **Su orden original (2026-09-10):** *«todas las veces que termines algo, documéntalo en **su guía
> respectiva**; y si no existe la guía, la creas.»* → **esa «guía respectiva» es LA GUÍA DEL MÓDULO**,
> nunca una crónica por sesión ni por cliente (eso lo añadieron los agentes, no el jefe).

- 🚫 **NO se crean crónicas** (`GUIA_SESION_AAAA-MM-DD_TEMA.md`) **ni guías por cliente.** Publicar una
  tienda **no genera ningún archivo nuevo**: se anota **una fila** en la tabla de registro de la guía del
  módulo (p. ej. §10 de `publicando a los amigos de jimmy.md`).
- ✅ **Lo que hay que saber se escribe en la guía del módulo**: lo nuevo, las trampas con su solución, los
  comandos exactos, los pendientes y el registro de lo hecho. **Si la guía del módulo no existe, se crea
  una** (una por tema, no una por cliente ni por sesión).
- 📜 **Las crónicas viejas** (`GUIA_SESION_*.md`, `_ARCHIVO_CRONICAS\`) son **historial**: no se leen, no se
  citan y no se les añade nada. **Lo que solo quede en el chat se pierde.**

### 2.1 LAS CUATRO LISTAS QUE VAN EN LA GUÍA DEL MÓDULO (pedido expreso del jefe, 2026-09-10)

> *"Documenta todos los cambios, los errores que encontramos, las soluciones que se dieron, las fricciones
> que ocurrieron y los pendientes, para que las próximas sesiones tengan de dónde continuar."*

**Cada vez que se termina una tarea** (no solo al cerrar la sesión), en la **guía del módulo**:

1. **Cambios** hechos (con evidencia medida: HTTP, píxeles, conteos).
2. **Errores** encontrados → **cómo se detectaron** → **cómo se arreglaron**.
3. **Fricciones** (lo que hizo perder tiempo o confundió **sin ser un error**: una guía desfasada, una
   herramienta reinventada, un dato mal leído) → cómo se destrabó.
4. **Pendientes**, cada uno con su **siguiente paso concreto**.

Formato mínimo de las listas 2 y 3 (una tabla las hace imposibles de olvidar):

| Fricción / error | Por qué pasó | Cómo se resolvió | Dónde quedó escrito |
|---|---|---|---|
| | | | |

### 2.2 Reglas de higiene de la guía del módulo

- **Números, no adjetivos:** "monocultivo 35 % → 0 %", no "quedó mucho mejor".
- **Nada de datos obsoletos "por si acaso"**: si algo ya no es cierto, se borra (Regla de Oro n.º 7).
- **Lo que solo quede en el chat se pierde**: lo reutilizable **se escribe en la guía del módulo**
  (y **una fila** en su tabla de registro si es una publicación).
- **Nada de claves ni contraseñas** dentro de las guías (las claves viven en los archivos de `deploy`).

---

## 3) CÓMO VERIFICAR EN VIVO **SIN DESCARGAR EL SITIO**

> 🔓 **2026-09-12 (orden del jefe):** el **respaldo del archivo vivo** y la **comparación local ↔ hosting**
> ya **no existen** en el flujo (Regla de Oro n.º 3). Verificar es **siempre por HTTP** (y con el navegador
> en pestaña nueva si es visual). No se baja nada para "comprobar" ni para "respaldar".

| Qué quiero saber | Herramienta | Coste real |
|------------------|-------------|------------|
| ~~¿El archivo local es el mismo que el vivo?~~ **Pregunta eliminada (2026-09-11): el local es la verdad** | — | 0 s |
| ~~¿Tengo copia del vivo antes de pisarlo?~~ **Pregunta eliminada (2026-09-12): no se respalda el vivo** | — | 0 s |
| ¿La página responde y trae lo nuevo? | `curl.exe -s -o NUL -w "%{http_code}" "https://dechimbote.com/neg/<slug>?cb=<random>"` | **~0,7 s** |
| ¿Hay errores PHP en el HTML? | buscar `Fatal error` / `Uncaught` / `Warning:` en la respuesta | 0 s |
| ¿Cómo se ve de verdad? (visual) | navegador en **pestaña nueva** (`active:false`) + captura | segundos |
| ¿El motor de recomendaciones reparte bien? | `python __reco_diversidad.py` (60 fichas) | **1–3 min** (medición opcional) |
| ¿Todos los rubros están cubiertos? | `python __reco_cobertura.py` (61 fichas) | 1–3 min (medición opcional) |
| Leer **datos** de producción (columnas, conteos) sin phpMyAdmin | **sonda** temporal por FTP (`GUIA_DESPLIEGUE_Y_ENTORNO.md` §3.1) | ~30 s |

**Regla operativa (2026-09-12):** *una duda de comportamiento → HTTP; una duda de aspecto → navegador en
pestaña nueva; una duda de datos → sonda de solo lectura. Nada de bajar el sitio ni de comparar archivos.*

### 3.1 POR QUÉ SE ELIMINARON EL md5 Y EL RESPALDO DEL VIVO (órdenes del jefe: 2026-09-11 y 2026-09-12)

- **La regla vieja (md5):** comparar por md5 los 2-5 archivos que se iban a pisar. Nació de un incidente
  real: subir un local viejo encima del vivo **tumbó el sitio entero con error 500**.
- **La regla vieja (respaldo):** antes de subir, bajar el archivo vivo a `backup\<fecha>\` por si había que
  volver atrás. Fue el "seguro" sustituto del md5 durante el 2026-09-11.
- **Por qué se eliminaron las dos (2026-09-12):** el jefe **nunca toca el hosting** (no sube, no edita, no
  renombra nada a mano): **el hosting es de uso exclusivo de la IA**. No existe el riesgo de divergencia
  que justificaba las dos reglas, y las dos costaban tiempo en cada tarea.
- **Qué queda en su lugar:** **`php -l`** + **subir solo lo cambiado** + **verificar por HTTP**. Si un
  despliegue sale mal, se **corrige en local y se vuelve a subir** (el local es la verdad y el código de la
  sesión está en la crónica).
- **Lo que ya existe** (`backup\`, `_backup_*`, `_vivos_*`, `espejo_vivo.py`, `__reco_md5.py`, los
  `__*_comparar.py`) queda como **archivo histórico**: nadie tiene que correrlo.

---

## 4) TRAMPAS YA CONOCIDAS (NO VOLVER A PERDER TIEMPO EN ESTAS)

| Trampa | Qué pasó / qué hacer |
|--------|----------------------|
| **La consola de PowerShell no imprime acentos/emojis** | `UnicodeEncodeError: 'charmap' codec…` al imprimir rubros con tilde. Solución: `$env:PYTHONIOENCODING='utf-8'` y volcar la salida a un `.txt` con `Out-File -Encoding UTF8` para leerla con la herramienta de archivos. |
| **`Select-Object -First 1,` en PowerShell 5.1** | Error *"No se puede convertir System.Object[] a System.Int32"*. Solución: hacer un `Select-Object -First 1` por separado por cada elemento. |
| **Migradores: 403 vs 404** | Un `migrar_*.php` que **existe** responde **403** sin clave; uno ya ejecutado (autodestruido) responde **404**. Así se sabe si una migración **ya corrió**, sin tocar la BD. |
| **Los IDs de las categorías no se adivinan** | Salen del **respaldo de la BD** (`_espejo_vivo_sitio_*\ _BASE_DE_DATOS\backup_*.sql.gz`) con `python __reco_rubros.py rubros`. Los comentarios de los migradores viejos tenían ids mal etiquetados. |
| **Sesiones en paralelo en la misma carpeta** | Si al editar salta *"file changed since it was read"*: **releer** el archivo y aplicar cambios en bloques pequeños. Otra sesión puede estar desplegando a la vez. |
| **Las guías pueden estar desactualizadas** | Antes de implementar algo "pendiente", comprobar por HTTP si ya está en producción. En esta sesión, M1/M2 llevaban días vivos y las guías decían "sin implementar". |
| **Medir antes de arreglar** | El defecto (35 % de fichas con un solo rubro) **no se veía a ojo**: se descubrió midiendo 60 fichas. Sin medir, se "arregla" lo que no está roto. |
| **Respuesta TRUNCADA justo después de subir un PHP (2026-09-10)** | Segundos después de reemplazar `includes/footer.php` por FTP, la portada respondió **HTTP 200 pero cortada** (sin `</html>`, sin modal, sin los `<script>`) y el navegador mostró lo mismo. **No era el archivo** (hoy ya no se comprueba con md5: basta saber que era el correcto). A los ~30 s: **5/5 cargas completas**, luego **10/10** en la portada y **3/3** en 5 páginas. **Qué hacer:** si ves una página cortada recién desplegada, **volver a pedirla 2-3 veces con `?cb=aleatorio` ANTES de asumir que rompiste algo y antes de "revertir"**: es hosting, no tu código. |
| **Un botón "que falta" puede estar tapado** | La ✕ del modal de banners existía, tenía CSS y no funcionaba: el encabezado guinda (posicionado, después en el HTML) la pintaba **encima**. Se descubrió con **`document.elementsFromPoint(x, y)`**, no leyendo el código. Ante *"falta un botón / no funciona"*, medir **qué elemento recibe el clic** en esa coordenada. |
| **El 404 reportado NO siempre viene del enlace que el jefe señala (2026-09-10)** | El jefe decía *"el botón reclamar me reporta enlaces rotos"*: el botón de la ficha **respondía 200 y funcionaba**. Los 404 venían de **otras** URLs del mismo tema (`/reclamar` sin `.php`, `/reclamar?q=…&rubro=…`, del buscador de reclamos viejo). **Orden correcto:** (1) leer la bitácora `directorio_avisos_log` (`tipo='pagina_404'`, agrupada por `SUBSTRING_INDEX(resumen,'?',1)`), (2) probar esas rutas por HTTP, (3) recién entonces tocar código. Ver `GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md` §9.1. |
| **`estado='agrupado'` significa "hay más de lo que ves"** | El tope por hora (`AVISOS_TOPE_HORA_404` = **5/h**) esconde avisos en el Telegram, pero **todos** quedan en la bitácora. Nunca dimensionar un problema por lo que llegó al chat: contarlo en la tabla. En el caso del 404 de `/reclamar`, el chat enseñaba unos pocos y la tabla decía **96 visitas humanas**. |
| **Una URL sin `.php` NO existe si no hay regla** | `https://dechimbote.com/reclamar` daba 404 aunque `reclamar.php` existiera: el `.htaccess` no tenía `RewriteRule`. Antes de "arreglar" un enlace amigable, mirar las reglas del `.htaccess`; para agregar una, **subir el `.htaccess` al final** (un error de sintaxis ahí tumba el sitio entero, no una página). |
| **`id="buscador-fuzzy"` ya está tomado** | Ese id vive en la cabecera (`includes/header.php`) y `assets/js/buscador_fuzzy.js` engancha **cualquier** `[data-fuzzy]` y manda a `/neg/<slug>`. Si una página nueva necesita un buscador predictivo que lleve a OTRO destino (el reclamo, por ejemplo), hay que escribir su propio JS con otro id (así lo hace `reclamar.php`). |
| **Temporales con claves dentro de `deploy/`** | `deploy\__mb_ro.php` (de otra sesión) lleva una clave y una contraseña de BD en texto dentro de la carpeta **desde la que se despliega**. Hoy no está publicado (404 verificado), pero un `subir_deploy.py` lo publicaría. **Regla:** las sondas y cualquier archivo con claves se guardan **fuera de `deploy/`** (ej. `D:\RELAX\__sonda404.php`). |
| **`python` no imprime emojis en la consola** | Además del `UnicodeEncodeError` de acentos, los emojis (🏪) rompen la salida de los scripts. Solución usada: `sys.stdout.reconfigure(encoding='utf-8', errors='replace')` al inicio del script **y** volcar lo grande a un `.txt` UTF-8 para leerlo con la herramienta de archivos. |
| **El respaldo del vivo se pisó a sí mismo (2026-09-11)** | 🕰️ **HISTÓRICO — ya no aplica (2026-09-12: no se respaldan los vivos).** Una 2.ª pasada del script de despliegue volvió a "respaldar" los archivos **ya subidos** y borró el respaldo bueno. Se recuperó reconstruyendo el contenido anterior al revés y **cuadrando el tamaño exacto en bytes**. |
| **`browser_click` en una pestaña de fondo (2026-09-11)** | En una pestaña **no activa** el navegador **no aplica el scroll** (`scrollTo`/`scrollIntoView` dejan `scrollTop` en 0) y el clic cae donde no está el botón (`hitVerified: false`) — incluso puede acabar pulsando el enlace **✕ cancelar** de al lado, que **no guarda nada y no avisa**. Antes de dar un clic por bueno: **activar la pestaña**, comprobar con `document.elementFromPoint(x, y)` que el punto devuelve **ese mismo botón** (`el === boton`) y, después, **confirmar el efecto en la BD** (sonda), no en la pantalla. |
| **Un formulario puede enviarse "bien" y no guardar nada** | El POST redirige siempre (`Location: superadmin.php?seccion=…`) aunque **ninguna rama de acciones coincida**, y el panel **no pinta los `flash()`** (`flash_html()` no se llama). Por eso "volvió al listado sin error" **no** significa "guardó": hay que comprobarlo con una sonda (`SELECT`) o con la cifra que cambie (impresiones, clics). |

---

## 5) HERRAMIENTAS PERMANENTES DEL PROYECTO (REUTILIZABLES)

> 🔓 **2026-09-12:** las herramientas que **respaldan el vivo** o **comparan local ↔ hosting (md5/sha)**
> quedan marcadas 🕰️ **HISTÓRICAS — no se usan**. Lo vigente para desplegar es `__subir_uno.py` + `curl.exe`.

| Script (en `D:\RELAX`) | Para qué | Guía que lo documenta |
|------------------------|----------|-----------------------|
| `__subir_uno.py <ruta>` | ✅ **El comando estándar**: sube UN archivo de `deploy` | `GUIA_DESPLIEGUE_Y_ENTORNO.md` §13 |
| `__reco_md5.py <ruta…>` | 🕰️ **HISTÓRICO** (md5 eliminado el 2026-09-11): comparaba md5 local ↔ hosting | esta guía §3.1 |
| `__reco_diff_vivo.py <ruta…>` | 🕰️ **HISTÓRICO**: mostraba **solo las líneas** que diferían del vivo | esta guía §3 |
| `__reco_deploy.py` | 🕰️ Histórico: respaldaba el vivo + subía `includes/helpers.php` | `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` |
| `__reco_cobertura.py` | % de rubros/negocios que publican el carrusel de recomendaciones | idem |
| `__reco_diversidad.py` | Mide el "monocultivo" de rubros en las 10 tarjetas | idem |
| `__reco_rubros.py` | Lee **datos** del respaldo de la BD: rubros con su **id** y pares de afinidad | idem |
| `__carrito_3_prueba.js` | **Corre el motor del carrito en Node con un DOM de mentira**: 22 pruebas (carrito por tienda, mensaje de WhatsApp, URL de envío, vistos, topes). Segundos, sin tocar el sitio | `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` §8.5 |
| `__carrito_prueba_lead.php` | Prueba **en local, sin BD**: el enlace `wa.me/…?text=` y el resumen 🛒 del aviso | idem |
| `__carrito_1_probe.py` · `__carrito_2_ficha.py` | Tiendas vivas con productos+WhatsApp y el markup real de las 3 plantillas (solo lectura) | idem |
| `__deploy_carrito.py [--solo-comprobar]` | 🕰️ Histórico: respaldaba los vivos + subía los 8 archivos del carrito | idem |
| `__busq_1_comparar.py` · `__busq_8_desplegar.py` · `__busq_11_borrar_sonda.py` | 🕰️ **Módulo del buscador**: comparación md5, despliegue con guardas y borrado de la sonda | `GUIA_CERCA_DE_MI_Y_GEOBUSQUEDA.md` §13 |
| `__busq_4_sonda.py` | **Sonda de una página PHP**: la sube como `__sonda_*.php` y la prueba por HTTP **antes** de pisar el vivo (así se detectó un HTTP 500 que `php -l` no ve) | esta guía §3 y `GUIA_CERCA_DE_MI_Y_GEOBUSQUEDA.md` §11 |
| `__subir_archivos.py <ruta…>` | 🕰️ Histórico: subía varios archivos con "guardián" anti-otra-sesión (hoy se usa `__subir_uno.py`) | `GUIA_DESPLIEGUE_Y_ENTORNO.md` §13 |
| `__bn_1_comparar.py` | 🕰️ Histórico: comparaba md5 de los 6 archivos del modal de banners | `GUIA_PUBLICIDAD_Y_BANNERS.md` §8.2 |
| `espejo_vivo.py` | 🕰️ **HISTÓRICO — no usar**: bajaba el sitio completo a local (~7 min) | `GUIA_DESPLIEGUE_Y_ENTORNO.md` §18 |
| `__sonda404.py` (+ plantilla `__sonda404.php`) | ✅ **Lee datos de PRODUCCIÓN sin phpMyAdmin**: sube un PHP de solo lectura con clave, guarda el JSON y **borra la sonda del servidor**. Sirvió para descubrir las 96 visitas humanas al 404 de `/reclamar` | `GUIA_DESPLIEGUE_Y_ENTORNO.md` §3.1 · `GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md` §9.1 |
| `__sonda_tiendas.py` (+ `__sonda_tiendas.php`) | ✅ Sonda de solo lectura: columnas reales y **conteos** de las 1 593 tiendas (usada el 2026-09-12 para la vista previa del Súper Admin) | `GUIA_SUPERADMIN_PANEL.md` §4.2 |
| `__sonda_ban_busq.py` (+ `__sonda_ban_busq.php`) | **Sonda de solo lectura** que dio el dato exacto de los banners: columnas reales de `directorio_banners`, la fila del banner #16 y **cuántos negocios devuelve `buscar.php` para cada término candidato** (con su mismo SQL, sin gastar avisos de Telegram). Se usa también **después de guardar** para comprobar en la BD que el panel guardó lo que se creía | `GUIA_PUBLICIDAD_Y_BANNERS.md` §8.3 |
| `__deploy_ban_busq.py` | 🕰️ Histórico: respaldaba y subía los archivos de ese módulo | `GUIA_PUBLICIDAD_Y_BANNERS.md` §8.3 |
| `__verif_ban_busq.py` | Verificación posterior de ese módulo (tamaño + sha256 + HTTP) — comparar con el local | idem |
| `__restaurar_backup_antes.py` | 🕰️ Histórico: reconstruía un vivo cuando el respaldo se había pisado | `GUIA_DESPLIEGUE_Y_ENTORNO.md` §4 |
| `__reclamar_1_comparar.py` | 🕰️ Histórico: bajaba y comparaba los vivos del módulo de reclamos | `GUIA_RECLAMOS_DE_TIENDAS.md` |
| `__reclamar_3_pisadas.py` | 🕰️ Histórico: informe de "líneas del vivo que desaparecerían" | idem |
| `__reclamar_4_subir.py` · `__reclamar_5_htaccess.py` · `__reclamar_6_verificar.py` | 🕰️ **Despliegue en 2 etapas** (primero el PHP, el `.htaccess` al final) — el orden **sí** sigue siendo la lección útil | idem |
| `__reclamar_7_funcional.py` | Comprueba el **contenido**, no solo el código HTTP: resultados del buscador, el **texto exacto del mensaje de WhatsApp** (decodificado), el tamaño del botón y las URLs del sitemap | idem |
| `__reclamar_14_paginas_viejas.py` | Sondea las **páginas de la arquitectura vieja** que siguen en el hosting y escribe `__reclamar_paginas_viejas.txt` (hoy: **10 devuelven 500**) | idem |

> Los `__*.py`/`__*.mjs` de la raíz son herramientas; **no** se suben al hosting y **no** son parte del
> sitio. Los que resultan útiles se documentan aquí; los de una sola vez se dejan como modelo.

---

> **Guías base:** `REGLAS_DE_ORO_PROYECTO.md` (canónico) · `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (índice y estado
> real) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (editar, subir, verificar) · `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md`
> (módulo de recomendaciones cruzadas).

_Última revisión: **2026-09-14** (🚫 **orden del jefe: se acaban las crónicas por sesión y las guías por
cliente** — la única documentación es **la guía del módulo**, que ya recibe lo nuevo, las trampas, los
comandos y el registro de lo publicado; ver §2)._
_2026-09-12 (🔓 **orden del jefe: el hosting es de uso exclusivo de la IA** — se eliminan de toda la guía
el **respaldo del archivo vivo** y la **comparación local ↔ hosting**; el flujo queda en `php -l` → subir
solo lo cambiado → verificar por HTTP, y las herramientas de md5/respaldo/espejo pasan a 🕰️
**históricas**)._
_2026-09-10 (creación, a pedido del jefe: documentar todo el trabajo en **su guía respectiva** y no perder
tiempo descargando archivos; añadidas las herramientas de **sonda** del buscador: probar una página PHP
contra la base real **antes** de pisar el vivo)._

> 📜 **Nota sobre las crónicas viejas:** los `GUIA_SESION_*.md` que quedan en `D:\RELAX` y en
> `_ARCHIVO_CRONICAS\` son **historial** de la práctica que el jefe retiró el 2026-09-14: **no se leen, no
> se citan y no se continúa esa costumbre**. Lo reutilizable de ellas ya vive en las guías de módulo.
