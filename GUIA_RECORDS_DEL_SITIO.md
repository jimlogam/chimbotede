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


# GUÍA — 📈 ESTADÍSTICAS Y 🏆 RÉCORDS DEL SITIO (una sola pestaña) · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** antes de tocar la pestaña **📈 Estadísticas y récords** del Súper Admin, el
> motor `includes/metricas.php`, los bloques `includes/vista_records_admin.php` o los **enganches de
> medición** (`buscar.php`, `includes/chatbot.php`, `api/lead.php`).
> **Pedidos del jefe:** *(2026-09-13)* *«en mi panel de super admin muéstrame los records de más vistos,
> más buscados, más botones de pedir pedidos y otras cosas que consideres interesantes para mi crecimiento
> de mi proyecto y encontrar personas dispuestas a invertir en mi sitio»* · y esa misma noche:
> *«puedes fusionar de manera inteligente records y estadísticas **conservando el diseño de estadísticas**
> pero agregando también los datos de record de manera útil y fluida para tener una mejor perspectiva del
> sitio»*.
> **Estado:** ✅ **EN PRODUCCIÓN Y MIDIENDO** (2026-09-13). **Una sola pestaña**:
> `superadmin.php?seccion=estadisticas` (y `?seccion=records` es un **alias** que abre la misma página).
> **Archivos que toca:** `deploy/includes/metricas.php` (**motor de los récords**, nuevo) ·
> `deploy/includes/vista_records_admin.php` (**bloques del negocio**, partial) ·
> `deploy/includes/vista_estadisticas_admin.php` (**dueña de la página**: diseño + secciones + resumen) ·
> `deploy/superadmin.php` (menú y enrutado) · `deploy/buscar.php` · `deploy/includes/chatbot.php` ·
> `deploy/api/lead.php` (los 3 enganches que miden).
> 📌 **Guía hermana:** `GUIA_ESTADISTICAS_DEL_SITIO.md` (las **tablas y el tracking** del módulo de
> estadísticas: cookie `cz_stats`, latidos, `stats_*`).

> Regla de Oro de UX: ver `REGLAS_DE_ORO_PROYECTO.md`

---

## 0) LO ESENCIAL EN 30 SEGUNDOS

- **UNA pestaña, dos mitades:** desde el 2026-09-13 **no** hay una pestaña de récords aparte. La pestaña
  **📈 Estadísticas y récords** (2.ª del menú, `?seccion=estadisticas`) cuenta el sitio en este orden:
  1. **🟢 En vivo ahora** — quién está dentro en este instante.
  2. **💼 El negocio** — **los récords**: visitas, búsquedas, fichas, **pedidos** y **soles**, el embudo,
     el día/hora récord, las tiendas más vistas y las que más piden, los productos más pedidos, lo más
     buscado, **lo que buscan y NO hay**, los rubros (demanda contra oferta), la conversión y las
     **tiendas que nadie miró**.
  3. **📈 El tráfico** — horas, dispositivos, entradas, salidas, rutas, navegación interna y fuentes.
  4. **👥 Las personas** — rankings por usuario y lo último que entró.
  5. **📣 Para enseñar** — el **resumen copiable** (para un socio o un inversionista): un botón y lo tienes
     en el portapapeles. **El jefe no selecciona texto** (Regla inviolable n.º 1).
- **El diseño es el de Estadísticas** (clases `st-*` y las tarjetas `sa-stat`): los récords **no traen su
  propio CSS**, se visten con el del módulo. Lo único que se añadió al diseño son **separadores de
  sección** (`.st-sep`), que son los que le dan el "relato" a la página.
- **UN SOLO RANGO PARA TODO** (`&rango=`): **Hoy (24 h) · 2 días (48 h) · 3 · 5 · 7 · 15 · 30 · 60 · 90
  días · 1 año** (escalera del **2026-09-18**, pedido del jefe: la escalera corta va delante y después se
  salta de frente a 15 · 30 · 60; el «14 días» se retiró). El tráfico y los
  récords hablan **siempre del mismo periodo**, para que los números no se contradigan.
- ⚠️ **Lo importante que cambió con los récords:** hasta el 2026-09-13 el sitio **no guardaba** ni las
  búsquedas de los visitantes anónimos ni los pedidos. Solo **avisaba al Telegram**. Ahora hay **2 tablas
  nuevas** (`directorio_busquedas` y `directorio_pedidos`) y **3 enganches** que las llenan.
- 🧺 Y como las tablas nacieron vacías, hay un botón **«Traer el histórico»** que las **siembra** con lo que
  ya estaba en el registro de avisos del Telegram.
- **Nada de esto puede frenar el sitio:** todo va en `try/catch`, la medición va **después** de responder al
  visitante y, si las tablas no existen, el sitio sigue igual (solo deja de medir).
- ⏱️ **Rendimiento medido (2026-09-13, rango 30 días):** **578 ms** de respuesta del servidor, 42 KB de
  HTML, 27 tarjetas y 17 tablas. Es una página pesada **a propósito** (una sola pantalla con todo) y sigue
  entrando de sobra en el presupuesto del sitio.

---

## 1) QUÉ MIDE CADA NÚMERO Y DE DÓNDE SALE (lo más importante de esta guía)

| Bloque del panel | De dónde sale el dato | Por qué ESA fuente y no otra |
|---|---|---|
| 👁️ **Tiendas más vistas** | `directorio_stats_eventos` con `tipo_pagina='tienda'` (pageviews puros) | ❌ **No** se usa `directorio_vistas`: `api/registrar_vista.php` casi no se llama (nadie lo invoca desde el JS). ❌ **Tampoco** `directorio_negocios.vistas_count`: `cron_reset_vistas.php` lo pone a **0 cada 10 días** |
| 🔍 **Más buscados** | `directorio_busquedas` (**tabla nueva**) | Antes solo se guardaban las búsquedas de usuarios **con sesión** (`directorio_historial_busqueda`, ver `guardar_busqueda_usuario()`): con 9 usuarios registrados, eso era **no medir nada**. Por eso se **retiró** el bloque «Búsquedas internas más usadas» de la tarjeta de últimas tiendas |
| 🔥 **Más pedidos** | `directorio_pedidos` (**tabla nueva**) | Antes **no se guardaba en la base**: solo se avisaba al Telegram (`directorio_avisos_log`, que además **borra a los 30 días**). Sin historial no se puede decir qué tienda vende |
| 💰 **Soles en pedidos** | `directorio_pedidos.total` (el «Total referencial» del mensaje del carrito) | Es el valor que el sitio **mueve** para los negocios: el dato que más pesa ante un inversionista |
| 🏃 Visitas / 📄 páginas / ⏱️ segundos | `directorio_stats_sesiones` + `directorio_stats_eventos` | Las mismas tablas del módulo de estadísticas (cookie anónima + latidos). **Se excluye al admin y a los robots conocidos** |
| 🏪 Tiendas y usuarios nuevos | `directorio_negocios.creado_en` / `directorio_usuarios.creado_en` | Fechas guardadas **desde PHP** (hora de Lima) |
| 🎯 Conversión (pedidos por 100 vistas) | **cruce** de las dos listas anteriores | Con menos de **3 vistas** no se calcula: «1 vista y 1 pedido = 100 %» es ruido |
| 💤 Tiendas que nadie miró | tiendas activas **menos** las que aparecen en los pageviews del rango | Se cuenta por resta (no con `NOT EXISTS` página por página: eso recorrería 1 600 tiendas × todos los eventos) |

Cuando el rango no es «Hoy», cada número grande lleva su **% contra el periodo anterior igual de largo**
(verde sube, rojo baja). ⚠️ Si en el periodo anterior **no hubo ni un dato**, el porcentaje no existe y se
pinta **«✨ nuevo»** (en este sitio es lo normal hasta que se acumulen semanas: el tráfico propio empieza a
contarse desde el 2026-09-10).

---

## 2) LAS DOS TABLAS NUEVAS

```
directorio_busquedas — una fila por CADA búsqueda (anónimos incluidos)
  termino (tal cual lo escribieron) · norm (sin tildes/minúsculas: sirve para AGRUPAR)
  origen (web | chat | aviso) · resultados (NULL = no se sabe · 0 = no encontró nada)
  categoria_id (el rubro al que apunta la palabra, si se sabe) · usuario_id
  dispositivo · ip · fecha

directorio_pedidos — una fila por CADA toque de un botón de pedir
  negocio_id · producto_id (si fue por un producto)
  tipo: 'clic' (WhatsApp de la ficha) | 'consulta' (preguntó por un producto) | 'pedido' (carrito 🛒)
  origen (ficha | carrito | aviso) · productos_n · total (S/) · descuento_pct · detalle
  usuario_id · dispositivo · ip · fecha
```

- **Se crean desde el panel** (botón **⚙️ Crear las tablas de récords**, en la misma sección 💼 El negocio),
  igual que las de estadísticas, empleos y banners: los `migrar_*.php` **los bloquea el antivirus del
  hosting** (dan 404). `metricas_instalar()` es idempotente y **solo la ejecuta un admin** (o una sonda que
  defina `METRICAS_INSTALAR`).
- **Mientras las tablas no existan, el sitio NO se rompe**: `metricas_tabla_lista()` comprueba una vez por
  petición y las funciones de registro devuelven `false` en silencio.
- 🔒 **Privacidad:** se guarda el **término buscado** y la IP (igual que `directorio_avisos_log` y
  `directorio_vistas`). No se guarda nada del contenido del chat ni datos personales.
- 🧹 **Retención:** `metricas_limpiar($dias)` borra lo de más de **365 días** (`METRICAS_DIAS_GUARDAR`).
  Todavía **no está colgada de ningún cron**: se llama a mano cuando haga falta.

---

## 3) LOS TRES ENGANCHES (dónde se mide)

| Archivo | Qué mide | Cómo |
|---|---|---|
| `buscar.php` (justo después del `aviso()` de la búsqueda) | **toda búsqueda del buscador** | `metrica_busqueda($termino, count($resultados), ['origen'=>'web', 'categoria_id'=>…])` |
| `includes/chatbot.php` (bloque 3-bis, el buscador vivo del chat 🥷) | **toda búsqueda del chat** | `metrica_busqueda($termino, $hay['total'], ['origen'=>'chat', 'categoria_id'=>$hay['rubro']['id']])` — se guarda **tenga o no resultados** |
| `api/lead.php` (tras la redirección) | **todos los botones de pedir** | `metrica_pedido([...])` con el tipo (`clic`/`consulta`/`pedido`), el nº de productos y el total en soles |

**Cómo se comportan (y por qué):**

1. **Nunca frenan al visitante.** En `api/lead.php` la escritura va **después** de mandar la redirección
   (igual que el aviso al Telegram y el registro del chat).
2. **Se descartan los robots** (`stats_es_bot()`: curl, python, Googlebot, la granja de scrapers…) y
   **el propio administrador** (`metricas_es_admin()`, que respeta la misma constante que estadísticas:
   `STATS_EXCLUIR_ADMIN`). Si algún día se quiere medir al jefe, se cambia **una sola vez** ahí.
3. **Se ignoran los términos de 1 letra** (`norm` con menos de 2 caracteres) y todo se corta a 120.
4. **`norm` agrupa**: «Tortas», «tortas» y «tortás» son el **mismo** término en el ranking (el panel
   muestra el término **tal como lo escribió la gente**, `MIN(termino)`).
5. **Sin dedupe**: si alguien busca 5 veces lo mismo, se cuentan 5 (es demanda real). El motor de avisos sí
   deduplica (para no saturar el Telegram): por eso el histórico sembrado cuenta **menos** que el total de
   filas del registro (§5).

---

## 4) LA PÁGINA FUSIONADA, BLOQUE POR BLOCK

> **Regla de la fusión (no romperla):** `vista_estadisticas_admin.php` es la **dueña de la página** (trae el
> `<style>` con `st-*`, el `<h2>`, el selector de rango, las secciones, el resumen y el JS de los gráficos).
> `vista_records_admin.php` es un **partial**: **no** trae `<style>`, **no** trae `<h2>` y **no** trae el
> resumen; solo pinta las tarjetas del negocio con las clases del otro. Lo incrusta el padre con un
> `require` (una línea) dentro de la sección **💼 El negocio**. El rango lo recibe en la variable
> **`$rec_dias`** (el padre la define como `$rango`).

**Dentro de 💼 El negocio (el partial), de arriba abajo:**

1. **Aviso de arranque** (solo si hace falta): ⚙️ crear las tablas / 🧺 traer el histórico.
2. **Los 7 números grandes**: visitas · páginas · fichas abiertas · búsquedas · pedidos · tiendas nuevas ·
   **S/ en pedidos del carrito**, cada uno con su crecimiento.
3. **🔥 El embudo**: entraron → buscaron → abrieron una ficha → pidieron (con las tasas entre pasos).
4. **🎖️ Los récords**: el día que más se movió, la hora punta y el día de la semana más movido.
5. **👁️ Tiendas más vistas** (ojo al aviso rojo **«sin WhatsApp»**: esa tienda no puede recibir pedidos) ·
   **🔥 Tiendas con más pedidos** (desglose 🛒/❓/💬 + soles + último pedido).
6. **📦 Productos más pedidos** · **🔍 Lo más buscado** (con las puertas: buscador vs chat).
7. **🗳️ Lo que buscan y NO hay** (la lista de negocios por captar) · **🗺️ Rubros** demanda contra oferta
   (marca **💡 pide más negocios** si pasa de 20 vistas por tienda).
8. **🎯 Las que mejor convierten** · **💤 Tiendas que nadie miró** (con enlace directo a
   `seccion=tiendas&estado=activo&orden=vistas`, que es la agenda comercial) y los **📍 distritos**.

**Y en el resto de la página (el padre):** 🟢 en vivo (6 tarjetas + lista + gráfico de visitas por día),
📈 tráfico (horas, dispositivos, entradas, salidas, rutas, navegación interna, por tipo de página, fuentes,
rankings por usuario), 👥 personas y contenido (últimas tiendas y últimos productos) y 📣 **el resumen
copiable** al final.

**El resumen copiable** lo arma **`metricas_resumen_inversionista($dias, $datos)`**, que vive en el
**motor** (no en la vista) por dos razones: el panel lo pinta con un botón y **el futuro aviso semanal por
Telegram** (`cron_reporte_ventas.php`, pendiente de la guía de avisos §17) puede mandar **el mismo texto**
sin que haya dos verdades. El botón usa `navigator.clipboard` con respaldo `execCommand('copy')`.

---

## 5) 🧺 EL HISTÓRICO (SEMBRAR) — QUÉ TRAE Y QUÉ NO

`metricas_sembrar($dias = 180)` vuelca a las tablas nuevas lo que ya estaba en
**`directorio_avisos_log`** (el registro del motor de avisos, `GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md`):

- **Búsquedas**: filas `busqueda` / `busqueda_vacia` con estado **`enviado` o `agrupado`** y `es_bot = 0`.
  El término se saca del campo **`resumen`**, que el motor escribe **siempre** con el formato
  `«<término> (<N> res.)»` → se parsea con `/^(.*) \((\d+) res\.\)$/u`.
  ⚠️ **DEPENDENCIA ENTRE MÓDULOS:** si algún día se cambia ese formato en `avisos_formato()`, el sembrado
  deja de reconocer las búsquedas (se salta las filas, no rompe nada). **Cambiar los dos a la vez.**
- **Pedidos**: filas `lead_precio` con estado `enviado` y con `negocio_id`. El tipo se deduce del resumen
  (`«envió su pedido del carrito a …»` → `pedido`; `«pidió precio a …»` → `clic`) y el total se saca de
  `«Total referencial: S/ …»`.
- **Lo que NO trae (a propósito)**: las filas **`duplicado`** (la misma persona repitiendo la misma
  búsqueda en 5 minutos o el mismo botón en 10) y las **`robot`**. Por eso el sembrado trae **menos** filas
  que el total del registro — y está bien: es la parte **humana**.
- **Es idempotente por origen**: solo siembra si todavía no hay filas con `origen='aviso'`. Volver a
  pulsar el botón responde *«Histórico traído: 0 búsqueda(s) y 0 pedido(s)»* (comprobado el 2026-09-13).
- ⚠️ **El registro de avisos se limpia a los 30 días** (`AVISOS_DIAS_HISTORIAL`): el histórico solo puede
  traer lo que quede dentro de esa ventana. **Cuanto antes se siembre, más se recupera.**

---

## 6) NÚMEROS REALES MEDIDOS EL 2026-09-13 (primera corrida)

| Dato | Valor |
|---|---|
| Histórico sembrado | **138 búsquedas** (79 términos distintos, 34 sin resultados) y **144 pedidos** |
| Filas descartadas del registro | **411 `lead_precio` y 18 búsquedas estaban marcados como `robot`**; 143 búsquedas eran `duplicado` |
| Lo más buscado (4 días) | `pollo` **22** · `Tortas` **9** · `polleria` **8** · `clavos` **7** |
| Buscado y NO disponible | `cómputo` (3) · `paseador` (3) · `idiomas` (2) · `pichi` (2) · **`zapaterías` (2)** · `parche llanta` · `camisas` · `platos` |
| Pedidos por día | 13/09: **108** · 12/09: 10 · 11/09: 19 · 10/09: 7 (a la hora del informe) |
| Tiendas con pedidos | **130** negocios distintos (el top tenía **2** cada uno) |
| Tráfico (30 días) | 5 588 visitas · 5 731 páginas · **2 440** fichas abiertas · pico el **13/09** con 2 244 páginas |
| Hora punta | **18:00** · día de la semana más movido: **domingo** |
| Directorio | 1 611 negocios (1 609 activos) · 3 947 productos · **542 con WhatsApp** · 9 usuarios |
| Trabajo comercial pendiente | **311** tiendas activas **sin una sola visita** en 30 días (1 298 sí tuvieron) |
| Rubros con más demanda | 💇 Peluquerías/Barberías (230) · 🍽️ Restaurantes (178) · 🔧 Ferreterías (76) · 🩺 Doctores (67) |

> 💡 **La primera oportunidad que encontró el panel:** `zapaterías` se buscó 2 veces **y no encontró
> nada**, aunque el rubro existe. Según la regla del proyecto, eso **no se arregla con código**: falta la
> frase `zapateria`/`zapaterias` en `directorio_categoria_claves` (ver `GUIA_CHATBOT_DEEPSEEK.md`
> §2nonies c-bis). **Pendiente.**

⚠️ **Aviso honesto sobre los datos:** buena parte del tráfico de este hosting es una **granja de robots**
con User-Agent de móvil (el motor de avisos los detecta como `robot`: **411 de 553** clics de precio). El
módulo de estadísticas descarta los robots **conocidos** por User-Agent, así que las visitas y los
`reclamar` (2 625 pageviews en 30 días, sospechosamente altos) **pueden incluir esa granja**. Los
**pedidos y búsquedas medidos por las tablas nuevas sí están filtrados** (robots y admin fuera).

---

## 7) TRAMPAS YA PISADAS (NO REPETIRLAS)

| Error / fricción | Por qué pasó | Cómo se resolvió |
|---|---|---|
| **`Unknown column 'e.fecha'`** en el conteo de tiendas sin visitas | El trozo de SQL de `metricas_sql_rango('e.fecha', …)` se aplica **dentro** de una subconsulta donde la tabla todavía no se llama `e` | El rango va con la columna **sin alias** (`fecha`). Lo delató la **sonda** (devolvía 0 en silencio: `stats_q()` se traga los errores) |
| **El sembrado de búsquedas traía 0 filas** | El `resumen` del aviso se trunca a 240 caracteres y el `estado` puede ser `duplicado`/`robot` | Se filtra `estado IN ('enviado','agrupado')` y `es_bot = 0`; el regex exige el `(N res.)` **al final** |
| **La prueba con `curl` no midió nada** | `curl` está en la lista de User-Agent de robots de `stats_es_bot()` | Es **correcto**: para probar hay que simular un navegador (`curl -A "Mozilla/5.0 (Linux; Android 13…) Chrome/120…"`) |
| **`api/registrar_vista.php` no lo llama nadie** | Se creía que era la fuente de «más vistos» | Se usa `directorio_stats_eventos` (`tipo_pagina='tienda'`) |
| **`vistas_count` se pone a 0 cada 10 días** | `cron_reset_vistas.php` | Nunca usarlo para récords históricos |
| **Analizar el histórico desde el navegador del jefe** | Su sesión es de **admin** y sus búsquedas/pedidos **no se cuentan** | Probar la medición con un UA de navegador desde `curl`, o desde el propio sitio |
| **`$rango=14` caía en «30 días»** | El partial usaba `metricas_dias($clave)`, que devuelve **30** cuando la clave no está en su catálogo (y «14» no lo estaba) | El partial ya **no** traduce el rango: el padre le pasa **los días** en `$rec_dias` (`$rec_dias = $rango`). `metricas_dias()`/`metricas_periodos()` siguen existiendo para otros usos, **pero el panel no los usa** |
| **Poner rangos nuevos NO exige tocar el motor** (2026-09-18) | Se creía que cada rango tenía que estar en el catálogo de `metricas_dias()` | Es **falso**: el padre le pasa los **días** al motor, así que basta con añadir el número y su etiqueta en **`$rangos_ok`** (`includes/vista_estadisticas_admin.php`). Se añadieron **2 · 3 · 5 · 15** sin tocar `metricas.php` (comprobado en vivo: los 10 rangos pintan la página sin avisos de PHP) |
| **El «14 días» quedó huérfano** | El jefe pidió **15** días el 2026-09-18 | Se reemplazó: hoy la escalera es 1 · 2 · 3 · 5 · 7 · **15** · 30 · 60 · 90 · 365. Un `&rango=14` viejo en un enlace guardado **cae en 7 días** (el panel no lo avisa) |
| **La fusión dejó el `<style>` duplicado / el resumen en medio de la página** | El partial era una pestaña entera | El partial **no** trae `<style>` ni el resumen: los pone el padre al final. Los colores del crecimiento (`.rec-up/.rec-down/.rec-flat`) los define el **padre** |

---

## 8) CÓMO VERIFICARLO (SIN BAJAR EL SITIO ENTERO)

```powershell
# 1) lint
C:\xampp\php\php.exe -l deploy\includes\metricas.php
C:\xampp\php\php.exe -l deploy\includes\vista_records_admin.php
C:\xampp\php\php.exe -l deploy\includes\vista_estadisticas_admin.php
C:\xampp\php\php.exe -l deploy\superadmin.php
# 2) subir SOLO lo cambiado
python __subir_uno.py includes/metricas.php
# 3) HTTP (302 = redirige al login: correcto; 500 = error de PHP)
curl.exe -s -o NUL -w "%{http_code}" "https://dechimbote.com/superadmin.php?seccion=estadisticas"
# 4) los includes NO se pueden pedir directo (protegidos): deben dar 403
curl.exe -s -o NUL -w "%{http_code}" "https://dechimbote.com/includes/vista_records_admin.php"
```

- **Sonda de datos**: `python __sonda_records.py` (sube `__sonda_records.php`, imprime el **esquema real**
  de las tablas, ejecuta **las consultas grandes tal cual** —para ver el error de SQL de verdad, no un
  array vacío— y llama a las funciones del motor; **se borra sola del servidor**). Resultado en
  `__sonda_records_resultado.json`.
- **Prueba visual**: pestaña **nueva** en `superadmin.php?seccion=estadisticas` (reutiliza la sesión del
  jefe, **no** hay que entrar como admin) y **cerrarla al terminar** (Regla de oro n.º 5). Probar los
  7 rangos: **Hoy, 7, 14, 30, 60, 90 y 1 año** (el «1 año» es el que más pesa: ~580 ms de servidor).
- **Prueba de que mide en vivo**: una búsqueda real desde el buscador (o `curl` con UA de navegador) y
  recargar con `&rango=1`: tiene que sumar 1 en «Lo que más busca la gente» y aparecer la puerta
  **🔍 Buscador**. Un clic real en un botón de pedir tiene que sumar 1 en su tienda.
  *(Comprobado el 2026-09-13: `camarones al ajillo` → 🔍 Buscador 1, y STEVEN GYM pasó de 2 a 3 pedidos.)*

---

## 9) PENDIENTES (con su siguiente paso)

1. **`zapaterías` sin resultados** → añadir la frase a `directorio_categoria_claves` del rubro Zapaterías
   (sonda de escritura, patrón `__claves_seed2.php`). Repetir con `cómputo`, `paseador`, `idiomas`,
   `pichi`, `parche llanta`, `camisas`, `dj`, `platos`.
2. **Productos más VISTOS** no se puede medir hoy: no hay página de producto y ningún JS llama a
   `registrar_vista(negocio, producto)`. *Siguiente paso:* un latido desde la ficha rápida del carrito
   (`assets/js/carrito.js`, **subirle el `?v=`**) hacia `api/lead.php` o un endpoint propio.
3. **📣 Aviso semanal por Telegram con el mismo resumen**: `metricas_resumen_inversionista()` ya devuelve
   el texto listo; falta el cron de los lunes (`cron_reporte_ventas.php`, guía de avisos §17).
4. **Colgar `metricas_limpiar()`** de un cron (retención de 365 días) cuando las tablas tengan volumen.
5. **La granja de robots** sigue inflando visitas/páginas: valorar endurecer `stats_es_bot()` (o contar
   solo sesiones con ≥ 2 páginas) antes de dar los números a un inversionista.
6. **La página ya es larga** (27 tarjetas): si el jefe pide más, la salida natural es **pestañas internas**
   (En vivo · Negocio · Tráfico) dentro de la misma sección, **no** volver a partir el menú en dos.

---

## 10) ARCHIVOS DEL MÓDULO (con sus bytes subidos el 2026-09-13)

| Archivo | Qué es | Bytes |
|---|---|---|
| `deploy/includes/metricas.php` | **Motor**: tablas, registro, consultas, siembra, ayudas y **`metricas_resumen_inversionista()`** | 41 732 |
| `deploy/includes/vista_records_admin.php` | **Partial** con los bloques del negocio (💼 El negocio), en el diseño `st-*` del padre | 19 620 |
| `deploy/includes/vista_estadisticas_admin.php` | **Dueña de la página fusionada**: `<style>`, secciones, rango, gráficos y 📣 resumen | 26 811 |
| `deploy/superadmin.php` | Menú con **una sola** pestaña **📈 Estadísticas y récords**; `records` queda como **alias** en la lista de secciones y en el enrutado | 51 223 |
| `deploy/buscar.php` | Enganche de la búsqueda del buscador | 47 520 |
| `deploy/includes/chatbot.php` | Enganche de la búsqueda del chat 🥷 | 84 470 |
| `deploy/api/lead.php` | Enganche de los pedidos (después de la redirección) | 13 557 |
| `__sonda_records.php` + `__sonda_records.py` | Sonda de verificación (**fuera de `deploy/`**, se borra sola del hosting) | 9 943 / — |

> **Guías base del proyecto:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (qué es el sitio, mapa de archivos, estado
> real de cada módulo) · `REGLAS_DE_ORO_PROYECTO.md` (reglas del jefe, canónicas) ·
> `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar sin romper el sitio).

_Última revisión: **2026-09-18** (la **escalera del rango** cambió, pedido del jefe: Hoy (24 h) · 2 días
(48 h) · 3 · 5 · 7 · 15 · 30 · 60 · 90 días · 1 año — tabla completa y semántica en
`GUIA_ESTADISTICAS_DEL_SITIO.md` §1.1)._
_2026-09-13: creación del módulo de récords **y su fusión con 📈 Estadísticas** en una sola pestaña._
