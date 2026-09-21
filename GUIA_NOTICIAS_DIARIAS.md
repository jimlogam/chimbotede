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


# 📰 GUÍA DEL MÓDULO DE NOTICIAS DIARIAS — dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Qué es:** la sección de noticias del sitio. Un robot busca cada mañana las noticias locales en los
> RSS de los medios de la zona, las **reescribe con nuestras palabras** (200-500 palabras) y las publica
> en **`/noticias`**. Dentro del texto, las palabras que son un **rubro del directorio** quedan como
> **enlaces suaves en gris** que llevan a las tiendas de ese rubro.
> **Quién lo pidió:** el jefe, el **2026-09-13**.
> **Estado:** ✅ **funcionando** (10 noticias publicadas y verificadas ese mismo día).

---

## §1. El pedido del jefe (textual) y lo que decidió

> *«Cómo podemos hacer para que nuestro sitio web tenga una página de noticias… hay alguna forma de
> hacer un script con clave API que todos los días a una determinada hora navegue por la red, encuentre
> 10 noticias locales todas relacionadas al distrito de Chimbote… ojo, Nuevo Chimbote todo es un solo
> texto: Nuevo Chimbote… la noticia serían título, fecha, hora y una descripción de la noticia de entre
> 200 a 500 palabras… me serviría para que el bot, cuando entre, inicie siempre hablando de una noticia
> que el usuario vería dentro de la web… usemos la API para leer la noticia y escribirla nuevamente de
> acuerdo a nuestro criterio, pero siempre enfocándonos al marketing… si la noticia dice "pollería fue
> premiada por su honradez", la palabra pollería es un enlace porque tenemos el rubro de pollería… **no
> un enlace llamativo, un enlace suave: la letra es negra, el enlace podría ser gris**… 10 es el tope
> máximo, puede haber menos, pero 10 es lo máximo… **siempre procuremos usar RSS para captar la noticia
> y leer, y luego ya nosotros volvemos a redactar nuestra noticia**»*

**Las 4 decisiones que confirmó** (2026-09-13):

| Tema | Decisión |
|---|---|
| Hora | **06:00 de Chimbote** (= **11:00 UTC** en el cron de hPanel) |
| Publicación | **Automática**, con aviso al Telegram |
| Fuentes | **RSS** (Google Noticias como radar + los RSS locales que traigan el texto) |
| Imagen | **Sin foto**: portada de color con el título |

**Y la respuesta a su pregunta de fondo** (por qué no basta la clave API): *una clave de API **no navega**.
Si se le pide «10 noticias de Chimbote de hoy» a secas, el modelo **inventa** calles, cifras y nombres.
Por eso el reparto es: **el robot busca y lee (RSS), la API redacta**. Nunca al revés.

---

## §2. Las fuentes: lo que se midió de verdad (2026-09-13)

Se probaron 15 medios uno por uno. **El requisito es duro: sin el TEXTO de la noticia no se puede
escribir nada** (inventarlo sería una noticia falsa; copiarlo sería plagio).

| Fuente | ¿Sirve? | Qué trae |
|---|---|---|
| **diariodechimbote.com/feed/** (+ `/seccion/noticias-locales/` y `/seccion/politica/`) | ✅ **la mejor** | El cuerpo completo en `content:encoded` (2 400-4 200 caracteres) |
| **ancashaldia.com/feed/** | ✅ | Cuerpo completo (~2 300 caracteres) |
| **bolognesinoticias.com/feed/** | ✅ con lector | El feed trae 536 caracteres; **abriendo el artículo** se leen 5 000 |
| **lalupa.pe/feed/** y **rpp.pe/feed/** | ✅ si la noticia es de la zona | Cuerpo completo (nacionales: solo cuentan si nombran el distrito) |
| **Google Noticias** (`news.google.com/rss/search?q=…`) | 🛰️ **solo radar** | Trae 100 titulares por consulta, **pero su enlace NO deja leer el artículo**: devuelve una página de Google de 125 000 caracteres sin la noticia |
| **radiorsd.pe** | ❌ | Es el medio que **más** publica de la zona (108 titulares vistos) y **no entrega ningún feed** utilizable (`/feed` → 404; el feed Joomla devuelve HTML). Queda **anotado en el registro** de cada corrida para pedirle el feed algún día |
| **laindustria.pe**, **chimboteenlinea.com** (DNS), **elferrol.com**, **noticiaschimbote.com**, **actualidadchimbote.com** | ❌ | No responden / no resuelven / sin feed |
| **andina.pe** | ❌ | El lector de párrafos no saca nada (su web no usa `<p>`); además es nacional |

**Resultado del día de la prueba:** **22 noticias locales con texto** (Chimbote, Nuevo Chimbote y Santa),
más de las 10 que es el tope. Las fuentes se cambian en `includes/config_noticias.php` → `NOTICIAS_FUENTES`.

---

## §3. El camino de una noticia (en orden)

```
1. RADAR     Google Noticias (3 consultas) → 120 titulares: sirve para saber QUÉ se está publicando
             y para anotar los medios de la zona que NO tenemos (Radio RSD).
2. MATERIAL  Se leen los RSS. Si el feed trae el cuerpo, se usa; si solo trae el titular, se ABRE el
             artículo y se leen sus párrafos (lector propio). Si no se junta el mínimo (800 caracteres),
             ESA NOTICIA SE DESCARTA: no se inventa ni se rellena.
3. DISTRITO  Se decide de qué distrito es (ver §4). Si no es de los nuestros, se descarta.
4. REDACTAR  La API (DeepSeek) la reescribe: 200-500 palabras, sin inventar un dato y sin copiar frases.
             Hasta 3 vueltas: la 1.ª escribe y las otras CORRIGEN lo que esté mal (palabras fuera de
             rango o una frase copiada del original).
5. ENLAZAR   Las palabras que son rubro del directorio quedan en GRIS llevando a /categoria/<slug> (§5).
6. PUBLICAR  Se guarda en `directorio_noticias` (sale en /noticias y en /noticia/<slug>) y se avisa al
             Telegram. El tope del día es 10; si solo hay 4, se publican 4.
```

**📅 LA VENTANA SON 3 DÍAS** (`NOTICIAS_DIAS_VENTANA` = 3): **hoy, ayer y antes de ayer**, que es lo que
pidió el jefe — *«busca las noticias de los últimos tres días para que así haya contenido»*. Los medios
locales no publican 10 noticias propias cada día; con la ventana de 3 días siempre hay material y la
sección no queda a medias. Dentro de la ventana manda **lo más nuevo** (se ordena por fecha y hora).

**Los controles que NO se saltan nunca** (están en `noticias_redactar()`):

| Control | Regla |
|---|---|
| Sin inventar | Si el modelo devuelve el cuerpo vacío (no pudo escribirla con ese material), **no se publica** |
| Palabras | Entre **200 y 500**. Fuera de rango → se le pide corregir |
| Plagio | Se mide **la secuencia de palabras seguidas más larga** que coincide con el original. **12 o más = copió** → se le manda el trozo exacto y se le pide reescribirlo, resumirlo o quitarlo |
| Fidelidad | El guion le prohíbe añadir nombres, edades, cifras, montos, causas, cargos o responsables que no estén en el material |

> ⚠️ **Por qué se mide «la secuencia más larga» y no «cuántas frases coinciden»:** al reescribir se repiten
> los nombres propios («quebrada San Antonio», «Municipalidad de Nuevo Chimbote»). Contando ventanas
> sueltas, un texto bien reescrito daba 71 coincidencias y se rechazaba solo. Con la secuencia más larga
> se mide lo que de verdad importa: **si copió una frase entera**.

---

## §4. Los distritos (y las trampas que se encontraron)

Orden del jefe: *«que aparezca el nombre del distrito suficiente pero sin confundir con otros distritos
similares»*. Tres distritos: **Chimbote · Nuevo Chimbote · Santa**.

| Trampa | Cómo se resuelve |
|---|---|
| ⚠️ **«Nuevo Chimbote» CONTIENE «Chimbote»** | Se cuentan por separado («nuevo chimbote» y «chimbote» **sin** el «nuevo » delante). Si el titular nombra los dos, manda **el que aparece primero** |
| ⚠️ **«Santa» es peligrosísimo** (Santa Claus, Santa María de Nieva, Santa Anita, Santa Cristina, Santa Rosa) | El «Santa» que cuenta tiene que estar **limpio**: no vale si le sigue otra palabra, salvo «Áncash» o «Perú». Y además hace falta señal de distrito/provincia («distrito de Santa», «provincia del Santa», «Santa, Áncash»). **Dos noticias falsas se colaron el 2026-09-13** por esto: un sismo de Amazonas («distrito de **Santa María** de Nieva») y un corte de luz de Casma («valle de **Santa Cristina**») |
| Nombres con mayúscula | En texto normal, «Santa + Mayúscula» (`Santa Cristina`) es nombre propio; en titulares TODO EN MAYÚSCULAS manda la lista de nombres |
| Barrios que delatan el distrito | Hay zonas que solo existen en un distrito y nombrarlas ya dice de dónde es: **Cascajal, La Caleta, Chimbote Viejo** → Chimbote · **Garatea, Villa Corina, Florida Baja/Alta** → Nuevo Chimbote. ⚠️ Los nombres que existen en medio mundo (**Miraflores, Santa Rosa, San Juan, Buenos Aires, 25 de Mayo**) **NO** entran: una noticia de Lima quedaría como de Chimbote |
| «Corte del Santa» | Es la Corte Superior de Justicia del Santa, que tiene su sede en Chimbote → cuenta como Chimbote |
| ⛔ No es nuestro | Coishco, Samanco, Nepeña, Casma, Huaraz y todo lo nacional **se descartan** (el jefe pidió 3 distritos). Es preferible perder una noticia que etiquetarla mal |

**Probado con 28 casos** (`__test_noticias.php`, borrado al terminar la sesión): **28 de 28 correctos**,
incluidos «Chimbote y Nuevo Chimbote: …» (→ Chimbote, porque va primero) y «Santa Claus ya está en el
mall» (→ no es nuestra).

---

## §5. 🎨 El enlace suave (la idea del jefe)

> *«no un enlace llamativo, un enlace suave: la letra es negra, el enlace podría ser gris»*

| Detalle | Cómo queda |
|---|---|
| Texto | Negro `#111827` |
| Palabra enlazada | **Gris `#6b7280`**, sin negrita, sin iconos, mismo tamaño |
| Subrayado | Punteado muy tenue (para que se note que se puede tocar) |
| Texto del enlace | **La palabra misma**: nunca «clic aquí» ni «ver más» |
| Destino | La página del rubro con sus tiendas: `/categoria/<slug>` (misma pestaña) |
| Cantidad | **1 enlace por rubro** y **máximo 6 por noticia** (el tope es de TODA la noticia, no de cada párrafo: la primera versión lo reiniciaba por párrafo y salían 9) |
| Dónde | Solo en el cuerpo; el **título queda limpio** (SEO) |

**De dónde salen las palabras:** del diccionario que **ya existía** en el sitio,
`directorio_categoria_claves` (**1 057 palabras** reales que la gente escribe en el buscador) cruzado con
`directorio_categorias`. **Cero mantenimiento:** si mañana se añade un rubro o una palabra, el enlace
aparece solo. Si una palabra sirve para dos rubros, gana **el que tiene tiendas** (que el clic no caiga en
una página vacía): por eso «pollo» lleva a Restaurantes y «taller» a Mecánicos.

### 🚫 Las palabras que NO se enlazan nunca (`NOTICIAS_ENLACE_NUNCA`)

El diccionario es «lo que la gente escribe en el buscador»; en un **texto periodístico** muchas de esas
palabras significan otra cosa. Esto se calibra mirando las **1 057 palabras reales** y los enlaces que
salieron en las primeras noticias:

| Palabra | A dónde iba | Por qué se veta |
|---|---|---|
| Defensa, civil, penal, laboral, legal, contrato, demanda | Abogados | «Defensa Civil», «obra civil», «demanda» son palabras de cualquier noticia |
| agua | Bidones de agua | Una noticia de un desborde no habla de bidones (se queda «agua purificada») |
| terreno, lote, casa, cuarto, alquiler | Inmobiliarias | Aparecen en cualquier noticia de obras |
| sector, puesto | Mercados y ferias | «el sector pesquero», «puesto de salud» |
| puerta | Carpinteros | «le cerraron las puertas» |
| llave | Cerrajería | «la llave del torneo» |
| cadena, plata | Joyas | «cadena perpetua», «la plata» (dinero) |
| banda, sonido | Música / shows | «banda criminal» |
| diario, radio, prensa, noticias | Medios | Es el **nombre del medio** que citamos al pie |
| trabajo, empleo, personal | Empleos | «mesa de trabajo» |
| municipio, alcalde, gobierno, policía, denuncia | Instituciones | No es lo que vende un negocio |
| **unas** | Salón de belleza | ¡Es el artículo «unas»! El diccionario dice «uñas» y sin tildes se confunde |
| nino, gato, salud, vision, consulta, analisis, capital, dinero, efectivo, cuotas, salon, novia, fiesta, eventos, usado, remate… | varios | Palabras de todos los días |

**Lo que SÍ se enlaza** (los ejemplos que pidió el jefe, comprobados en noticias reales el 2026-09-13):

```
«auto»      → /categoria/mecanicos            (noticia de un choque)
«camioneta» → /categoria/venta-de-vehiculos
«Hospital»  → /categoria/clinicas             (el ejemplo «hospital y clínica» del jefe)
«celular»   → /categoria/informatica
«drones»    → /categoria/alquiler_de_drones
«limpieza»  → /categoria/limpieza
```

Al final de cada noticia, un bloque discreto recuerda **«🔎 En esta noticia se mencionan: …»** con los
mismos rubros, y el robot añade **una sola línea** de cierre cuando la noticia habla de un rubro real
(«Los negocios de X del distrito están en DeChimbote.com.»). Se apaga con `NOTICIAS_CIERRE_MARKETING`.

---

## §6. La tabla y los archivos

**Tabla `directorio_noticias`** (se crea sola, defensiva, como la de empleos):

`id · slug · titulo · entradilla · cuerpo · palabras · distrito · zona · fecha · hora · fuente_nombre ·
fuente_web · fuente_enlace · rubros · huella · estado · vistas · creada_en`

- `huella` (md5 de título+enlace): **impide publicar dos veces la misma noticia**, aunque llegue por dos
  feeds del mismo medio.
- `fuente_enlace`: el enlace al artículo original. **Se guarda siempre**: la noticia es del medio; el
  resumen es nuestro y se cita la fuente al pie.
- La fecha y la hora se guardan **desde PHP** (hora de Lima), nunca con `NOW()` del MySQL (va en UTC).

| Archivo | Qué es |
|---|---|
| `deploy/includes/config_noticias.php` | **Todos los ajustes**: fuentes, distritos, zonas, topes, palabras vetadas, modelo |
| `deploy/includes/noticias.php` | **El motor**: radar, lector de artículo, distritos, redacción, enlaces suaves, tabla, listados. Funciones que se usan desde fuera: **`noticias_listar()`** (todos los listados y `api/noticias_json.php`), **`noticias_obtener()`** (la ficha por slug), `noticias_ultimas()`, `noticias_publicadas_hoy()`, `noticias_por_dias()`, `noticias_guardar()`, `noticias_contar()`, `noticias_fecha_corta()` (§6) |
| `deploy/includes/chatbot_diario.php` | **El contexto del día del chat 🥷**: fecha, hora, clima de Chimbote y **nuestras noticias**. Entra por `chatbot_diario()` y lee las noticias con **`chatbot_diario_noticias_propias()`** (§9) |
| `deploy/cron/noticias_diarias.php` | **El robot** de las 06:00 |
| `deploy/cron/monitoreo_sistema.php` | El cron horario que **ya corría**: a las 06:00 llama al robot (§7) |
| `deploy/noticias.php` | Página **`/noticias`** (listado con filtros por distrito y buscador) |
| `deploy/noticia.php` | Ficha **`/noticia/<slug>`** (con `NewsArticle` de Google) |
| `deploy/api/noticias_json.php` | **La puerta para el chatbot** (§9): las noticias del día en JSON, con el enlace de casa ya armado |
| `deploy/cache/noticias/AAAA-MM-DD.json` | **Registro del día**: fuentes que fallaron, descartes (y por qué), publicadas, tokens |

**📅 LA FECHA VA EN CORTO (orden del jefe, 2026-09-13):** *«usa formato de fecha tipo 01/05/2026, no
"lunes 1 de mayo del 2025", porque ocupa mucho espacio en los titulares, cabeceras o fichas»*.
- **En todo lo visible** (listado, ficha, «otras noticias» y slide) se usa `noticias_fecha_corta()` →
  **`13/09/2026`** y, cuando la hora aporta (listado y ficha), **`13/09/2026 · 6:12 a. m.`**.
- Las horas de las fichas del slide van con `noticias_hora_corta()` (la fecha ya se ve en la cinta arriba).
- El formato en palabras (`noticias_fecha_larga()`) **se queda solo para el chatbot**
  (`fecha_texto` en el JSON), porque ahí se dice hablando: «domingo 13 de septiembre…».
- El `datetime` de los datos estructurados y del `<time>` sigue en ISO (`2026-09-13`), que es lo que
  leen Google y los lectores de pantalla.

---

## §7. ⏰ Cómo se dispara todos los días a las 06:00

**✅ Ya está funcionando, y sin que nadie toque hPanel.** El módulo se cuelga del **Cron Job que ya
existía**: el del **monitoreo** (`0 * * * *`, comprobado el 2026-09-13 con **79 ejecuciones**). Cuando
ese cron entra a las **06:00 de Chimbote**, llama al robot de noticias y este publica. Después, a las
07:00 y siguientes, ve que **el registro del día ya existe y no repite nada**.

```
Cron Job del monitoreo (cada hora)  →  a las 06:00 de Lima llama a  →  cron/noticias_diarias.php
                                        (11:00 UTC)                    (busca, redacta, publica, avisa)
```

**Cómo comprobarlo** (en el navegador, en una pestaña nueva; con `solo-mostrar=1` no envía nada):

```
https://dechimbote.com/cron/monitoreo_sistema.php?k=ChimboteCron2026%23Jimmy&noticias=1&solo-mostrar=1
```

Al final del informe sale el bloque `📰 Hora de las noticias (06:00)…`.

### Si algún día se quiere separar (opcional, más limpio)

Su propio Cron Job en **hPanel → Avanzado → Cron Jobs**, **tipo Personalizado** (⚠️ hPanel va en **UTC**:
11:00 UTC = 06:00 en Chimbote). **No es obligatorio**: hoy la tarea diaria ya sale por el monitoreo.

```
0 11 * * * /usr/bin/php /home/u196269909/domains/dechimbote.com/public_html/cron/noticias_diarias.php
```

> ⚠️ Si se ponen los dos, **no se publica el doble**: el robot cuenta las noticias **ya publicadas hoy** y
> solo completa lo que falte hasta 10 (`noticias_publicadas_hoy()`). El tope es **10 en total por día**,
> no 10 por corrida.

**Probar sin publicar nada** (no gasta saldo, no escribe en la web):

```
https://dechimbote.com/cron/noticias_diarias.php?k=ChimboteCron2026%23Jimmy&ver=1
```

Muestra: fuentes que respondieron y cuántas candidatas dio cada una, **qué se publicaría**, **qué se
descarta y por qué**, y los medios de la zona que no tenemos. Con `&limite=3` publica como máximo 3.

Con el robot en marcha, el **registro del día** queda en `cache/noticias/<fecha>.json` (por FTP).

---

## §8. 🔔 El aviso del Telegram

Cada mañana, después de publicar, llega **un solo mensaje** con el aviso `noticias_dia`:
cuántas se publicaron, el desglose por distrito, los primeros titulares y el enlace a `/noticias`.
Se enciende/apaga en **Súper Admin → 📱 Telegram** (por defecto: encendido). Si un día **no se publica
ninguna**, avisa igual (`error_sitio`) para que la sección no se muera sin que nadie se entere.

---

## §9. 🥷 El chatbot ya no manda al visitante fuera

> 🆕 **2026-09-13 (noche): el diccionario creció de 1 057 a 1 437 palabras.** Al arreglar el buscador del
> chat (el fallo de «clavos») se cargaron **240 frases de Ferreterías** y **140 de Farmacias** en la misma
> tabla `directorio_categoria_claves` que alimenta los enlaces suaves de aquí: ahora hay **más palabras
> que pueden quedar grises** en una noticia. ⚠️ Al añadir frases hay que mirar **`NOTICIAS_ENLACE_NUNCA`**
> (`config_noticias.php`), que es la lista de las que en un diario significan otra cosa («lima», «cadena»,
> «materiales», «puesto»…). Detalle: `GUIA_CHATBOT_DEEPSEEK.md` **§2nonies c-bis**.

### La puerta que usa el chatbot: `GET /api/noticias_json.php`

```
GET https://dechimbote.com/api/noticias_json.php          → las últimas 10 (ventana de 3 días)
GET https://dechimbote.com/api/noticias_json.php?n=1      → solo la última (lo que usa el saludo)
GET https://dechimbote.com/api/noticias_json.php?distrito=nuevo-chimbote
```

Devuelve, por noticia: `titulo · url (NUESTRA página) · entradilla · distrito · zona · fecha · hora ·
fecha_texto · fuente · fuente_enlace · palabras`. **El cuerpo no se devuelve a propósito**: el contenido
se lee en dechimbote.com, que es el objetivo del módulo. Lleva **caché de 5 minutos** (`?refresh=1` la
ignora) y si la tabla estuviera vacía responde `"noticias": []` con **HTTP 200, jamás un 500**.

⛔ **Lo que el chatbot tiene prohibido:** enlazar al medio de afuera (`fuente_enlace` es solo para citar
al pie); el enlace que se le da al visitante es **`url`**. Y el **titular es el enlace**, nunca «clic aquí».

### Lo que hace nuestro propio bot

**Antes:** el saludo contaba una noticia de **Google Noticias** y el enlace se llevaba al visitante a otro
medio (justo lo que el jefe no quería). **Ahora:** `chatbot_diario_noticia_chimbote()` busca **primero
nuestra noticia** (la del robot, publicada hace menos de 2 días) y solo si no hay usa la de Google.

**📅 DÓNDE SALE LA NOTICIA HOY (cambio del 2026-09-13, 4.º pedido del jefe): EN LA PREGUNTA 3 O 4, NO EN EL
SALUDO.** El jefe pidió que el saludo fuera un **mensaje personalizado** (*«hoy será un día soleado, te
saluda el ninja, ¿tienes alguna pregunta para mí?»*) y que la noticia llegara cuando el visitante ya está
conversando: *«el usuario hará su pregunta y el bot responderá y en la pregunta 3 o 4 dirá: hoy "noticia X"
es bueno estar informado»*. Así queda:

```
SALUDO (2 burbujas, al abrir el chat):
  Buenos días! 🥷 Te saluda **El ninja**.

  🌤️ Hoy el cielo está nublado.
  ¿Tienes alguna pregunta para mí? 🥷

PREGUNTAS 1 y 2:  nada de la noticia.
PREGUNTA 3 (o 4): la respuesta de siempre + esta línea al final:
  📰 **Hoy**: [Nuevo Chimbote evalúa riesgo en la quebrada San Antonio](https://dechimbote.com/noticia/…). Es bueno estar informado 🥷
PREGUNTAS 5 y siguientes: no se repite (el enlace ya viaja en el historial).
```

Lo hace **`chatbot_noticia_turno()`** (`includes/chatbot_diario.php`), llamada desde **`api/chatbot.php`**
(el único embudo por el que pasan todas las respuestas): es una consulta a nuestra base pegada al texto,
así que **sale siempre, con nuestro enlace y sin gastar tokens**. Detalle completo:
`GUIA_CHATBOT_DEEPSEEK.md` **§2ter**.

⚠️ **La caché del día se queda con la forma vieja**: el archivo del día se graba UNA vez, así que si
alguien entra al chat a las 5:50 y el robot publica a las 6:00, la noticia contada seguiría siendo la de
Google 24 h. Por eso **nuestra noticia se refresca siempre** al leer la caché (es una consulta a la base,
no gasta internet ni tokens). Y el mapa de etiquetas del chat sabe nombrar `/noticias` («las noticias de
hoy») y `/noticia/<slug>` (el titular real, leído de la tabla).

### 🔒 El 2026-09-13 se cerraron las 3 fugas que quedaban (el saludo no era el único camino)

El saludo ya estaba bien (enlazaba a nuestra ficha), pero el chat **todavía sacaba al visitante del sitio
por tres caminos** que no se ven en el saludo. Los tres se cerraron el mismo día, porque la orden del jefe
es absoluta (*«El enlace que se le da al visitante es SIEMPRE el campo "url"… PROHIBIDO mandar al
visitante al medio de afuera: para eso existe este módulo»*):

| Fuga | Dónde estaba | Cómo quedó |
|---|---|---|
| La respuesta local a «dame las noticias de hoy» | `chatbot_kb.php` → `chatbot_noticias_texto()`: devolvía **las 10 de Agencia Andina** con sus enlaces | `chatbot_noticias_propias_texto()`: **nuestros** titulares (titular = enlace a `/noticia/<slug>`) + la sección `/noticias` |
| El guion que recibe el modelo | `chatbot_diario_guion()`: le pasaba esos 10 titulares externos y le decía «puedes contarlas» | Solo recibe **nuestras** noticias (y solo las marcadas `propia`), con la orden de **no salir del sitio** |
| Las reglas 13 y 16 del prompt | «buscarlo en la fuente (Andina o RPP)» y «si pide más, las nacionales» | Se cambiaron por la sección del sitio + **regla 17 nueva**: *«LAS NOTICIAS SON NUESTRAS Y NO SE SALE DEL SITIO»* |

**Lo que se retiró del código** (y no se vuelve a poner): la función `chatbot_diario_noticias()` (RSS de
Andina/RPP), la función `chatbot_noticias_texto()` y la constante `CHATBOT_NOTICIAS_FUENTES`. La lista del
contexto del día (`cache/chatbot/diario/AAAAAMMDD.json`) **se refresca sola** al leer la caché si su
primera entrada no está marcada como `propia`: así un archivo del día viejo no puede devolver enlaces de
afuera.

**Lo único que sigue saliendo de casa es el respaldo del saludo** (Google Noticias por «chimbote») y
**solo si el sitio no tiene ninguna noticia publicada en 3 días**: es el «último recurso» que el jefe dio
por bueno (*«el saludo busca PRIMERO nuestra noticia y solo si no hay usa la de Google»*), y en ese caso
el modelo tiene la orden de contar **solo el titular, sin enlazar nada**. La palabra clave de esa búsqueda
es **CHIMBOTE**, y valen «nuevo chimbote» y «santa».

**📅 Las fechas visibles van en corto (`13/09/2026`)**: el formato en palabras quedó **solo** para el
`fecha_texto` del JSON del chatbot, que lo dice hablando (ver §6).

---

## §10. Las trampas que aparecieron (y cómo quedaron resueltas)

1. **El hosting mata la petición larga (503).** Publicar 10 noticias por **navegador** dura minutos y el
   hosting devolvió **503** a las 7 publicadas (no se perdió nada: el robot guarda a medida que avanza).
   **Publicar es trabajo del CRON (CLI)**; por navegador se usa `&limite=N`.
2. **Lazo infinito si la base de datos no responde** (encontrado de rebote al probar esto): `db()`
   avisaba al Telegram al fallar… y el aviso **vuelve a consultar la base** → se llamaba a sí mismo hasta
   colgar la petición (7 minutos en la prueba local). Corregido en `config.php`: **se avisa UNA sola vez
   por petición**.
3. **El control de plagio contaba ventanas sueltas** → rechazaba textos bien reescritos (71 coincidencias
   por los nombres propios). Ahora mide la secuencia más larga (**12+ palabras seguidas = copia**).
4. **Bajar la temperatura empeoraba el copiado:** con 0.4 el modelo se pegaba al original. Con **0.55** y
   **0.8 en las correcciones** reescribe de verdad (la fidelidad la dan las reglas y el material).
5. **El tope de enlaces se reiniciaba en cada párrafo** → 9 enlaces con «agua» repetida. Ahora el estado
   es de toda la noticia.
6. **«La Lupa» a veces tarda más de 15 s** y se cae la fuente: el robot lo anota y sigue con las demás.
7. **La primera `noticias.php` del hosting devolvía 500** (arquitectura vieja): se **reescribió** con este
   módulo. `cron_noticias.php` (viejo, también 500) queda reemplazado por `cron/noticias_diarias.php`.

---

## §10bis. 📰 El slide de «fichas rápidas» y los botones de noticias

Pedido del jefe (2026-09-13): *«pon un botón en mi panel de Súper Admin y en todos los paneles de
usuarios logueados… también agrega al menú hamburguesa de la cabecera… en el área de categorías, rubros
y tiendas agrega un slide de 4 noticias actuales al azar en una parte no principal para ofrecerlas como
opción tipo fichas rápidas. Usa el color blanco y morado o rojo oscuro.»*

| Dónde | Qué se puso |
|---|---|
| **Menú hamburguesa** (`includes/header.php`) | Opción **«📰 Noticias de Chimbote»** en el grupo *Descubrir en Chimbote*, debajo de *Buscar negocios* |
| **Página de rubro** (`categoria.php`) | El **slide de 4 fichas**, al final: **después de las tiendas del rubro** (parte no principal) |
| **Buscador** (`buscar.php`) | El **slide de 4 fichas** al final, después de los resultados |
| **Panel del usuario** (`panel.php`) | Un **botón-tarjeta «📰 Noticias de Chimbote»** en la cuadrícula de arriba, al lado del Ninja |
| **Súper Admin** (`superadmin.php`) | Botón **«📰 Noticias (web) ↗»** en el menú del panel (abre `/noticias` en pestaña nueva) |

**El bloque es reutilizable** (`includes/noticias_slide.php`, una sola línea desde cualquier página):

```php
require_once __DIR__ . '/includes/noticias_slide.php';
echo noticias_slide_html();                          // 4 fichas al azar
echo noticias_slide_html(6, '📰 Otras noticias');    // cantidad y título a gusto
```

| Detalle | Cómo es |
|---|---|
| Colores | **Blanco** (`#fff`) + **rojo oscuro** (el granate del sitio, `--color-primario` `#6d071a`) en la cinta de cada ficha y en el icono |
| 🟣 ¿Y el morado? | Está a **un cambio**: en `noticias_slide_css()` poner `--noti-ac:#6d28d9` y `--noti-ac-osc:#5b21b6` |
| Cuántas y cuáles | **4 al azar** (`ORDER BY RAND()`) entre las **publicadas de los últimos 3 días** (`noticias_aleatorias()`), así el bloque cambia solo en cada visita |
| Deslizar | Celular: se desliza con el dedo (scroll-snap, **sin librerías**). Escritorio: flechas **‹ ›** (JavaScript de 10 líneas; si no corre, igual se desliza) |
| Enlace | **El titular es el enlace** a `/noticia/<slug>` + botón «Ver todas ›» a `/noticias`. Nada de «clic aquí» |
| Tipografía | 16 px en el titular de la ficha (Regla de Oro: móvil primero, ≥16 px) |
| Vacío | Si **no hay noticias publicadas**, el bloque **no pinta nada** (ni marco ni título): ninguna página se queda con un hueco |
| Auto-contenido | El CSS viaja con el bloque y se imprime **una sola vez** por página, aunque se llame dos veces |

**Comprobado el 2026-09-13** en pestaña nueva: `/categoria/restaurantes` y `/buscar.php?q=pollo` lo pintan
como **último elemento** del contenido (después de las tiendas / de los resultados), con **4 fichas**, la
cinta en `rgb(109,7,26)`, el texto en `rgb(17,24,39)` y el enlace «Ver todas» a `/noticias`; en cada
recarga salen **noticias distintas** (el azar funciona). Si algún día se quiere también **en la portada**
(`index.php`), es añadir las mismas dos líneas donde el jefe diga.

---

## §10ter. 📅 El listado va AGRUPADO POR DÍAS

Orden del jefe (2026-09-13): *«sí, solo fecha»* y *«las noticias se agrupan por días»*.

| Detalle | Cómo quedó |
|---|---|
| Fecha en la ficha | **Solo la fecha**: `🗓️ 13/09/2026`. La hora no aporta en una tarjeta y ocupaba espacio; **sigue guardada** y se ve dentro de la noticia (`/noticia/<slug>`) y en los datos estructurados |
| Agrupación | El listado va **por días**, del más nuevo al más viejo, con una **cabecera por día**: `🗓️ 13/09/2026 · Hoy · 3 noticias` |
| Etiqueta Hoy/Ayer | La calcula `noticias_etiqueta_dia()` con la fecha de **Lima**; los días más viejos no llevan etiqueta |
| Paginación | **Por DÍAS, no por noticias** (`NOTICIAS_DIAS_POR_PAGINA` = **7** por página): así **un día nunca queda partido** entre dos páginas (que es lo que pasaría paginando de 12 en 12). Los botones dicen «‹ Días anteriores» / «Días siguientes ›» |
| Filtros | El distrito y la búsqueda siguen funcionando y agrupan **solo lo que coincide** (p. ej. Nuevo Chimbote → «13/09/2026 · Hoy · 1 noticia») |
| Motor | `noticias_dias()` (qué días tienen noticias y cuántas) y `noticias_por_dias()` (las noticias de esos días, ya agrupadas) |

**Comprobado el 2026-09-13** en `/noticias`: tres cabeceras (`13/09/2026 · Hoy · 3 noticias`,
`12/09/2026 · Ayer · 2 noticias`, `11/09/2026 · 5 noticias`), ninguna ficha con hora, y el paginador
oculto porque los 3 días caben en una página.

---

## §11. Lo que NO se hace nunca

1. **No se inventa.** Sin texto de la fuente no hay noticia: se descarta. Una noticia falsa sobre un
   accidente o un negocio real de Chimbote es difamación.
2. **No se copia.** El texto es nuestro; el medio se cita al pie con su enlace (`isBasedOn` en los datos
   estructurados). Sin atribución no hay agenda posible.
3. **No se rellena hasta 10.** Si el día da 4 noticias locales, se publican 4.
4. **No se edita a mano.** Las escribe el robot; la página solo las muestra.
5. **No se le da la clave al navegador.** La clave de DeepSeek vive solo en `includes/config_chatbot.php`
   (bloqueado por `.htaccess`).

---

## §12. Pendientes y próximos pasos

- ✅ **La tarea diaria YA corre** (§7): el cron del monitoreo (que ya existía) llama al robot a las 06:00.
  Poner el Cron Job propio es **opcional** y solo para separar responsabilidades.
- 📻 **Pedirle el feed a Radio RSD** (es el que más publica de la zona y el único grande que no entrega
  feed). El registro diario dice cuántos titulares suyos vimos.
- 🧭 **Confirmar la zona «Cascajal»** (¿Chimbote o Nuevo Chimbote?) antes de usarla como señal de
  distrito: hoy se usa como zona visible, no como delator del distrito.
- 🔗 **Decidir si `/noticias` entra al menú del sitio** y si se muestra un bloque de noticias en el index
  (hoy se llega por el chatbot y por el enlace directo; el jefe no lo pidió).
- 📰 **Titulares de medios sin feed**: hoy solo sirven de radar. Para publicarlos habría que leer su web
  (no tienen RSS) y eso se evalúa aparte.
- 📊 **Mirar la tasa de rechazo por copiado**: el 2026-09-13 se descartaron 4 candidatas de 7 en una
  corrida por frases copiadas del original. Si se repite mucho, se ajusta el guion (o el umbral).

---

## §13. Cómo verificar (sin navegador)

```powershell
# 1) Sintaxis de todo lo tocado
C:\xampp\php\php.exe -l D:\RELAX\deploy\includes\noticias.php

# 2) Mirar qué publicaría hoy (en el servidor, sin gastar saldo) — abrir en pestaña nueva:
#    https://dechimbote.com/cron/noticias_diarias.php?k=ChimboteCron2026%23Jimmy&ver=1

# 3) La página y una ficha
(Invoke-WebRequest 'https://dechimbote.com/noticias' -UseBasicParsing).StatusCode
#    y los enlaces suaves de una ficha:
#    (Invoke-WebRequest 'https://dechimbote.com/noticia/<slug>' -UseBasicParsing).Content |
#        Select-String 'noti-suave'

# 4) El saludo del bot (que ya no manda a Google)
(Invoke-WebRequest 'https://dechimbote.com/api/chatbot.php' -UseBasicParsing).Content | Select-String 'noticia/'
```

---

*Guía creada el 2026-09-13.*
