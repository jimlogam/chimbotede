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


# GUÍA DE RECLAMOS DE TIENDAS — "Reclamar mi negocio" · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Qué es:** el camino por el que **el dueño de una tienda que YA está publicada en el directorio pide
> tomar el control de su ficha** para corregir nombre, dirección, WhatsApp, fotos y productos. Es el
> módulo que convierte **anónimos en dueños**: hoy el directorio tiene más de 1 500 fichas activas y casi
> ninguna con dueño.
> **Cuándo leer esta guía:** cuando el jefe diga **«reclamos»**, **«reclamar mi tienda»**, **«reclamar mi
> negocio»** (o pregunte por el **botón 🗝️ de la ficha**, por la **página 404** o por el **WhatsApp del
> administrador**). También antes de tocar `reclamar.php`, `reclamar_negocio.php`, `404.php`, el bloque
> `.reclama-caja` de `negocio.php` o la constante `ADMIN_WHATSAPP`.
> **Páginas y archivos:** `/reclamar` (`deploy/reclamar.php`) · `/reclamar_negocio.php?slug=<slug>`
> (`deploy/reclamar_negocio.php`) · la **404 propia** (`deploy/404.php`, la sirve `ErrorDocument 404
> /404.php`) · el **bloque 🗝️ de la ficha** (`deploy/negocio.php`, `.reclama-caja` + `.reclama-btn`) ·
> el **motor** (`deploy/includes/helpers.php`: `ADMIN_WHATSAPP` + 6 funciones de reclamos) · el
> **aviso por Telegram** (`deploy/includes/avisos.php`) · la **bandeja del administrador** (`deploy/superadmin.php`
> → `?seccion=reclamos`) · la **regla `^reclamar/?$`** en `deploy/.htaccess`.
> **Estado:** ✅ **EN PRODUCCIÓN** desde el **2026-09-10** (verificado por HTTP ese día:
> `/reclamar`, `/reclamar/`, `/reclamar?rubro=…`, `/reclamar?q=…&rubro=…`, `/reclamar_negocio.php?slug=…`
> y `/neg/…` con el botón grande). La base `directorio_reclamos` se creó con
> `migrar_reclamos_postulantes.php` (migración de un solo uso, **se autodestruye al terminar**).
> **Última revisión: 2026-09-14.**
> **De dónde salió esta guía:** se **rescató** de dos archivos que pasan al **archivo histórico** en esta
> misma sesión: la **§19 «RECLAMAR MI NEGOCIO: EL FLUJO COMPLETO (2026-09-10)»** de
> `GUIA_TABLONES_B2B_NEGOCIOS.md` (esa guía documenta un **módulo ya retirado** del sitio el 2026-09-13:
> su §19 era la única referencia viva de reclamos) y la crónica completa del módulo,
> `GUIA_SESION_2026-09-10_RECLAMAR_Y_404.md`. Todo lo de aquí se **verificó además en el código real**
> (`deploy/`, que es la verdad); lo que no se pudo confirmar va marcado como **«por confirmar»** (§15).
> ⚠️ Los comentarios del código todavía dicen «ver el §19 de la guía del proyecto»: **desde ahora la
> referencia de reclamos es ESTE archivo.**

---

## §1 LA IDEA EN UNA FRASE (y el problema que resolvió)

**Cualquier persona que encuentre su tienda publicada tiene que poder reclamarla sin crear una cuenta, sin
escribir un correo y sin caer en un enlace muerto.** El jefe lo pidió así (2026-09-10, textual):

> *"En la parte baja de los negocios hay un botón muy interesante que se llama reclamar negocio: me está
> reportando muchos enlaces rotos… Hazlo también un poco más grandecito porque es muy importante que los
> dueños de las tiendas reclamen su tienda: hay anónimos que llegan, encuentran su tienda, dicen «mi
> tienda» y quieren reclamarla; para eso está ese botón."*

**El problema, medido (no supuesto).** La bitácora `directorio_avisos_log` del **2026-09-10** tenía **858
avisos de 404 en el día**, de los cuales **96 visitas HUMANAS** (bots = 0) eran a `/reclamar` y
`/reclamar?q=…&rubro=…` — justo el patrón de enlaces del buscador de reclamos **viejo** (el `reclamar.php`
de la arquitectura anterior, que en el hosting devolvía **ERROR 500**, y la URL sin `.php` no tenía regla,
así que daba **404**). **El enlace que el jefe reparte y que Google indexó estaba muerto**: 96
oportunidades de reclamo perdidas en un día.

**La lección de la sesión (no repetir):** el botón de la ficha **no** estaba roto
(`reclamar_negocio.php?slug=…` respondía 200 en 4 slugs reales). Lo roto eran **las URLs históricas**. Por
eso el arreglo no fue cambiar el enlace del botón, sino **escribir la página nueva con el mismo nombre de
archivo** (`reclamar.php`) **+ una regla en `.htaccess`**.

**Reglas que no se deben romper:**
- `reclamar.php` es el archivo **NUEVO**: **no** volver a la versión con `includes/funciones.php` (era la
  que daba 500). El viejo pesaba **4.753 bytes**; si aparece uno así, es el viejo.
- El menú ☰ **«Reclamar mi negocio»** apunta a **`/reclamar`**, **NO** a `reclamar_negocio.php` sin slug:
  sin slug el formulario no sabe de qué negocio se trata (eso dejaba al dueño en un aviso de error).
- Si no se identifica el negocio, la página **nunca** deja al dueño en un callejón sin salida: ofrece
  **negocios parecidos**, **buscador** y el **WhatsApp del administrador**.

## §2 EL FLUJO COMPLETO, DE PRINCIPIO A FIN

```
   /reclamar                    reclamar_negocio.php?slug=<slug>        directorio_reclamos
 (buscador de reclamos)  ──►   (formulario del dueño)          ──►   (solicitud 'pendiente')
        ▲                              │                                     │
        │                              │                                     ▼
   menú ☰ · 404 · chatbot ·       sin slug o slug viejo              aviso Telegram 🙋
   la ficha (.reclama-caja)       → parecidos + WhatsApp             Súper Admin → 🏪 Reclamos
                                                                     (aprobar / rechazar / borrar)
```

| # | Pieza | Archivo | Qué hace |
|---|---|---|---|
| 1 | **Buscador de reclamos** | `reclamar.php` (URL `/reclamar`) | Paso 1: rejilla de **rubros con su conteo**. Paso 2: **resultados** (`?q=`, `?rubro=`). Sugerencias **predictivas** (Fuse) mientras se escribe, que enlazan al formulario |
| 2 | **Formulario de reclamo** | `reclamar_negocio.php?slug=<slug>` | Nombre, email, teléfono (opcional) y motivo → `crear_reclamo()` → tabla `directorio_reclamos` + **aviso Telegram 🙋** |
| 3 | **Tabla de solicitudes** | `directorio_reclamos` | Nace **`pendiente`**; el admin la pasa a `aprobado` / `rechazado` |
| 4 | **Aviso al jefe** | `includes/avisos.php`, caso `reclamo` | **🙋 Reclamo de negocio** al Telegram, con el negocio, quién lo pide, email, teléfono, motivo y el enlace para revisarlo |
| 5 | **Bandeja del admin** | `superadmin.php?seccion=reclamos` | **✓ Aprobar** (transfiere la ficha), **✓ Rechazar** y **🗑️ Borrar**, más el historial |
| — | **Botón de la ficha** | `negocio.php`, bloque `.reclama-caja` | La puerta principal: el dueño anónimo entra por aquí **desde su propia ficha** |
| — | **Página 404 propia** | `404.php` | La otra puerta: si la URL murió, no se va con las manos vacías (§11) |

## §3 LA PÁGINA `/reclamar` (`reclamar.php`)

**Tres formas de llegar a la misma página** (verificadas en `reclamar.php`):

| URL | Qué pinta |
|---|---|
| `/reclamar` | **Paso 1**: «1 · ¿De qué rubro es tu negocio?» — rejilla de rubros (`contar_negocios_por_categoria()`), **solo los que tienen negocios** (`n > 0`) y con su conteo |
| `/reclamar?rubro=<slug>` | **Paso 2** ya filtrado por ese rubro (chip «cambiar de rubro ↩»). Un rubro que **ya no existe** (enlace viejo) **se ignora**: no rompe la página |
| `/reclamar?q=<texto>&rubro=<slug>` | Resultados de `buscar_negocios_para_reclamar($q, $rubro, 60)` («N negocio(s) que coinciden con «…» · toca el tuyo 👇») |

- **Buscador predictivo propio** (`#recBuscar` / `#recSug`): trae `/api/negocios_json.php`, indexa con
  **Fuse** (claves `n` nombre · `r` rubro · `d` distrito, `threshold: 0.3`, tope 6 sugerencias) y **cada
  sugerencia lleva al RECLAMO** (`/reclamar_negocio.php?slug=<slug>`), no a la ficha.
  ⚠️ **A propósito NO usa el buscador global de la cabecera**: ese (`[data-fuzzy]`) lleva a `/neg/<slug>`.
  (Probar «zapatiya» → sugiere Zapatillas.)
- Sin JavaScript el `<form>` sigue funcionando (envía a `/reclamar`).
- Cada resultado es una tarjeta con icono, nombre, rubro, distrito y dirección + el CTA
  **«Este es mi negocio →»**.
- **Si no hay resultados:** caja con «No encontramos ningún negocio…», el consejo de probar **con una sola
  palabra** («Marina» en vez del nombre completo), el enlace a **`crear_negocio.php`** («Regístralo tú
  mismo en 2 minutos») y el **botón de WhatsApp** (`url_admin_reclamo()` con lo que se buscó).
- **Abajo, siempre**, una caja de rescate: «¿Tu tienda no aparece o te sale un error?» con el segundo
  WhatsApp (`url_admin_reclamo(['detalle' => 'Quiero reclamar mi tienda y necesito ayuda.'])`), el enlace
  a registrar el negocio y el enlace a `buscar.php`.
- UX según las Reglas de Oro: input de **17 px** (sin zoom en el celular), móvil-primero (los CTA se
  estiran al 100 % por debajo de 520 px).

## §4 LA REGLA DEL `.htaccess` (lo que hace que `/reclamar` funcione)

En `deploy/.htaccess`, dentro del bloque **«3.b) URLs HISTÓRICAS que la gente (y Google) sigue abriendo y
que daban 404»**:

```apache
RewriteRule ^reclamar/?$ reclamar.php [L,QSA]
RewriteRule ^buscar/?$   buscar.php  [L,QSA]
```

- Hace que **`/reclamar` y `/reclamar/` caigan en `reclamar.php`** (con `?q=…&rubro=…` intactos: `QSA`),
  y de paso converge con `/reclamar.php`, que ya funcionaba: **una sola URL buena, sin duplicar para
  Google**.
- **Si `/reclamar` responde 404, es esta regla** (falta o se pisó el `.htaccess`).
- La 404 propia se declara en el mismo archivo: **`ErrorDocument 404 /404.php`** (§11).
- ⚠️ El `.htaccess` se sube **al final y aparte**: un error de sintaxis ahí **tumba el sitio entero**.

## §5 EL FORMULARIO (`reclamar_negocio.php?slug=<slug>`)

**Cómo identifica el negocio** (en este orden):
1. `?slug=<slug>` → `obtener_negocio_por_slug()` (solo fichas **activas**).
2. Si no, `?negocio=<id>` → `vista_negocio_ficha_completa` por id.
3. Si **no** lo identifica (slug viejo, ficha borrada o mal escrita) → modo rescate: **negocios parecidos**
   (`negocios_parecidos_a_slug($slug, 6)`, o `buscar_negocios_para_reclamar($pista, '', 6)` si vino `?q=`),
   **buscador** y el **WhatsApp del administrador** con el enlace que falló. Antes solo salía un aviso
   amarillo.

**Qué pide y qué valida** (POST a `reclamar_negocio.php`, con `csrf_verificar()` y `negocio_id` en un campo
oculto):

| Campo | Obligatorio | Validación |
|---|---|---|
| Tu nombre completo | ✅ | mínimo **2** caracteres |
| Tu email | ✅ | `FILTER_VALIDATE_EMAIL` |
| Teléfono / WhatsApp | — | opcional (`null` si va vacío) |
| ¿Por qué es tu negocio? | ✅ | mínimo **10** caracteres |

- **Anti-duplicado:** antes de insertar, `ya_reclamo_pendiente($negocio_id, $usuario_id, $email)` — busca
  otra solicitud **`pendiente`** del mismo negocio para ese **usuario** (si hay sesión) o para ese
  **email** (si no). Si existe: «Ya enviaste una solicitud de reclamo para este negocio. Está en
  revisión.» y redirige a `reclamar_negocio.php?negocio=<id>`.
- **Éxito:** `crear_reclamo()` + pantalla «✅ ¡Solicitud enviada! Nuestro equipo la revisará y te
  contactaremos al email que registraste» (y termina ahí: solo un botón «Volver al inicio»).
- Si hay sesión, **nombre y email vienen precargados** del usuario.
- Si el negocio se identificó, arriba se ve la caja «Reclamando: **<nombre>**» con su rubro y distrito, y
  el enlace «¿No es este tu negocio? Busca el tuyo en la lista» → `/reclamar`.
- Nota de confianza al pie del botón: «Un administrador revisará tu solicitud antes de darte el control.»

## §6 LA TABLA `directorio_reclamos`

Una fila = **una solicitud de reclamo**. Columnas (definición de `migrar_reclamos_postulantes.php`, la
migración que la creó; ver §15):

| Columna | Tipo | Para qué |
|---|---|---|
| `id` | INT UNSIGNED AI | PK |
| `negocio_id` | INT UNSIGNED NOT NULL | la ficha que se reclama (indexada) |
| `usuario_id` | BIGINT UNSIGNED NULL | quién la pide **si tiene cuenta** (sin cuenta = `NULL`) |
| `nombre` | VARCHAR(120) NOT NULL | nombre de quien reclama |
| `email` | VARCHAR(150) NOT NULL | contacto (y clave del anti-duplicado sin sesión) |
| `telefono` | VARCHAR(40) NULL | teléfono / WhatsApp |
| `explicacion` | TEXT NULL | por qué es su negocio |
| `estado` | ENUM(`pendiente`,`aprobado`,`rechazado`) DEFAULT `pendiente` | estado de la solicitud (indexado) |
| `atendido_por` | BIGINT UNSIGNED NULL | id del admin que la resolvió |
| `creado_en` | DATETIME DEFAULT CURRENT_TIMESTAMP | cuándo entró |
| `atendido_en` | DATETIME NULL | cuándo se resolvió |

Índices: `idx_negocio (negocio_id)` y `idx_estado (estado)`. Motor InnoDB, `utf8mb4_unicode_ci`.
⚠️ En el hosting los `migrar_*.php` están **bloqueados por el antivirus**: por eso los módulos nuevos se
**auto-instalan de forma defensiva** desde el panel.

**Estado del dato:** al 2026-09-10 la tabla estaba **vacía (0 filas)** y el aviso 🙋 **nunca se había
disparado**. Cuántas solicitudes hay hoy: **por confirmar** (§15).

## §7 EL AVISO POR TELEGRAM (`aviso('reclamo', …)`)

Lo dispara `crear_reclamo()` (motor de avisos en `deploy/includes/avisos.php`; se enciende/apaga en
**Súper Admin → 📱 Telegram**). Caso registrado como:

| Dato | Valor |
|---|---|
| Tipo | `reclamo` · grupo **Personas** · estado **encendido por defecto** |
| Título | **🙋 Reclamo de negocio** |
| Nota en el panel | «Alguien dice "este negocio es mío".» |
| Contenido | `🏪` negocio · `👤` quien reclama · `📧` email · `📞` teléfono · `📝` explicación · `🛠️ Revisar: superadmin.php?seccion=reclamos` · `🕒` hora |
| Clave / dedupe | `clave = 'reclamo:<negocio_id>'`, `dedupe_min = 1`, `resumen = 'reclamo de <nombre>'` |
| Modelo de datos | `crear_reclamo()` devuelve el **id** insertado; `usuario_id`, `telefono` y `explicacion` se guardan como `NULL` si van vacíos |

## §8 EL SÚPER ADMIN: 🏪 RECLAMOS

`superadmin.php` — pestaña **🏪 Reclamos** (`?seccion=reclamos`), con **contador** de pendientes en la
barra y tarjeta «Reclamos pendientes» en el resumen. Lista **🕒 Solicitudes pendientes** y **📜 Historial**
(aprobadas + rechazadas). Tres acciones (todas con `csrf_campo()` y `confirm()`):

| Acción | Qué hace exactamente |
|---|---|
| `reclamo_aprobar` → **✓ Aprobar** | Si la solicitud tiene `usuario_id`, **transfiere la ficha**: `UPDATE directorio_negocios SET dueno_id = <usuario_id>`; después marca `estado='aprobado'` con `atendido_por` (id del admin) y `atendido_en=NOW()` |
| `reclamo_rechazar` → **✓ Rechazar** | Solo cambia `estado='rechazado'` + `atendido_por` + `atendido_en` |
| `reclamo_borrar` → **🗑️ Borrar** | `DELETE FROM directorio_reclamos WHERE id=?` |

⚠️ **Ojo con la aprobación:** la transferencia **solo ocurre si el reclamante tiene cuenta** (`usuario_id`).
Un reclamo **anónimo** (que es el caso normal, porque el formulario no exige cuenta) se aprueba pero
**no asigna dueño automáticamente**: hay que contactarlo por el email/teléfono y darle la ficha por el
camino normal. *Cuando entre el primer reclamo real, comprobar que la aprobación asigna de verdad la
ficha* (pendiente anotado desde el 2026-09-10).
Además: al **borrar una tienda** desde `editatiendas.php` o desde el Súper Admin se limpian también sus
filas en `directorio_reclamos` (`DELETE … WHERE negocio_id=?`).

## §9 EL BOTÓN DE LA FICHA (`negocio.php` → `.reclama-caja`)

Es **la puerta más importante** del módulo: el dueño anónimo entra por aquí, desde su propia ficha.
Antes era un **enlace subrayado de 12 px** y casi nadie lo veía; el jefe pidió hacerlo protagonista.

```html
<div class="reclama-caja">
    <span class="reclama-caja__t">🗝️ ¿Este negocio es tuyo?</span>
    <span class="reclama-caja__s">
        Reclámalo gratis y toma el control de la ficha para corregir el nombre, la dirección,
        el WhatsApp, las fotos y tus productos. No necesitas crear una cuenta.
    </span>
    <a class="reclama-btn" href="/reclamar_negocio.php?slug=<slug>">🏪 Reclamar mi negocio</a>
</div>
```

- **El enlace lleva el slug**, así el formulario ya sabe de qué negocio se trata.
- Medidas: bloque de **máx. 640 px** centrado, botón de **17 px** de fuente con `min-width: 320 px` en
  pantallas ≥ 560 px (ancho completo en móvil); medido en el navegador el 2026-09-10: **320×54 px**.
  El botón toma el **color de la paleta de esa ficha** (`var(--color-primario)` / `--color-acento`).
- Se pinta **al final de la ficha**, después del artículo del negocio.

**Y la ficha muerta tampoco es un callejón sin salida:** si `obtener_negocio_por_slug()` no devuelve nada
(ficha suspendida, borrada o URL vieja), `negocio.php` **ya no hace `die('Negocio no encontrado')`**:
hace `require __DIR__ . '/404.php'` y pinta la página 404 propia (§11).

## §10 EL WHATSAPP DEL ADMINISTRADOR (el jefe)

| Pieza | Detalle |
|---|---|
| Constante | **`ADMIN_WHATSAPP`** en `deploy/includes/helpers.php`, valor **`908785164`** desde el **2026-09-19** (antes `955041690`, que pasó a ser el número **personal** del jefe y quedó en `ADMIN_WHATSAPP_VIEJOS`; ver **§13.1**). Solo dígitos, celular peruano: se le antepone el `51`. **Vacía = los botones se ocultan solos** |
| Quién la usa | la **404**, el **formulario** y el **pie de página** (`includes/footer.php`, que antes apuntaba al número falso `51999999999`) |
| `url_whatsapp_admin($mensaje = '')` | `https://wa.me/<51+número>` con el mensaje ya escrito (`?text=`); devuelve **`''`** si no hay número (el que lo use debe **ocultar el botón**) |
| `url_admin_reclamo(array $d = [])` | El mensaje **en primera persona, listo para enviar** (el jefe no escribe nada) con: `🔗 Mi tienda:` (ficha), `🔍 Busqué:`, `⚠️ Enlace que no funcionó:`, `📝` detalle libre, `↩️ Venía desde:` (referer real si no se pasa `origen`) y `🕒` fecha y hora. Acepta `negocio`, `ficha`, `buscado`, `roto`, `origen`, `detalle` |
| Estilo | Icono oficial con `wa_icono_svg()` (SVG inline, cero librerías) y **nunca** un chat en blanco: la regla del sitio es que todo WhatsApp sale con contexto + el enlace de la página |
| Si cambia el número | editar la constante y subir `includes/helpers.php`: **no hay otra copia** del número en el flujo de reclamos (la última vez que cambió, el 2026-09-19, está todo el paso a paso en **§13.1**) |

Cómo se ve el mensaje (plantilla real de `url_admin_reclamo()`, con los datos que tenga):

```text
¡Hola! 👋 Soy el dueño de «Bodega Marina» y quiero reclamar mi tienda en DeChimbote.com para corregir sus datos.
🔗 Mi tienda: https://dechimbote.com/neg/bodega-marina
🔍 Busqué: bodega marina
⚠️ Enlace que no funcionó: https://dechimbote.com/neg/bodega-marina-antigua
📝 La ficha de este negocio ya no se puede ver (estado: suspendido).
↩️ Venía desde: https://www.google.com/
🕒 10/09/2026 18:42
```

## §11 LA PÁGINA 404 PROPIA (`404.php`)

**Quién la usa (dos puertas):**
1. el **`ErrorDocument 404 /404.php`** del `.htaccess` (cualquier URL muerta del sitio), y
2. las **fichas muertas** (`negocio.php` hace `require 404.php` y `exit`).

**Qué hace, en orden:**
1. **Responde 404 de verdad** (`http_response_code(404)`, para que Google no indexe la página rota).
2. **Avisa al jefe por Telegram**: `aviso('pagina_404', ['ruta' => <URI completa>, 'clave' => '404:<hash de la ruta>', 'dedupe_min' => 720, 'resumen' => <URI>])`. El «↩️ de dónde venía» lo agrega el motor de avisos con el **referer real**.
   ⚠️ El motor de avisos tiene **tope por hora** (`estado='agrupado'` en `directorio_avisos_log`): al
   Telegram llegan **menos** 404 de los que hay. Para diagnosticar un 404 «fantasma» se lee esa bitácora con
   la sonda (§14), **nunca** suponiendo de dónde viene.
3. **Ayuda en vez de disculparse:**
   - **Título:** «🔍 No encontramos lo que buscas» + la ruta muerta en un recuadro monoespaciado.
   - **Buscador grande** que envía a `buscar.php?q=…` (y precarga lo que la URL traía en `?q=`).
   - **«👀 ¿Quizá buscabas uno de estos?»** — candidatos con `negocios_parecidos_a_slug($slug, 5)` si la
     URL muerta era una ficha (`/neg/<slug>` o `/negocio/<slug>`), o con
     `buscar_negocios_para_reclamar($q_muerta, '', 5)` si la URL traía `?q=…`. Cada candidato lleva
     «Ver ficha →» (si está activo) o **«Reclamarla →»** (si no lo está). En las pruebas del 2026-09-10
     `/neg/cevicheria-el-buen-sabor` recuperó **6** cevicherías reales.
   - **Botón 🗝️ «Reclamar mi tienda»** → `/reclamar` (con `?q=` si lo había).
   - **Botón de WhatsApp al administrador** (`url_admin_reclamo()`): «💬 ¿No encuentras lo que buscas?
     Comunícate con nuestro administrador», con el mensaje ya escrito (qué buscaba, el enlace que falló y
     la hora). Medido: **460×58 px** (el CSS fija `max-width: 460px` y **17 px** de fuente).
   - **🧭 Mira lo que sí tenemos:** los **8 rubros con más negocios activos** (`contar_negocios_por_categoria()`
     ordenado por conteo) + enlaces al inicio, al buscador y a `/reclamar`.
- **Contexto que recibe el jefe en el WhatsApp** (lo arma el propio 404): el nombre del negocio si era una
  ficha (`negocio_por_slug_cualquiera()`, que mira **cualquier estado**), el motivo («La ficha de este
  negocio ya no se puede ver (estado: …)») y, si hay candidatos, **la pista de qué tienda puede ser**
  («Puede que mi negocio sea: X · Y · Z»), para que no tenga que preguntar «¿cuál es tu tienda?».

## §12 DÓNDE MÁS APARECE EL MÓDULO (vivo, verificado en el código)

| Sitio | Qué hace |
|---|---|
| `includes/header.php` (menú ☰) | «🏪 **Reclamar mi negocio** — Ya está publicado y es mío» → **`/reclamar`** (al buscador, **no** al formulario sin slug) |
| `includes/footer.php` | El WhatsApp del pie usa `url_whatsapp_admin()` con el **número real** del administrador |
| `includes/estadisticas.php` | Clasifica las visitas: `/reclamar` y `/reclamar.php` → página **`reclamar`** (grupo `reclamar`); `/reclamar_negocio.php` → página **`reclamar_negocio.php`** (mismo grupo), así el panel cuenta el módulo |
| `sitemap.php` | Publica `url('reclamar')` en el sitemap (`/sitemap.xml`, prioridad 0.6, `monthly`) |
| `includes/chatbot.php` | Si el bot enlaza `/reclamar`, lo nombra al hablar: «reclamar tu negocio» |
| `superadmin.php` | Pestaña 🏪 Reclamos + contador + tarjeta de pendientes (§8) |
| `editatiendas.php` | Al **eliminar** una tienda, borra sus reclamos (`DELETE … WHERE negocio_id=?`) |
| `.htaccess` | Regla `^reclamar/?$` (§4) y `ErrorDocument 404 /404.php` (§11) |

## §13 LAS FUNCIONES DEL MOTOR (`deploy/includes/helpers.php`)

**De reclamos** (el bloque «RECLAMAR UN NEGOCIO» y el de «RECLAMOS DE NEGOCIO»):

| Función (nombre exacto) | Para qué sirve |
|---|---|
| `buscar_negocios_para_reclamar($q = '', $rubro_slug = '', $limite = 60)` | Negocios **activos** filtrados por rubro (slug de categoría) y/o por las palabras escritas. **Cada palabra debe aparecer en el nombre o en la dirección** (tope **4 palabras**), así «bodega marina» no devuelve las 1.500 tiendas. Ordena destacados primero, luego por nombre. Devuelve `id, nombre, slug, direccion, rubro, icono, rubro_slug, rubro_color, distrito`. Ante error: `error_log` y `[]` (nunca rompe la página) |
| `contar_negocios_por_categoria()` | Rubros **activos** con cuántos negocios activos tiene cada uno (`COUNT(n.id) AS n`), ordenados por `orden` y nombre. Alimenta **la rejilla del paso 1** de `/reclamar` y los **rubros populares** de la 404 |
| `negocio_por_slug_cualquiera($slug)` | Un negocio por slug **sin importar su estado** (`directorio_negocios`: `id, nombre, slug, estado`). Sirve para poner nombre y ficha en el WhatsApp cuando la URL muerta era una ficha **suspendida o borrada** |
| `negocios_parecidos_a_slug($slug, $limite = 5)` | El «¿Quisiste decir…?» de una URL muerta: toma las **2 palabras más largas** del slug (mínimo 4 letras) y busca negocios con nombre parecido. Ordena **activos primero**. Devuelve `[]` si no hay nada razonable |
| `crear_reclamo($negocio_id, $usuario_id, $nombre, $email, $telefono, $explicacion)` | `INSERT` en `directorio_reclamos` + **aviso Telegram 🙋**; devuelve el id insertado |
| `ya_reclamo_pendiente($negocio_id, $usuario_id = null, $email = null)` | ¿Ya hay una solicitud **`pendiente`** de ese negocio? Busca por `usuario_id` si hay sesión, si no por `email` (anti-duplicado del formulario) |
| `obtener_reclamos($estado = null, $limite = 200)` | Lista de solicitudes (con `negocio_nombre` y `negocio_slug`) para la bandeja del Súper Admin |
| `reclamo_por_id($id)` | Una solicitud concreta con los datos del negocio (la usa la aprobación). Vive en `superadmin.php` |

**De WhatsApp (del mismo archivo):** `url_whatsapp_admin($mensaje = '')` · `url_admin_reclamo(array $d = [])` ·
`url_whatsapp($numero, $texto = '')` (base: limpia el número, añade `51` si son 9 dígitos) ·
`wa_icono_svg($estilo = '')` (el ícono oficial) · `url_negocio($slug)` y `url($path)` (los enlaces que usan
todas las páginas del módulo).

### §13.1 🔴 EL NÚMERO DEL ADMINISTRADOR CAMBIÓ A `908785164` (2026-09-19) — y las 484 fichas que llevaban el viejo

**La orden del jefe (textual):** *«908785164 usa este número de teléfono para recibir mensajes de WhatsApp
que vayan dirigidos al administrador, todo lo que tenga que ver con administración del sitio… repito, solo
para temas relacionados de administración… digamos que es el número del dueño de la página web»*.

| Qué | Cómo quedó |
|---|---|
| El número | **`ADMIN_WHATSAPP` = `908785164`** (en `deploy/includes/helpers.php`): **el número del dueño de la página**, el que recibe **todo lo de administración** (reclamos y 404 de este módulo, el pie de página, los avisos de empleo, la recuperación de cuenta, «hablar con una persona» del chat y el «quiero más productos» de El maestro, que lee `TIENDA_IA_WHATSAPP_MAS`) |
| El número viejo | **`955 041 690` es el número PERSONAL del jefe** y ya **no** es el de la administración. Sigue declarado en **`ADMIN_WHATSAPP_VIEJOS` = `955041690`** por un solo motivo: que las fichas viejas que lo traen escrito se sigan reconociendo como **«sin dueño»** |
| La función nueva | **`telefono_es_del_admin($tel)`** (`helpers.php`): mira el número nuevo **y** los viejos, comparando solo los dígitos y solo el final (por si viene con el `51`). La usan el chat de la ficha (`chatbot_ficha_es_telefono_admin()`, que ahora delega en ella para ofrecer «reclámala gratis») y `perfil.php` |
| Las 484 fichas | Se pasaron al número nuevo **484 fichas que llevaban el `955041690`** como teléfono y WhatsApp (todas activas, todas sin dueño, todas con catálogo: Mercado Modelo, MegaPlaza, Centro Cívico de Nuevo Chimbote, ORABELA, Boticas Dr. Simi…). **Comprobado después: 484 con el `908785164` y 0 con el viejo.** No se tocó `directorio_usuarios` (la cuenta del jefe sigue con su teléfono: es su login) ni los avisos de empleo (medido: 0 con ese número) |
| `perfil.php` + `negocio.php` | El mensaje firmado y su botón publican **908 785 164**, y **`/perfil/<número del administrador>` responde 404**: ese número no es el perfil de una persona (la regla del jefe: *«la palabra perfil es para alguien que tiene varios negocios»*), era una página de **4,6 MB con 484 tiendas** de gente distinta. Por lo mismo, `negocio.php` **ya no pinta el botón «👤 Ver sus N tiendas»** cuando el número de la ficha es el del administrador (decía «Ver sus **484** tiendas» y llevaba a ese 404) |
| `invitaciones.php` | Si el número de la tienda es el del administrador, la invitación **sale sin usuario ni contraseña** y avisa en pantalla (antes eso ocurría «de rebote», porque la cuenta de ese número era la del admin; sin la guarda se habría creado una cuenta con el número del administrador como usuario) |

**Cómo se hizo y cómo se comprueba** (las sondas se suben con `__sonda_run.py` y se borran solas):

```powershell
cd D:\RELAX
# 1) Simulacro de la migración de las fichas (no escribe nada) y después el cambio de verdad
python __sonda_run.py __ep_admin_tel_migrar.php adm-2026-9kQ7 x        # fichas_con_viejo: 484
python __sonda_run.py __ep_admin_tel_migrar.php adm-2026-9kQ7 go       # cambiadas_fichas: 484

# 2) Comprobación en vivo (constantes, enlaces, quién cuenta como admin y las fichas)
python __sonda_run.py __ep_admin_check.php adm-2026-9kQ7 x
#   ADMIN_WHATSAPP 908785164 · ADMIN_WHATSAPP_VIEJOS 955041690 · TIENDA_IA_WHATSAPP_MAS 908785164
#   pie/reclamos OK wa.me/51908785164 · fichas: 484 con el nuevo y 0 con el viejo
#   invitacion_ficha_1: ok=false ← la invitación de una ficha con el número del admin NO crea cuenta

# 3) Por HTTP (el jefe da el clic él mismo; no se abre nada)
#   https://dechimbote.com/                      → wa.me/51908785164 en el pie
#   https://dechimbote.com/reclamar.php          → 2 botones al número nuevo
#   https://dechimbote.com/pagina-que-no-existe  → 404 con el número nuevo (y 0 con el viejo)
#   https://dechimbote.com/neg/mercado-modelo-de-chimbote → la ficha muestra 908785164 y SIN «Ver sus N tiendas»
#   https://dechimbote.com/perfil/908785164      → 404 (ya no se arma ese «perfil»)
```

⚠️ **Para la próxima vez que cambie el número:** es **una constante** (`ADMIN_WHATSAPP`) + los respaldos en
`chatbot_kb.php`, `chatbot_ficha.php` y `config_tienda_ia.php`; y hay que decidir qué se hace con las fichas
que llevan el número viejo (esta vez el jefe mandó pasarlas al nuevo).

## §14 CÓMO VERIFICAR EN 30 SEGUNDOS

```powershell
cd D:\RELAX

# 1) Sintaxis de lo que se vaya a tocar (⚠️ php NO está en el PATH)
C:\xampp\php\php.exe -l deploy\reclamar.php
C:\xampp\php\php.exe -l deploy\reclamar_negocio.php
C:\xampp\php\php.exe -l deploy\404.php
C:\xampp\php\php.exe -l deploy\includes\helpers.php

# 2) El flujo completo por HTTP (16 comprobaciones) y la parte funcional
python __reclamar_6_verificar.py
python __reclamar_7_funcional.py   # buscador, texto del WhatsApp, botón grande, sitemap
python __reclamar_8_404.py         # 404 de una ficha muerta: código + textos + candidatos

# 3) ¿Siguen llegando 404 a /reclamar? (sonda temporal: se borra sola del hosting)
python __sonda404.py
```

**Señales de que algo se rompió** (revisar en este orden):
- **`/reclamar` responde 404** → falta la regla **`^reclamar/?$`** en `.htaccess` (§4).
- **Una ficha responde 500** → se subió un `negocio.php` o un `helpers.php` viejo.
- **El WhatsApp aparece sin texto** → la constante **`ADMIN_WHATSAPP`** quedó vacía (o el botón debería
  haberse ocultado y no lo hace).
- **La 404 no muestra el nombre del negocio** → `url_admin_reclamo()` perdió el campo `negocio`.
- **El buscador de reclamos lleva a la ficha en vez del formulario** → alguien cambió el destino de las
  sugerencias: tienen que llevar siempre a `reclamar_negocio.php?slug=…`.
- **El botón de la ficha mide poquísimo** → se perdió el bloque `.reclama-caja` / `.reclama-btn` (§9).

⚠️ **Reglas del proyecto que aplican aquí:** el código real es `D:\RELAX\deploy`; el despliegue es
**`php -l` → subir solo lo modificado → verificar por HTTP**; **las pestañas y ventanas del navegador las
abre el jefe** (se le entrega el enlace, no se le roba el foco); y los scripts `__*.py` se corren
**desde `D:\RELAX`** (hacer `cd deploy` los rompe).

## §15 LO QUE QUEDA «POR CONFIRMAR»

Nada de lo que sigue se pudo comprobar en el código de hoy: **no darlo por hecho**.

| Dato | Por qué no se puede confirmar | Cómo se confirmaría |
|---|---|---|
| **El esquema real de `directorio_reclamos` en producción** | Las columnas de §6 salen de `migrar_reclamos_postulantes.php`, una migración **de un solo uso que se autodestruye** al terminar | `SHOW CREATE TABLE directorio_reclamos` con una **sonda temporal** por FTP (el MySQL remoto está cerrado) |
| **Si `migrar_reclamos_postulantes.php` sigue en el hosting** | El archivo existe en `deploy/`, pero la migración se borra sola al ejecutarse | Pedirlo por HTTP (esperado **404** o **403** si sigue) |
| **Cuántas solicitudes hay hoy en `directorio_reclamos`** | El último dato medido es del **2026-09-10: 0 filas**; el aviso 🙋 no se había disparado nunca | Súper Admin → **🏪 Reclamos**, o la sonda de §14 |
| **Si alguna aprobación ha asignado realmente la ficha** | Hay que comprobar que `directorio_negocios.dueno_id` cambia al aprobar (el código solo lo hace **si el reclamante tiene cuenta**) | Primer reclamo real: aprobar y mirar el `dueno_id` |
| **Si los 404 de `/reclamar` bajaron a 0** | La comprobación quedó pendiente el 2026-09-10 («revisar en 1-2 días») | `python __sonda404.py`: que `SUBSTRING_INDEX(resumen,'?',1) = '/reclamar'` **no** aparezca |
| **El alto exacto de los botones (54 px y 58 px)** | Son **mediciones en el navegador** del 2026-09-10; el CSS fija la fuente (17 px) y el ancho máximo, **no** la altura | Inspeccionar en el navegador (lo abre el jefe) |
| **`directorio_avisos_log` como fuente de diagnóstico** | Se usa para contar 404 y su `estado='agrupado'`; su estructura no se revisó en esta sesión | Ver `GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md` §9.1 |
| **El índice del proyecto apunta a la §19 vieja** | `GUIA_MAESTRA_CHIMBOTE_XYZ.md` sigue diciendo que la referencia de reclamos es la §19 de la guía del módulo retirado | Actualizar esa fila del índice a **este archivo** (no se tocó aquí: esta sesión solo crea esta guía) |

## §16 ARCHIVOS VIVOS DEL MÓDULO

Todo lo de esta tabla está **verificado en el código** (rutas relativas a `D:\RELAX\deploy\`):

| Archivo | Qué hace en el módulo |
|---|---|
| `reclamar.php` | **Página `/reclamar`**: paso 1 rubros con conteo, paso 2 resultados, buscador predictivo propio (Fuse sobre `api/negocios_json.php`), CTA «Este es mi negocio →» y el WhatsApp de rescate |
| `reclamar_negocio.php` | **Formulario** (`?slug=` o `?negocio=`): valida, evita duplicados, llama a `crear_reclamo()`; con slug muerto muestra parecidos + buscador + WhatsApp |
| `404.php` | **Página 404 propia**: responde 404, avisa al jefe, buscador, candidatos, botón «🗝️ Reclamar mi tienda» y WhatsApp del administrador con contexto |
| `negocio.php` | **Bloque `.reclama-caja` + `.reclama-btn`** (botón grande de la ficha) y, en la **ficha muerta**, `require 404.php` en vez de `die()` |
| `includes/helpers.php` | `ADMIN_WHATSAPP` (**908785164** desde el 2026-09-19; `ADMIN_WHATSAPP_VIEJOS` = 955041690 · §13.1) + las funciones de reclamos (`buscar_negocios_para_reclamar`, `contar_negocios_por_categoria`, `negocio_por_slug_cualquiera`, `negocios_parecidos_a_slug`, `crear_reclamo`, `ya_reclamo_pendiente`, `obtener_reclamos`) y las de WhatsApp (§13) |
| `includes/avisos.php` | El caso **`reclamo`** del motor de avisos (mensaje 🙋 al Telegram) y el caso `pagina_404` que usa la 404 |
| `includes/header.php` | El menú ☰ «🏪 Reclamar mi negocio» → `/reclamar` |
| `includes/footer.php` | El WhatsApp del pie con `url_whatsapp_admin()` (número real del administrador) |
| `includes/estadisticas.php` | Clasifica `/reclamar`, `/reclamar.php` y `/reclamar_negocio.php` como páginas del módulo |
| `includes/chatbot.php` | Nombra `/reclamar` cuando el bot enlaza el módulo |
| `superadmin.php` | Bandeja **🏪 Reclamos**: aprobar (transfiere la ficha), rechazar, borrar + contador e historial |
| `editatiendas.php` | Al eliminar una tienda, borra sus reclamos |
| `api/negocios_json.php` | Los datos del **buscador predictivo** de `/reclamar` (y del buscador fuzzy del sitio) |
| `sitemap.php` | Publica `/reclamar` en `/sitemap.xml` |
| `.htaccess` | Regla `^reclamar/?$ reclamar.php [L,QSA]` y `ErrorDocument 404 /404.php` |
| `migrar_reclamos_postulantes.php` | La migración que **creó** `directorio_reclamos` (un solo uso, se autodestruye: **por confirmar** si sigue en el hosting, §15) |
