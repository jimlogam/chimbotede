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


# REGLAS DE ORO DEL PROYECTO — dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Este es el documento canónico de reglas.** Si otra guía dice algo distinto a este archivo,
> manda este archivo. Última revisión: **2026-09-12**.

**Las 7 reglas, en una línea cada una:**

| # | Regla | En una frase |
|---|-------|--------------|
| 1 | 🚫 Jimmy no selecciona texto | Nunca pedirle copiar/recortar: todo va en bloque de código con botón "Copiar". |
| 2 | ⭐ UX predictiva | Todo campo es predictivo (escribe y aparecen coincidencias); nunca listas largas. |
| 3 | 🔓 El hosting es de la IA | Se edita y se despliega **solo** desde `D:\RELAX\deploy` (el local **es** la verdad): **sin respaldos del vivo y sin comparar local ↔ hosting** — el jefe nunca toca el hosting. |
| 4 | 🚀 Despliegue | **`php -l`** → subir solo lo cambiado → verificar por HTTP (3 pasos, sin respaldo). |
| 5 | 🖥️ Navegador | Probar en pestaña nueva; no tocar las pestañas del jefe; cerrarla al terminar. |
| 6 | 📝 Documentación | Se documenta en **la guía del módulo** (y **una fila** en su registro si se publicó algo). **No hay crónicas por sesión ni guías por cliente.** |
| 7 | 👁️ Verificar, no suponer | Nada se da por hecho sin comprobarlo en vivo (HTTP, `php -l` o navegador). |
| 8 | 📥 Descargas es la única zona de trabajo | Todo archivo que se revise o publique llega a `C:\Users\Usuario\Downloads` y **jamás** a otra carpeta. |

---

## 🚫 REGLA DE ORO N.º 1 — JIMMY NO SELECCIONA TEXTO (NI EDITA ARCHIVOS) · INVIOLABLE

> **El jefe es Jimmy López.** Nunca selecciona texto en una conversación y nunca edita archivos.
> Esta regla es **inviolable** y tiene prioridad sobre cualquier otra costumbre del proyecto.

- **Mensajes para pegar en otro sitio** (un bot que espera el mensaje, un chat, un formulario, un correo,
  un proveedor, un agente de soporte, otro agente de IA): se entregan **dentro de un bloque de código**,
  que en la GUI de DeepSeek muestra el botón **"Copiar"**. Un clic copia el mensaje **completo** y él lo
  pega tal cual.
- **JAMÁS** pedirle que seleccione o resalte un pedazo de la conversación, ni "copia solo esta parte",
  ni que arme el mensaje a mano.
- **Archivos: a Jimmy se le entregan COMPLETOS**, nunca fragmentos para recortar ni instrucciones del
  tipo "pega este pedazo dentro del archivo". Si un archivo debe cambiar, **lo cambia el agente**; si él
  tiene que subirlo a mano, se le da el **archivo entero**.
- Cuando el mensaje vaya a **otro bot/agente**, indicar además **a quién va dirigido** y **qué debe
  devolver** (la confirmación que él espera), para que solo copie, pegue y espere.
- **Prueba obligatoria antes de responder:** *"¿Jimmy tiene que copiar esto en algún lado?"*
  Si la respuesta es sí y **no** está en un bloque de código con botón de copiar, la respuesta está mal.

## ⭐ REGLA DE ORO N.º 2 — TODO DEBE SER PREDICTIVO E INTELIGENTE (UX)

> El jefe quiere una web **MUY inteligente, SIEMPRE predictiva**. No es una preferencia: es un requisito.

- **En TODOS los campos, formularios, buscadores y `<select>`**: el usuario escribe y las coincidencias
  aparecen solas mientras escribe (filtro en vivo, ignorando tildes y mayúsculas).
- **Muchas opciones ≠ listas largas.** Nunca mostrar decenas de chips/categorías a la vez: al **tocar**
  el campo (o un botón ▾) se ven **TODAS** las opciones en **orden alfabético (A–Z)**, y al escribir la
  lista se filtra al instante.
- **Enter** selecciona la primera coincidencia; una **píldora verde** confirma lo elegido y se puede
  quitar con ✕.
- **Móvil-primero siempre**: poco espacio, pocos toques, una sola columna, fuentes **≥16 px** (evita el
  zoom automático en iOS) y feedback inmediato.
- Aplica a Caminante, altas, asistentes, filtros, paneles y a cualquier formulario futuro.
- **Psicología (efecto IKEA):** primero dejar que el usuario "arme" con acciones (fotos → productos) y
  pedir los datos al final; mostrar cada logro al instante (contador "tu tienda se está armando") y al
  terminar celebrar el resultado recordándole que **siempre podrá editarlo él mismo** (botón ✏️).
- **Patrón de referencia ya implementado:** el rubro predictivo de Caminante →
  `GUIA_CAMINANTE_WEB.md`.

## 🔓 REGLA DE ORO N.º 3 — EL HOSTING ES DE USO EXCLUSIVO DE LA IA (2026-09-12)

> **Orden del jefe (2026-09-12):** *"elimina de las guías la regla de hacer respaldo del vivo o de tener
> que comprobar si lo que está en local coincide con lo que está en el hosting, debido a que yo nunca
> subo nada al hosting de modo manual ni edito archivos ni renombro nada… en pocas palabras el hosting
> es de uso exclusivo de la IA que crea el sitio."*

- 📦 **El código REAL vive en `D:\RELAX\deploy`.** Se edita y se despliega **SOLO desde ahí**.
- 🔓 **El hosting es de uso exclusivo de la IA.** El jefe **nunca** sube, edita, renombra ni borra nada a
  mano: ni por FTP, ni por el hPanel, ni por el Administrador de archivos, ni por phpMyAdmin.
- 🚫 **PROHIBIDO hacer copia de respaldo del archivo vivo antes de subir: ya no es un paso del flujo.**
- 🚫 **PROHIBIDO comparar local ↔ hosting de cualquier forma**: **md5, tamaños, fechas o bajadas de
  comprobación**. No existe el riesgo de "divergencia" que esas dos reglas justificaban, porque **nadie
  de fuera de la IA toca el hosting**: si el hosting tuviera algo distinto, es porque **una sesión
  anterior de la IA lo subió**, y eso se arregla **subiendo el local**, no comparando.
- ✅ **El local ES la verdad.** Se trabaja directo sobre `D:\RELAX\deploy`: sin espejos, sin copias y sin
  ceremonia previa.
- ℹ️ **Lo que ya existe se queda como archivo histórico y NO se usa:** las carpetas `backup\`,
  `_backup_*`, `_vivos_*`, `_live\`, `_espejo_vivo_*` y los scripts de espejo/comparación
  (`espejo_vivo.py`, `espejo_verificar_deploy.py`, `__reco_md5.py`, `__bajar_vivos_*.py`, `__*_comparar.py`).
  Ninguna sesión tiene que correrlos: son el registro de cómo se trabajaba antes.
- ✅ Las guías del proyecto viven junto al código, en `D:\RELAX\*.md`. El índice está en
  `GUIA_MAESTRA_CHIMBOTE_XYZ.md`. **Desde el 2026-09-14 son 41** (las de módulo, las reglas y los datos);
  **para buscar algo se empieza por la `GUIA_MAESTRA`, nunca barriendo la carpeta**.
- 🗃️ Las copias viejas (`_ARCHIVO_GUIAS_OBSOLETAS\`, `_ARCHIVO_CRONICAS\`, `__backup_*`, `_live\`,
  `D:\desorden\chimboteweb\...`) son **obsoletos: no editar ni desplegar desde ahí**. Subir un archivo de
  una copia vieja **rompe el sitio** (fue la causa del HTTP 500 del 2026-09-09).
  🆕 **Todo el papeleo ya cumplido (crónicas, cartas a la IA de imágenes, actas y traspasos de tandas
  cerradas, guías de la PC del jefe y de los módulos retirados) se juntó el 2026-09-14 en
  `_ARCHIVO_HISTORICO_2026-09-14\`** (dentro de los dos archivos anteriores, que se movieron ahí). Es
  **historial: no se lee ni se busca ahí para trabajar** (su mapa está en el `README.md` de esa carpeta).

## 🚀 REGLA DE ORO N.º 4 — DESPLIEGUE (3 PASOS, SIN RESPALDO)

1. **Validar sintaxis antes de subir**: `C:\xampp\php\php.exe -l <archivo.php>` → *"No syntax errors detected"*.
2. **Subir solo lo modificado**, un archivo por vez: `python __subir_uno.py <ruta relativa>`.
3. **Verificar después** por HTTP (`curl.exe`: lo esperado es 200 o 302, **nunca 500**) y, si es visual,
   en el navegador **en pestaña nueva**.

> 🔴 **LA TRAMPA QUE HAY QUE CONOCER ANTES DE SUBIR NADA (2026-09-14): el FTP deja al usuario en
> `/public_html`, y esa carpeta NO es la web: es una COPIA VIEJA anidada DENTRO de la raíz viva.
> La raíz viva es `/`.** Subir a `/public_html` «funciona» (no da error y los bytes quedan idénticos)
> pero **la web no cambia nunca**, y parece cuarentena del antivirus sin serlo. `__subir_uno.py` y
> `__sonda_run.py` ya hacen `cwd('/')` y **comprueban la marca** `assets/css/carrito.css` antes de
> escribir: **no quitar esa comprobación**. Detalle y cómo detectarlo: **`GUIA_DESPLIEGUE_Y_ENTORNO.md`
> §11.1**.

- 🚫 **No hay paso 0 de respaldo** (Regla n.º 3): el jefe nunca toca el hosting, así que no hay nada que
  "proteger" de sus cambios.
- Credenciales FTP: `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` · host **`ftp.dechimbote.com`** · usuario **`u196269909.dechimboteftp`** (cuenta del 2026-09-15).

> Método completo, trampas del entorno y plantillas de script: **`GUIA_DESPLIEGUE_Y_ENTORNO.md`**.

## 🖥️ REGLA DE ORO N.º 5 — NAVEGADOR (CONTROL DEL NAVEGADOR)

1. **Abrir SIEMPRE una pestaña nueva** para pruebas e inspecciones. **Nunca** usar ni tocar la pestaña
   activa del jefe (ni su GUI de DeepSeek ni la que esté usando).
2. Si se le presenta al jefe una **pregunta de selección** y esa pregunta queda esperando en el
   navegador, hay que **regresar/activar esa pestaña** para que él la vea.
3. **Al terminar la tarea o cuando él deba intervenir, cerrar la pestaña de pruebas**, dejando intacta la
   GUI de DeepSeek y cualquier pestaña suya.
4. 🔴🔴 **NUNCA MÁS DE 10 PESTAÑAS ABIERTAS A LA VEZ (orden del jefe, 2026-09-20).** Textual: *«no se debe
   abrir más de 10 pestañas: empezar por 10, cerrar, luego 10 más, luego cerrar, porque si lo llenas de
   pestañas la computadora se reinicia por temperatura… máximo 10 pestañas, y así como las abres, cuando ya
   acabas también cerrarlas, no dejarlas.»* → Se trabaja **por tandas**: se abren **5** (o hasta 10), se
   leen, **se CIERRAN** y recién se abren las siguientes; **al terminar no queda ninguna pestaña abierta**
   de las que abrió el agente. **La GUI de DeepSeek no se cierra nunca**, ni las pestañas personales del
   jefe (si hay duda, **no se toca** y se le avisa). En el flujo de Facebook está desarrollado como
   **regla 23** de `sacando tiendas de vendedores de facebook.md` (§0, §4.2 y §14.4).
5. 💰 **EL TRABAJO DE FACEBOOK CON VENDEDORES SE HACE EN «MODO DEMO» Y CON TOPE DE GASTO (orden del jefe,
   2026-09-20).** Las tiendas que se sacan de un perfil de Marketplace **son DEMO para un PROSPECTO**, no
   para un cliente que ya pagó: *«con que vea su tienda funcionando y vea al menos una tienda, es más que
   suficiente»* → **1 sola tienda · 8 productos (los 8 primeros buenos, elegidos entre 12 anuncios) · un
   solo rubro (el que más anuncios tenga) · hasta 3 fotos por producto · SIN perfil** · **una sesión por
   prospecto** · **tope $0.10**. El trabajo completo (varias tiendas, perfil y catálogo grande) queda para
   **cuando el cliente ya pagó**. Todo el detalle —y las **10 reglas de ahorro** con los precios y el costo
   real medido— está en la **TARJETA DE BOLSILLO (§0.1)** y el **§19** de
   `sacando tiendas de vendedores de facebook.md`, que es lo único que hay que leer para ese trabajo.

## 📝 REGLA DE ORO N.º 6 — DOCUMENTACIÓN (en la guía del módulo, sin crónicas)

> **Orden del jefe (2026-09-14, textual):** *«veo que creas guías por cada cliente y eso no es necesario; la
> única guía que sirve es "publicando a los amigos de Jimmy". No tienes por qué documentar cada sesión…
> Yo nunca pedí que cada sesión sea documentada, porque son más de 1500 negocios: en teoría son 1500
> sesiones, serían demasiadas guías, nadie lo va a leer, se convertiría en basura.»*
> **Su orden original (2026-09-10):** *«todas las veces que termines algo, documéntalo en **su guía
> respectiva**; y si no existe la guía, la creas.»* → **esa «guía respectiva» es LA GUÍA DEL MÓDULO**;
> la crónica por sesión la añadieron los agentes, no el jefe.

- ✅ **La documentación es LA GUÍA DEL MÓDULO** que se tocó (índice en `GUIA_MAESTRA_CHIMBOTE_XYZ.md`):
  ahí van lo nuevo, las trampas con su solución, los comandos exactos, los pendientes y el registro de lo
  hecho. **Si la guía del módulo no existe, se crea una** (una por **tema**, nunca una por cliente).
- 🚫 **NO se crean crónicas por sesión** (`GUIA_SESION_AAAA-MM-DD_TEMA.md`) **ni guías por cliente.**
  Publicar una tienda **no genera ningún archivo nuevo**: se anota **una fila** en la tabla de registro de
  la guía del módulo (p. ej. **§10** de `publicando a los amigos de jimmy.md`).
- 📜 Las crónicas viejas (`GUIA_SESION_*.md`) quedan como **historial**: no se leen, no se citan y no se
  les añade nada. **El 2026-09-14 se movieron todas** (con las cartas, actas, traspasos y las guías de los
  módulos retirados) a **`_ARCHIVO_HISTORICO_2026-09-14\`**.
- Si al terminar una tarea la guía del módulo y el sitio ya no coinciden, **la guía está mal**:
  corregirla antes de cerrar la tarea.

### 6.1 QUÉ SE ESCRIBE EN LA GUÍA DEL MÓDULO (pedido expreso del jefe, 2026-09-10)

> *"Siempre cada vez que termines algo documéntalo en su guía respectiva; y si no existe la guía, la
> creas. Documenta todos los cambios, los errores que encontramos, las soluciones que se dieron, las
> fricciones que ocurrieron y los pendientes, para que las próximas sesiones tengan de dónde seguir."*

**No es solo al cerrar la sesión: es cada vez que se termina una tarea.** Cuatro listas, siempre, en la
**guía del módulo** (lo reutilizable):

| # | Lista | Qué va dentro |
|---|-------|---------------|
| 1 | **Cambios** | Qué quedó distinto y **con qué evidencia** (HTTP, medidas reales, no adjetivos) |
| 2 | **Errores** | Qué falló, **cómo se detectó** y **cómo se arregló** (paso a paso, reproducible) |
| 3 | **Fricciones** | Lo que hizo perder tiempo o confundió **aunque no fuera un error** (una guía desfasada, una herramienta reinventada, un dato mal leído) y cómo se destrabó |
| 4 | **Pendientes** | Lo que queda y **cuál es el siguiente paso concreto** de cada cosa |

Reglas de la §6: **si la guía del módulo no existe, se crea**; los pendientes **viven en la guía del
módulo** (o en la tabla de estados de `GUIA_MAESTRA_CHIMBOTE_XYZ.md`); y **nada de datos obsoletos "por si
acaso"**: si algo dejó de ser cierto, se borra.

**Plantilla rápida para la lista 3 (fricciones), que es la que más se olvida:**

| Fricción | Por qué pasó | Cómo se resolvió | Dónde quedó escrito |
|---|---|---|---|
| *(ej. "el banco de pruebas daba resultados falsos")* | *(faltaba un parámetro en la URL)* | *(abrir con `?api=…` y volver a medir)* | *(§14 de la guía del módulo)* |


## 👁️ REGLA DE ORO N.º 7 — VERIFICAR, NO SUPONER

- Ningún cambio se declara "hecho" sin evidencia: respuesta HTTP, `php -l` o prueba en
  el navegador.
- Los datos de la BD se comprueban con `SELECT COUNT(*)` real; no se copian de guías viejas.
- Si una guía dice algo que el sitio ya no hace, **borrar el dato viejo** en lugar de dejarlo "por si
  acaso": un dato obsoleto cuesta más que un dato ausente.

---

## 📥 REGLA DE ORO N.º 8 — DESCARGAS ES LA ÚNICA ZONA DE TRABAJO (orden del jefe, 2026-09-12)

> **Texto del jefe:** *"ahora una regla para ti: todo se va a encontrar en la carpeta de descargas, nunca se
> va a descargar en otro lugar; su única zona de trabajo es la carpeta Descargas."*

- **La carpeta es `C:\Users\Usuario\Downloads`** y es la **única** zona de trabajo para lo que llega de fuera
  (imágenes de la IA, fotos de negocios, capturas de anuncios, `gemini-code-*.txt`, carpetas por negocio).
- **No se busca ni se descarga en otra carpeta**: nada de Escritorio, Documentos, `D:\RELAX` ni subcarpetas
  propias para material que viene de fuera. Si algo aparece en otro sitio, **se mueve a Descargas** y se
  anota (el jefe no toca archivos: lo hace el asistente).
- **Cada sesión que toca este flujo empieza listando Descargas** y comparando con lo que espera.
- **Al terminar se limpia**: lo ya publicado/usado **se borra de Descargas** (regla permanente del jefe:
  ninguna sesión deja imágenes publicadas acumuladas).
- **Sirve para todo el flujo**: publicar productos (regla del publicador), carpetas por negocio y las
  **portadas de tiendas** (carta a la IA de imágenes → imágenes en Descargas → asociar por ID → publicar →
  borrar).

---

_Documento canónico de reglas. Revisión: 2026-09-10 (fusión de `REGLAS_DE_ORO_PROYECTO.md` y el antiguo `REGLAS_TRABAJO.md`)._
_2026-09-10: aclarado el **alcance del md5** en la regla n.º 3 — se comparan **solo los 2-5 archivos que se
van a pisar**; bajar el sitio entero es un **respaldo**, no una rutina previa a cada tarea._ *(SUPERADO.)*
_2026-09-11 (orden del jefe): **se ELIMINA la comparación por md5** (reglas n.º 3 y 4); el seguro pasó a ser
el respaldo del archivo vivo + `php -l` + verificación por HTTP._ *(SUPERADO.)*
_**2026-09-12 (orden del jefe, la que manda): 🔓 EL HOSTING ES DE USO EXCLUSIVO DE LA IA.** El jefe nunca
sube, edita ni renombra nada a mano, así que **se ELIMINAN también el respaldo del vivo y cualquier
comparación local ↔ hosting** (md5, tamaños, bajadas de comprobación). El flujo queda en **3 pasos**:
`php -l` → subir solo lo modificado → verificar por HTTP. Las carpetas y scripts de respaldo/espejo que ya
existen se conservan **solo como archivo histórico** y nadie tiene que correrlos. Además sigue vigente:
publicaciones de Facebook con **máximo 2 minutos y máximo 2 revisiones** por publicación._
_**2026-09-12 (orden del jefe): 📥 REGLA N.º 8 — DESCARGAS ES LA ÚNICA ZONA DE TRABAJO** (arriba, con el
detalle): todo lo que llega de fuera vive en `C:\Users\Usuario\Downloads`, no se busca en otra carpeta y se
borra al terminar._
