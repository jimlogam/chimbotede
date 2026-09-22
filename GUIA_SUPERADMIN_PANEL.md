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


# GUÍA — PANEL DEL SÚPER ADMIN · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** antes de agregar, quitar o reordenar una sección (pestaña) del Súper Admin, o de tocar su menú, su ancho, sus tablas o la **pestaña 🏬 Tiendas** (vista previa y filtros).
> **Archivos que toca:** `deploy/superadmin.php` (menú y CSS del panel) · `deploy/includes/vista_tiendas_admin.php` (**pestaña 🏬 Tiendas: vista previa, filtros y orden**) · `deploy/includes/invitaciones.php` (**📨 motor de las invitaciones por WhatsApp de la pestaña 🏬**, 2026-09-17) · `deploy/includes/vista_estadisticas_admin.php` (vista de la pestaña Estadísticas)
> **Estado:** EN PRODUCCIÓN — menú en cuadrícula y **pestaña 🏬 Tiendas con vista previa + filtros (2026-09-12)**
> 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni renombra nada a mano, así que **no se hace respaldo del archivo vivo** y **no se compara el local con el hosting** (ni md5, ni tamaños). Despliegue = `php -l` → subir solo lo modificado → verificar por HTTP (§7).

> Regla de Oro de UX: ver REGLAS_DE_ORO_PROYECTO.md

## 0) LO ESENCIAL EN 30 SEGUNDOS

- El panel vive en **`deploy/superadmin.php`** y se navega con **pestañas** (`superadmin.php?seccion=<nombre>`), no con un menú lateral.
- El menú es una **cuadrícula responsiva** (`grid` + `repeat(auto-fill,minmax(160px,1fr))`) que se apila sola y **crece hacia abajo** cuando se agregan pestañas.
- **Antes era una sola fila horizontal con scroll escondido:** con 12 pestañas, las últimas quedaban cortadas fuera de pantalla y sin barra visible (§2).
- **Para agregar una sección nueva basta con una `<a>` nueva dentro de `.sa-nav`:** la cuadrícula abre otra fila sin tocar el CSS (§5).
- `.sa-wrap` usa el **ancho completo** de `<main>` (1200 px), no 1100 px; y `.sa-table-wrap` hace scroll horizontal **dentro de su caja** para las tablas anchas.
- **🏬 Tiendas (2026-09-12) ya no es una tabla suelta:** es una **vista previa en tarjetas con foto** + **filtros en píldoras** (con/sin foto, WhatsApp, productos…) + **orden** + **buscador predictivo**. Todo vive en `deploy/includes/vista_tiendas_admin.php` (§4.2).
- **🏬 Tiendas · rejilla nueva (2026-09-17, pedido del jefe):** **5 columnas en PC**, **todas las fotos con la MISMA altura corta** (130 px en PC / 152 px en móvil) y **debajo de cada tienda un botón 📨 de invitación** que abre WhatsApp con el mensaje ya escrito y cambia de color según cuántas veces se le mandó (⚫ sin enviar · 🟠 1 · 🟢 2 · 🔵 3 o más). Motor: `deploy/includes/invitaciones.php` (§4.4).

## 1) QUÉ ES EL PANEL Y CÓMO SE NAVEGA

- URL: **https://dechimbote.com/superadmin.php** (requiere sesión de admin).
- Cada sección es una pestaña con su propia URL: `superadmin.php?seccion=resumen`, `?seccion=estadisticas`, `?seccion=usuarios`, etc.
- La pestaña activa se marca con la clase `activo` (fondo granate, texto blanco).
- Las pestañas pueden llevar un **globito numérico de pendientes** a la derecha del botón (clase `.n`, con `margin-left:auto`). Es genérico: lo usa la pestaña que tenga pendientes.

## 2) EL PROBLEMA: LOS BOTONES QUE SE OCULTABAN A LA DERECHA

**Petición del jefe:** *"Los botones de herramientas de Súper Admin se han ocultado a la derecha (Productos, Usuarios, Tablones y otros que no se pueden leer). Corrige eso y apila en cuadrículas al ancho de pantalla, para que a futuro se agreguen más botones."*

**Causa exacta:** el menú era **una sola fila horizontal con scroll escondido**:

```css
.sa-nav{display:flex;gap:6px;overflow-x:auto;scrollbar-width:none}
.sa-nav::-webkit-scrollbar{display:none}   /* <- nadie ve que hay más botones a la derecha */
.sa-nav a{white-space:nowrap;...}
.sa-wrap{max-width:1100px}                 /* <- más angosto que el resto del sitio (1200px) */
```

Con **12 pestañas**, las últimas quedaban **cortadas fuera de pantalla y sin barra visible** → imposible leerlas o pulsarlas. Cada pestaña nueva empeoraba el problema.

## 3) LA SOLUCIÓN: MENÚ EN CUADRÍCULA RESPONSIVA (ARCHIVO ÚNICO)

Todo el arreglo está en el CSS de **`deploy/superadmin.php`** (ningún PHP se tocó):

```css
.sa-wrap{max-width:100%;margin:0 auto}   /* usa TODO el ancho útil de <main> (1200px), no 1100px */

/* Cuadrícula que se apila sola y crece hacia abajo cuando se agregan más botones */
.sa-nav{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;margin-bottom:18px}
.sa-nav a{display:flex;align-items:center;gap:6px;min-height:46px;padding:10px 12px;border-radius:12px;
          background:#fff;border:1px solid var(--color-borde);box-shadow:var(--sombra-tarjeta);
          font-size:14px;font-weight:600;color:var(--color-texto);line-height:1.15;
          text-decoration:none;white-space:normal;overflow-wrap:break-word}
.sa-nav a:hover{border-color:var(--color-primario);color:var(--color-primario)}
.sa-nav a.activo{background:var(--color-primario);color:#fff;border-color:transparent}
.sa-nav a .n{margin-left:auto;...}       /* el globito de pendientes se va a la derecha del botón */
@media(max-width:640px){.sa-nav{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
                        .sa-nav a{font-size:13px;padding:9px 10px;min-height:44px}}
.sa-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}  /* estaba usado pero SIN definir */
```

Claves del arreglo:

- **`auto-fill` + `minmax(160px,1fr)`**: el navegador calcula cuántas columnas caben. En 1366 px salen **6 por fila (2 filas, 12 botones)**; en móvil se fuerza a **2 columnas**.
- Se eliminó `overflow-x:auto` con la scrollbar oculta: **ya nada queda escondido**, todo se ve.
- Se quitó `white-space:nowrap`: si un nombre es largo, **baja de línea** en vez de cortarse.
- Botones de **46 px de alto** (medida táctil) y texto a 14 px → legibles en móvil.
- **A futuro:** agregar una `<a>` nueva a `.sa-nav` no requiere tocar CSS: la cuadrícula abre otra fila sola.

## 4) ÍNDICE DE SECCIONES (PESTAÑAS) DEL PANEL

Las **11 pestañas** que existen hoy, en el orden del menú. **Las guías fuente no describen el contenido de
cada una**, así que solo se anota lo que sí dicen:

| # | Pestaña | Sección (`?seccion=`) | Qué se sabe |
|---|---|---|---|
| 1 | 📊 Resumen | `resumen` | Es la pestaña por defecto. Sin más detalle documentado |
| 2 | 📈 **Estadísticas y récords** | `estadisticas` (**`records` = alias**) | **Módulo fusionado el 2026-09-13** (antes eran dos pestañas: 📈 Estadísticas y 🏆 Récords). Cuenta el sitio en 5 tramos: 🟢 **en vivo** · 💼 **el negocio** (los récords: más vistos, más buscados, más pedidos, soles, embudo, rubros, tiendas sin visitas) · 📈 **el tráfico** · 👥 **las personas** · 📣 **para enseñar** (resumen copiable). **Un solo rango** (`&rango=`: Hoy (24 h) · 2 días (48 h) · 3 · 5 · 7 · 15 · 30 · 60 · 90 días · 1 año). **Guías: `GUIA_RECORDS_DEL_SITIO.md`** (mitad del negocio) y `GUIA_ESTADISTICAS_DEL_SITIO.md` (tracking y tablas) |
| 3 | 🏪 Reclamos | `reclamos` | Reclamos de negocio (`directorio_reclamos`). Su globito = pendientes sin atender |
| 4 | 🚩 Reportes | `reportes` | Reportes de contenido que llegan de `api/reportar.php` (`directorio_reportes`) y avisan al Telegram. Detalle del aviso: `GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md` §11 |
| 5 | 📱 Telegram | `avisos` | **La pestaña de los avisos**: casillas para encender/apagar cada aviso + botón "Guardar cambios" (eran **21**; 🗑️ el del **chat comunitario** se retiró el 2026-09-13, así que **hoy hay uno menos** — el número al día está en `GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md` §10). Documentada entera en `GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md` §10 |
| 6 | 💼 Postulantes | `postulantes` | Postulantes de "Trabaja con nosotros" (`directorio_postulantes`) |
| 7 | 🏬 Tiendas | `tiendas` | **Vista previa con foto + filtros + orden + buscador predictivo** (2026-09-12): `includes/vista_tiendas_admin.php`. Acciones por tarjeta: ✓ Aprobar / ⛔ Rechazar (pendientes), 🙈 Ocultar (activas), 🟢 Publicar (ocultas), 👁 Ver ficha, 🗑 Eliminar. **Rejilla de 5 columnas en PC + botón 📨 de invitación por WhatsApp (2026-09-17).** **Contenido completo en §4.1, §4.2 y §4.4** |
| 8 | 📦 Productos | `productos` | Productos (`directorio_servicios`). Tabla ancha |
| 9 | 👥 Usuarios | `usuarios` | Botones de fila: ⭐ Hacer Premium / 🚫 Suspender / 🗑 Eliminar *(verificar en el panel)* |
| ~~10~~ | 🗑️ ~~🗂️ Tablones~~ **RETIRADO (2026-09-13, orden del jefe)** | ~~`tablones`~~ | Era la pestaña de los **Tablones B2B** (conceder Premium, ver y borrar hilos), con su **contador «Tablones B2B»** y la acción **`tablon_eliminar`**. Se quitó del panel al **retirar el módulo completo del sitio** (el jefe ordenó quitar toda referencia a los tablones). **Hoy el Súper Admin no tiene nada de tablones.** |
| 11 | 📢 Banners | `banners` | Módulo de publicidad: `GUIA_PUBLICIDAD_Y_BANNERS.md` §9 (crear, editar, pausar, borrar, estadísticas). Tabla ancha |
| 12 | 📍 Cerca de mí | `cerca` | Distritos con coordenadas. Detalle: `GUIA_CERCA_DE_MI_Y_GEOBUSQUEDA.md` |

> Los **valores de `?seccion=`** están **comprobados en vivo** (2026-09-10) contra `superadmin.php`.
> ⚠️ **Hoy son 11 pestañas:** la **🗂️ Tablones** se retiró con su módulo (2026-09-13) y ese mismo día
> **📈 Estadísticas y 🏆 Récords se fusionaron en una sola** (📈 Estadísticas y récords). Lo que sigue sin
> documentar es el **contenido interno** de cada sección (qué campos y validaciones tiene): para eso hay que
> leer la sección en `deploy/superadmin.php` **y documentarla aquí**.

> 🔗 **Dos enlaces del menú que NO son pestañas `?seccion=`** (no están en la tabla de arriba porque abren
> **páginas propias**, no una sección del panel). Están en el `<nav class="sa-nav">`, entre *📦 Productos* y
> *👥 Usuarios*:
> - **`🎨 Editar productos (IA)`** → `editaproductos.php` · `GUIA_EDICION_PRODUCTOS_PROMPTS_IA.md`.
> - **`🖼️ Editar tiendas (portadas + rubros)`** → `editatiendas.php` (añadido el **2026-09-12**): el
>   **editor masivo de tiendas** (portada por arrastrar y soltar + prompt de IA + **hasta 4 rubros**).
>   Detalle: `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` **PARTE B**.
> ⚠️ Ese segundo enlace se añadió porque **el jefe perdió el link** de `editatiendas.php`: si algún día
> "desaparece" el editor, **no está roto**, es que falta la sesión de admin (**302 → `login.php`**).

### 4.1 🏬 TIENDAS — QUÉ ES Y QUÉ HACE CADA BOTÓN (reescrito el 2026-09-12)

**Qué es hoy.** La pestaña `superadmin.php?seccion=tiendas` es el **panel de trabajo de las tiendas**:
**vista previa en tarjetas con foto** (por defecto) o **tabla** (`&vista=tabla`), con **filtros**,
**orden** y **buscador predictivo**. Todo el código vive en **`deploy/includes/vista_tiendas_admin.php`**
(el bloque `$seccion === 'tiendas'` de `superadmin.php` solo hace `require`). El detalle de filtros y
medidas está en **§4.2**.

**Acciones por tienda** (las 4 siguen siendo POST al mismo handler del panel):

| Botón | Cuándo sale | Qué hace (`$accion`) |
|---|---|---|
| ✓ Aprobar | solo si está `pendiente` | `tienda_estado` con `estado=activo` → sale en el directorio |
| ⛔ Rechazar | solo si está `pendiente` | `tienda_estado` con `estado=rechazado` |
| 🙈 Ocultar | si está `activo` | `tienda_estado` con `estado=inactivo` (deja de verse, no se borra) |
| 🟢 Publicar | si está `inactivo`/`rechazado` | `tienda_estado` con `estado=activo` |
| 👁 Ver ficha | siempre | abre `/neg/<slug>` en pestaña nueva |
| 🗑 Eliminar | siempre | `tienda_eliminar` → `eliminar_negocio_completo()` (borra productos, fotos, opiniones, vistas, mensajes, pagos y reclamos) |

```php
} elseif ($accion === 'tienda_estado') {
    $est = trim($_POST['estado'] ?? '');
    if (in_array($est, ['pendiente','activo','inactivo','rechazado'], true)) {
        $pdo->prepare("UPDATE directorio_negocios SET estado=? WHERE id=?")->execute([$est, $id]);
        flash('Estado de la tienda actualizado a ' . $etiqueta . '.', 'exito');
    }
}
```

> ✅ **`fuzzy_olvidar_cache()` SÍ corre**: se llama **una sola vez al final del bloque POST** de
> `superadmin.php`, así que aprobar/ocultar/borrar se refleja **ya** en las sugerencias del buscador.
> ⚠️ **Esta guía decía lo contrario hasta el 2026-09-12** (dato obsoleto corregido leyendo el archivo).
> ⚠️ Lo que la aprobación **NO** hace: no manda aviso a Telegram y no toca HubSpot. Si además se cambió el
> **nombre o el rubro** de la tienda, hay que borrar la caché del buscador aparte
> (`fuzzy_olvidar_cache()`), porque si no tarda **1 hora** en reflejarse.

> 🆕 **Los filtros SE CONSERVAN al aprobar/ocultar/eliminar (2026-09-12).** El POST vuelve al panel con
> los mismos `q`, `estado`, `foto`, `prod`, `wa`, `tel`, `ubi`, `dueno`, `redes`, `dest`, `orden`, `vista` y `p`
> (lista blanca en el `header('Location: …')` al final del bloque POST de `superadmin.php`).
> Antes, cualquier acción devolvía a `?seccion=tiendas` y se perdía el filtro (y la página) donde estabas.

**Cómo aprobar desde el navegador (agente), sin sacar al jefe de su sesión:**

1. Abrir **pestaña NUEVA** (`active:false`) en `https://dechimbote.com/superadmin.php?seccion=tiendas`.
   Al ser el mismo dominio **se reutiliza la sesión del jefe**: **no** hay que iniciar sesión (entrar
   como `admin@dechimbote.com` lo **sacaría** de la suya).
2. ⚠️ **El clic real en «✓ Aprobar» no engancha**: el botón lleva
   `onclick="return confirm('¿Aprobar…?')"` y el clic del agente no llega a dispararlo
   (`hitVerified:false` y la fila sigue `pendiente`).
   **Solución medida:** enviar el **propio formulario** de la fila (es el mismo POST con el `_csrf` del
   panel):

   ```js
   // en la consola de ESA pestaña, para cada id pendiente
   [...document.querySelectorAll('form')].filter(x => {
     const a = x.querySelector('[name=accion]'), i = x.querySelector('[name=id]'), e = x.querySelector('[name=estado]');
     return a && i && e && a.value === 'tienda_estado' && e.value === 'activo' && i.value === '1566';
   })[0].submit();
   ```
3. Confirmar que la tarjeta/fila pasó a `activo` y que aparece el aviso
   *"Estado de la tienda actualizado a 🟢 Aprobado / público."*
4. **Cerrar la pestaña de pruebas** al terminar (regla de oro n.º 5).

> 💡 **Truco de agente para probar el POST sin tocar datos:** mandar `estado` con un valor **inválido**
> (p. ej. `PRUEBA_INVALIDA`) → el handler responde *"Estado inválido."* **sin escribir en la BD**, y sirve
> para comprobar que el redirect conserva los filtros. Medido el 2026-09-12.

**Antes de aprobar, revisar (orden recomendado):**

1. **Rubro**: ¿es el negocio que dice ser? (El rubro por defecto de una captura puede ser cualquiera:
   *"Los niños del futuro"* llegó como **Abogados** y es un colegio.) Criterio completo en
   `GUIA_PLANTILLAS_FICHA_Y_COLORES.md` §7.
2. **Copy**: reescribirlo con el estándar `.cz-*` si viene del dictado de Caminante (texto corrido, sin
   puntuación). Mismo §7 y §6 de esa guía.
3. **Afinidades del rubro** si se movió a un rubro con 0 filas (o la ficha sale sin el carrusel 🤝).
4. **Fotos**: si tiene 1 sola, avisar (viene del bug de Caminante del 2026-09-10, ver
   `GUIA_CAMINANTE_WEB.md` §11.1). **Sin foto la tarjeta sale con un recuadro rojo "SIN FOTO"** y se
   encuentra con el filtro **📷 Sin foto**.
5. **Contacto**: sin `telefono`/`whatsapp` la ficha no puede recibir pedidos; conviene pedírselos al dueño.
   En la vista previa se ve en rojo (**📱 sin WhatsApp · 📞 sin teléfono**).

### 4.2 🖼️ LA VISTA PREVIA Y LOS FILTROS (2026-09-12) — PEDIDO DEL JEFE

> **Pedido textual:** *"necesito tener una vista previa de las tiendas y filtros para ordenarlas por
> con foto, sin foto, con whatsapp, sin whatsapp, con productos, sin productos, etc. — filtros activos"*.

**Qué se ve, de arriba abajo:**

1. **Barra**: buscador + **🏷️ Rubro** (2026-09-20) + **Ordenar por** + **Vista previa / Tabla**. Todo en un
   solo formulario `GET` (los filtros viajan en campos ocultos, así buscar no los pierde).
2. **Filtros rápidos** (siempre visibles): 📷 Con/Sin foto · 📦 Con/Sin productos · **✅ Revisados / ⬜ Sin revisar** (2026-09-18) · 📱 Con/Sin WhatsApp ·
   **🎵 Con/Sin música** (2026-09-18, pedido del jefe: *«en el super admin agrega "Con musica"»*).
   **⚙️ Más filtros ▾** (`<details>`, se abre solo si hay uno puesto): 📞 teléfono · 📍 GPS · 👤 dueño ·
   🌐 redes sociales · ⭐ destacadas. **Cada píldora trae el número REAL de tiendas del sitio.**
   * **🎵 Con música** cuenta las tiendas cuya canción **de verdad suena** en su ficha (misma condición
     que el reproductor: fila en `directorio_canciones` con `estado = 'listo'` y con archivo). El
     2026-09-18: **59 con música · 1 649 sin música** (1 708 en total).
   * El código está en `includes/vista_tiendas_admin.php`: los pares en `sat_pares()`, el «con/sin» en
     `sat_where()` (`sat_cancion_sql()`) y los contadores en `sat_contar_global()`.
   * **A prueba de fallos:** si el módulo de las canciones no estuviera instalado, el filtro **no se
     ofrece** (`sat_canciones_ok()`) y el listado se pinta igual: ningún filtro puede tumbar el panel.
   * Se combina con todo lo demás: `superadmin.php?seccion=tiendas&mus=con&wa=con&orden=vistas` lista
     las tiendas con música **y** WhatsApp, las más vistas primero.
   * ✅ **REVISADOS (2026-09-18, pedido del jefe:** *«quiero que esta tienda tenga la opción de ingresar a
     un nuevo filtro al que llamaremos "Revisados"»***).** Es el filtro de la **revisión tienda por tienda**
     (catálogo, imágenes de producto, rubro y canción): **✅ Revisados N** · **⬜ Sin revisar N**. Al montarlo
     quedó en **1 revisada · 1 707 sin revisar** (1 708 en total).
     * **Dónde vive la marca:** columna **`directorio_negocios.revisado_en`** (DATETIME NULL; **NULL = sin
       revisar**), creada con la sonda temporal **`__rev_columna.php`** (`ALTER TABLE`, protegida por clave,
       se borra al terminar).
     * **Quién la pone:** el botón **«✅ Marcar revisada»** que va **debajo de la invitación 📨 en cada
       tarjeta** (y **«↩️ Quitar»** para deshacerla). Al marcarla, la tarjeta muestra **✅ Revisada · fecha**.
       El POST (`tienda_revisar` / `tienda_revisar_quitar`) está en **`superadmin.php`**, con CSRF y solo
       para admin; el botón de marcar **no pregunta nada** (se usa mucho) y el de quitar **sí confirma**
       (`sat_boton_post()` acepta confirmación vacía desde este cambio).
     * **Cómo se enlaza:** `superadmin.php?seccion=tiendas&rev=con` (las ya revisadas) ·
       `...&rev=sin` (las que faltan) · y se combina con todos los demás filtros.
     * **A prueba de fallos:** si la columna no existiera, el filtro **no se ofrece** y el listado se pinta
       igual (`sat_revisados_ok()`; el `SELECT` y los contadores se arman condicionalmente).
     * **Comprobado en vivo el 2026-09-18:** `rev=con` → *«Mostrando 1–1 de 1 tienda(s)»* (Laceados & Color
       Chimbote, con ✅ Revisada · 18/09/2026) y `rev=sin` → *«1–30 de 1 707 · página 1 de 57»*, sin ninguna
       marca pintada.
3. **📍 Distrito y 🛵 Cómo atiende (2026-09-20)** — una caja propia, con dos bloques de píldoras:
   * **📍 Distrito** (los **9**, alfabéticos, con su número): **Chimbote 3 250 · Nuevo Chimbote 625 ·
     Santa 383 · Coishco 275 · Samanco 28 · Nepeña 18 · Moro 12 · Cáceres del Perú 8 · Macate 7**
     (medido en vivo el 2026-09-20, 4 395 tiendas). **Pedido textual:** *«en el filtro de tiendas
     también falta que filtres por distritos, solamente estás filtrando por otros factores pero no por
     distrito… quiero ver esto pero solo para el distrito de Santa»* → `superadmin.php?seccion=tiendas&dist=3`.
   * **La regla del distrito es la MISMA que la de la búsqueda del sitio** (`buscar.php`): la tienda entra
     si **está** ahí (`n.distrito_id`) **o si ATIENDE ahí** (`directorio_negocio_cobertura`: los 🧰 servicios
     a domicilio y los que marcaron «🌎 Todos los distritos»). Por eso el filtro de Santa da 383 y no 341:
     son los mismos que ve el cliente que busca en Santa. ⚠️ Consecuencia: **la suma de los distritos pasa
     del total del sitio** (una tienda que atiende en 4 distritos cuenta en los 4) — no es un error.
   * **🛵 Cómo atiende** (el `ubicacion_tipo`): **🏪 Tiene local y ahí atiende 4 286 · 🏠 Lo lleva a la casa
     del cliente 85 · 🚚 Vende a todo el país (internet) 15 · 🛵 Ambulante (por las calles) 3 ·
     📦 Vende al por mayor 2 · ❔ Sin dato 4**. Es lo que pedía el jefe: *«vendedores que venden desde
     internet… o ambulantes»* → `...&vent=ambulante` (3 tiendas), `...&vent=nacional` (15, los de internet),
     `...&vent=domicilio` (85), `...&vent=sin_dato` (4 tiendas con el campo vacío, para arreglarlas).
   * **Se combinan entre sí y con todo lo demás:** `?seccion=tiendas&dist=3&vent=ambulante` → **1 tienda**
     (Casa del plástico, Santa) · `?seccion=tiendas&dist=3&foto=sin` → 16. Comprobado en vivo el 2026-09-20.
   * **Cómo está hecho:** `sat_distritos()` (lista + números en **una sola** consulta con `UNION ALL` +
     `COUNT(DISTINCT)`, y plan B sin cobertura si la tabla faltara) · `sat_vende()` / `sat_contar_vende()`
     (el `GROUP BY ubicacion_tipo`, con la clave de mentira `sin_dato` para el campo vacío) ·
     `sat_pildora_valor()` (píldora de **valor único**: tocarla la pone, tocarla otra vez la quita) ·
     el `WHERE` en `sat_where()` **con parámetros preparados** (un `dist=999` o un `vent` inventado que
     venga en la URL **se ignora**: no llega nunca al SQL) · `sat_vende_corto()` pinta la etiqueta.
   * **En la tarjeta y en la tabla** la tienda dice cómo atiende: en la rejilla la píldora **solo sale
     cuando NO es un local** (🏪 es lo normal en 4 286 de 4 395: así los 🛵 🏠 🚚 📦 saltan a la vista);
     en la tabla sale siempre, debajo del distrito.
   * ⚠️ **Trampa ya pisada:** los filtros nuevos hay que agregarlos a la **lista blanca del `Location:`**
     de `superadmin.php` (junto a `q`, `estado`, `foto`…): si no, al aprobar/ocultar/marcar revisada una
     tienda el panel **volvía al listado completo** y el jefe perdía el distrito que estaba mirando.
3.bis. **🏷️ RUBRO (2026-09-20)** — el desplegable de la **barra de arriba** (al costado del buscador):
   * **`…&rub=<id>`** deja SOLO ese rubro. Es un `<select>` (no píldoras) porque son **44** rubros: así no
     hay una lista eterna a la vista y el desplegable nativo **salta al rubro al escribir sus primeras
     letras** (UX predictiva). Cada opción trae **su número**: *Restaurantes (981) · Bodegas, Minimarkets y
     Supermercados (555) · Mecánicos y Llantas (238) · Tiendas de ropa (207) · Farmacias / Boticas (203) ·
     Ferreterías y Construcción (198) · Panaderías y Pastelerías (171) · Belleza y Maquillaje (166)…*
   * **La regla es la MISMA que la del sitio** (`rubro_filtro_id()`): la tienda sale por su rubro
     **principal** y también por sus **rubros extra** (`directorio_negocio_rubros`, 2026-09-12) — o sea, el
     número del panel es el mismo que ve el cliente en la página de ese rubro.
   * Se **combina con todo**: `?seccion=tiendas&rub=<Restaurantes>&dist=3` → **119** (los restaurantes de
     Santa) · `…&rub=<id>&vent=ambulante` · `…&rub=<id>&foto=sin` (los que les falta portada), etc.
   * **Cómo está hecho:** `sat_rubros()` (lista + números en **una sola** consulta con `UNION ALL` +
     `COUNT(DISTINCT)`, con el `LEFT JOIN` a `directorio_categorias`; plan B sin rubros múltiples) ·
     la condición en `sat_where()` vía **`rubro_filtro_id()`** (parámetros preparados) · un rubro que no
     exista o basura en `rub` **se ignora** (comprobado: `rub=999999` devuelve el listado completo).
   * Un rubro **apagado y sin ninguna tienda** no se ofrece en la lista; hoy **los 44 tienen tiendas**.
   * **Comprobado en vivo el 2026-09-20** con la sonda `__sat_filtro_check.php` ejecutando el propio
     include: restaurantes 981 · restaurantes de Santa 119 · `rub=999999` ignorado · 44 opciones pintadas
     en el `<select>` y su píldora en «Filtros activos».
4. **Estado**: 🗂️ Todas · ⏳ Pendientes · 🟢 Activas · 🙈 Ocultas · ⛔ Rechazadas (con sus números).
5. **Barra "Filtros activos"** (fondo ámbar): una píldora por filtro puesto con **✕** para quitarlo, y
   **🧹 Quitar todos**. Sin filtros puestos no se pinta.
6. **Resumen**: *"Mostrando 1–30 de 244 tienda(s) · 1 593 en total en el sitio · página 1 de 9"*.
7. **La leyenda del botón 📨** (2026-09-17): la barra blanca con los 4 colores de la invitación
   (⚫ sin enviar · 🟠 1 vez · 🟢 2 veces · 🔵 3 veces o más). Solo se pinta si hay resultados.
8. **Las tarjetas** (30 por página; 60 en `vista=tabla`): **rejilla de 5 columnas en PC** (1 en
   celular, 2/3/4 en tamaños intermedios, §4.4), **foto de portada SIEMPRE de la misma altura
   corta** (130 px en PC · 152 px en móvil, `object-fit:cover`), badge de estado, nombre (máximo 2
   líneas), rubro, 📍 distrito, `/slug` y los datos en píldoras — lo que **falta** va en **rojo**:
   `📦 0 prod`, `📷 0 fotos`, `📱 sin WhatsApp`, `📞 sin teléfono`, `📍 sin GPS`, `👤 sin dueño`.
   Cierra con **🙈 Ocultar / 🟢 Publicar**, **👁 Ver ficha**, **🗑** y — debajo de todo — el
   **botón 📨 de invitación** con su ↺ (§4.4).
9. **Paginación** ← Anterior · Página X de Y · Siguiente → (los filtros y el orden se conservan).

**Estados del "sin":** sin foto → recuadro rojo a rayas **SIN FOTO** (no se pide ninguna imagen);
sin WhatsApp/teléfono/GPS/dueño → píldora roja.

**Buscador predictivo (2 velocidades):**

- **Mientras escribes** (sin recargar) se **filtran al instante las tarjetas de esa página** y el resumen
  dice *"🔎 N de 30 tiendas de esta página coinciden con «…»"*.
- **Si dejas de escribir 0,8 s**, la página se **recarga sola** y busca **en TODAS las tiendas del sitio**
  (`nombre`, `slug`, rubro, distrito y **correo del dueño**), sin tildes. Al volver, el cursor queda
  **otra vez en el buscador** con el texto al final, para seguir escribiendo.
- Si el valor no cambió respecto a lo que traía la página, **no recarga** (evita el parpadeo inútil).

**Parámetros de la URL** (todos opcionales; los genera `sat_url()`, con `p` = página):

| Parámetro | Valores | Qué hace |
|---|---|---|
| `q` | texto (máx. 60) | busca en nombre, slug, rubro, distrito y correo del dueño |
| `estado` | `pendiente` · `activo` · `inactivo` · `rechazado` | estado de la tienda |
| `dist` | id del distrito (1 Chimbote · 2 Nuevo Chimbote · 3 Santa · 4 Coishco · 5 Samanco · 6 Nepeña · 7 Macate · 8 Moro · 9 Cáceres del Perú) | 🆕 **solo ese distrito** (está ahí **o** atiende ahí). Un id que no exista se ignora |
| `vent` | `fisica` · `ambulante` · `domicilio` · `nacional` · `mayorista` · `sin_dato` | 🆕 **cómo atiende** (tipo de vendedor; `nacional` = vende por internet a todo el país) |
| `rub` | id del rubro (`directorio_categorias.id`) | 🆕 **solo ese rubro** (cuenta el principal **y** los rubros extra). Un id que no exista se ignora |
| `foto` `prod` `wa` `tel` `ubi` `dueno` `redes` `dest` | `con` · `sin` | cada pareja de píldoras (tocar la puesta la quita) |
| `orden` | `recientes` (def.) · `antiguas` · `pendientes` · `nombre` · `vistas` · `fotos` · `sin_fotos` · `productos` · `sin_prod` · `actualizadas` | SQL real en `sat_orden_sql()` |
| `vista` | `previa` (def.) · `tabla` | tarjetas con foto o la tabla clásica |
| `p` | entero | página (30 por página en previa, 60 en tabla) |

**El código (todo en `deploy/includes/vista_tiendas_admin.php`, prefijo `sat_`):**

| Función | Para qué |
|---|---|
| `sat_pares()` / `sat_pares_rapidos()` | catálogo de filtros con/sin y cuáles van en "Más filtros" |
| `sat_estados()` / `sat_ordenes()` / `sat_orden_sql()` | estado, etiquetas de orden y el `ORDER BY` real |
| `sat_leer()` | **normaliza** el `$_GET` (solo entran valores válidos; sin esto la URL podría inyectar SQL) |
| `sat_distritos()` · `sat_vende()` · `sat_contar_vende()` · `sat_vende_corto()` | 🆕 (2026-09-20) los 9 distritos con sus números · los tipos de vendedor, sus conteos y la etiqueta corta |
| `sat_rubros()` | 🆕 (2026-09-20) los 44 rubros con sus números (principal + rubros extra) para el desplegable |
| `sat_pildora_valor()` | 🆕 (2026-09-20) píldora de **valor único** (distrito y cómo atiende) |
| `sat_url($f,$cambios)` | arma los enlaces conservando los filtros |
| `sat_from()` / `sat_where()` | `JOIN`s y `WHERE` con **parámetros preparados** (nunca concatenados) |
| `sat_contar_global()` | **una sola pasada** con `SUM(...)` → los números de TODAS las píldoras |
| `sat_total($f)` / `sat_listar($f,$por,$offset)` | cuántas hay con los filtros y las filas de la página |
| `sat_pildora()` / `sat_pildora_estado()` / `sat_boton_post()` | las píldoras y los botones POST |

> 📌 **Los números de las píldoras son del UNIVERSO (todo el sitio), no del filtro puesto.** Es a
> propósito: son **listas de trabajo** ("hay 296 sin foto"). El número de **resultados** del filtro actual
> está en el **resumen** ("de 244 tienda(s)").

**Medido en vivo el 2026-09-12 (sonda de solo lectura sobre la BD real, 1 593 tiendas):**
`1 297 con foto · 296 sin foto · 1 547 con productos · 46 sin productos · 527 con WhatsApp · 1 066 sin
WhatsApp · 528 con teléfono · 1 481 con GPS · 112 sin GPS · 62 con dueño · 1 531 sin dueño · 23 con redes ·
34 destacadas · 1 592 activas · 1 inactiva · 0 pendientes · 0 rechazadas`.

> ⚠️ **Rendimiento:** la página hace **2 consultas de conteo** (la global y la del filtro) y **1 del
> listado**. Las píldoras **no** usan conteos contextuales a propósito (8 consultas más por página): si
> algún día se quiere "cuántas quedarían si toco esta píldora", hay que medir antes en el hosting.

**Trampas ya pisadas (no repetirlas):**

1. **No reusar el `<style>` de `superadmin.php` para la sección**: el include trae su propio `<style>`
   (igual que Estadísticas y Banners) para no engordar el bloque global.
2. **La píldora "Todas" no lleva ✕**: no es un filtro puesto. Se arregló en la 2.ª pasada.
3. **El `<form method="post">` sin `action`** de cada botón envía el **query string** de la URL, y por eso
   el handler conserva los filtros sin tocar los formularios: **si algún día se les pone `action`, los
   filtros se pierden**.
4. **`LIMIT`/`OFFSET` se inyectan como `(int)`** (MySQL no acepta `LIMIT ?` con `execute()` en modo
   emulación apagada de forma fiable): nunca vienen de la URL sin castear.
5. **Bajar el sitio entero NO hace falta** para trabajar esto: el listado se armó con una **sonda**
   (`__sonda_tiendas.py`, patrón de `GUIA_DESPLIEGUE_Y_ENTORNO.md` §3.1) que se borra sola del servidor.

### 4.3 🏆 RÉCORDS DEL SITIO (2026-09-13) — PEDIDO DEL JEFE

> **Pedido textual:** *«en mi panel de super admin muéstrame los records de más vistos, más buscados,
> más botones de pedir pedidos y otras cosas que consideres interesantes para mi crecimiento de mi
> proyecto y encontrar personas dispuestas a invertir en mi sitio»*.

**Qué es.** 🔗 **Ya NO es una pestaña aparte (2026-09-13, noche).** El jefe pidió fusionarla con
📈 Estadísticas: *«puedes fusionar de manera inteligente records y estadísticas **conservando el diseño de
estadísticas** pero agregando también los datos de record de manera útil y fluida para tener una mejor
perspectiva del sitio»*. Hoy es la **mitad del negocio** de la pestaña **📈 Estadísticas y récords**
(`superadmin.php?seccion=estadisticas`; **`?seccion=records` es un alias** que abre la misma página, para
no romper enlaces ni marcadores).

**Cómo está montado** (respetar esta separación al tocarlo):

| Archivo | Papel |
|---|---|
| `deploy/includes/vista_estadisticas_admin.php` | **Dueña de la página**: `<style>` (`st-*`), título, selector de rango, las 5 secciones (`.st-sep`), los gráficos y el **📣 resumen copiable** |
| `deploy/includes/vista_records_admin.php` | **Partial** con los bloques del negocio (💼 El negocio). **NO** trae `<style>`, **NO** trae `<h2>` y **NO** trae el resumen: se viste con las clases del padre y recibe el rango en **`$rec_dias`** |
| `deploy/includes/metricas.php` | **Motor**: tablas, registro, consultas, siembra y `metricas_resumen_inversionista()` (el texto del resumen vive aquí, no en la vista, para que el futuro aviso semanal por Telegram mande el mismo) |

**Lo que trae:** los 7 números grandes (con 💰 soles), el **embudo** (visitas → búsquedas → fichas →
pedidos), los récords (día, hora punta, día de la semana), 👁️ **tiendas más vistas**, 🔥 **tiendas con más
pedidos**, 📦 productos más pedidos, 🔍 **lo más buscado**, 🗳️ **lo que buscan y NO hay**, 🗺️ rubros
(demanda contra oferta), 🎯 conversión, 📍 distritos y 💤 tiendas que nadie miró.

**Dos acciones POST propias** (añadidas al handler del panel, junto a `stats_instalar` / `stats_limpiar`):

| Acción | Qué hace |
|---|---|
| `metricas_instalar` | Crea las **2 tablas nuevas** (`directorio_busquedas`, `directorio_pedidos`) sin subir migradores (el antivirus los bloquea) |
| `metricas_sembrar` | 🧺 **Trae el histórico** del registro de avisos del Telegram (idempotente por origen) |

**Rango por URL:** `&rango=1|2|3|5|7|15|30|60|90|365` (**Hoy (24 h) · 2 días (48 h) · 3 · 5 · 7 · 15 ·
30 · 60 · 90 días · 1 año** — escalera del 2026-09-18; cualquier otro valor cae en **7 días**), el mismo
control que ya usaba Estadísticas y que ahora **manda sobre las dos mitades**. ⚠️ El partial recibe el
rango **en días** por `$rec_dias` (el padre hace `$rec_dias = $rango`): **no** use
`metricas_dias($clave)` ahí — devuelve 30 para cualquier clave que no esté en su catálogo y el panel
mostraría un periodo distinto del que dice el título (trampa pisada el 2026-09-13).
⚠️ `per` sigue en la lista blanca del redirect del POST junto a `q`, `estado`, `p`…

⚠️ **La medición NO vive en el panel**: vive en `buscar.php`, `includes/chatbot.php` y `api/lead.php`
(§3 de la guía del módulo). Si se toca `api/lead.php` hay que recordar que **cada botón de pedir de todo
el sitio pasa por ahí**.

## 4.4 📨 LA REJILLA DE 5 COLUMNAS Y EL BOTÓN DE INVITACIÓN (2026-09-17) — PEDIDO DEL JEFE

> **Pedido textual:** *«todas las imágenes de tiendas en este panel deben tener la misma altura…
> altura corta, en modo PC muéstralo en 5 columnas y debajo de cada tienda pon un botón de
> invitación»*. El botón trabaja con **4 colores** (⚫ negro = no se le mandó ninguna invitación ·
> 🟠 naranja = 1 vez · 🟢 verde = 2 veces · 🔵 azul = 3 veces) y manda **al WhatsApp de la propia
> tienda** este mensaje:

```text
Hola "Decoraciones Chimbote" 👋, tenemos activo una tienda para ti. https://dechimbote.com/neg/decoraciones-chimbote

Puedes editarlo, borrarlo.
Tus clientes te escriben a: 977462032

Usuario: 977462032
Contraseña: abc8
```

> 🆕 **Segunda parte del pedido (mismo día):** *«en el primer mensaje también se debe incluir el
> usuario y su contraseña · Usuario: numero de telefono · Contraseña: 3 letras y un número (ej.
> abc8), y a fin de evitar errores no usaremos el 0 ni el o»* → las dos últimas líneas. **No es
> texto decorativo: es la cuenta de verdad de la tienda** (§4.4.5).
>
> 📖 **Guía completa de este módulo: `GUIA_INVITACIONES_A_NEGOCIOS.md`** — trae el detalle, los
> **13 problemas que aparecieron y cómo se resolvieron**, el censo de dueños, la receta de prueba
> con sondas y los **usos a futuro**. Lo de abajo (§4.4.1 a §4.4.5) es el resumen del panel.

### 4.4.1 La rejilla y las fotos (CSS, `includes/vista_tiendas_admin.php`)

| Qué | Cómo quedó |
|---|---|
| Columnas | `1` (celular) · `2` (≥470 px) · `3` (≥700 px) · `4` (≥860 px) · **`5` en PC (≥980 px)** |
| Alto de la foto | **`--sati-foto`**: `152px` por defecto y **`130px` en PC** — igual para TODAS, con `object-fit:cover` (recorta parejo, así una foto vertical y una apaisada ocupan el mismo alto) |
| Nombre | Máximo **2 líneas** (`-webkit-line-clamp:2` + `min-height:2.4em`) para que las tarjetas de una fila midan lo mismo |
| Botones | En PC se compactan (`.sati-card__cuerpo`, `.sati-dato`, `.sati-acciones`) para que quepan en ~226 px |

> 🔴 **El ancho útil del panel tiene TOPE y hay que tenerlo en cuenta:** `<main>` mide **1200 px**
> (`--max-ancho` en `assets/css/base.css`) y le deja **1168 px** al `.sa-wrap` (16 px de relleno por
> lado). O sea: en **cualquier** PC ancha la rejilla recibe **siempre 1168 px** → 5 columnas de
> **~226 px**. Por eso el corte de 5 columnas se puso en **980 px** (no en 1024): así también salen 5
> columnas con la ventana sin maximizar. ⚠️ Un `.sati-card__foto` con `aspect-ratio` **no** sirve
> aquí: el alto dependería del ancho de la columna y las fotos quedarían de alturas distintas.

### 4.4.2 El botón 📨 y sus 4 colores

- Va **debajo de la tienda**, en su propia fila (`.sati-inv-fila`, con una línea de puntos arriba),
  en la tarjeta **y también en `vista=tabla`** (ahí comparte la celda de acciones).
- Abre **WhatsApp con el mensaje ya escrito** (la invitación **+ el usuario y la contraseña**,
  §4.4.5) al número de la tienda. Hay **dos caminos**, y los distingue el atributo `data-listo`:
  - **`data-listo="1"`** (las credenciales ya están guardadas): el `href` viene armado desde el
    servidor → es un **enlace normal** (`target="_blank"`), se abre al instante y el envío se apunta
    de fondo con un `fetch`.
  - **`data-listo="0"`** (primera invitación de esa tienda): hay que **crearle la cuenta y la
    contraseña**, así que el clic **abre una pestaña en blanco DENTRO del gesto** (`window.open`,
    si no el navegador la bloquea) y, cuando el servidor responde con el enlace, esa pestaña se va a
    WhatsApp. Después el `href` queda armado y el siguiente clic ya es instantáneo.
- El **color** lo decide `invitacion_nivel($n)` → clase `.sati-inv--n0|n1|n2|n3`
  (⚫ `#111827` · 🟠 `#ea580c` · 🟢 `#15803d` · 🔵 `#1d4ed8`). El número de envíos se ve en el
  globito del propio botón y, al pasar el mouse, el `title` dice cuántas veces, **cuándo** fue el
  último envío y **con qué usuario y contraseña** va el mensaje.
- Al costado va el **↺** (`.sati-inv__undo`): quita la **última** invitación apuntada, con
  confirmación, por si el jefe tocó de más. Está **deshabilitado** mientras el conteo sea 0.
  ⚠️ **El ↺ solo baja el contador: NO borra la cuenta ni la contraseña** (a propósito: si vuelve a
  invitar, el mensaje sale con **la misma clave**, no con una nueva).
- **Tienda sin WhatsApp ni teléfono:** el botón sale **apagado en rojo** («📨 Sin número») y **no
  enlaza** — nunca se abre un chat en blanco (regla del sitio). En la página 1 del panel hay **3**
  tiendas así.

### 4.4.3 Dónde se guarda (tablas `directorio_invitaciones` y `directorio_invitacion_claves`)

**Una fila = una invitación enviada** (así hay historial y el ↺ puede deshacer):

| Columna | Para qué |
|---|---|
| `id` | autonumérico (el ↺ borra el último de esa tienda: `ORDER BY id DESC LIMIT 1`) |
| `negocio_id` | la tienda (`INDEX (negocio_id, enviado_en)`) |
| `admin_id` | quién la mandó (`NULL` si fue una sonda) |
| `canal` | `whatsapp` |
| `enviado_en` | fecha y hora (es lo que sale en el `title` del botón) |

Y **la otra tabla, la de las credenciales** (`directorio_invitacion_claves`, una fila por tienda:
`negocio_id` PK · `usuario_id` · `usuario` · `clave` · `creado_en`): es lo que permite que el envío
número 2 y 3 lleven **el mismo usuario y la misma contraseña** — el detalle, en **§4.4.5**.

- La tabla **se crea sola** (`CREATE TABLE IF NOT EXISTS`) la primera vez que hace falta:
  **no hay que subir ningún migrador** (el antivirus del hosting devuelve 404 a los `migrar_*.php`).
- **A prueba de fallos, y esto es importante:** el conteo va en una **consulta aparte** del listado
  (`invitaciones_mapa()`, un `IN (...)` con los 30 ids de la página, todos casteados a `int`) y el
  `try/catch` **reintenta creando la tabla**; si aun así falla, devuelve `[]` y las tarjetas se pintan
  igual con el contador en 0. **La pestaña 🏬 Tiendas no se puede quedar en blanco por este módulo.**
- **Acciones POST nuevas** en `superadmin.php` (junto a `tienda_estado`):
  `tienda_invitar` y `tienda_invitar_deshacer`. Si la petición trae `X-Requested-With: XMLHttpRequest`
  (el `fetch` de la tarjeta) responden **JSON** (`{ok, n, msg}`) y salen **antes** del
  `flash()`/redirect, para no dejar un aviso raro en la pantalla siguiente. Los filtros de la URL se
  conservan solos porque el formulario oculto **no lleva `action`** (igual que los demás botones).
- Y **`eliminar_negocio_completo()` borra también sus invitaciones** (con `try/catch`, porque la
  tabla puede no existir todavía): al borrar una tienda no quedan filas huérfanas.

### 4.4.4 Cómo se probó (sin abrir el navegador — Regla de Oro n.º 5)

🔧 Sonda temporal **`__inv_verificar.php`** (raíz de `D:\RELAX`, se sube, se lee y se **borra** del
servidor): captura el HTML de la vista con `ob_start()` y devuelve un JSON con lo que hay que
comprobar; con `&go=1` además hace la **ida y vuelta completa en los DOS caminos reales** (tienda
sin dueño y tienda cuyo dueño es el administrador): invita, comprueba que la clave tenga el formato
correcto, **que `password_verify()` la acepte** (o sea que el dueño SÍ pueda entrar), que el segundo
envío repita la MISMA clave, que el botón quede armado y que el ↺ deshaga; y al final **borra todo
lo que creó** (cuentas incluidas) y devuelve `dueno_id` a su valor original.

```bash
python __sonda_run.py __inv_verificar.php inv-revert-2026-9kQ7 x     # solo lectura (crea las tablas)
python __sonda_run.py __inv_verificar.php inv-revert-2026-9kQ7 go    # + ida y vuelta + limpieza
python __sonda_run.py __inv_dueno.php      inv-revert-2026-9kQ7 x    # censo de dueños (solo lectura)
python __subir_uno.py includes/invitaciones.php                      # subir el motor
python __subir_uno.py includes/vista_tiendas_admin.php               # subir la vista
python __subir_uno.py superadmin.php                                 # subir las acciones POST
```

**Medido en vivo el 2026-09-17** (página 1 del panel, 1 706 tiendas): `html_bytes 133 700` ·
**30 tarjetas** · **30 botones `--n0`** · **3 «sin número»** · leyenda, formulario y JS presentes ·
`--sati-foto` con alto fijo y `repeat(5,minmax(0,1fr))` en el CSS · envío 1→2→3→4 con niveles
🟠🟢🔵🔵 · ↺ 3→2→1→0 · `filas_despues = 0` (quedó limpio) · panel `302` · `/includes/…` `403` ·
sondas borradas `404`.

> ✅ **Un solo color por tienda y sin estados intermedios:** el azul es **3 o más** (un 4.º envío
> sigue azul); el ↺ va bajando de a uno.

### 4.4.5 🔑 EL USUARIO Y LA CONTRASEÑA DEL MENSAJE (segunda parte del pedido, 2026-09-17)

> **Pedido textual:** *«en el primer mensaje también se debe incluir el usuario y su contraseña ·
> Usuario: numero de telefono · Contraseña: 3 letras y un número ejemplo abc8, a fin de evitar
> errores no usaremos el 0 ni el o»*.

**Las dos líneas del mensaje son una cuenta REAL**, no texto decorativo. Para que lo sean, la
tienda necesita una cuenta de dueño, y **el censo del 2026-09-17** (sonda `__inv_dueno.php`) dice:
**1 706 tiendas · 1 531 sin dueño · 172 con dueño = el ADMINISTRADOR · 3 con dueño de verdad**
(en total hay **13 usuarios**: 4 `dueno` y 2 `admin`). Lo que se decidió con el jefe ese día:

| Caso | Qué se hace al tocar 📨 por primera vez |
|---|---|
| **Tienda sin dueño** (1 531) | Se le **crea la cuenta** (usuario = su número, contraseña de 3 letras + 1 número) y **la tienda queda a su nombre** (`dueno_id`) |
| **«Dueño» = administrador** (172) | Igual que el anterior: el administrador **no es un dueño de verdad** (es el que las cargó) y a una cuenta de admin **no se le puede cambiar la clave**, así que se le crea la cuenta al número de la tienda. `dueno_id` pasa del admin al dueño nuevo (el jefe sigue manejando esa tienda desde el Súper Admin) |
| **Dueño de verdad** (3) | Se le da una **clave NUEVA** a ESA cuenta (`clave_restablecer()`: cierra sus sesiones abiertas) y el mensaje lleva **su** usuario (su teléfono) |
| **El número es el del admin** (p. ej. las 484 tiendas sin número propio llevan hoy el **908785164**, el número del administrador desde el **2026-09-19**; el 955041690 es hoy su número **personal** y quedó en `ADMIN_WHATSAPP_VIEJOS`) | 🔒 No se toca nada: `clave_restablecer()` se niega con las cuentas de administrador y **la invitación sale sin las 2 líneas**, con un aviso en pantalla al jefe |

**De dónde sale la contraseña:** de **`clave_generar()`** (`includes/clave_recuperar.php`), la MISMA
que usa «El maestro» 🛠️ y el botón «🔑 Restablecer contraseña» de 👥 Usuarios: **3 letras + 1 número
mezclados, sin la `O` ni el `0`** (constantes `TIENDA_IA_CLAVE_LETRAS` / `TIENDA_IA_CLAVE_NUMEROS`).
Así el dueño reconoce el formato de siempre.

**El usuario es el teléfono… bien normalizado:** se toman los **últimos 9 dígitos** cuando el número
viene con el 51 delante (`+51 934 274 553` → `934274553`). ⚠️ **Esto no es un detalle:** `login()`
compara el número **tal como se escribe**, así que una cuenta guardada como `51934274553` **no
entraría** escribiendo `934274553`. La cuenta se crea con la convención de El maestro
(`correo interno <numero>@dechimbote.com`, `tipo='dueno'`, `activo=1`).

**La clave se guarda TAL CUAL en `directorio_invitacion_claves`** (una fila por tienda:
`negocio_id` PK · `usuario_id` · `usuario` · `clave` · `creado_en`). Es a propósito y es necesario:
en `directorio_usuarios` solo queda el **hash bcrypt** (irreversible) y la invitación se puede
mandar 2 o 3 veces — si se generara otra clave en cada envío, al dueño le llegarían claves distintas
y la anterior dejaría de servir. **La clave se genera UNA vez por tienda y se repite en todos los
envíos**; el ↺ del conteo **no** la borra.

> 🔒 **Tabla de solo-panel:** guarda claves en claro y por eso es del Súper Admin (`/includes/`
> está bloqueado por `.htaccess` con `Redirect403`; la tabla no se expone por ninguna API).

## 4.5 👥 USUARIOS — ➕ CREAR USUARIO (2026-09-21, orden del jefe: «crea usuarios»)

**El problema:** la pestaña 👥 Usuarios **solo administraba** las cuentas que ya existían (⭐ Premium ·
⏸ Suspender · 🔑 Restablecer contraseña · 🗑 Eliminar). Las cuentas nacían **solas**, por 4 puertas:
`registro.php`, `google_callback.php`, la invitación de tienda (`includes/invitaciones.php`) y El maestro 🛠️
(`includes/tienda_ia.php`). El jefe no tenía **ninguna** puerta para crearlas a mano.

**Lo que se agregó** (todo en `deploy/superadmin.php`, sección `usuarios`):
- Acción POST **`usuario_crear`** + un formulario arriba de la tabla (Nombre · Correo · WhatsApp · Rol ·
  Contraseña · casilla «Puede entrar ya»).
- **Se entra con el correo O con el número de WhatsApp** (igual que `login()`): si se deja el correo en
  blanco y hay teléfono, el correo interno es `<numero>@dechimbote.com` (el formato de las cuentas de tienda).
- **La clave:** si el jefe la escribe se usa esa; si la deja en blanco se genera con `clave_generar()`
  (3 letras + 1 número, como El maestro) y **se muestra UNA sola vez** con el mismo cartel de
  «Restablecer contraseña» (la clave `nuevo => true` cambia el texto del cartel a «➕ Cuenta creada») más el
  mensaje listo para copiar y el botón verde de WhatsApp.
- Avisa al jefe por `aviso('usuario_nuevo', …)`, como las otras altas.
- Roles: `cliente` · `dueno` · `admin` (es exactamente el `enum` de `directorio_usuarios.tipo`).

### 🎭 CAMBIAR EL ROL DESPUÉS (2026-09-21, orden del jefe: «cambiale el rol a admin»)

El rol **antes solo se podía poner al crear la cuenta**: no había ninguna forma de cambiarlo después.
Ahora **cada fila de la tabla trae su selector** (cliente · dueño · admin) + botón **🎭 Cambiar rol**
(acción POST **`usuario_rol`**, en la columna **Rol**).

- 🛡️ **Red de seguridad:** al **último administrador activo** no se le puede quitar el rol (el selector
  se oculta y el servidor lo rechaza con un aviso): si no, **nadie podría volver a entrar al Súper Admin**.
  Para dejarlo sin rol hay que crear antes otro admin. El conteo vive en `$admins_activos`.
- Para cambiar un rol **sin abrir el navegador** (o desde fuera) está la sonda temporal **`__us_rol.php`**:
  `python __sonda_run.py __us_rol.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&email=<correo>&tipo=admin"` → devuelve
  el antes y el después y cuántos admins activos quedan (se borra del servidor en el mismo paso).

### 🔴 LA TRAMPA QUE COSTÓ UN ERROR 500 (leer antes de escribir consultas nuevas)

La primera versión comprobaba el duplicado con **un solo SQL**:
`... WHERE email = ? OR (? <> '' AND telefono = ?)`. En el hosting eso **revienta**:

```text
SQLSTATE[HY000]: General error: 1267 Illegal mix of collations
(utf8mb4_general_ci,COERCIBLE) and (utf8mb4_unicode_ci,COERCIBLE) for operation '<>'
```

MariaDB no deja comparar **dos literales/parámetros** con collations distintas (el parámetro vacío contra
`''`), la `PDOException` **no estaba en try/catch** y la página se caía con **500**. Es la **misma trampa**
ya anotada en `includes/telegram_subs.php` (allí: «el nombre se decide en PHP, NO con `IF(?, '', …)`»).

**Regla:** **ninguna comparación entre parámetros o literales dentro del SQL**; la decisión se toma en
**PHP** (primero la consulta por `telefono`; si no hay resultado, la consulta por `email`) y **toda consulta
de apoyo va en `try/catch`** para que no pueda tumbar el alta.

**Cómo se encontró sin ver los logs:** sonda temporal **`__us_diag.php`** —
`python __sonda_run.py __us_diag.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x ""` — que imprime `SHOW COLUMNS` de la tabla y
prueba **cada pieza por separado**: la consulta del duplicado, el INSERT completo y el INSERT sin `telefono`
(**dentro de una transacción que se deshace**, así no deja basura) y las funciones del módulo de claves.
El informe queda en `__us_diag_resultado.json`.

**Probado de verdad (2026-09-21):** cuenta inventada **«Prueba IA Chimbote» /
`prueba.ia.20260921@test.com` / clave `Prueba2026`** → salió el cartel «➕ Cuenta creada» y el **login quedó
comprobado** (POST a `/login.php` → **302**, y `/panel.php` → **200** con esa sesión).

---

## 5) CÓMO AGREGAR UNA SECCIÓN (PESTAÑA) NUEVA

1. **Menú:** agregar una `<a>` dentro de `.sa-nav` en `deploy/superadmin.php`, con su icono, su texto y `href="<?= url('superadmin.php?seccion=<nombre>') ?>"`, y la clase `activo` cuando `$seccion === '<nombre>'`. No hay que tocar el CSS: la cuadrícula abre fila sola.
2. **Contenido:** añadir el bloque de la sección en la misma página **o —mejor— requerir su include**, como
   se hizo con `includes/vista_estadisticas_admin.php` (Estadísticas), `includes/vista_banners_admin.php`
   (Banners) y **`includes/vista_tiendas_admin.php` (🏬 Tiendas, 2026-09-12)**. El include trae su propio
   `<style>` y, si tiene datos que cargar, los carga él: deja `superadmin.php` como simple enrutador.
3. **Globito de pendientes (opcional):** añadir `<span class="n">N</span>` dentro del enlace; el CSS lo empuja a la derecha del botón.
4. **Acciones del panel (opcional):** las pestañas pueden manejar acciones POST propias; Estadísticas usa `stats_instalar` y `stats_limpiar`.
5. **Filtros de la sección (opcional):** si la sección va a llevar filtros, **pásalos por la URL** (GET) y
   añádelos a la **lista blanca del redirect del POST** (al final del bloque POST de `superadmin.php`)
   para que actuar no devuelva al usuario a la vista sin filtros.
6. **Documentar:** actualizar esta guía y sumar la pestaña a la tabla de §4.

## 6) `.sa-table-wrap`: LAS TABLAS ANCHAS NO ROMPEN LA PÁGINA

- `.sa-table-wrap` **no existía en el CSS** aunque ya se usaba en Tiendas, Productos, Usuarios, ~~Tablones~~ (🗑️ retirada el 2026-09-13) y Banners.
- Ahora está definido con `overflow-x:auto` (+ `-webkit-overflow-scrolling:touch`), así que las tablas anchas hacen **scroll horizontal dentro de su caja** en vez de romper la página.

## 7) CÓMO VERIFICAR UN CAMBIO DEL PANEL ANTES Y DESPUÉS DE SUBIR

> 🔓 **Actualizado el 2026-09-12 (orden del jefe):** **el hosting es de uso exclusivo de la IA** — el jefe
> nunca sube, edita ni renombra nada a mano. Por eso **no se respalda el archivo vivo** y **no se compara
> el local con el hosting** (ni md5, ni tamaños): el local es la verdad y el despliegue son **3 pasos**.

1. Editar y luego **`php -l`** sobre el archivo (el `php` de Windows no está en el PATH: usar `C:\xampp\php\php.exe`).
2. Subir **solo** el archivo modificado (un archivo por vez: `python __subir_uno.py <ruta relativa>`).
3. **Verificar por HTTP:** `superadmin.php?seccion=resumen` debe responder **302** (redirige a login si no
   hay sesión); un **500** delataría un error de PHP. Un include nuevo (`includes/vista_*.php`) responde
   **403** si se pide directo (el hosting protege `/includes/`): es lo correcto, no un error.
4. **Prueba visual real en pestaña NUEVA** (`active:false`, sin tocar las pestañas del jefe): al abrir el mismo dominio se reutilizan sus cookies de sesión, así que **no hay que iniciar sesión otra vez** (entrar como `admin@dechimbote.com` **sacaría al jefe de su sesión**). Comprobar que se ven todos los botones y que la pestaña queda `activo`. **Cerrar la pestaña de pruebas al terminar.**

> ⚠️ **Actualizado el 2026-09-12:** el paso de **md5 quedó eliminado** por orden del jefe (el local es la
> fuente de verdad, `REGLAS_DE_ORO_PROYECTO.md` n.º 3), y **tampoco se hace respaldo del vivo** (Regla
> n.º 3 reescrita ese mismo día: **el hosting es de uso exclusivo de la IA**). El seguro es `php -l` +
> subir solo lo cambiado + verificación por HTTP.

### 7.1 🧪 PROBAR UNA SECCIÓN SIN PISAR EL VIVO (patrón del 2026-09-12)

Para una sección nueva o muy tocada (como la vista previa de tiendas) se puede **probar antes** sin tocar el panel:

1. Se sube el **include nuevo** (`includes/vista_tiendas_admin.php`) — es inofensivo mientras nadie lo
   requiera — y una **página de prueba temporal** en la raíz (`__pv_tiendas.php`, **fuera de `deploy/`**)
   que hace `require` del include con `requiere_login()` + `es_admin()`.
2. Se abre esa página en **pestaña nueva** (misma sesión del jefe) y se prueba TODO: filtros por URL,
   `vista=tabla`, el buscador, la paginación.
3. Se **borra la página de prueba del servidor** (y se comprueba que responde **404**).
4. Recién entonces se sube `superadmin.php` y se verifica la sección real.
5. Los filtros se prueban **por URL** (`?wa=sin&foto=sin&orden=vistas`) y luego se hace **un POST
   inocuo** (`estado` inválido → *"Estado inválido."*, sin escribir en la BD) para comprobar que tras
   actuar **no se pierden los filtros ni la página**.

> 🔧 Script de la sesión: `__sat_desplegar.py` (subir include + página de prueba / borrar prueba / subir el
> panel / estado). La sonda de datos de solo lectura: `__sonda_tiendas.py` (§3.1 de la guía de despliegue).
> 🔓 Su modo `respaldo` **se eliminó** al recibir la orden del 2026-09-12 y ahora **falla a propósito** si
> alguien lo llama.

## 8) ARCHIVO AFECTADO Y PATRÓN A REPETIR

| Archivo | Cambio |
|---|---|
| `deploy/superadmin.php` | CSS: `.sa-wrap` a ancho completo, `.sa-nav` en cuadrícula responsiva y `.sa-table-wrap` definido. En vivo |
| `deploy/superadmin.php` (2026-09-12) | Sección `tiendas`: ahora hace `require` del include nuevo; `.b-oculto` para el badge "oculto"; el **redirect del POST conserva los filtros** (lista blanca de claves GET). En vivo |
| `deploy/includes/vista_tiendas_admin.php` (**nuevo**, 2026-09-12) | **Vista previa de tiendas con foto + filtros + orden + buscador predictivo + vista tabla.** Funciones `sat_*`. En vivo |
| `deploy/includes/vista_tiendas_admin.php` (2026-09-17) | **Rejilla de 5 columnas en PC**, **fotos con la misma altura corta** (`--sati-foto`, 130 px en PC / 152 px en móvil), nombre a 2 líneas, **leyenda de los 4 colores**, **botón 📨 de invitación + ↺** por tienda (tarjeta y tabla), formulario oculto `#sati-inv-form` y el JS que apunta el envío. En vivo |
| `deploy/includes/invitaciones.php` (**nuevo**, 2026-09-17) | **📨 Motor de las invitaciones por WhatsApp**: tablas `directorio_invitaciones` (una fila por envío) y `directorio_invitacion_claves` (**usuario + contraseña** por tienda), que se crean solas; `invitaciones_mapa()` (conteo de la página), `invitacion_credenciales()` (crea la cuenta de la tienda o le da clave nueva), `invitacion_enviar()` / `invitacion_deshacer()`, `invitacion_mensaje()` / `invitacion_url()` y `invitacion_nivel()` (los 4 colores). Funciones `invitacion*`. En vivo |
| `deploy/superadmin.php` (2026-09-17) | Acciones POST **`tienda_invitar`** y **`tienda_invitar_deshacer`** (responden **JSON** al `fetch` de la tarjeta) + `require_once includes/invitaciones.php`. En vivo |
| `deploy/includes/metricas.php` (**nuevo**, 2026-09-13) | **Motor de los 🏆 Récords**: tablas `directorio_busquedas` y `directorio_pedidos`, registro de búsquedas y pedidos, consultas del tablero, siembra del histórico y **`metricas_resumen_inversionista()`**. En vivo |
| `deploy/includes/vista_records_admin.php` (**nuevo**, 2026-09-13) | **Partial de los bloques del negocio** (💼 El negocio de la pestaña fusionada). Prefijo `rec_` en las funciones. **Sin `<style>` propio** desde la fusión. En vivo |
| `deploy/includes/vista_estadisticas_admin.php` (2026-09-13) | **Dueña de la página fusionada** 📈 Estadísticas y récords: 5 secciones (`.st-sep`), rango único Hoy/7/14/30/60/90/1 año, `require` de los bloques de récords y 📣 resumen copiable. En vivo |
| `deploy/superadmin.php` (2026-09-13) | Menú con **una sola** pestaña **📈 Estadísticas y récords** (`in_array($seccion, ['estadisticas','records'])` marca el activo); `records` queda como **alias** en `$secciones_validas` y en el enrutado; acciones `metricas_instalar` / `metricas_sembrar`; **`per` en la lista blanca del redirect**. En vivo |

- **Patrón seguro para filas de botones que crecen:** `grid` + `repeat(auto-fill,minmax(Npx,1fr))`. **Nunca** `flex` con `overflow-x:auto` y scrollbar oculta: esconde botones sin que nadie lo note.
- **Patrón para secciones grandes del panel:** una **sección = un include** (`includes/vista_*_admin.php`) con su propio `<style>` y sus funciones prefijadas. `superadmin.php` solo enruta.
- **Patrón para listados con filtros:** filtros **en la URL** (compartibles y sin JS), `WHERE` con **parámetros preparados**, conteos en **una sola pasada** con `SUM(...)`, y el `ORDER BY` saliendo de una **lista blanca** (`sat_orden_sql()`), nunca de la URL cruda.

---

> **Guías base del proyecto:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (qué es el sitio, mapa de archivos,
> estado real de cada módulo y qué hacer después) · `REGLAS_DE_ORO_PROYECTO.md` (reglas del jefe,
> canónicas) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar sin romper el sitio).


_Última revisión: **2026-09-13** (**📈 Estadísticas y 🏆 Récords fusionadas en UNA pestaña**: §4 tabla de
las 11 pestañas y §4.3 reescrito con el reparto de papeles entre la vista dueña y el partial.
Guía del módulo: `GUIA_RECORDS_DEL_SITIO.md`)._
_2026-09-12: pestaña 🏬 Tiendas **reescrita entera** (§4.1 acciones y handlers,
§4.2 **vista previa con foto + filtros con/sin + orden + buscador predictivo**, §7 sin md5 y §7.1
"probar sin pisar el vivo")._
_2026-09-10: consolidación de guías + §4.1 (aprobación de negocios pendientes)._
