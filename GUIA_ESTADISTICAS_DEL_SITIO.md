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


# GUÍA — MÓDULO DE ESTADÍSTICAS DEL SITIO (SÚPER ADMIN) · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> 🆕 **FUSIONADO CON LOS 🏆 RÉCORDS (2026-09-13, pedido del jefe):** la pestaña **📈 Estadísticas** y la
> pestaña **🏆 Récords** eran dos. **Hoy es UNA**: **📈 Estadísticas y récords**
> (`superadmin.php?seccion=estadisticas`; `?seccion=records` es un **alias** que abre la misma página).
> El diseño que manda es **el de este módulo** (`st-*` + las tarjetas `sa-stat`), y en medio se incrustan
> los bloques del negocio (`includes/vista_records_admin.php`, que ya **no** trae CSS propio).
> El **rango es uno solo** para todo (`&rango=`: **Hoy (24 h) · 2 días (48 h) · 3 · 5 · 7 · 15 · 30 · 60 ·
> 90 días · 1 año** — escalera del **2026-09-18**, ver §1) y manda sobre las
> dos mitades. Los separadores de sección (`.st-sep`) son lo único que se añadió al diseño.
> **Guía de la mitad del negocio: `GUIA_RECORDS_DEL_SITIO.md`.**

> **Cuándo leer esta guía:** antes de tocar el tracking propio del sitio, la pestaña 📈 Estadísticas del Súper Admin o sus tablas.
> **Archivos que toca:** `deploy/migrar_estadisticas.php` · `deploy/api/estadisticas.php` · `deploy/includes/estadisticas.php` · `deploy/includes/vista_estadisticas_admin.php` · `deploy/assets/js/estadisticas.js` · `deploy/includes/header.php` · `deploy/includes/footer.php` · `deploy/superadmin.php`
> **Estado:** ✅ **EN PRODUCCIÓN Y MIDIENDO** (verificado en vivo el 2026-09-10: la portada carga
> `assets/js/estadisticas.js?v=1`, `api/estadisticas.php` responde, y la pestaña del panel muestra datos
> reales — "3 en línea ahora", "12 páginas vistas hoy", "11 visitas hoy").
> ⚠️ Ojo con el histórico: una comprobación previa del esquema (hecha por script desde fuera) creyó que
> las tablas no existían; **sí existen** y el módulo mide. No volver a documentarlo como "pendiente".
> 🆕 **Módulo hermano (2026-09-13): `GUIA_RECORDS_DEL_SITIO.md` (mitad 🏆 Récords de ESTA misma
> pestaña).** Este módulo dice *cuánta gente entra y por dónde*; aquel dice *quién gana, qué piden y
> cuánto dinero mueve el sitio* (más vistos, más buscados, más pedidos). **Reutiliza estas mismas tablas**
> y estas funciones (`stats_es_bot()`, `stats_dispositivo()`, `stats_q()`, `stats_duracion_txt()`), y
> respeta la misma exclusión del admin (`STATS_EXCLUIR_ADMIN`).

> Regla de Oro de UX: ver REGLAS_DE_ORO_PROYECTO.md

## 0) LO ESENCIAL EN 30 SEGUNDOS

- Es una pestaña **📈 Estadísticas** dentro del Súper Admin (`superadmin.php?seccion=estadisticas`) con **analytics propios** (cookie anónima + base de datos) que **complementa a GA4**, no lo sustituye.
- 📈 **GA4 ya está instalado (2026-09-20):** el jefe dio su etiqueta `gtag.js` (ID **`G-5L3BK3WLFB`**) y vive en el `<head>` de **`includes/header.php`**, así que mide **todas** las páginas públicas. Es la etiqueta oficial de Google, tal cual; **no choca con este módulo** (son dos medidas distintas: GA4 da el histórico y la comparación, el panel propio da el detalle del negocio). Si hay que cambiar de propiedad, se cambia **solo ahí**. Detalle: `GUIA_SEO_Y_GOOGLE.md` §5.1.
- Funciona con una **cookie anónima `cz_stats`** que crea el servidor y con **latidos JS** (`api/estadisticas.php`) que miden tiempo visible y "en vivo".
- Los datos propios **empiezan a llenarse desde el momento del despliegue**: no hay histórico anterior (ese lo sigue aportando GA4).
- Todo el tracking va en `try/catch`: si falla, la página del sitio sigue funcionando igual.
- Requiere **crear 2 tablas** (`directorio_stats_sesiones` y `directorio_stats_eventos`) y **liberar los PHP nuevos del escáner del hosting**.

## 1) QUÉ MIDE LA PESTAÑA 📈 ESTADÍSTICAS

> ⚠️ **Desde el 2026-09-13 la pestaña se llama 📈 Estadísticas y récords** y está **fusionada** con los
> 🏆 récords (§0). Lo de abajo es la mitad **de tráfico**; la mitad **del negocio** está en
> `GUIA_RECORDS_DEL_SITIO.md` §4. El rango (`&rango=`) es **uno solo** para las dos mitades.

- 🟢 **En vivo:** cuánta gente está en el sitio AHORA (últimos 3 min), cuántos son usuarios registrados y en qué páginas están. Se actualiza solo cada 30 s.
- Visitas, visitantes únicos, páginas vistas y tiempo medio por visita (hoy).
- Serie por día (gráfico), actividad por hora del día, dispositivos, fuentes de tráfico (Google, Facebook, WhatsApp, directo…) y top de rutas exactas y por tipo de página.
- **Entradas** (por dónde llegan) y **salidas** (por dónde se van): las páginas de salida se afinan con el evento `salida` del navegador.
- **Navegación interna:** "a qué página cambiaron" (transiciones A → B dentro de la misma visita).
- Rankings por USUARIO: ⏱️ más tiempo en línea, 🏪 más tiendas creadas, 📦 más productos.
- Últimas tiendas creadas y últimos productos agregados.
- 🔍 Búsquedas internas más usadas. ⚠️ **Retirado de la vista el 2026-09-13**: leía
  `directorio_historial_busqueda`, que **solo tiene las búsquedas de usuarios con sesión**. Ahora manda el
  bloque **«Lo que más busca la gente»** de la mitad del negocio (`directorio_busquedas`, anónimos
  incluidos). La función `stats_busquedas()` sigue existiendo pero **ya no se pinta**.
- 🧹 Botón para limpiar datos viejos (eventos > 90 días, sesiones > 180 días).

### 1.1) 🆕 EL SELECTOR DE RANGO (escalera del 2026-09-18 — pedido del jefe)

> Pedido textual: *«ofréceme la opción de poder ver resultados cada hoy 24 horas, dos días 48 horas, tres
> días, cinco días y 7 días, y luego ya te pasas de frente a 15 días, 30 días, 60 días»*.

| Botón | `&rango=` | Qué abarca exactamente |
|---|---|---|
| **Hoy (24 h)** | `1` | El **día en curso** (00:00 → 23:59 en hora de Lima), no una ventana móvil de 24 h |
| **2 días (48 h)** | `2` | Hoy + ayer |
| **3 días** | `3` | Hoy y los 2 días anteriores |
| **5 días** | `5` | Hoy y los 4 anteriores |
| **7 días** | `7` | Hoy y los 6 anteriores (**el que viene por defecto**) |
| **15 días** | `15` | Reemplazó al viejo «14 días» |
| **30 · 60 · 90 días · 1 año** | `30` / `60` / `90` / `365` | Los grandes de siempre (se conservaron: son los del resumen de inversionista) |

- **Los valores viven en `$rangos_ok`** (`includes/vista_estadisticas_admin.php`, la dueña de la página):
  **cualquier otro número cae en 7 días** y el botón marcado como activo es siempre el que se pidió.
- **La escalera corta NO hace falta programarla**: el motor `includes/metricas.php` recibe los **días**
  (el padre hace `$rec_dias = $rango`) y todas sus funciones —KPIs, embudo, día récord, hora punta,
  rubros, conversión, tiendas sin visitas— aceptan cualquier cantidad de días. El «periodo anterior» con
  el que se calcula el % de crecimiento es **igual de largo** (con 5 días compara contra los 5 previos).
- ⚠️ **Con «Hoy (24 h)» el % de crecimiento compara un día EN CURSO contra un día COMPLETO** (el de ayer):
  por la mañana saldrá bajo casi siempre. No es un error de la cuenta.
- ⚠️ **Desde «15 días» hacia arriba los números salen casi iguales hoy**, porque el tráfico propio solo
  existe desde el **2026-09-05**: no hay más historia que esa (y los eventos se borran a los 90 días).

## 2) CÓMO FUNCIONA EL TRACKING PROPIO

- **Cookie anónima `cz_stats`** (32 hex, sin datos personales, ~400 días, `SameSite=Lax`). La crea el servidor en `includes/header.php`.
- **Pageview registrado del lado del servidor** en cada página (solo GET, sin bots, sin el propio admin y sin migraciones/api/includes): 1 evento en `directorio_stats_eventos` + UPDATE/INSERT de la sesión en `directorio_stats_sesiones`.
- **Tiempo y "en vivo":** `assets/js/estadisticas.js` (cargado desde `includes/footer.php`) manda un **latido** (`t=latido`) a `api/estadisticas.php` cada ~60 s mientras la pestaña está visible, y un `t=salida` al ocultarla o cerrarla (con `sendBeacon`) con los segundos visibles acumulados. El servidor suma esos segundos (tope 15 min por latido).
- El endpoint también sirve el "en vivo" en JSON por GET `action=ahora`, solo para el admin.
- **Fechas:** todas se generan en PHP (`America/Lima` vía `config.php`) y se guardan como `DATETIME`. El módulo NO depende de la zona horaria del MySQL del hosting ni abre una segunda conexión: usa la conexión normal `db()`.
- Todo va en `try/catch`: si algo falla, la página sigue funcionando igual.

## 3) TABLAS NUEVAS Y SUS COLUMNAS

```
directorio_stats_sesiones — una fila por "visita continua" (cookie + ventana de 25 min)
  cookie, usuario_id (si está logueado), inicio, ultimo_activo, segundos, paginas,
  entrada (1ª página), salida (última conocida), pagina_actual, ref_dominio,
  ref_url, dispositivo (desktop/movil/tablet), es_bot

directorio_stats_eventos — cada pageview / acción con fecha
  sesion_id, cookie, usuario_id, tipo (pv/salida), pagina, tipo_pagina,
  ref_dominio, fecha
```

## 4) ARCHIVOS DEL MÓDULO

### Nuevos (dentro de `deploy/`)

| Archivo | Qué es |
|---|---|
| `migrar_estadisticas.php` | Crea las 2 tablas y se autodestruye. La clave va **dentro del propio archivo**; **hoy ya da 404** (se ejecutó y se borró) |
| `api/estadisticas.php` | Recibe los latidos (POST) y sirve el "en vivo" en JSON (GET `action=ahora`, solo admin) |
| `includes/estadisticas.php` | Librería: tracking + todas las consultas del dashboard |
| `includes/vista_estadisticas_admin.php` | Vista HTML/CSS/JS de la pestaña Estadísticas (Chart.js por CDN). **Desde el 2026-09-13 es la DUEÑA de la página fusionada**: trae el `<style>` (`st-*`), el rango, las secciones (🟢 en vivo · 💼 el negocio · 📈 el tráfico · 👥 las personas · 📣 para enseñar), el `require` de los bloques de récords y el JS de los gráficos + el botón «📋 Copiar resumen» |
| `assets/js/estadisticas.js` | Latidos del navegador (heartbeat) |

### Modificados

| Archivo | Cambio |
|---|---|
| `includes/header.php` | Carga `estadisticas.php` y llama a `stats_registrar_visita()` |
| `includes/footer.php` | Añade `<script src="assets/js/estadisticas.js">` |
| `superadmin.php` | Pestaña `estadisticas` + badge "en línea" en el menú + acciones `stats_instalar` y `stats_limpiar` |

### Fuera de `deploy/`

| Archivo | Qué es |
|---|---|
| `estadisticas_sql.sql` | ❌ **No existe** en `D:\RELAX` (se citaba como respaldo, pero no está en disco). El SQL de las tablas está dentro de `migrar_estadisticas.php`; si hiciera falta a mano, sacarlo de ahí. |

> 📌 **Este módulo YA ESTÁ DESPLEGADO Y MIDIENDO** (verificado en vivo el 2026-09-10). Los pasos de abajo
> quedan como registro de cómo se publicó y por si hay que reinstalarlo en otro entorno; **no** son una
> tarea pendiente.

## 5) DESPLIEGUE DEL MÓDULO, PASO A PASO

1. **Subir por FTP** desde `D:\RELAX\deploy` a la **raíz viva `/`** (⚠️ **no** a `/public_html`, que es la copia vieja anidada: `GUIA_DESPLIEGUE_Y_ENTORNO.md` §11.1), **un archivo por vez** con
   `python __subir_uno.py <ruta relativa>`: los archivos nuevos y los modificados de las tablas de §4.
   *(`deploy_files.py`, que citaban las guías viejas, ya no existe.)*
2. ⚠️ **Escáner del hosting:** los archivos PHP NUEVOS pueden quedar en 404 hasta liberarlos (hPanel → Seguridad → Imunify360 → lista blanca / cuarentena). Liberar `api/estadisticas.php`, `includes/estadisticas.php`, `includes/vista_estadisticas_admin.php` y `migrar_estadisticas.php`. Los modificados (header, footer, superadmin) corren al sobrescribir.
3. **Crear las tablas** — cualquiera de estas tres opciones:
   - **(Recomendada)** Súper Admin → 📈 Estadísticas → botón "⚙️ Crear tablas de estadísticas" (no requiere subir nada más).
   - Visitar `https://dechimbote.com/migrar_estadisticas.php?key=<CLAVE>` (la clave está en el archivo; se autodestruye). **Hoy ya da 404: ya se usó.**
   - Pegar en phpMyAdmin (hPanel) el SQL de las 2 tablas, que está dentro de `migrar_estadisticas.php`.
4. **Verificar en vivo** (ver §8).
5. **Bump de versión** (caché de estáticos): al cambiar `assets/js/estadisticas.js` hay que actualizar el `?v=N` en `includes/footer.php`. Al tocar el CSS de la vista no hace falta versión, porque el CSS está inline en `vista_estadisticas_admin.php` y viaja en el HTML del panel.

Reglas generales de despliegue y entorno: ver `GUIA_DESPLIEGUE_Y_ENTORNO.md`.

## 6) EXCLUSIONES Y DECISIONES DE MEDICIÓN

- **El propio admin NO se cuenta** como tráfico: constante `STATS_EXCLUIR_ADMIN = true` en `includes/estadisticas.php`. Si algún día se quieren contar las visitas del dueño, se pone en `false`.
- Los **bots** conocidos (Googlebot, Facebook…, herramientas SEO) se descartan por User-Agent.
- Páginas excluidas de medir: `superadmin.php`, migraciones y crons, `/api/`, `logout.php`, errores 404 y archivos (assets e imágenes).
- El tiempo de permanencia mide segundos con la pestaña **visible**: si el usuario deja la pestaña en segundo plano, deja de acumular (es lo correcto).
- Chart.js se carga por CDN (jsdelivr) solo dentro de la pestaña Estadísticas.
- **Privacidad:** la cookie es anónima y sin datos personales. Si en el futuro se pide un aviso de cookies, basta con mencionar `cz_stats` (análisis propio) en la política de privacidad.

## 7) RETENCIÓN Y LIMPIEZA DE DATOS

- La limpieza se hace **desde el botón del panel** (eventos > 90 días, sesiones > 180 días), que llama a `stats_limpiar()`.
- Alternativa: un cron opcional que llame a `stats_limpiar()` vía `api/estadisticas.php?action=limpiar`… **no está expuesto**; por eso se prefiere el botón.
- Si se quiere automático, la sugerencia es añadir al cron existente un `DELETE` con las mismas reglas. No se tocó `cron_reset_vistas.php` para no romper nada.

## 8) VERIFICACIÓN EN VIVO DESPUÉS DE DESPLEGAR

1. HTTP 200 en las páginas del sitio (el tracking no debe romper nada).
2. Abrir Súper Admin → 📈 Estadísticas: las tarjetas deben mostrar "0/—" o datos.
3. Abrir el sitio en una pestaña, esperar ~10 s y ver que "En línea ahora" sube a ≥ 1; esperar 60 s y ver el contador de "Registrados / páginas".
4. Cerrar la pestaña y comprobar que la sesión deja de aparecer en ~3 min.

## 9) EXTENSIONES FUTURAS PREVISTAS

- Agregar clics de salida (WhatsApp, teléfono, mapas) → evento tipo `clic` en `estadisticas.js`.
  ✅ **Hecho en parte el 2026-09-13**: los clics de pedido (WhatsApp de la ficha, consulta de un producto
  y pedido del carrito) se guardan en `directorio_pedidos` desde `api/lead.php` y se ven en la pestaña
  **🏆 Récords** (`GUIA_RECORDS_DEL_SITIO.md`). En ESTA pestaña siguen sin aparecer.
- Exportar CSV desde el panel.
- Aviso al admin por WhatsApp o email cuando "En línea ahora" pase de X.
- Integrar los datos de banners (`directorio_banner_stats`) en la misma pestaña.
- "Términos de búsqueda" también para visitantes anónimos (hoy solo se miden usuarios logueados, vía `directorio_historial_busqueda`).
  ✅ **RESUELTO el 2026-09-13 con otra tabla**: `directorio_busquedas` guarda **todas** las búsquedas
  (anónimas incluidas) con su número de resultados y se ven en 🏆 Récords. **Pendiente aquí**:
  `stats_busquedas()` sigue leyendo `directorio_historial_busqueda` (solo usuarios con sesión).

---

> **Guías base del proyecto:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (qué es el sitio, mapa de archivos,
> estado real de cada módulo y qué hacer después) · `REGLAS_DE_ORO_PROYECTO.md` (reglas del jefe,
> canónicas) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar sin romper el sitio).


_Última revisión: **2026-09-18** (§1.1: **la escalera del selector de rango** — Hoy (24 h) · 2 días (48 h) ·
3 · 5 · 7 · 15 · 30 · 60 · 90 · 1 año —, pedido del jefe; el «14 días» se retiró. Comprobado en vivo con
`__sonda_rangos.py`: los 10 rangos pintan la página sin un solo aviso de PHP y el botón activo es el pedido)._
_**2026-09-13**: (§0: **la pestaña se fusionó con los 🏆 récords** — una sola pestaña
**📈 Estadísticas y récords** con el diseño de este módulo, rango único `&rango=`
Hoy/7/14/30/60/90/1 año, y el bloque de búsquedas internas retirado. Mitad del negocio:
`GUIA_RECORDS_DEL_SITIO.md`)._
_2026-09-13: los clics de pedido y las búsquedas anónimas ya se miden, en la tabla nueva y en la mitad del negocio._
