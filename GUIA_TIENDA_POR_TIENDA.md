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


# GUÍA — TIENDA POR TIENDA: UNA URL, UN CICLO COMPLETO · dechimbote.com

> **Para qué sirve:** el jefe pide *«provemos con uno más»* y hay que cerrar **una tienda entera** sin
> preguntar nada: revisar su catálogo, crear lo que le falta, pedir las fotos con su **ID impreso**, pedir
> **su canción** y dejarla publicada y marcada como revisada.
> **Regla de oro:** **UNA URL A LA VEZ** (nunca bloques), y **las dos cartas se le entregan al jefe EN EL
> MISMO MENSAJE** (él trabaja con ellas mientras el asistente sigue con lo demás).

## 1. 📥 LA LISTA DE ENLACES (`URLS_EXTRAIDAS.txt`) — DÓNDE VIVE

| Dónde | Qué es |
|---|---|
| `C:\Users\Usuario\Downloads\URLS_EXTRAIDAS.txt` | **La copia de trabajo** (el jefe la deja ahí) ⚠️ **Descargas se borra cada día** |
| **`D:\RELAX\URLS_EXTRAIDAS.txt`** | ⭐ **LA COPIA QUE SOBREVIVE** (orden del jefe, 2026-09-18: *«la lista de enlaces guárdala en otra ubicación más segura dentro del proyecto, porque la carpeta Descargas se borra cada día»*). **Cuando el jefe traiga una lista nueva, se refresca esta copia** |

**Formato de cada línea** (¡no confundir los dos números!):

```text
1492. https://dechimbote.com/neg/taller-de-llaves-y-cerrajeria-sotil 1512
 ↑ número de listado (no sirve para nada)        ↑ **EL ID DE LA TIENDA** (este manda)
```

**Sacar UNA al azar** (sin leer el archivo entero) — se salta las que ya se hicieron:

```powershell
$f = 'D:\RELAX\URLS_EXTRAIDAS.txt'      # la copia segura
$lineas = Get-Content $f -Encoding UTF8 | Where-Object { $_ -match 'https?://\S+/neg/' -and $_ -notmatch '\s123\s*$' }
$lineas | Get-Random                    # 123 = una tienda ya hecha (se excluye a mano)
```

## 2. EL CICLO, EN 6 PASOS

| # | Paso | Herramienta / dónde |
|---|---|---|
| 1 | **Leer la ficha**: nombre, rubro real, distrito, descripción, productos y si tienen foto | Se baja el HTML de la ficha y se miran los `data-id` / `data-titulo` / `data-precio` / `data-img` de las tarjetas; la verdad fina, con la sonda de lectura |
| 2 | **Productos**: si tiene menos de 5, **CREARLOS** (sacados de su propia descripción); **el que esté en S/ 0 se le pone precio a criterio** | Sonda temporal con clave (`__*.php` + `python __sonda_run.py`), idempotente por título |
| 3 | **La descripción**: **sin precios y sin dirección** (ya están arriba y en cada producto). El horario se muda al **campo `horario`** de la tienda | La misma sonda del paso 2 |
| 4 | 🎨 **LA CARTA DE IMÁGENES** (una sola, un bloque): **cada producto con su ID, y la orden del ID EN IMPERATIVO al inicio y al cierre de cada prompt** (*«ESCRIBE EN ESTA IMAGEN… EL NÚMERO X»*) | Molde: **`CARTA_IA_PRODUCTOS_CERRAJERIA_SOTIL_6.md`** y **`CARTA_IA_PRODUCTOS_LACEADOS_COLOR_5.md`** |
| 5 | 🎵 **EL PROMPT DE MÚSICA**: el telonero dice **el ID EN LETRAS** («mil quinientos doce»), nunca en cifras | Molde: **`PROMPT_MUSICA_CERRAJERIA_SOTIL_1512.md`** · formato: **`GUIA_PROMPTS_DE_MUSICA.md`** |
| 6 | **Cuando llegan las fotos y el mp3**: se asocian **MIRANDO el número impreso** (los nombres en inglés no sirven), se renombran a `producto-<slug>-<id>.jpeg`, se publican y **se borra de Descargas**; la canción se pasa a **OGG** y se publica | `python __pub_productos.py <modo>` (los pares se agregan al script) · `python __cancion_uno.py <id> <slug> go` |
| 7 | ✅ **Marcar la tienda como REVISADA** (así entra al filtro «Revisados» del panel) | `python __sonda_run.py __rev_columna.php sNd4-rev-col-chimbote-7tQ go "&marcar=<id>"` |

**Lo que NO se hace:** oír/verificar la canción (confianza total en el jefe: *«confía en mí, esa es»*),
verificar lo ya publicado, ni abrir carpetas de Descargas.

## 3. QUÉ SE LE ENTREGA AL JEFE (en un solo mensaje, lo antes posible)

1. **El link** que salió al azar (con nombre, rubro, distrito y qué se encontró).
2. **La carta de imágenes** para el diseñador (un bloque de código, con el ID impreso de cada producto).
3. **El prompt de música** (un bloque de código, con el ID en letras).
4. Lo que ya quedó hecho (productos creados, precios, descripción) para que él solo trabaje con las cartas.

## 4. 📋 REGISTRO DE TIENDAS HECHAS (una fila por tienda)

| Fecha | Tienda (ID) | Rubro · distrito | Lo que se hizo | Fotos | Canción |
|---|---|---|---|---|---|
| 2026-09-18 | **Laceados & Color Chimbote** (123) | Peluquerías / Barberías · Chimbote | 2 → **5 productos** (se crearon 12285, 12286, 12287); descripción sin precios ni dirección; horario al campo | ✅ **5 de 5 publicadas** (todas con el ID impreso) | ✅ `Cita_en_Chimbote.mp3` → OGG 318 KB |
| 2026-09-18 | **Taller De Llaves Y Cerrajeria Sotil** (1512) | 🔐 Cerrajería · Chimbote | 2 → **6 productos** (creados 12288, 12289, 12290, 12291); precios a los que estaban en 0; descripción reescrita sin dirección | ✅ **5 de 6 publicadas** (falta la del **12291** · emergencia 24 h) | ✅ `Siempre_Listos.mp3` → OGG 313 KB |

> **Pendiente de la 2.ª:** la imagen del **12291 «Servicio de emergencia 24 horas»** (no llegó con las
> otras 5): hay que pedírsela al diseñador cuando el jefe quiera.
