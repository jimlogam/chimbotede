# INSTRUCCIONES DEL ESPACIO DE TRABAJO — dechimbote.com (`D:\RELAX`)

> Este archivo se carga solo al inicio de **toda** sesión de agente en este espacio.
> **Arranque obligatorio:** leer `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (índice de todo el proyecto) y
> `REGLAS_DE_ORO_PROYECTO.md` (reglas canónicas), y después **la guía del módulo** que se vaya a tocar.
>
> 🧭 **CÓMO SE BUSCA ALGO EN LA DOCUMENTACIÓN (2026-09-14):** se empieza por la **`GUIA_MAESTRA`**, que es
> el índice: ahí está **a qué guía ir** para cada tema. **Solo existen las guías de la raíz de `D:\RELAX`**
> (hoy **43** archivos `.md`: las guías, las reglas, este archivo y los apuntes de trabajo); si un documento no está ahí, **no existe para trabajar**: `_ARCHIVO_HISTORICO_2026-09-14\` es
> **historial** (crónicas, cartas viejas, actas y traspasos) y **no se lee ni se busca ahí** salvo que el
> jefe lo pida. Así la búsqueda es rápida y no se pierde el tiempo en papeles ya cumplidos.

## ⚠️ LO QUE MANDA HOY EN PRODUCTOS E IMÁGENES (2026-09-22) — leer antes de tocar productos

> El bloque largo de productos está **más abajo (byte 85 000)** y **NO entra en el presupuesto de lectura**
> de este archivo: por eso el resumen vive aquí arriba. 📖 El detalle, en
> **`GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md`**: **§A.9.3** (reutilizar la misma imagen) · **§A.9.4** (el
> banco) · **§A.9.5** («tener foto» no es «tener buena foto»).

- 🏦 **HAY UN BANCO DE IMÁGENES Y SE MIRA ANTES DE PEDIR NADA.** Registro **`__banco_imagenes.json`** ·
  archivos en **`fotos/banco/`** del hosting (carpeta **neutra**, para que borrar una tienda no rompa las
  fichas que la reutilizan). **`python __banco.py listar`** · **`python __banco.py mirar <nombre>`**.
  🔑 **La clave es NOMBRE + RUBRO**: la «mica» de celular no es la mica de contacto, el «té» del
  restaurante no es el té de la agrícola, la «papa» frita no es el saco de papa ni el «papá» de una
  barbería. Si ya existe, **se reutiliza su ruta** con `__pub_reuso_prod.php` y **no se pide imagen nueva**.
  💳 **«BANCO» SIGNIFICA PRÉSTAMO (orden del jefe, 2026-09-22, textual): el banco PRESTA la imagen a la
  tienda, y cuando la tienda pone su propia imagen el banco la RECUPERA solo. Y el banco TIENE QUE TENER
  SUS IMÁGENES: no se presta un archivo que vive en la carpeta de un cliente («no es como que el banco
  tuviera su dinero guardado en la casa de los clientes»). Si el banco necesita una imagen de alguien,
  **la COPIA** (con sus versiones: móvil · escritorio 800 · completo) y esa copia es del banco.** La
  auditoría es **`__ep_banco_audit.php`** y la mudanza **`__bm_run.py ia [go]`** + `__banco_mudar.php`.
- 🔴 **«TENER FOTO» NO ES «TENER BUENA FOTO».** De las fichas con foto, **1 900 muestran la portada del
  local** en vez del producto; en una muestra de 25 miradas una por una, **19 no sirven** (precios,
  teléfonos, folletos, **nombre de otro negocio**). Y **50 fichas tienen la FOTO ROTA** (la ruta está en la
  base y el archivo no existe). El banco sirve para **tapar huecos Y desplazar la foto mala**.
- 📊 **EL BANCO HOY (2026-09-22): 363 imágenes · el sitio en 7 134 fichas con foto de 11 462 (quedan 4 328
  sin foto).** ⭐ **LA VÍA RÁPIDA ES EL MANIFIESTO DEL JEFE:** él entrega la lista `archivo = C-xxx` de las
  imágenes que deja en Descargas (llegan con nombre genérico, tipo `1 (15).jpeg`) → se guardan en
  **`__bn_manifiesto.json`** y se corre **`python __bn_manifiesto.py [go]`**, que **sube lo nuevo y BORRA lo
  ya usado** («vas borrando lo que ya usaste»), **sin usar visión ni subagentes**.
  El camino completo y las trampas están en el §A.9.4, y **el traspaso de la sesión en el §A.10.0**
  (el mensaje va listo para copiar). Sigue pendiente: **32 códigos que no llegaron** (bloques C y D de la
  tanda 6 = `CARTA_IA_PRODUCTOS_BANCO6_{C,D}_15.md`), la duda del **`C-350`** y las **12 imágenes sin
  manifiesto** que quedan en Descargas.
- 📊 **Tiendas: 5 300** (5 162 con portada · 137 sin ninguna foto) pero **solo 2 917 tienen productos**.
  El 2026-09-21 hubo una **carga de 3 490 portadas** llamadas **`<slug>_01.webp`** (ya **no** `photo_`):
  los pozos de portadas medidos antes de esa fecha **están viejos, hay que volver a medirlos**.

## 🔴 ANTES DE SUBIR NADA AL HOSTING — LA TRAMPA DEL FTP (2026-09-14)

> **El FTP deja al usuario en `/public_html`, y esa carpeta NO es la web: es una COPIA VIEJA y completa
> del sitio, anidada DENTRO de la raíz viva. La raíz viva es `/`.**
> Subir a `/public_html` **no da error** (los bytes y el md5 quedan idénticos) pero **la web no cambia
> nunca**, y eso hace pensar en la cuarentena del antivirus cuando no lo es.
> ✅ `__subir_uno.py` y `__sonda_run.py` ya hacen **`cwd('/')`** y **comprueban la marca
> `assets/css/carrito.css`** antes de escribir: **no quitar esa comprobación**.
> 📖 Detalle, cómo se detecta en 10 segundos y qué más se aprendió: **`GUIA_DESPLIEGUE_Y_ENTORNO.md` §11.1**.

> 🔑 **LA CUENTA FTP ES LA NUEVA DEL DOMINIO NUEVO (2026-09-15, la dio el jefe al mudar el sitio):**
> host **`ftp.dechimbote.com`** · puerto **21** · usuario **`u196269909.dechimboteftp`** ·
> contraseña **`PON_AQUI_LA_CLAVE_DEL_FTP`** · carpeta inicial `public_html` (**pero la raíz viva es `/`**, ver arriba).
> Las cuentas viejas (`u196269909.chimboteftp`, `u196269909.chimbote.xyz`) **ya no existen** (dan **530**).
> Los valores viven en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json`, que es lo que leen los scripts.

## 💬 OPINIONES ANÓNIMAS DE LAS TIENDAS (módulo del 2026-09-14 — pedido del jefe)

> En la ficha, **📝 Descripción** y **💬 Opiniones** son **dos pestañas** (en escritorio, una al costado de
> la otra). 🆕 **CÓMO ESTÁN HOY (pedido del jefe, 2026-09-16):** la **Descripción va COMPLETA de frente**
> (se quitó el recorte de las 100 palabras y el botón «Ver más ▾»), la pestaña de **Opiniones se ve en
> NARANJA** (más notoria, con su contador) y **muestra SOLO las opiniones, de frente y a todo el ancho del
> celular sin bordes a los costados**: se borraron el título «💬 Opiniones de clientes» y el párrafo
> «este es un chat de opiniones anónimo…» (el jefe: *«esos textos totalmente bórralo, no sirve de nada; pon
> las opiniones inmediatamente»*) y **el círculo con la inicial del que opina** (*«el nombre ya se
> encuentra dentro del comentario»*). El formulario no está a la vista — lo abre el **botón-imagen
> «ESCRIBE TU OPINIÓN»** (`assets/img/boton-escribe-tu-opinion.webp`, 11.9 KB, recortado y optimizado a
> WebP del archivo que mandó el jefe) que va **DEBAJO de las opiniones**, dentro de una **ventana modal**
> (se cierra con la ✕, tocando el fondo, con Escape, o sola al publicar). Debajo de cada opinión hay
> **🚩 Reportar** y esos reportes caen en
> **Súper Admin → 🚩 Reclamos de opiniones**, donde el jefe decide con **🗑️ Borrar** o **🙈 Ignorar**.
> Las **99 últimas tiendas creadas** ya tienen **3 opiniones positivas con contexto** (297 en total, escritas
> leyendo el catálogo real de cada una). Guía del módulo: **`GUIA_OPINIONES_ANONIMAS.md`** (motor
> `includes/opiniones.php` · vista del panel `includes/vista_opiniones_admin.php` · endpoint
> `api/reportar.php` con `que=opinar` / `que=reportar_opinion` · semilla `includes/opiniones_semilla.php` ·
> receta para la próxima tanda: `__op100_cosechar.py` → semilla → `__semilla_check.py` → subir → sonda).

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


## 🚫 REGLA INVIOLABLE N.º 1 — JIMMY NO SELECCIONA TEXTO (NI EDITA ARCHIVOS)

> **El jefe es Jimmy López.** Nunca selecciona texto en una conversación y nunca edita archivos.

1. **Todo mensaje que Jimmy deba llevar a otro sitio** (un bot que espera el mensaje, un chat, un
   formulario, un correo, un proveedor, un agente de soporte, otro agente de IA) se entrega **dentro de
   un bloque de código**, que en la GUI de DeepSeek muestra el botón **"Copiar"**: un clic copia el
   mensaje **completo** y él lo pega tal cual.
2. **JAMÁS** pedirle que seleccione o resalte un pedazo de la conversación, ni "copia solo esta parte",
   ni que arme el mensaje a mano.
3. **Archivos: a Jimmy se le entregan COMPLETOS**, nunca fragmentos para recortar ni "pega este pedazo
   dentro del archivo". Si un archivo debe cambiar, **lo cambia el agente**; si él lo sube a mano, se le
   entrega el archivo entero.
4. Si el mensaje va a **otro bot/agente**, indicar **a quién va dirigido** y **qué debe devolver**.
5. **Prueba obligatoria antes de responder:** *"¿Jimmy tiene que copiar esto en algún lado?"*
   Si la respuesta es sí y **no** está en un bloque de código con botón de copiar, la respuesta está mal.

## 📌 LAS REGLAS DE ORO (resumen; el detalle manda en `REGLAS_DE_ORO_PROYECTO.md`)

1. **UX predictiva siempre:** todo campo/buscador autocompleta mientras se escribe; nunca listas largas;
   móvil-primero con fuentes ≥16 px.
2. **El código real vive en `D:\RELAX\deploy`** — prohibido editar o desplegar desde copias viejas
   (`_ARCHIVO_*`, `__backup_*`, `_vivos_*`, `_live`, `D:\desorden\chimboteweb`).
3. **🔓 EL HOSTING ES DE USO EXCLUSIVO DE LA IA (orden del jefe, 2026-09-12): el jefe NUNCA sube, edita
   ni renombra nada a mano** — ni por FTP, ni por hPanel, ni por phpMyAdmin. Por eso: **NO se hace copia
   de respaldo del archivo vivo** antes de subir y **NO se compara nada entre local y hosting** (ni md5,
   ni tamaños, ni bajadas de comprobación): **no existe el riesgo de divergencia** que las justificaba.
   Se trabaja SOLO con `D:\RELAX\deploy`, que **es la verdad**. ⚠️ **Bajar el sitio entero NO es parte del
   flujo** (`espejo_vivo.py` y las carpetas `backup\`, `_backup_*`, `_vivos_*` quedan como **archivo
   histórico**: nadie tiene que correrlos).
4. **Despliegue (3 pasos, sin respaldos):** **`php -l`** → subir **solo lo modificado**
   (`python __subir_uno.py <ruta relativa>`) → **verificar por HTTP** (y en el navegador, en pestaña
   nueva, si es visual). Credenciales FTP: `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json`.
5. **Navegador — EL JEFE DA CLIC ÉL MISMO (orden del jefe, 2026-09-14, textual: *«no es necesario que abras
   en una nueva ventana… yo le doy clic siempre, me gusta dar clic… nunca me robes la atención del
   navegador»*):** **NO se abre ninguna pestaña ni ventana** (ni para probar ni para mostrar), no se
   activa/cierra nada del navegador y **no se le quita el foco**: se le entrega **el enlace** y él hace
   clic. ⚠️ Lo anterior («probar siempre en pestaña nueva» y «cerrar la pestaña de pruebas») queda
   **histórico**: ya no se abre nada.
6. **Documentación — SOLO LA GUÍA DEL MÓDULO (orden del jefe, 2026-09-14):** *«veo que creas guías por cada
   cliente y eso no es necesario; la única guía que sirve es "publicando a los amigos de Jimmy". No tienes
   por qué documentar cada sesión… son más de 1500 negocios, en teoría son 1500 sesiones, serían demasiadas
   guías, nadie lo va a leer, se convertiría en basura.»* → **🚫 NO se crean crónicas por sesión**
   (`GUIA_SESION_AAAA-MM-DD_TEMA.md`) **ni guías por cliente**: lo que hay que saber se escribe en la
   **guía del módulo** que se tocó (si no existe, se crea **una por tema**) y una publicación se anota como
   **una fila** en su tabla de registro (p. ej. §10 de `publicando a los amigos de jimmy.md`).
   Flujo de trabajo y qué escribir: **`GUIA_CRONICA_Y_TRABAJO_DE_SESION.md` §2** (ese archivo conserva su
   nombre viejo, pero ya **no** es plantilla de ninguna crónica). Las crónicas viejas son historial.
7. **📥 DESCARGAS ES LA ÚNICA ZONA DE TRABAJO (Regla de Oro n.º 8, orden del jefe 2026-09-12):** todo lo que
   llega de fuera (imágenes de la IA, fotos de negocios, capturas, carpetas por negocio) vive en
   **`C:\Users\Usuario\Downloads`** — **nunca** se busca ni se descarga en otra carpeta — y lo ya publicado
   **se borra de ahí** al terminar. Las **portadas de tiendas** siguen este flujo: carta IA-a-IA
   (`CARTA_IA_IMAGENES_FLOW.md`) → imágenes en Descargas → asociar por el **ID del nombre** → publicar
   **sin navegador** → **limpiar Descargas**. ⚠️ **NO hay paso de verificación**: publicar cierra el asunto
   (ver la regla 🚫 de más abajo).
   🆕 **FLUJO SIMPLIFICADO (2026-09-13) — si el jefe dice «lee `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` y dame
   20 imágenes», la receta exacta es el §0.1 de esa guía** (20 tiendas = **2 bloques: 15 + 5**). Herramientas
   nuevas: **`__carta_gen.py`** (generador **genérico** para cualquier tanda y cantidad: escribe la
   **plantilla de escenas**, reparte en bloques de 15 máx, saca los pares y **apunta los ids** en
   **`__portadas_pedidas.json`**, que es el registro de las tiendas **ya pedidas y sin publicar** — hoy
   **35** — para no volver a pedirlas: por eso la sonda se pide con **`&n=20+35`**) y el publicador
   `__pub_portadas_tiendas.py` con los modos **`listar <json>`** (mira qué llegó, no publica) ·
   **`json <json>`** (publica) · **`limpiar <json>`** (borra de Descargas lo publicado OK). Ya **no** hay que
   escribir listas `PARES` ni copiar el generador de la tanda anterior (`__carta30_gen_tanda6.py` y los
   otros quedan **históricos**).
   **⏳ LO ÚNICO QUE HAY QUE SABER DE LAS TANDAS VIEJAS: cuáles esperan imagen.** La **tanda 6** (30 tiendas,
   ids **1029 → 805**: se publican con `python __pub_portadas_tiendas.py t6a` y `t6b`; pares en
   `__tanda6_30.json`), la **tanda 7** (20 tiendas, ids **801 → 698**; pares en **`__tanda7_20.json`** y
   escenas en `__tanda7_escenas.json`; al llegar las imágenes: **`python __pub_portadas_tiendas.py listar
   __tanda7_20.json`** → **`json __tanda7_20.json`** → **`limpiar __tanda7_20.json`**) y los **5 archivos de
   la tanda 5**. 🆕 **La tanda 8 (2026-09-15, pedido del jefe: «de 15 en 15»): 15 tiendas, ids 696 → 643, UN
   SOLO BLOQUE** — carta **`CARTA_IA_IMAGENES_TANDA8_A_15.md`** · escenas `__tanda8_escenas.json` · pares
   **`__tanda8_15.json`** ✅ **15 de 15 PUBLICADAS el 2026-09-16** (WebP 1024² · 180-290 KB · todas «antes:
   (sin portada)»). 🔴 **TRAMPA NUEVA (2026-09-16): las 15 llegaron a Descargas con el NOMBRE EN INGLÉS de la
   escena** (`Owner_serving_coffee_at_bodega_…jpeg`, `Pharmacist_working_in_drugstore_…jpeg`…): **el agente
   mira cada foto, la asocia a su tienda por lo que se ve y la renombra él mismo** a
   `portada-<slug>-<id>.jpeg` (renombrador **`__t8_renombrar.py`**, simulacro y luego `go`) **antes** de
   `listar` → `json` → `limpiar`. **Nunca se le pide al jefe que renombre.** 🆕 **La tanda 9 (2026-09-15, noche): 15 tiendas, ids 641 → 545, UN SOLO BLOQUE** —
   carta **`CARTA_IA_IMAGENES_TANDA9_A_15.md`** · escenas `__tanda9_escenas.json` · pares **`__tanda9_15.json`**
   (⏳ esperando imágenes; se publican con `python __pub_portadas_tiendas.py json __tanda9_15.json`).
   🆕 **La tanda 10 (2026-09-15, noche) es LA PRIMERA CARTA CON LAS 15 REGLAS** (objeto **muy cerca** con sus
   estructuras al detalle + nombre en **color claro** + limpio, elegante, de impacto y **sin defectos**):
   **15 tiendas, ids 543 → 449, UN SOLO BLOQUE** — carta **`CARTA_IA_IMAGENES_TANDA10_A_15.md`** · escenas
   `__tanda10_escenas.json` · pares **`__tanda10_15.json`** ✅ **15 de 15 PUBLICADAS el 2026-09-16** (44-182 KB).
   🆕 **La tanda 11 (2026-09-16) también va con las 15 reglas: 15 tiendas, ids 421 → 236, UN SOLO BLOQUE**
   — carta **`CARTA_IA_IMAGENES_TANDA11_A_15.md`** · escenas `__tanda11_escenas.json` · pares
   **`__tanda11_15.json`** ✅ **15 de 15 PUBLICADAS el 2026-09-16** (70-147 KB).
   🆕 **La tanda 12 (2026-09-16) también va con las 15 reglas: 15 tiendas, ids 227 → 86, UN SOLO BLOQUE**
   (1 botica, 1 tienda con servicio técnico de computadoras, 1 taller de gigantografías, 4 de motos y 8 de
   belleza: 2 salones-spa y 6 barberías) — carta **`CARTA_IA_IMAGENES_TANDA12_A_15.md`** · escenas
   `__tanda12_escenas.json` · pares **`__tanda12_15.json`** (⏳ esperando imágenes; se publican con
   `python __pub_portadas_tiendas.py json __tanda12_15.json`).
   🆕 **La tanda 13 (2026-09-16) también va con las 15 reglas: 15 tiendas, ids 82 → 40, UN SOLO BLOQUE**
   (3 barberías, 6 alquileres/inmobiliarias, 1 salón de belleza, 1 escuela de manejo, 1 consultorio dental,
   1 taller de autos y 2 carpinterías) — carta **`CARTA_IA_IMAGENES_TANDA13_A_15.md`** · escenas
   `__tanda13_escenas.json` · pares **`__tanda13_15.json`** ✅ **15 de 15 PUBLICADAS el 2026-09-16**
   (45-106 KB · renombrador `__t13_renombrar.py`; 6 variantes de más borradas).
   🆕 **La tanda 14 (2026-09-16) también va con las 15 reglas: 15 tiendas, ids 39 → 19, UN SOLO BLOQUE**
   (1 carpintería de melamina, 2 vulcanizadoras, 2 consultorios/laboratorio médico, 1 estudio de abogados,
   1 taller mecánico, 3 inmobiliarias, 1 radio, 1 escuela de natación, 1 centro de estimulación temprana,
   1 complejo de canchas sintéticas y 1 restaurante de menú) — carta
   **`CARTA_IA_IMAGENES_TANDA14_A_15.md`** · escenas `__tanda14_escenas.json` · pares **`__tanda14_15.json`**
   (⏳ esperando imágenes; se publican con `python __pub_portadas_tiendas.py json __tanda14_15.json`).
   🆕 **La tanda 15 (2026-09-16, pedido del jefe: «dame 15 más, solo los prompts, cada prompt lo más amplio
   posible») también va con las 15 reglas: 15 tiendas, ids 1650 → 13, UN SOLO BLOQUE** (2 de masajes, 1 de
   cuentas/streaming, 1 electricista, 2 municipalidades —Coishco y Santa—, la biblioteca de la UNS,
   1 veterinaria, 2 talleres mecánicos, 1 consultorio de psicología, 1 agencia de viajes, 1 gimnasio,
   1 mercado y 1 hotel) y **con los prompts más amplios de todas las tandas** — carta
   **`CARTA_IA_IMAGENES_TANDA15_A_15.md`** · escenas `__tanda15_escenas.json` · pares **`__tanda15_15.json`**
   · fuente elegida a mano **`__tiendas_sinportada_t15.json`** (⏳ esperando imágenes; se publican con
   `python __pub_portadas_tiendas.py json __tanda15_15.json`).
   🆕 **NUEVO POZO DEL 2026-09-16 — LAS TIENDAS CON PORTADA VIEJA** (el pozo «sin ninguna foto» se agotó: 138 sin
   foto, 130 ya pedidas y las 8 que quedan incluyen 3 avisos de empleo que **no llevan portada**). Son las
   **278 tiendas activas que YA tienen foto pero cuya PRIMERA foto (la portada) es la VIEJA del proveedor**
   (cargada el **2026-08-31**), más 137 con foto sin fecha. Se miden con la **sonda temporal
   `__ep_tiendas_fotovieja.php`**: `python __sonda_run.py __ep_tiendas_fotovieja.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&n=40&corte=2026-09-01"`
   (se autoborra; devuelve las tiendas **ordenadas por catálogo** con su descripción y sus primeros 6 productos,
   que es lo que come `__carta_gen.py`). De ese pozo salieron **la tanda 16** (ids 1 → 203) y **la tanda 17**
   (ids 242 → 65) —cartas **`CARTA_IA_IMAGENES_TANDA16_A_15.md`** y **`CARTA_IA_IMAGENES_TANDA17_A_15.md`**,
   escenas `__tanda16_escenas.json` / `__tanda17_escenas.json`, pares `__tanda16_15.json` / `__tanda17_15.json`,
   fuente `__tiendas_fotovieja_src.json`— ✅ **PUBLICADAS el 2026-09-16: tanda 17 · 15 de 15** y **tanda 16 · 11 de 15**
   (WebP 1024² · **todas «antes: `fotos/negocio_XX/photo_1.webp`»**, o sea el publicador **REEMPLAZA** la portada
   vieja en su sitio, tal como estaba previsto). 🔴 **4 DE LA TANDA 16 RETENIDAS** porque la portada traía el
   **nombre EN INGLÉS** (prohibido por la orden A «100 % en español»): **63** («"INVICTUS" BARBERSHOP» + lema
   inglés), **194** («Veterinary Clinic»), **240** («Gismondi Eye Clinic / Optometry & Vision Care») y **246**
   («WHOLESALE MARKET») → el diseñador las rehace y **esas 4 imágenes quedaron en Descargas** (no se
   publican). Renombrador de las dos tandas: **`__t1617_renombrar.py`** (asociación hecha **mirando cada foto**;
   en esta ocasión los nombres de archivo venían mezclados en inglés y español). ⚠️ Estas portadas REEMPLAZAN la
   foto vieja
   (no son «antes: (sin portada)»): **comprobado el 2026-09-16 en `__pub_portadas_tiendas.php` — el publicador
   YA lo hace bien**: si la tienda tiene fotos, toma la 1.ª (`ORDER BY orden ASC, id ASC`) y le **UPDATEA la
   ruta** (o sea reemplaza la portada en su sitio); si no tiene ninguna, hace `INSERT … orden 0`. No hay que
   tocar nada.
   🆕 **La tanda 18 (2026-09-16) también del pozo de portada vieja: 15 barberías/peluquerías, ids 67 → 104**
   (Amazing Spa, BARBER SHOP, Barber Shop "CUADRA 8", Diego's, José, Barber Studio & Spa, BARBERIA - MELISSA,
   Barberia 007, Barberia Barbertshop, Barbershop Adrian, Barbershop Estilo & Creacion, Barberstudio Paul
   Saucedo, Blondy Spa & Barberia, Blonx Barber Studio y Don Barbillas), **cada una con un primer plano
   DISTINTO** (el poste de barbero, el lavacabezas, la taza de afeitar con la espuma batida, la outliner, el
   secador de pie, el estuche de cuero de la barba…) — carta **`CARTA_IA_IMAGENES_TANDA18_A_15.md`** ·
   escenas `__tanda18_escenas.json` · pares **`__tanda18_15.json`** (⏳ esperando imágenes; se publican con
   `python __pub_portadas_tiendas.py json __tanda18_15.json`).
   🔴 **TRAMPA DEL POZO RESUELTA (2026-09-16): al publicar, la tienda NO salía del pozo** (el publicador solo
   cambia la `ruta` y `creado_en` sigue viejo) y el generador volvía a proponer tiendas **ya publicadas**.
   **Arreglado en `__ep_tiendas_fotovieja.php`**: ahora exige que la portada sea la foto del proveedor
   (`ruta LIKE '%photo_%'`, parámetro **`solo_viejas`**, por defecto 1) y devuelve **`portada_ruta`**.
   Con eso el pozo quedó en **252 tiendas** y la **tanda 19** (15 barberías/salones, ids **105 → 166**, cada
   una con primer plano distinto) se armó sin repetir ninguna: carta
   **`CARTA_IA_IMAGENES_TANDA19_A_15.md`** · escenas `__tanda19_escenas.json` · pares **`__tanda19_15.json`**
   (⏳ esperando imágenes; se publican con `python __pub_portadas_tiendas.py json __tanda19_15.json`).
   🆕 **La tanda 20 (2026-09-16, pedido del jefe: «dame 30 más en 2 bloques de 15») = 30 tiendas del mismo
   pozo, ids 167 → 243, EN DOS BLOQUES**: bloque A (167 Valexi Beauty Salón → 207 Joyería Jhobel) y bloque B
   (213 PERFECT NAILS BY ELI ABAD → 243 Distribuciones Olano SAC), **cada una con un primer plano distinto** —
   cartas **`CARTA_IA_IMAGENES_TANDA20_A_15.md`** y **`CARTA_IA_IMAGENES_TANDA20_B_15.md`** · escenas
   `__tanda20_escenas.json` (30) · pares **`__tanda20_30.json`** (⏳ esperando imágenes; se publican con
   `python __pub_portadas_tiendas.py json __tanda20_30.json`). ⚠️ **El generador reparte solo en bloques de
   15**: con `--n 30` escribe las DOS cartas y un solo archivo de pares.
   📊 **El pozo sigue con material: de 120 devueltas quedaban 86 libres** (tras pedir estas 30, ~56), así que
   hay para varias tandas más sin volver a correr la sonda.
   🆕 **La tanda 21 (2026-09-16, pedido del jefe: «30 más en 2 bloques») = otras 30 del pozo, ids 245 → 353,
   EN DOS BLOQUES**: bloque A (245 Clínica Veterinaria "Mi Vet" → 294 SERVILLAVES "TERRONES") y bloque B
   (297 Taller Bolaños → 353 Carpintería CHALLE), cada una con primer plano distinto — cartas
   **`CARTA_IA_IMAGENES_TANDA21_A_15.md`** y **`CARTA_IA_IMAGENES_TANDA21_B_15.md`** · escenas
   `__tanda21_escenas.json` (30) · pares **`__tanda21_30.json`** (⏳ esperando imágenes; se publican con
   `python __pub_portadas_tiendas.py json __tanda21_30.json`). ⚠️ **Ojo con los nombres raros de esta tanda**
   (los prompts los copian tal cual): `Dani_nailsstudio` (con guion bajo), `plazaVea express Chimbote Centro`
   (marca, sin logos), `Librería “JOHAN”` (comillas tipográficas), `Lubricantes y vulcanizadora j y D`
   (minúsculas), `Carpinteria` (sin tilde), `Cafeteria Brunei` (sin tilde) y `FERRETERIA ROMEGA COLORS`
   (sin tilde, en mayúsculas) ✅ **28 de las 30 PUBLICADAS el 2026-09-16** con el renombrador
   **`__t21_renombrar.py`** (WebP 1024² · 41-142 KB · **todas «antes: `fotos/negocio_XX/photo_1.webp`»**).
   ⏳ **Faltan 2 portadas de esa tanda: 253 «"NOVEDADES BAZAR JD"» y 286 «plazaVea express Chimbote Centro»**
   (el diseñador mandó **2 variantes** de la Clínica Belen y de KEMEL SPORT: se publicó una y se borró la otra).
   🆕 **La tanda 22 (2026-09-16, pedido del jefe: «30 más en 2 bloques») = otras 30 del pozo, ids 363 → 152,
   EN DOS BLOQUES**: bloque A (363 Salon De Belleza D Reyes → 97 Carlos Barber Shop: 10 locales variados
   —biomarket, pollería, rectificadora, 2 grifos, cevichería, agropecuaria, consultorio— y 5 barberías) y
   bloque B (98 Colombier barber shop → 152 Royal fade studio: barberías, estéticas y salones), cada una con
   primer plano distinto — cartas **`CARTA_IA_IMAGENES_TANDA22_A_15.md`** y
   **`CARTA_IA_IMAGENES_TANDA22_B_15.md`** · escenas `__tanda22_escenas.json` · pares
   **`__tanda22_30.json`** (⏳ esperando imágenes; se publican con
   `python __pub_portadas_tiendas.py json __tanda22_30.json`).
   🔴 **TRAMPA DE LA FUENTE VIEJA (2026-09-16, pasó al armar la tanda 22):** `__tiendas_fotovieja_src.json`
   es una **foto fija** del pozo; al publicar una tanda, esas tiendas **salen del registro**
   `__portadas_pedidas.json` y el generador, si sigue leyendo la fuente vieja, las **vuelve a proponer**.
   → **SIEMPRE que se pidan tiendas nuevas, primero se refresca la fuente con la sonda** y recién después se
   corre `__carta_gen.py`:
   `python __sonda_run.py __ep_tiendas_fotovieja.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&n=160&corte=2026-09-01"` →
   `Move-Item __ep_tiendas_fotovieja_resultado.json __tiendas_fotovieja160.json` → reconstruir el `src`
   (la receta está en la guía §A.8.2). ⚠️ **Ojo con los nombres raros de esta tanda**: `Rectificadora jhon`
   (minúscula), `Barber shop santiago` y `Blecx barber estudio` / `Olimpo barber studio` / `Royal fade studio`
   / `Colombier barber shop` (todo en minúsculas menos la primera letra), `BROOKLYN BARBERSHOP`, `MANJARES`,
   `ORABELA` y `ROMA SALON DE BELLEZA` (mayúsculas y **SALON sin tilde**), `Estética Unisex "ADÁN & EVA"`
   (comillas, &, mayúsculas) frente a `Estética Unisex Adan y Eva` (**sin** tilde, otra tienda distinta),
   `Cevicheria Cielo Azul` y `Barberia Mauri Shop` (sin tilde) y `Pelukitas Kids - PELUQUERÍA INFANTIL SEDE CHIMBOTE`.
   📊 **El pozo quedó en 54 tiendas libres** de las 120 que devuelve la sonda; para más tandas hay que
   subir el `&n=` (el tope de la sonda es 200).
   ✅ **BLOQUE A DE LA TANDA 22 PUBLICADO (2026-09-16): 14 de 15** (ids 363, 366, 368, 372, 373, 374, 379,
   380, 382, 388, 78, 91, 96, 97 · WebP 1024² · 41-119 KB · todas reemplazando la portada vieja) con el
   renombrador **`__t22_renombrar.py`**; llegaron **4 archivos de más** (1 duplicado exacto + 3 variantes) que
   se borraron. ⏳ **Falta la 77 «Barber Shop Mr Mohicano»** (el partido en la televisión) y **todo el bloque B**.
   🔴 **EL TOPE DE LA SONDA DEL POZO SUBIÓ DE 120 A 400** (2026-09-16, estaba escrito en el propio PHP:
   `min(120, n)`): con `&n=300` devolvió **224 tiendas con portada vieja y 128 libres** — material de sobra.
   🆕 **La tanda 23 (2026-09-16, pedido del jefe: «30 más en bloques de 15») = 30 tiendas del pozo, ids
   156 → 122, EN DOS BLOQUES**: bloque A (156 Salón Roxi → 324 Vulcanizadora Caramelito: estéticas, 4 talleres
   de motos, 3 ópticas, 3 dentales, inmobiliaria, ferretería y 2 vulcanizadoras) y bloque B (385 Experiencia
   Dental → 122 Kathyssha Belleza & Estilo: **incluye las 2 PLAZAS** —4 Centro Cívico de Nuevo Chimbote y
   5 Plaza de Armas de Chimbote— y 13 salones/barberías), cada una con primer plano distinto — cartas
   **`CARTA_IA_IMAGENES_TANDA23_A_15.md`** y **`CARTA_IA_IMAGENES_TANDA23_B_15.md`** · escenas
   `__tanda23_escenas.json` · pares **`__tanda23_30.json`** (⏳ esperando imágenes; se publican con
   `python __pub_portadas_tiendas.py json __tanda23_30.json`). ⚠️ **Ojo con los nombres**: `The BROTHER'S
   BARBER STUDIO` (apóstrofo), `Mi casita (Da)` (paréntesis), `Vulcanizadora "Ramón Castilla"` (comillas),
   `Ópticas Crizal lens` y `Optica Alfa` (una con tilde y otra sin), `Centro Cívico` (tilde), `Angeles`
   (sin tilde), `Belle coquette` y `Dayana beauty studio` (minúsculas), y los `&` de `Hope Beauty & Details`
   y `Kathyssha Belleza & Estilo`.
   🔴 **CÓMO ESTÁ EL POZO «SIN NINGUNA FOTO» (2026-09-16, medido al armar la tanda 24):** la sonda
   `__ep_tiendas_sinportada.php` con `&n=200&con_prompt=1` devuelve **123 tiendas sin ninguna foto**, de las
   cuales **118 YA se habían pedido** (tandas 5, 6, 7, 9, 12, 14 y 15: 115 pendientes de imagen + 3 avisos de
   empleo que **no llevan portada**) y **solo 5 estaban sin pedir**: **12 Hospedaje Chimbote, 11 Boticas
   Pharmax, 10 Boticas Dr. Simi, 9 Simbote Restaurant y 8 El Salpreso** → **el pozo 1 está AGOTADO**.
   ⚠️ **La tanda 24 se armó con esas 5 + 25 pendientes** (10 de la tanda 9 con sus prompts originales y las
   15 bodegas de la tanda 7 **con los prompts REHECHOS** con la dirección de arte vigente). ⚠️ **TRAMPA AL REUTILIZAR TIENDAS YA PEDIDAS:** `__carta_gen.py` **salta** los ids que
   están en `__portadas_pedidas.json`, así que hay que **quitarlos del registro antes de generar** (los
   vuelve a apuntar él al terminar) — pasó dos veces seguidas al armar la 24 y salieron cartas de 5 y de 25
   tiendas en vez de 30.
   🆕 **La tanda 24 (2026-09-16, pedido del jefe: «30 más en 2 bloques de cabeceras de tiendas que no tienen
   imagen») = 30 tiendas SIN NINGUNA IMAGEN, ids 12 → 728, EN DOS BLOQUES** — **bloque A**: los 5 que
   quedaban sin pedir (Hospedaje Chimbote, Boticas Pharmax, Boticas Dr. Simi, Simbote Restaurant y El
   Salpreso) + 10 pendientes de la tanda 9 · **bloque B**: 15 bodegas/minimarkets pendientes desde la tanda 7
   con los prompts rehechos. Cartas **`CARTA_IA_IMAGENES_TANDA24_A_15.md`** y
   **`CARTA_IA_IMAGENES_TANDA24_B_15.md`** · escenas `__tanda24_escenas.json` (30; las 5 nuevas en
   `__tanda24_nuevas5.json` y las 15 bodegas rehechas en `__tanda24_escenas_b.json`) · pares
   **`__tanda24_30.json`** · fuente `__tiendas_sinportada_t24.json` (⏳ esperando imágenes; se publican con
   `python __pub_portadas_tiendas.py json __tanda24_30.json`).
   📊 **Para las próximas tandas, el pozo con material es el de PORTADA VIEJA (224 tiendas, 128 libres).**
   🔴 **TRAMPA DE LA SONDA RESUELTA (2026-09-16): la sonda ESCONDÍA tiendas sin portada** — su filtro «sin
   prompt del asistente» dejaba fuera a **15 tiendas con prompt viejo del 2026-09-12 y NINGUNA foto** (por eso
   solo salían 8 «nuevas»). **Ahora la sonda acepta `&con_prompt=1`** y devuelve esas tiendas con el campo
   **`prompt_previo`** (`lote|origen|fecha`): con eso devolvió **138 (todas las sin foto)** y la tanda 15 se
   armó con 12 de ellas + 3 nuevas. ⚠️ **Los avisos de empleo (rubro «Empleos y Trabajos») NO llevan portada**:
   se excluyen a mano. Orden para la próxima:
   `python __sonda_run.py __ep_tiendas_sinportada.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&n=200&con_prompt=1"` →
   `Move-Item __ep_tiendas_sinportada_resultado.json __tiendas_sinportada_200.json`.
   🔴 **Y OJO CON LAS IMÁGENES CUANDO LLEGAN (2026-09-16, comprobado con las tandas 10 y 11): vienen con el
   NOMBRE EN INGLÉS DE LA ESCENA Y ESE NOMBRE NO COINCIDE CON LA TIENDA** (`Pharmacist_reaching_for_medicine`
   era la Botica "SANTO REMEDIO"; `Pharmacist_assisting_customer` era Botica Los Santeños; `Woman_serving_customer_at_bodega`
   era Bodega Nathaniel). **La única forma de asociarlas es MIRAR cada foto y leer el nombre que la portada
   lleva encima** (el diseñador escribe el nombre de la tienda sobre la imagen). Luego el agente las
   **renombra él mismo** a `portada-<slug>-<id>.jpeg` (renombradores `__t8_renombrar.py`, `__t10t11_renombrar.py`)
   y recién ahí publica. **Buscarlas por trozo de nombre + marca de tiempo** (los nombres largos vienen
   cortados con «…»). Varias portadas traen además una **línea de lema en español** debajo del nombre
   («TU SALUD, NUESTRA PRIORIDAD», «100% ORGÁNICO…»): no es defecto, se publica.
   🎨 **DIRECCIÓN DE ARTE NUEVA DEL JEFE (2026-09-15, textual: *«las portadas de negocio deben ser limpias,
   muy elegantes y de impacto visual con colores comerciales»*):** el punto **C** de las 4 órdenes se
   reescribió (la foto es **real y del local, aseada y ordenada**, mercadería alineada con la etiqueta al
   frente, luz pareja y cálida), la
   **R4** y la **R13** se ajustaron y **nació la R14 «ALTO IMPACTO VISUAL Y COLORES COMERCIALES»** (paleta
   viva y cálida; luz abundante y colores vivos) y **después nació la R15
   «LOS OBJETOS MUY CERCA, CON SUS ESTRUCTURAS AL DETALLE»** con el **texto del nombre en color claro** →
   **la carta pasó de 13 a 15
   reglas**, y cada prompt sigue llevando su línea **«ELEMENTO ARTÍSTICO Y TEMÁTICO DEL RUBRO»** (siempre como
   adorno **REAL** del local). También se mantiene lo de la tanda 8: portada **temática del rubro**, con el
   **nombre AL MEDIO, grande y nítido**. El registro
   `__portadas_pedidas.json` va en **120 ids** y ✅ **la sonda trae 68 tiendas nuevas sin pedir** (material de
   sobra para las próximas tandas de 15). Los prompts de cualquier tanda se
   rehacen con su generador (`__carta30_gen_tanda*.py`, `__carta_gen.py`) y **las cartas ya pegadas a la IA,
   los traspasos y las actas están en `_ARCHIVO_HISTORICO_2026-09-14\`** (no se leen para trabajar).
   ⭐ **CAPA DE ESTILO «CARTEL PUBLICITARIO» (orden del jefe, 2026-09-16 — la más nueva y la que manda
   hoy):** el jefe manda a la IA de imágenes **UNA IMAGEN DE REFERENCIA ADJUNTA POR BLOQUE** (un cartel de
   flyer: neón de discoteca, madera ámbar de restaurante, rojo vino de comida, noche urbana amarilla,
   atardecer tropical…) y pide **imitar SOLO SU ESTILO** —tipo de letra, sombras, contrastes y colores— y
   **JAMÁS su contenido** (ni su nombre, ni su rubro, ni sus teléfonos, ni precios, ni fechas, ni logos,
   ni personas). La escena y el texto siguen siendo **los del pedido** (rubro real + nombre de la tienda,
   100 % en español). Nace la **R16** y el punto **3.1** de la carta (qué se copia y qué NO), la **R2** se
   ajustó (ahora el titular **SÍ** lleva contorno grueso, sombra dura y glow) y **cada prompt lleva su
   línea «ESTILO»** con una de **5 maquetas de titular** rotando (2 líneas arriba · 3 líneas al medio ·
   arco · banda · gigante de borde a borde). Sigue siendo **foto real del local** (R5), limpia y elegante,
   en **1:1 (1024²)**.
   🛠️ **La capa se agrega sin reescribir los prompts:** `python __carta26_estilo.py [A|B|C|D]` toma
   `CARTA_IA_IMAGENES_TANDA26_<L>_15.md` y escribe `CARTA_IA_IMAGENES_TANDA26_ESTILO_<L>_15.md` +
   `__carta26e_<l>_cuerpo.txt` (el cuerpo pelado, para pegarlo en el chat dentro de un bloque de código).
   La primera tanda con estilo es la **26 (2026-09-16): 60 tiendas del pozo de portada vieja en 4 bloques
   de 15** (ids **233 → 370**; pares `__tanda26_60.json`, escenas `__tanda26_escenas.json`) — ✅ **10 de 60
   PUBLICADAS** el 2026-09-16 con el renombrador **`__t26_renombrar.py`** (319, 340, 344, 345, 348, 354,
   355, 356, 360 y 367) y **50 RETENIDAS** (ver la R17 de abajo).
   🔴 **R17 — LO QUE SALIÓ MAL EN LA PRUEBA DEL 2026-09-16 (leer SIEMPRE antes de pedir imágenes con
   imagen de referencia):** al imitar el cartel adjunto la IA **se trajo su contenido**: teléfonos y
   WhatsApp, precios, fechas, promociones, las **listas de 3 puntos con sus iconos**, lemas ajenos
   («¡RÁPIDO, SEGURO Y EFICIENTE!», «¡FRESCO, LOCAL Y DELICIOSO!»), direcciones y ciudades que no eran
   del negocio («São Paulo», «Madrid», «Lima»), nombres de **OTROS** negocios (incluido el del propio
   cartel), el **NOMBRE DEL ARCHIVO impreso** en una esquina (chiquito, como marca de agua) y hasta
   **portugués o inglés** en 4 portadas. Por eso la carta lleva ya la regla **R17** («prohibido copiar
   los textos del cartel: el ÚNICO texto es el nombre de la tienda») y, cuando un bloque sale así, se le
   entrega al jefe el **mensaje de corrección** listo para pegar en Flow y **no se publica nada**.
   ✅ **Regla de oro nueva:** antes de dar un bloque por bueno, **mirar 2-3 imágenes** y **no publicar**
   ninguna que traiga teléfono, precio, URL, fecha, nombre de archivo, otro idioma u otro negocio.
   🆕 **La tanda 27 (2026-09-16, pedido del jefe «dame 60 más en bloques de 15») = las 60 tiendas que
   SEGUÍAN sin portada propia de las tandas 20 y 23** (bloque A = tanda 20 A ids 167→207 · B = tanda 20 B
   · C = tanda 23 A · D = tanda 23 B; ids **167 → 122**), reescritas con el estilo de cartel **y la R17
   ya dentro** — cartas **`CARTA_IA_IMAGENES_TANDA27_ESTILO_{A,B,C,D}_15.md`** · generador general
   **`__carta_estilo.py`** · pares **`__tanda27_60.json`** · cuerpos `__carta27e_{a,b,c,d}_cuerpo.txt`.
   ⚠️ **EL POZO «NUNCA PEDIDAS» ESTÁ AGOTADO (medido el 2026-09-16 con `&n=400&corte=2026-09-01`):
   187 tiendas con portada vieja, 179 ya pedidas → quedan 8 libres; y del pozo «sin ninguna foto» quedan
   5.** Por eso cualquier tanda nueva se arma con **tiendas que ya esperan su portada** (el listado sale
   de **`python __t27_pendientes.py`**, que cruza los pares de todas las tandas con el pozo actual).
   🆕 **⭐ FORMATO DE PROMPT NUEVO Y DEFINITIVO (orden del jefe, 2026-09-16 noche — reemplaza a las cartas
   largas):** *«de fondo va una imagen referente al rubro, en texto va el nombre de la tienda; eso es toda
   la información que debemos obtener del sitio: el rubro y el nombre… tú escribes un prompt que diga
   “Crea una imagen con un fondo de una ferretería y crea un texto que diga Ferretería El Buen Pastor, y
   copia los estilos de diseño, color, forma, fuentes de la imagen que se asocia como ingrediente”. Eso es
   todo lo que debes decirle al diseñador.»* → **cada pedido son TRES cosas y nada más: el FONDO (el
   rubro), el TEXTO (el nombre tal cual) y copiar el estilo de la IMAGEN INGREDIENTE adjunta.** ⛔ **FUERA
   las 16 reglas, las escenas largas, el MANIFIESTO, los nombres de archivo dentro del prompt (eso era lo
   que hacía que la IA los imprimiera) y —orden textual— el «OBJETO EN PRIMER PLANO», que «está
   interrumpiendo».** Generador: **`python __carta_simple_gen.py`** (dict `FONDO` id→rubro en palabras) →
   cartas **`CARTA_IA_IMAGENES_TANDA27_SIMPLE_{A,B,C,D}_15.md`** (3.2 KB cada una, 15 pedidos por bloque) ·
   cuerpos para el chat `__t27s_{a,b,c,d}_cuerpo.txt`. Se pega en Flow **con la imagen ingrediente adjunta**.
   🆕 **LA TANDA 29 (2026-09-16, noche — «PORTADAS CON PROMPT CORTO»): 60 tiendas, ids 1032 → 223, EN 4 BLOQUES
   de 15.** El pozo de tiendas **nunca pedidas** está **AGOTADO** (las 3 que quedan son **avisos de empleo**, que
   **no llevan portada**), así que la tanda se armó con **las que llevan MÁS TIEMPO esperando su portada**, con
   las **dos sondas refrescadas el mismo día** (`__tiendas_sinportada_t29.json` = **94 sin ninguna foto**, 91 ya
   pedidas + 3 empleos · `__tiendas_fotovieja_t29.json` = **23 con la portada vieja del proveedor**) → **114
   tiendas esperando portada**; se eligieron **las 60 más antiguas** (tandas **5, 6, 7, 9 y 12**) y **quedan 54**
   para la próxima. Selección **`__t29_seleccion.py`** → `__tanda29_pedido.json`; generador
   **`__carta_simple29_gen.py`** (mismo formato corto) → cartas **`CARTA_IA_IMAGENES_TANDA29_SIMPLE_{A,B,C,D}_15.md`** ·
   cuerpos `__t29s_{a,b,c,d}_cuerpo.txt` · pares **`__tanda29_60.json`** (⏳ esperando imágenes; se publican con
   `python __pub_portadas_tiendas.py json __tanda29_60.json` tras asociar cada foto a su tienda).
   🆕 **La tanda 30 (2026-09-17, pedido del jefe: «búscame tiendas que NO tengan cabecera»): 15 tiendas SIN
   NINGUNA FOTO, ids 227 → 19, UN SOLO BLOQUE** — **el pozo «nunca pedidas» sigue AGOTADO** (sondas
   refrescadas hoy: `__ep_tiendas_sinportada_go_resultado.json` = **93 sin ninguna foto** = 90 ya pedidas +
   las 3 de siempre que son **avisos de empleo** · `__ep_tiendas_fotovieja_resultado.json` = **23 con portada
   vieja**), así que la carta se armó con **las tiendas sin cabecera que llevan más tiempo esperando** (la
   **227 Botica Infarma** de la tanda 12 + 14 de la tanda 14, dejando fuera la 24 para no repetir rubro:
   227, 39, 38, 37, 36, 35, 33, 31, 30, 29, 27, 26, 25, 22, 19 · botica, carpintería, 2 vulcanizadoras,
   2 médicos, abogados, mecánico, 2 inmobiliarias, radio, natación, estimulación temprana, canchas y
   restaurante) — carta **`CARTA_IA_IMAGENES_TANDA30_SIMPLE_A_15.md`** · generador
   **`__carta_simple30_gen.py`** (lee la sonda fresca: los nombres salen de la base, no a mano) · cuerpo
   `__t30s_a_cuerpo.txt` · pares **`__tanda30_15.json`** (⏳ esperando imágenes; se publican con
   `python __pub_portadas_tiendas.py json __tanda30_15.json` tras asociar cada foto a su tienda).
   ⭐ **ORDEN NUEVA DEL JEFE (2026-09-17): el pedido lleva TAMBIÉN EL NÚMERO DE ID** de la tienda, para que
   el diseñador lo ponga **discreto en alguna parte** de la imagen y **en texto plano, sin diseño**
   (cifras simples, sin adornos ni cursiva: *«para no confundir un i con un l»*). Va dentro de cada pedido
   («Añade además, en texto plano y discreto, el número 227») y explicado en la cabecera del bloque.
   📊 **Quedan sin cabecera, para la próxima: la 24** (inmobiliaria, tanda 14) + las **11 de la tanda 15**
   (1650, 1637, 1553, 1539, 1538, 1537, 1536, 1535, 1533, 1532, 1527) + **18, 14 y 13** (tanda 15) y las
   **23 de portada vieja**.
   🆕 **La tanda 31 (2026-09-17, pedido del jefe: «agrúpalos por rubro… elige un rubro al azar y solo en ese
   rubro dame 60 tiendas en bloques de 15, pedidos por carta») = 60 tiendas de UN SOLO RUBRO: Bodegas /
   Minimarkets, EN 4 CARTAS DE 15.** Rubro elegido al azar entre los dos únicos que llegan a 60 pendientes
   (Restaurantes 140 · Bodegas/Minimarkets 135). Cartas
   **`CARTA_IA_IMAGENES_TANDA31_BODEGAS_{A,B,C,D}_15.md`** · generador **`__carta_bodegas_gen.py`** · cuerpos
   `__t31b_{a,b,c,d}_cuerpo.txt` · pares **`__tanda31_60.json`** (ids **405 → 700**: bloque A 405, 607, 822,
   946, 947, 1006, 691, 704, 705, 712, 730, 733, 763, 767, 768 · B 403, 404, 406, 407, 456, 457, 458, 548,
   549, 553, 575, 576, 577, 578, 579 · C 605, 606, 608, 610, 616, 620, 626, 627, 629, 639, 640, 642, 644,
   645, 646 · D 652, 656, 657, 667, 674, 677, 679, 680, 681, 694, 695, 697, 698, 699, 700), las de más
   catálogo primero y **cada una con su FONDO real** leído de su descripción (bodega de abarrotes, de
   bebidas heladas y hielo, licorería, distribuidora de abarrotes, bodega y ferretería, bodega y
   pastelería…). ⏳ esperando imágenes; se publican con
   `python __pub_portadas_tiendas.py json __tanda31_60.json`.
   🆕 **La tanda 32 (2026-09-17, pedido del jefe: «dame 60 más si los hubiera, nunca me des de otro rubro,
   solo Bodegas / Minimarkets por ahora») = los 60 SIGUIENTES del mismo rubro, EN 4 CARTAS DE 15.** De las
   **135 bodegas pendientes** ya se habían pedido 60 (tanda 31) → este lote son las **posiciones 61 a 120**
   (ids **701 → 974**) y **quedan 15** para la próxima. Cartas
   **`CARTA_IA_IMAGENES_TANDA32_BODEGAS_{A,B,C,D}_15.md`** · generador **`__carta_bodegas2_gen.py`** ·
   cuerpos `__t32b_{a,b,c,d}_cuerpo.txt` · pares **`__tanda32_60.json`** (bloque A 701, 703, 706, 711, 713,
   714, 717, 719, 720, 721, 722, 727, 734, 735, 736 · B 737, 739, 740, 742, 743, 744, 754, 755, 756, 758,
   759, 760, 762, 765, 766 · C 769, 770, 773, 774, 775, 776, 777, 778, 779, 780, 781, 785, 786, 787, 788 ·
   D 790, 791, 792, 794, 823, 824, 825, 826, 827, 828, 943, 944, 945, 948, 974). ⏳ esperando imágenes; se
   publican con `python __pub_portadas_tiendas.py json __tanda32_60.json`.
   ✅ **PUBLICADAS EL 2026-09-17 (el jefe dejó 105 imágenes en Descargas): 66 de las 120 bodegas** — 33 de la
   tanda 31 y 33 de la tanda 32 (WebP · 896x1200 o 1024x1024 · 49-202 KB · **todas reemplazan la portada
   vieja del proveedor** `fotos/<slug>/photo_1.webp`; 3 eran «(sin portada)»: 824, 827 y 945). Cómo se
   hizo, porque **este es el camino largo que hay que repetir**:
   **1)** inventario de Descargas: **105 imágenes sueltas** con el **nombre en inglés de la escena**
   (10:40-10:44) → `__t33_lista.txt`;
   **2)** **7 subagentes con visión, 15 imágenes cada uno**, cada uno leyó con `read_image` y devolvió
   `sufijo | NOMBRE_IMPRESO | ID_VISIBLE | DEFECTOS`: **el diseñador YA imprime el número de ID discreto en
   una esquina** (lo pidió el jefe el 2026-09-17), y eso hace la asociación exacta sin adivinar;
   **3)** el agente miró las dudosas de «precios» y fijó el criterio: **escena real del local + nombre legible
   + ID ⇒ SE PUBLICA** (aunque traiga lema en español o etiquetas de precio del propio anaquel); **folleto
   con precios encima, teléfono/WhatsApp, texto en inglés, nombre de OTRO negocio, rótulos deformados o
   nombre mal escrito ⇒ SE RETIENE**;
   **4)** `python __t33_armar.py go` deja cada imagen con el nombre que espera el publicador
   (`portada-<slug>-<id>.jpeg`) y borra los duplicados exactos (1: la 579 venía dos veces);
   **5)** `python __pub_portadas_tiendas.py json __tanda31_60.json` (33 ✅) y `... __tanda32_60.json` (33 ✅) ·
   **6)** `python __t33_limpiar_descargas.py go` borra de Descargas las 66 publicadas (132 archivos: la
   copia renombrada + el original en inglés) — ⚠️ **el `limpiar` del publicador solo recuerda la última
   corrida** (dijo «de otra tanda (no se tocan): 33»), por eso el borrado se hace con la lista propia.
   🔴 **TRAMPA NUEVA (2026-09-17): el publicador NO alcanza a borrar su sonda** — el FTP se duerme
   (`421 Idle timeout (60 seconds)`) mientras se publican las imágenes por HTTP y `ftp.delete` falla al
   final (y `ftp.quit()` revienta con `EOFError`). **La sonda `__pub_portadas_tiendas.php` y su `.log`
   quedan en la raíz viva**: se borran aparte con **`python __t33_limpia_sonda.py`** (y comprueba que ya no
   responden). Hacerlo SIEMPRE después de cada corrida.
   ⏳ **LO QUE QUEDÓ PENDIENTE DE ESTE RUBRO (54 = 38 retenidas + 16 sin llegar)**: carta de rehacer
   **`CARTA_IA_IMAGENES_TANDA33_BODEGAS_{A,B,C,D}_15.md`** (A/B/C de 15 y **D de 9**) · generador
   **`__carta_bodegas3_gen.py`** · cuerpos `__t33b_{a,b,c,d}_cuerpo.txt` · pares **`__tanda33_54.json`**.
   **RETENIDAS (38)**: 405 («BODEGA» repetida + otro negocio en el fondo «BODEGA LAYNER»), 403 («Bodeega»
   mal escrito), 553 («Authentic»), 578 (inglés de fondo), 605 (fecha + iconos ✕ ⋯), 607, 610, 616, 644,
   652, 656, 677, 680 («SADIKU»), 681, 691, 694, 698, 699, 712, 733, 744, 755, 763, 767, 768, 769, 776,
   777, 778, 779, 781, 785 (folleto con precios), 787, 788, 822, 946, 947 y 1006 (los motivos, uno por uno,
   en `__t33_armar.py`). **SIN LLEGAR (16)**: **701, 703, 706, 711, 713, 714, 717, 719, 720, 721, 722, 727,
   734, 735, 736** (todo el bloque A de la tanda 32) y **773**. Esas 38 imágenes retenidas **siguen en
   Descargas** (no se borran: son la prueba de lo que hay que rehacer).
   ⚠️ **Muchas retenidas traen el mismo defecto repetido**: el texto inglés «QUALITY YOU CAN TRUST» y el
   **nombre de otro negocio en el fondo («BODEGA LAYNER»)** salen de la **imagen ingrediente** que se pegó
   en Flow — la IA los copió, que es justo lo que prohíbe la **R17**. Para la próxima: **revisar la imagen
   ingrediente** y, si trae textos o rótulos ajenos, recortarla o cambiarla.
   📌 **ORDEN PERMANENTE DEL JEFE (2026-09-17): no darle tiendas de otro rubro mientras esté con uno**
   (*«nunca me des de otro rubro ok, solo Bodegas / Minimarkets por ahora»*) → las próximas tandas de
   portadas siguen siendo **Bodegas / Minimarkets** hasta que él diga lo contrario (quedan 15 de ese rubro;
   cuando se acaben, se le avisa y él decide).
   🆕 **CAMBIO DE RUBRO (2026-09-17, pedido del jefe: «dame otro rubro y dime cuántas son») → el rubro pasa a
   RESTAURANTES**, que es **el pozo más grande que queda: 140 tiendas esperando portada** (medido con
   `__ep_rubro_conteo.php` ese mismo día: **2 sin ninguna foto + 138 con la foto vieja del proveedor**),
   repartidas en Chimbote 52 · Nuevo Chimbote 44 · Santa 16 · Coishco 11 · Samanco 8 · Nepeña 7 · Moro 1 ·
   Macate 1 (son cevicherías, pollerías, parrillas, restobares, chifas, cafeterías y menús).
   ⚠️ **El rubro «Restaurantes» NO incluye a los que tienen rubro propio** (Cevicherías, Chifas, Pollerías,
   Heladerías, Dark Kitchens y Tiendas Veganas se cuentan aparte en su propia fila de la tabla).
   **La tanda 34 son las 60 de más catálogo, en 4 bloques de 15** (ids **390 → 687**) — cartas
   **`CARTA_IA_IMAGENES_TANDA34_RESTAURANTES_{A,B,C,D}_15.md`** · generador **`__carta_resto_gen.py`**
   (cada una con su **FONDO real** leído de su descripción: restaurante de ceviche y mariscos, de sushi,
   parrilla, pollería, restobar, chifas al wok, cafetería, menú del día…) · cuerpos
   `__t34r_{a,b,c,d}_cuerpo.txt` · pares **`__tanda34_60.json`** (⏳ esperando imágenes; se publican con
   `python __pub_portadas_tiendas.py json __tanda34_60.json`). **Quedan 80 restaurantes** para las
   siguientes tandas de ese rubro.
   🆕 **La tanda 35 = los 60 SIGUIENTES de Restaurantes (posiciones 61 a 120, ids 702 → 1318)**, pedidos por
   el jefe el 2026-09-17 con la orden *«respeta este orden: dame las próximas 60… después de eso busca en la
   carpeta de descargas»* — cartas **`CARTA_IA_IMAGENES_TANDA35_RESTAURANTES_{A,B,C,D}_15.md`** · generador
   **`__carta_resto2_gen.py`** · cuerpos `__t35r_{a,b,c,d}_cuerpo.txt` · pares **`__tanda35_60.json`**
   (⏳ esperando imágenes). **Quedan 20 restaurantes** para una última carta de ese rubro.
   ✅ **SEGUNDA VUELTA DEL 2026-09-17 (tarde): el jefe dejó 74 imágenes en Descargas → 50 publicadas.** Los
   dos lotes: **14 imágenes a las 19:18** (bodegas **rehechas** de las retenidas de la tanda 33 → 13
   publicadas) y **60 a las 19:59-20:01** (los restaurantes de la tanda 34 → **37 publicadas**). Total del
   día: **116 portadas** (66 de la mañana + 50 de la tarde). Camino usado, otra vez el mismo:
   **5 subagentes con visión** (15+15+15+15+14 imágenes) → `sufijo | NOMBRE_IMPRESO | ID_VISIBLE | DEFECTOS`
   → **`python __t36_armar.py go`** deja cada imagen con su nombre definitivo → `json __tanda33_54.json`
   (13 ✅) y `json __tanda34_60.json` (37 ✅) → **`python __t36_limpiar_descargas.py go`** borró las 50
   publicadas (100 archivos: la copia renombrada + el original en inglés).
   🔴 **EL DEFECTO QUE MÁS SE REPITIÓ EN LOS RESTAURANTES (2026-09-17 tarde): el fondo con «FAMILIA Campero»
   repetido** — es el **cartel de OTRO negocio** (la cadena guatemalteca Pollo Campero) que se usó como
   imagen ingrediente y la IA copió al fondo. Se **retuvieron 10 por eso** (582, 583, 580, 572, 581, 569,
   568, 565, 564, 556) y 2 más con el mismo fondo (585, 556). Otros motivos de retención: **nombre
   deformado** (661 «RESTAURNT», 597 «PASTAUR NTE MARY», 571 «Braava»), **titular deformado** (557, 558),
   **nombre mezclado con otro** (687 «¡PASTELMIA BOHEMIA!», 636 «PASTELITOS LAS CAMARITAS», 403 «Bodega de
   todo y para todos»), **lema deformado** (659 «¡Rábares Caseros!»), **bloque vacío** (664),
   **palabra repetida** (538 «TRATTORIA» tres veces) y **texto en inglés** (433 ROUTE 66, 570 MADE IN).
   ⏳ **QUEDAN 24 RETENIDAS EN DESCARGAS** (no se borran: son la prueba de lo que hay que rehacer) — 23
   restaurantes + la bodega 403. **Las 116 publicadas SÍ se borraron de Descargas.**
   ✅ **Dato bueno del lote nuevo:** las portadas de restaurantes salen **limpias y con el ID discreto**
   (390 El Cevichón, 465 Sushi Top, 829 Flora, 440 D'Carito, 723 Barvaria, 936 1969 Grill, 19 El Oso Bejar,
   399 Pollos Alfamás…) y **12 de las 14 bodegas rehechas salieron limpias** (se fueron los teléfonos y el
   «BODEGA LAYNER» del fondo).
   ⚠️ **Ojo con los lemas nuevos**: muchas traen «Su mejor mercado», «entrega rápida y calidad garantizada»
   o la **firma del diseñador** («Creaciones de LEO y ELI», «Creaciones YOYE Yoxsily Yese») en un listoncito
   abajo: **no se retuvo por eso** (son en español y chicos), pero el jefe puede pedir que se quiten.
   📊 **Reparto de pendientes por rubro al 2026-09-17 (después de publicar las 66 bodegas): activas 1 707 ·
   sin ninguna foto 88 · portada del proveedor 900 · PENDIENTES 988.** Los grandes: **Restaurantes 140 ·
   Bodegas/Minimarkets 69 · Farmacias/Boticas 49 · Ferreterías 43 · Gimnasios 33 · Librerías 33 ·
   Veterinarias 32 · Mecánicos 31 · Dentistas 31 · Salones de belleza 29 · Doctores 29 · Ópticas 28 ·
   Barberías 27 · Tiendas de ropa 27.**
   🔴 **DATO GRANDE — CORRECCIÓN DE UN ERROR VIEJO (2026-09-17): el pozo de «portada vieja» NO eran 23
   tiendas, son 962.** La sonda `__ep_tiendas_fotovieja.php` exigía `creado_en < '2026-09-01'` y las fotos
   del proveedor se cargaron el **2026-08-31 (23)**, pero sobre todo el **2026-09-01 (607)** y el
   **2026-09-03 (319)**: quedaban fuera casi todas. **La medida correcta es «la 1.ª foto (la portada) tiene
   `ruta LIKE '%photo_%'`, sin filtro de fecha»** → activas **1 705** · **sin ninguna foto 92** · **portada
   del proveedor 962** · **pendientes de portada 1 054**. Sondas nuevas (se suben con `__sonda_run.py`, se
   autoborran): **`__ep_rubro_conteo.php`** (el reparto por rubro) y **`__ep_bodegas_pendientes.php`**
   (lista un rubro entero: `python __sonda_run.py __ep_bodegas_pendientes.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x
   "&rubro=Bodegas%20/%20Minimarkets&n=200"` — ⚠️ **el rubro va con `%20`: el espacio rompe la URL**).
   📊 **Pendientes de portada por rubro (2026-09-17): Restaurantes 140 · Bodegas/Minimarkets 135 ·
   Farmacias/Boticas 49 · Ferreterías 43 · Gimnasios 33 · Librerías 33 · Veterinarias 32 · Mecánicos 31 ·
   Dentistas 31 · Salones de belleza 29 · Doctores 29 · Ópticas 28 · Barberías 27 · Tiendas de ropa 27 ·
   Hoteles 26 · Cevicherías 24** … (la tabla completa está en `__ep_rubro_conteo_resultado.json`).
   ⚠️ **LO QUE SIGUE ESPERANDO (23 de portada vieja):** las **15 barberías/peluquerías del bloque B de la tanda
   28** (75, 76, 79, 81, 83, 84, 85, 87, 89, 93, 94, 104, 105, 106, 107 · su bloque nunca bajó imagen) + **133**,
   **116**, **277** y las **5 de la tanda 25** (153, 154, 155, 157, 162).
   🔴 **LA SONDA DE PORTADAS SE SUBE CON `__sonda_run.py`, NUNCA CON `__ep_run.py`** (2026-09-15):
   `__ep_run.py` escribe en **`/public_html`** (la copia vieja anidada) y el sitio responde **404 en todo**;
   la orden exacta es
   `python __sonda_run.py __ep_tiendas_sinportada.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&n=200"` (el 3.º argumento es
   la clave `go`, que aquí no se usa; el 4.º es la cola de la consulta) y **el tope de la sonda pasó de 80 a 200**.
   ⭐ **LAS 4 ÓRDENES DEL JEFE que van ARRIBA en toda carta** (la carta pasó de 11 a **15 reglas**; la **C**
   fue **reescrita el 2026-09-15**): **A) 100 % en español** (nunca traducir) · **B) 100 % orgánicas** ·
   **C) FOTOS REALES DEL LOCAL, LIMPIAS Y MUY ELEGANTES, de ALTO IMPACTO VISUAL y con COLORES COMERCIALES**
   (⭐ **TODO SE VE NUEVO, LIMPIO Y BIEN CUIDADO**; ⛔ **en las cartas NO se escribe el vocabulario de la vejez,
   el descuido ni el desgaste** — el generador **copia lo que lee**: si una imagen sale con el objeto
   maltratado, **se retiene y se repite**, pero eso **no se anuncia en el prompt**) · **D) EL NOMBRE DEL NEGOCIO NO REPRESENTA
   NADA**: es **solo una etiqueta de texto** y la escena es la del rubro — ferretería «Virgen del Carmen» =
   ferretería (no vírgenes ni santuario), colegio «Miguel Grau» = **fachada de colegio** (no el personaje),
   «Dios Con Su Poder» = farmacia (no manos celestiales), «lucero» = bodega (no estrellas).
   🆕 **Y LAS DOS REGLAS NUEVAS DEL 2026-09-15 (noche, textual del jefe: *«me gustan los trabajos limpios,
   elegantes, de impacto visual, y muy cercanos los objetos, con detalles de sus estructuras, y con textos
   claros como lila claro, melón, blanco, celeste… colores claros que destaquen sobre el fondo»*):**
   **R15 = EL OBJETO MUY CERCA, CON SUS ESTRUCTURAS AL DETALLE** (primer plano: cómo está hecho, su
   material, su trama, sus uniones) y **el TEXTO DEL NOMBRE EN COLOR CLARO QUE DESTAQUE SOBRE EL FONDO**
   (blanco, celeste, lila claro, melón…), nunca un tono que se confunda con la escena. Detalle:
   `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` §A.4 punto 5 y `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md` §B.1 y §B.3.
   **Tanda 5:** 30 tiendas (ids **1246 → 1032**); ⏳ **faltan 5 archivos** en Descargas: **1234** (`t5a2`) y
   **1045, 1042, 1040, 1032** (`t5b2`). Generador `__carta30_gen_tanda5.py`, pares en `__tanda5_30.json`.
   **Las tandas 1 a 4 están cerradas y publicadas** (la historia, en el archivo). De la tanda 4 en adelante
   vale esta regla: la guía **`GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` PARTE A** — **es AUTOSUFICIENTE: al agente
   de portadas se le manda leer SOLO esa** (ahí está la base de datos de donde salen las tiendas sin foto,
   los comandos, las reglas y lo que debe devolver el jefe).
   ✅ **CABECERAS · TANDA 1 (2026-09-15): 20 de 20 PUBLICADAS** (ids **199, 43, 44, 175, 52, 61, 247, 51,
   274, 28, 46, 50, 49, 17, 60, 62, 21, 23, 237, 326** — las tiendas **sin cabecera con más catálogo**,
   rubros variados; todas «antes: (sin portada)», WebP 1024² de 93 a 292 KB). Carta en 2 bloques
   (`CARTA_IA_IMAGENES_TANDA1_A_15.md` y `..._B_5.md`) · pares **`__tanda1_20.json`** ·
   escenas **`__cab_tanda1_escenas.json`** · **nombres en `__portadas_pedidas.json`**.
   ⚠️ **El jefe dejó las 20 imágenes dentro de una CARPETA** (`Downloads\Nueva carpeta (4)`, con el
   manifiesto en un `.txt`): se pasaron a Descargas **con su nombre de portada** usando
   **`python __cab_preparar.py go`** (copia desde la carpeta) → `listar` → `json __tanda1_20.json` →
   `limpiar __tanda1_20.json` → **y la carpeta se borró** al terminar. Esa es la receta cuando el lote
   llegue dentro de una carpeta: **copiar con nombre, publicar y borrar la carpeta**.
   ⏳ **Quedan 183 tiendas sin cabecera** (203 menos estas 20) para las próximas tandas.
   ⚠️ **2026-09-12 — REGLA IRREVOCABLE (aclarada por el jefe):** el agente de portadas trabaja **SOLO con
   los archivos sueltos `portada-*.jpeg` de Descargas** y **JAMÁS abre, lista ni husmea dentro de una
   carpeta** (ni las `Sesion-*`: ese flujo de carpetas es de **otro agente**). Así queda resuelto el
   choque con la orden «solo carpetas `Sesion *`»: **esa orden no es para este flujo**.
   🧭 **Criterio del jefe al revisar (2026-09-12):** una **letra** distinta (i/Y, c/s, b/v, tilde) **no es
   problema: se publica**; lo que **obliga a retener** es una **palabra distinta** («GARCÍA» → «Silla») o
   una **traducción** («pollo caliente» → «Pollo Hot»). Detalle: `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` §A.6.
   🚫 **NO SE VERIFICA NADA (orden del jefe, 2026-09-12, textual):** *«Yo pedí que no se verifique nada…
   debemos confiar en el trabajo de los compañeros. El diseñador ya verificó: debes confiar en su trabajo; y
   si tú ya subiste, nadie debe dudar de tu trabajo subiendo, ni siquiera tú.»* → **ni sondas de
   verificación, ni comprobaciones por HTTP, ni revisar lo publicado**: publicar **cierra** el asunto
   (guía `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` §A.2 regla 8).
   Detalle: `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` §A.2.

## 🗂️ DATOS ÚTILES DEL PROYECTO

- 🏷️ **LOS RUBROS SON 40 Y HAY UNA PÁGINA CON LA LISTA COMPLETA (`/rubros`, 2026-09-19).** El jefe pidió
  **ver todos los rubros** (*«¿existe alguna página donde yo pueda ver los rubros?… una página donde se vean
  TODOS los rubros. Cuando digo rubros me refiero a los NOMBRES DE LAS CATEGORÍAS, no a las tiendas ni a los
  productos que viven adentro»*) y, el mismo día, **reorganizarlos** (*«haz esos cambios usando tu criterio y
  trata de tener rubros un poco más abiertos más extensos»*): **de 124 rubros activos a 40** (fusiones,
  renombres y bajas), **519 tiendas y 9.319 palabras clave movidas** y **ninguna tienda quedó sin rubro**.
  La página es **`deploy/rubros.php`**, se sirve en **`/rubros`** (y en **`/categorias`**), lista los 40 en
  orden alfabético **con las tiendas de cada uno** y filtra al instante por nombre **y por palabras clave**
  («pollo» → Restaurantes). Está enlazada desde el **menú ☰** («Ver todos los rubros»), el **pie del sitio**,
  el **cajón de rubros del Explorer** y el **`sitemap.xml`**. ⚠️ **Cada rubro viejo tiene su 301 al nuevo en
  el `.htaccess`, y esos 301 van ANTES de la regla general `^categoria/<slug>` (que lleva `[L]`: puestos
  debajo no se aplicarían nunca)**. 🔴 **Y la página de un rubro (`categoria.php`) AHORA TIENE PAGINADOR
  (`?p=2`…, 24 por página): mostraba solo las 12 primeras y decía «12 negocio(s)», así que al jefe le pareció
  que se habían perdido las otras 196 de Bodegas. El buscador tenía el mismo recorte (24) y ahora avisa
  «hay N más» con el enlace al rubro.** Si un rubro pasa de 24 tiendas, **tiene que paginar**; y cualquier
  lista con `LIMIT` sin decir el total es el mismo problema. El mapa rubro por rubro, las herramientas
  (`__rr_mapa.py` + `__rr_aplicar.php` con **simulacro obligatorio**) y las 6 trampas: **`GUIA_TIENDAS_PORTADAS_Y_RUBROS.md`
  §B.12 y §B.13** (la paginación, en el §B.13.6). ⚠️ Y una lección de red: **una ráfaga de peticiones seguidas
  hace que el hosting devuelva 403 en TODO el sitio** (parece que rompiste la web y no es eso): verificar con
  **2 s entre peticiones**.
- 📞 **EL NÚMERO DEL ADMINISTRADOR CAMBIÓ A `908785164` (orden del jefe, 2026-09-19 — LO QUE MANDA HOY).**
  Textual: *«908785164 usa este número de teléfono para recibir mensajes de WhatsApp que vayan dirigidos
  al administrador, todo lo que tenga que ver con administración del sitio… repito, solo para temas
  relacionados de administración… digamos que es el número del dueño de la página web»*. Vive en la
  constante **`ADMIN_WHATSAPP`** (`deploy/includes/helpers.php`): con ella reciben **el pie de página, la
  404, los reclamos de tiendas, los avisos de empleo, la recuperación de cuenta, «hablar con una persona»
  del chat y el «quiero más productos» de El maestro** (`TIENDA_IA_WHATSAPP_MAS` = `ADMIN_WHATSAPP`).
  ⚠️ **El `955 041 690` es el número PERSONAL del jefe** y ya **no** es el de la administración: se quedó
  en **`ADMIN_WHATSAPP_VIEJOS`** solo para **reconocer como «sin dueño»** las fichas viejas que lo traen
  escritas (`telefono_es_del_admin()`). **Y el 2026-09-19 se pasaron al número nuevo las 484 fichas que
  lo llevaban** (todas sin dueño: Mercado Modelo, MegaPlaza, Centro Cívico de Nuevo Chimbote…) — hoy hay
  **484 con el 908785164 y 0 con el viejo**. Dos consecuencias que ya están puestas: el chat sigue
  ofreciendo «reclámala gratis» en esas fichas, y **`/perfil/<número del administrador>` responde 404**
  (ese número no es el perfil de una persona: son 484 tiendas de gente distinta). Detalle, comandos y
  cómo se comprobó: **`GUIA_RECLAMOS_DE_TIENDAS.md` §13.1**.
- 💰 **LA OFERTA DE LAS 50 VENTAS (2026-09-18 — la idea del jefe, convertida en herramienta):** lo que
  se le vende al dueño **no es «una página web», es resultado con riesgo cero**: *«50 ventas en 30
  días; el primer mes es gratis; si no las logro, no pagas; si funciona, pagas S/ 20 al mes»*. Vive
  **dentro de 👑 El Supremo**: el **botón 💰 de la cabecera** abre **la calculadora en vivo** en
  cualquier momento de la venta (y hay un paso propio, `sup_oferta`, al cerrar). Se escribe el precio
  de **un producto real del local** (o se toca el suyo, que ya trae precio) y sale al instante: el
  ingreso con 50 ventas, **el 10 % que cobrarían otros**, la tarifa, **el ahorro**, la **comisión
  efectiva** y **cuántas ventas cubren la tarifa** —con S/ 80: 4,000 · 400 · ahorro 380 · 0.50 % · 3
  ventas—, más la **frase para decir en voz alta**, el **guion de 6 pasos** y **las 10 condiciones**,
  todo **para copiar de un clic**. **Es aritmética pura: no gasta IA ni espera.** ⚠️ **Avisa solo
  cuando la cuenta no luce** (un producto de S/ 8 daría 5 % de comisión real; el mínimo que la hace
  lucir es **S/ 40**). Los tres números viven en **`deploy/includes/config_supremo.php`**
  (`SUPREMO_OFERTA_TARIFA` · `..._VENTAS` · `..._COMISION` · `..._EFECTIVA_IDEAL`): cambiar el precio
  de la oferta es cambiar **una línea**. Trae su **hoja de Excel** en
  **`C:\Users\Usuario\Downloads\Oferta 50 ventas - calculadora.xlsx`** (5 pestañas: Calculadora con
  fórmulas vivas · Oferta · Guion · Reglas · **Las 5 primeras**; se regenera con
  **`python D:\RELAX\__sup_oferta_xlsx.py`**, que lee los números del config). **Guía completa (leer
  SOLO esa): `GUIA_OFERTA_50_VENTAS.md`**. Decisiones ya tomadas: la garantía es de **50 ventas en 30
  días del producto o productos ACORDADOS** (el producto es solo el ejemplo para calcular) y **S/ 20
  es precio de ENTRADA** para conseguir las primeras 5 tiendas (después: S/ 50 o S/ 100 · S/ 20 + 5 %
  · 10 % con mínimo). **La atención se pone en el RESULTADO, nunca en desviarla.**
- 👑 **EL SUPREMO — LA VENTA EN VIVO (`/supremo`, 2026-09-18 — pedido del jefe):** es la página
  **PRIVADA DEL SÚPER ADMINISTRADOR** para crear la tienda de un cliente **en vivo, delante de él**
  (el «modo vendedor»). **Nadie más lo puede usar:** el **único enlace** que existe es el botón
  negro/oro de la pestaña **👑 El Supremo** de su panel (`superadmin.php?seccion=supremo`), y tanto la
  página como su puerta (`api/supremo.php`) comprueban **`es_admin()`** en cada petición
  (comprobado por HTTP: `/supremo` → **302 al login**, `/api/supremo.php` → **403**). La venta es
  **exprés y casi sin escribir** (orden del jefe, 2026-09-18: *«esto es para demostrarle a los clientes
  cuán rápido podemos crear una tienda»*): **empieza pidiendo LA UBICACIÓN** (solo con su botón 📍),
  **sigue con las fotos** y **pasa SOLO a la pregunta del nombre** (sin botón de confirmación): la IA
  **propone un nombre** y se pregunta con **Sí / No** (y **Escribir** desde la segunda propuesta, con el
  cursor puesto solo en el área de escribir). El **rubro** se confirma con **Sí / No** y sus opciones van
  en **grilla 2 × 2** (tres rubros + «ninguno de estos»), igual que «otros rubros» (4 + `✅ Continuar`),
  «cómo atiende» (4 botones) y **el horario (5 opciones en dos columnas)**; **la dirección ya no se
  pregunta** (la ubicación se pidió primero) y el **WhatsApp es obligatorio** (se propone el número leído
  en las fotos con Sí / No / Escribir). Después la tienda **se publica al instante a nombre del cliente**
  (**sin gastar IA en la publicación**: el copy va después), con **sus 8 productos ya armados** (los que
  la IA vio + los que inventa: **no se le pregunta nada al vendedor**), la **descripción definitiva y las
  primeras opiniones escritas en el paso de los productos** (las opiniones **sin IA**), **la portada lista
  en los códigos** (texto artístico sobre una foto del rubro) y **un banner de NUESTRA empresa cada dos
  fichas**. El paso de los códigos **no dice nada** (el panel se abre solo) y trae los atajos: **`🏬 Ver
  la tienda`** (enlace de verdad), **`📲 Mandarle sus datos por WhatsApp`** (mensaje con **UN SOLO
  enlace**), `📥 Subir las imágenes` y `🎵 Subir la canción`; al cerrar se ve **la URL cliqueable** y el
  WhatsApp al cliente. Después le entrega al jefe, listos para copiar: 🎨 el código de **LA CABECERA**
  (para Gemini/Flow con una imagen ingrediente adjunta), **🎵 el de LA MÚSICA** (para el generador de
  música de Flow), **🛍️ el de LAS IMÁGENES de los productos** (de uno en uno, con el **ID impreso**
  dentro de cada foto) y **📲 el MENSAJE del cliente** con su enlace, su usuario y su clave **como botón
  de WhatsApp**. En **la sala de espera** sube las imágenes del diseñador y el motor **lee el ID impreso**
  de cada una para ponerla en su producto.
  🆕 **Dos botones que están SIEMPRE** (en cualquier paso): **📍 Compartir ubicación** (menú ⋯: guarda
  lat/lng + distrito —y lo corrige en la ficha si la tienda ya está publicada— sin mover la venta de
  paso) y **🧠 Piensa mejor** (cabecera y menú: **analiza lo publicado, reescribe la descripción y los
  títulos/precios, y ofrece un botón por cada dato que falte**, devolviendo la venta a su sitio). Con
  **⏱️ cronómetro** (meta: 4-5 minutos; una venta = **4-7 llamadas de IA**, ~US$ 0,001). **Guía completa
  (leer SOLO esa):** **`GUIA_EL_SUPREMO.md`** — motor `includes/supremo.php`, página `deploy/supremo.php`,
  API `api/supremo.php`, cliente `assets/js/supremo.js`, estilos `assets/css/supremo.css`,
  pestaña `includes/vista_supremo_admin.php`; **pruebas: `__sup_prueba.php` (72 de 72) · `__sup_ver.php`
  · `__sup_parse.php` · `__sup_js_test.mjs` (7 casos) · `__sup_pantalla.php "&paso=nombre|rubro|tipo|horario|cliente|codigos|fin"`**,
  con `python __sonda_run.py` (y se borran solas); si una sonda devuelve **500 sin cuerpo**,
  **`__sup_err.py`** dice el fatal con archivo y línea.
  Reutiliza El maestro (`GUIA_CONSTRUCTOR_DE_TIENDAS.md`) con **2 líneas compatibles** en
  `includes/tienda_ia.php` (y un parámetro nuevo `$sin_copy` que **sin él no cambia nada**).
- 🛠️ **LA TARJETA DEL EDITOR DE TIENDAS (`/editatiendas.php`, 2026-09-12 noche — pedido del jefe):** cada
  tarjeta ya **NO muestra el cuadro del prompt**: el prompt queda **oculto** (`.ep-prompt__oculto`) y se copia
  con **📋 Copiar prompt**; en su lugar se ven las **primeras 50 palabras de la DESCRIPCIÓN** de la tienda
  (`et_primeras_palabras()`: quita el HTML, que las descripciones **sí traen**, y corta en la palabra 50),
  **solo ver, sin editar**. Además hay **✏️ Editar** (abre `productos.php?n=<ID>`) y **🗑️ Eliminar tienda**
  (POST `eliminar_negocio`: borra la tienda con productos, fotos, opiniones, rubros, prompts, avisos de empleo
  **y sus archivos WebP**, con `confirm()`; **irreversible**). Detalle: `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md`
  **§B.8.1**. ⚠️ `php` no está en el PATH:
  el lint es **`C:\xampp\php\php.exe -l <archivo>`**.
- 📨 **INVITACIONES A LOS NEGOCIOS POR WHATSAPP (2026-09-17 — pedido del jefe):** en
  `superadmin.php?seccion=tiendas`, **debajo de cada tienda** hay un **botón 📨 Invitar** que abre WhatsApp
  con el mensaje ya escrito (la invitación **+ el usuario y la contraseña**) y que cambia de color según
  cuántas veces se le mandó: **⚫ 0 · 🟠 1 · 🟢 2 · 🔵 3 o más** (con **↺** para corregir). La cuenta de la
  tienda **se crea en el primer clic** (usuario = su teléfono, contraseña de **3 letras + 1 número sin `O`
  ni `0`**) y **la tienda queda a su nombre**. **Guía completa (leer SOLO esa):**
  **`GUIA_INVITACIONES_A_NEGOCIOS.md`** — motor `includes/invitaciones.php`, vista
  `includes/vista_tiendas_admin.php`, acción POST `tienda_invitar` en `superadmin.php`, prueba con las
  sondas `__inv_verificar.php` / `__inv_dueno.php`. Resumen del panel: `GUIA_SUPERADMIN_PANEL.md` §4.4.
  🆕 **Y EL MISMO BOTÓN 📨 ESTÁ EN LA FICHA (2026-09-17, segunda parte del pedido):** *«cuando estoy
  logueado como súper administrador, AHÍ debe aparecer un botón de enviar invitación»* → en **cualquier
  ficha** (`/neg/<slug>`), **debajo de los botones de WhatsApp/Llamar**, el admin ve el **mismo bloque**
  (los 4 colores + ↺). **Lo ve SOLO el admin** (el público recibe una cadena vacía y el POST sin sesión
  responde **403**). Motor: `includes/invitacion_ficha.php` (`invitacion_ficha_accion()` atiende el POST al
  principio de `negocio.php` y `invitacion_ficha_html()` pinta el bloque) · sonda
  **`__inv_ficha_verificar.php`** (mide como admin y como público + ida y vuelta) · guía §12.
- 🛡️ **EDITAR LA TIENDA DESDE SU PROPIA FICHA (2026-09-18 — pedido del jefe):** *«cuando estoy en modo
  súper administrador, bloqueado, dentro de cada tienda debe aparecerme un menú para editar esa tienda:
  su número de teléfono, su descripción, sus productos y otras cosas más. En las áreas de editar sus
  productos o su descripción coloca un botón que diga IA: significa que al presionar ese botón esa área
  va a ser reescrita usando inteligencia artificial.»* → en **cualquier ficha** (`/neg/<slug>`) el
  **súper admin** ve, debajo de los botones de WhatsApp/Llamar (y del bloque 📨), un **menú con 4
  pestañas**: **🏪 Datos** (nombre, rubro, zona, cómo atiende, dirección, horario, **WhatsApp**,
  **teléfono**, Facebook/Instagram/TikTok y si la tienda se ve u se oculta) · **📝 Descripción** ·
  **🛍️ Productos** (buscador + nombre, precio, unidad, texto, ocultar, borrar y «producto nuevo») ·
  **🖼️ Fotos** (subir, ⭐ portada, 🗑️ borrar). **Los botones 🤖 IA están en la descripción de la tienda
  y en cada producto** y reescriben **esa área** con **la misma IA del sitio**
  (`tienda_ia_ia_descripcion` / `tienda_ia_ia_descripcion_producto`, que mira la foto del producto):
  **el texto NO se guarda solo** —el jefe lo mira (o toca **👁️ Ver cómo queda**) y le da 💾 Guardar—.
  🔒 **Solo lo ve el admin** (el público recibe **una cadena vacía**: ni el panel ni su CSS/JS/CSRF, y
  cualquier POST sin sesión responde **403** sin tocar la tienda). ⚠️ **No pide contraseña** (esa es la
  regla del **dueño** en `mi-tienda.php`; aquí la llave es la sesión del admin + CSRF). **Guía completa
  (leer SOLO esa): `GUIA_EDITAR_TIENDA_DESDE_LA_FICHA.md`** — motor `includes/editar_ficha.php`
  (`editar_ficha_accion()` al principio de `negocio.php` + `$edt_en_A/B/C` en las 3 plantillas), sondas
  **`__ef_prueba.php` (24 de 24) · `__ef_ver.php`** y la captura **`__ef_pantalla.php` + `__sup_bajar.py`**.
- 📄 **Publicar las fichas de Descargas (orden del jefe, 2026-09-10) — NO hace falta leer guías para
  esto:** los `gemini-code-*.txt` con sus fotos llegan a `C:\Users\Usuario\Downloads`; se publican con
  **`python __pub2_run.py crear`** (publicador genérico `__pub2_publicar.php` + `__pub2.json`,
  **idempotente**: salta lo ya publicado) y **al publicar se BORRAN de Descargas el txt y las fotos**
  (el script ya lo hace solo). **Todas las fotos se comprimen a WebP** con el motor del sitio
  (`img_guardar_subida`: WebP ≤1600 px + versiones de 800 y 300 px).
  ⚠️ **REGLA PERMANENTE DEL JEFE (2026-09-11):** 1) toda imagen ya usada y subida al hosting
  **SE BORRA de Descargas en el mismo flujo de publicación** (el script lo hace; ninguna sesión debe
  dejar imágenes publicadas acumuladas en Descargas), y 2) **todas las imágenes del sitio SIEMPRE se
  suben en formato WebP** (el motor del sitio lo hace solo; nunca guardar/enlazar jpg o png directo). También sirve para **ventas
  puntuales** sin tienda (ej. 2026-09-11: camioneta CHANGHE Q25 en Casma, negocio 1602): el publicador
  crea también **rubros nuevos** con claves + afinidades si el JSON los trae (ese día nació
  **Venta de Vehículos**, id 108 🚘). ⚠️ **Casma no existe como distrito** (la BD solo tiene los 9 de
  la provincia del Santa): en ventas de otras ciudades, distrito base Chimbote + referencia clara.
  📖 **GUÍA DEL FLUJO COMPLETO: `GUIA_CARGA_PRODUCTOS_FACEBOOK.md`** — autocontenida: cuando el jefe
  mande anuncios de Facebook para publicar, **léela esa sola** y publica rápido (flujo, SPEC, tabla de
  rubros/distritos/afinidades, casos especiales y registro de lo publicado en su propia tabla).
- 🤝 **«PUBLICANDO A LOS AMIGOS DE JIMMY» (guía nueva, 2026-09-14 — pedido del jefe):** cuando el jefe
  deje en Descargas las imágenes de los **servicios de un amigo** (una podóloga, un cerrajero, un
  restaurante…) y diga *«lee publicando a los amigos de Jimmy»*, la **guía autosuficiente** es
  **`publicando a los amigos de jimmy.md`** (leer SOLO esa): flujo completo (leer imágenes →
  SPEC en `__pub2_run.py` → `python __pub2_run.py crear` → verificar por HTTP → **borrar las imágenes de
  Descargas**), la **trampa de la clave de `FOTOS`** (tiene que ser el slug del negocio, si no el borrado
  automático dice «borrados 0»), y sobre todo **el kit de COLORES Y BOTONES del copy**: clases
  `cz-tit` `cz-sub` `cz-lista` `cz-caja` `cz-precio` `cz-ok` `cz-alerta` `cz-nota` `cz-cta` `cz-cta-final`
  + **`<p class="cz-btn cz-wa" data-msg="…">`** (el motor `descripcion_negocio_html()` lo convierte en un
  `<a>` verde de WhatsApp con el **ícono oficial** y el mensaje del copy; ⚠️ **desde el 2026-09-16 el
  mensaje se escribe con el marcador `{URL}`** —p. ej. `data-msg="Hola, la vi en {URL} y quiero
  consultarle:"`— y el motor cambia `{URL}` por el enlace de la ficha: el jefe dijo que la línea aparte
  «🔗 Página donde lo vi: \<URL\>» era **demasiado texto**) y **`<p class="cz-btn cz-tel">`** para llamar.
  **Nunca un WhatsApp en blanco**: sin
  `data-msg` el mensaje sale con contexto, y sin número sale una nota (jamás un enlace roto).
  ⚠️ El copy solo admite `h3 p strong b em i ul ol li br` (los atributos sí se conservan): **nada de
  `<a>`, `<span>` ni `<div>`** (se caen solos y el validador rechaza la ficha). Estrenos reales: **Licenciada
  Grecia** (ficha 1677, 15 servicios de podología y enfermería a domicilio, S/ 80-140, copy con 4 botones
  de WhatsApp + 1 de llamada) y **Khalid Impresiones** (ficha **1678** `khalid-impresiones`, el taller de
  impresiones gráficas de la **señora Elsa**: 15 productos **ids 9702-9716** sin precio —«se cotiza»—, 15
  fotos, rubro **93 Imprentas y Publicidad +98 claves**, taller físico en Elías Aguirre 550, copy con 5
  botones de WhatsApp + 1 de llamada; el jefe lo dictó como «Calid» y las 15 imágenes dicen **KHALID** →
  manda el nombre impreso y quedan las dos formas como claves) y **Sra. Doris** (ficha **1679**
  `sra-doris-santa`, el puesto de la **señora Doris** en el **distrito de Santa frente a Mi Banco**: huevos
  de corral, frutas, ropa, medias, brasieres y arreglos florales; 15 productos **ids 9717-9731** sin precio
  —«precios del día»—, 15 fotos, rubro principal **83 Mercados y Ferias** **+ rubro múltiple 17 Tiendas de
  ropa y 106 Florerías y Regalos**, +68 claves, paleta 3, copy con 5 botones de WhatsApp + 1 de llamada) y
  **ETRO SYSTEM** (ficha **1680** `etro-system`, la empresa de **Edgar Moreno** en el **Shopping Center de
  Chimbote**: reparación de computadoras, laptops y servidores, armado de PC, mantenimiento, cámaras de
  seguridad y suministros; 10 productos **ids 9746-9755** sin precio —«revisión y presupuesto»—, 10 fotos
  **que venían en `Downloads\Nueva carpeta`**, rubro principal **126 Servicio Técnico + 26 Informática /
  Celulares y 103 Seguridad y Vigilancia**, +112 claves, horario en el copy, paleta 1) y **Sra. Cinthia**
  (ficha **1681** `sra-cinthia-santa`, el puesto de la señora Cinthia en el **Mercado Central de Santa**:
  zapatillas y yanquis, sandalias y botas, **disfraces para el colegio que se alquilan**, polos de
  olimpiadas, mochilas de la campaña escolar y calendarios; 10 productos **ids 9756-9765** sin precio, 10
  fotos **de `Downloads\Nueva carpeta`**, rubro principal **83 Mercados y Ferias + 18 Calzado, 17 Tiendas
  de ropa y 22 Librerías / Útiles**, +80 claves, **coordenadas del enlace de Google Maps**
  (lat -8.9861572 / lng -78.6149873), paleta 3).
  **El registro de los cinco vive en la §10 de la propia guía** (no hay crónicas por cliente: las que se
  crearon el 2026-09-14 se borraron por orden del jefe — ver la Regla de Oro n.º 6).
  ⚠️ **Trampas nuevas (guía §11 y §13):** `tipo_producto` es `ENUM('fisico','virtual')` — **«servicio» NO
  existe** y MySQL lo guarda **vacío** sin dar error; un producto **sin precio ya NO se ve «S/ 0.00»**:
  desde el **2026-09-14** `formato_precio()` devuelve **«A consultar»** cuando el precio es 0 o vacío
  (antes lo mostraban así **1 487 productos** del sitio; cambio desplegado y verificado por HTTP); **antes de
  crear** una ficha se busca el nombre en la base (hay fichas viejas homónimas, p. ej. 1515
  `calidimpresiones`); **una tienda que vende de todo = rubro principal + hasta 3 EXTRA en
  `directorio_negocio_rubros`** (plantilla `__doris_rubros.php`, con simulacro antes de escribir: Doris vive
  en Mercados **y** Ropa **y** Florerías; las **claves se reparten por rubro**, no todas juntas);
  **`categoria.php` no pagina: solo muestra las 12 tiendas con más vistas**, así que una ficha recién
  publicada no sale en la página de un rubro grande (se la halla por el buscador, el sugeridor y su enlace);
  y **las claves del buscador solo actúan si la búsqueda por texto no encuentra nada**. Para cambiar **solo
  la SPEC** sin tocar el motor: `python __amigos_armar.py <doc.txt> <spec.txt> <copia.bak.py>`.
- 🔄 **REEMPLAZAR LAS FOTOS VIEJAS FUERA DE CONTEXTO (2026-09-15 — orden del jefe):** los **primeros
  productos publicados** traen la foto del **proveedor viejo** y no tienen nada que ver con lo que venden (a
  «Obras y mantenimiento integral» le pusieron **una moto Honda**, a «Decoración de cumpleaños» **un iPhone
  con texto en inglés**, a «Alquiler de terraza» **un dibujo de videojuego**, a «Paracetamol 500 mg» **la
  fórmula química**, a «Vitamina C» **una infografía** y a «Protector Solar FPS 50+» **un jeep en el
  desierto**). El jefe ya tiene un **proveedor nuevo con contexto** y va a cambiar las fotos: se empezó por
  los **10 primeros** (los más antiguos con foto viva). Es el **flujo hermano** del de productos sin foto:
  mismo motor y misma guía (**`GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md` §A.7.0**), pero se buscan los que
  **YA TIENEN** foto (`ORDER BY creado_en ASC, id ASC`) y se **reemplaza** (el publicador cambia la 1.ª
  foto de la galería + `directorio_servicios.imagen` y deja el **↩️ Deshacer 24 h**). Grupo 1 (ids **1, 4,
  6, 8, 9, 10, 81, 82, 83, 84**): ✅ **10 de 10 PUBLICADAS el 2026-09-15** (WebP 1024², **Descargas limpia**,
  sonda borrada) · carta `CARTA_IA_PRODUCTOS_VIEJOS_10.md` · pares `__productos_viejos_10.json` ·
  publicador **`python __pub_productos.py ov1`**. **Grupo 2 (ids 85 → 94: shampoo anticaída, colágeno,
  termómetro, mascarillas, gel antibacterial, jabón de manos, antihistamínico, suero oral, neomicina,
  antiácido): ✅ 10 de 10 PUBLICADAS el 2026-09-15** · carta **`CARTA_IA_PRODUCTOS_VIEJOS_2_10.md`** ·
  generador **`__carta_viejos2_gen.py`** · pares **`__productos_viejos2_10.json`** · renombrador
  **`__viejos2_renombrar.py`** · publicador **`python __pub_productos.py ov2`** (los 10 eran de botica, así
  que cada foto fue en una **situación distinta** para que no salieran diez iguales).
  **Grupo 3 (pendiente): ids 31 → 40** de *MegaPlaza* (canasta de emergencia, microondas, ropa de niña,
  ollas, kit de higiene, zapatillas, licores, útiles, juguetes, licuadora) ⏳ sin pedir — ⚠️ **su archivo de
  imagen NO existe (404)**: la web los muestra **sin foto**, así que ahí la imagen se **crea**.
  🧼 **DIRECCIÓN DE ARTE (jefe, 2026-09-15 — la que manda):** *«en las cartas dame imágenes
  limpias, ambientes ordenados, que sea atractivo y que la gente quiera hacer negocios con esas empresas…
  no quiero imágenes sucias o ambientes desordenados. Elegancia, pulcritud.»* → **TODO SE VE NUEVO, LIMPIO
  Y BIEN CUIDADO**: lugar aseado, productos alineados con las etiquetas al frente, luz pareja y composición
  atractiva. ⛔ **En las cartas NO se escribe el vocabulario de la vejez ni del descuido** (el generador
  **copia lo que lee**): si una imagen sale con el objeto maltratado, **se retiene y se repite**, pero eso
  **no se anuncia en el prompt**. Sigue: foto **real** de un negocio
  real (nunca caricatura ni render), **sin textos** salvo la etiqueta impresa del envase, **nítida**,
  **1:1** y **manifiesto** obligatorio. Generadores: **`__carta_limpia_gen.py`** (lote 1) y
  **`__carta_limpia2_gen.py`** (lote 2) · cartas: **`CARTA_IA_PRODUCTOS_LIMPIOS_15.md`** y
  **`CARTA_IA_PRODUCTOS_LIMPIOS_2_15.md`** · pares: `__productos_limpios_15.json` y
  `__productos_limpios2_15.json` · publicador **`python __pub_productos.py lp1`** / **`lp2`**.
  ✅ **LOTE 1 (ids 95-100 y 171-179): 15 de 15 PUBLICADAS** (WebP 1024² · 53-132 KB) y
  ✅ **LOTE 2 (id 180, ids 181-190 y 201-204): 15 de 15 PUBLICADAS** (WebP 1024² · 91-272 KB), el 2026-09-15.
  ⚠️ **Las imágenes de estos lotes llegaron DENTRO DE CARPETAS** (`Downloads\Nueva carpeta` y
  `Downloads\Nueva carpeta (4)`, con nombre en inglés): se pasaron a Descargas **con su nombre de
  producto** con **`python __limpios_renombrar.py go`** (copia desde la carpeta) → `lp1`/`lp2` →
  `limpiar` → **y las carpetas se borraron**. Receta general para un lote que llega en carpeta.
  ✅ **LOTE 3 (ids 31-40 de MegaPlaza + ids 311-315 del Restaurant El Cevichón): 15 de 15 PUBLICADAS**
  (WebP 1024² · 70-131 KB) — ⚠️ **los ids 31-40 NO tenían archivo de imagen (404) y con este lote
  recibieron imagen POR PRIMERA VEZ** · carta **`CARTA_IA_PRODUCTOS_LIMPIOS_3_15.md`** · pares
  `__productos_limpios3_15.json` · publicador **`lp3`** · renombrador **`__limpios3_renombrar.py`**.
  ⏳ **LOTE 4 (ids 316 → 330 · 15 platos del Cevichón)**: carta **`CARTA_IA_PRODUCTOS_LIMPIOS_4_15.md`** ·
  generador **`__carta_limpia4_gen.py`** · pares `__productos_limpios4_15.json` · publicador **`lp4`**
  (imágenes pedidas al jefe).
  ⚠️ **Las imágenes de estos lotes llegaron SUELTAS en Descargas** (con nombre en inglés): se renombran con
  **`python __limpios3_renombrar.py go`** → `lp3` → `limpiar`. (Los lotes 1 y 2 llegaron **dentro de
  carpetas** y se usó `__limpios_renombrar.py`; la receta es la misma: **renombrar → publicar → limpiar**.)
  🔴 **TAMBIÉN SE CORRIGIÓ EL OTRO PUBLICADOR:** `__pub_portadas_tiendas.py` subía su sonda a
  `/public_html` (la copia vieja) y el sitio respondía **404 en los 20 pares**; ahora hace `ftp.cwd('/')` +
  marca `assets/css/carrito.css`. **Regla: todo script que suba al hosting usa `/` y comprueba la marca.**
  🔴 **TRAMPA GRANDE (2026-09-15): `__pub_productos.py` subía la sonda a `/public_html`** (la copia vieja
  anidada) y **el sitio respondía 404 en los 10 pares**, como si fuera un problema de red. **Corregido: ahora
  hace `ftp.cwd('/')` + comprobación de la marca `assets/css/carrito.css`.** Regla general: **todo script que
  suba algo al hosting usa `/` y comprueba la marca** (ver la trampa del FTP, arriba).
  ⚠️ **TRAMPA: «tiene foto» en la base NO quiere decir que la foto exista** — hay **830** con
  `fotos/producto_<id>.webp` del proveedor viejo y **muchos de esos archivos ya no están** (los ids **31 a
  40** de MegaPlaza dan **404**: la web los muestra rotos). Se comprueba la URL, no la columna
  (**`python __vivos_check.py <ids…>`**). Esos 10 (MegaPlaza) van en su **propio grupo**, después.
  ⚠️ **Y las sondas de este flujo se suben con `python __sonda_run.py <sonda>.php rb-rubros-2026-9kQ7`**
  (hace `cwd('/')`), **no** con `__ep_run.py` (sube a `/public_html` y el sitio responde **404**).
- 🏦 **EL BANCO DE IMÁGENES (2026-09-22 — lo que manda hoy): el sitio YA TIENE una imagen canónica por NOMBRE
  de producto, y hay que MIRARLA antes de pedir nada nuevo.** El registro es
  **`D:\RELAX\__banco_imagenes.json`** (código · nombre · rubro · ruta · fichas) y los archivos viven en
  **`fotos/banco/`** del hosting, que es una **CARPETA NEUTRA** (no es de ninguna tienda: así el borrado de una
  tienda no puede romper las fichas que la reutilizan). 👉 **Antes de pedirle una imagen al diseñador:**
  `python __banco.py listar` (qué hay) · `python __banco.py mirar <nombre>` (¿ya existe?) — si ya está, se
  **reutiliza su ruta** entre todas las tiendas que venden eso con **`__pub_reuso_prod.php`** y **no se pide
  nada**. 🔑 **La clave es NOMBRE + RUBRO** (una «mica» de celular **no** es una mica de contacto; el «té» de un
  restaurante **no** es el té de una tienda agrícola; la «papa» frita **no** es el saco de papa **ni** el
  **papá** padre de una barbería). Cargar una tanda nueva: `__bn_armar2.py` → **`python __banco.py cargar go`**
  (sube + reparte al grupo **y a su familia**), corregir los que caen en 0 con **`__bn_fix.py`** (el título tiene
  que ser el EXACTO de la base), y **borrar de Descargas lo cargado**. 📖 Todo el detalle (comandos, las
  trampas del apóstrofo y del nombre inventado, y los números): **`GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md`
  §A.9.4** (banco) · **§A.9.3** (reutilización) · **§0.1.1** (la ley del contexto).
- 🖼️ **IMÁGENES DE PRODUCTOS CON CONTEXTO (módulo nuevo, 2026-09-13 — orden del jefe):** es el **flujo hermano
  del de portadas**, pero para las fotos de los **PRODUCTOS que no tienen imagen** (**2 758** productos sin
  foto en **1 380 tiendas**, de 3 947 totales). Guía **autosuficiente**:
  **`GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md`** (leer SOLO esa). **La ley del contexto manda:** la palabra
  sola NO alcanza — *«taco»* es **comida** en un restaurante, el **taco del zapato** en una zapatería y el
  **taco de madera que traba una rueda** en una carpintería; *«collar»* es **joya**, **collar de mascota** o
  **collarín de eje** según el rubro. Por eso **cada prompt dice 3 cosas obligatorias: QUÉ ACCIÓN, QUÉ
  ELEMENTOS y BAJO QUÉ CONTEXTO/RUBRO**, más **el objeto MUY CERCA con las estructuras al detalle** (primer
  plano) y **personas que no posan** (pero aseadas y presentables). ⭐ **TODO SE VE NUEVO, LIMPIO Y BIEN
  CUIDADO**, y ⛔ **en las cartas no se escribe el vocabulario de la vejez ni del descuido** (el generador
  copia lo que lee; si el objeto sale maltratado, **se retiene y se repite**, pero no se anuncia en el prompt).
  **Los textos, si los hay, van en colores CLAROS que destaquen sobre el fondo** (blanco, celeste, lila
  claro, melón). ⚠️ **SIN TEXTOS** en la
  imagen (solo números): la IA traduce español→inglés y el nombre ya está en la ficha; los servicios se
  cuentan **con la acción** (entregar la llave = alquiler). Formato **1:1** · se pide en **bloques de 10**
  (20 productos = 2 bloques) · **nunca entrar como admin** · **publicar sin navegador** con
  `python __pub_productos.py l1a` / `l1b` / **`l2a` / `l2b`** (espejo de `ep_publicar_imagen`: WebP + ↩️ Deshacer 24 h) ·
  **no se verifica nada** (publicar cierra). 🔴 **LA CARTA VA SIEMPRE CON SUS BLOQUES VISIBLES EN EL CHAT**
  (orden del jefe, 2026-09-13: *«siempre debe estar visible los bloques»*): lo primero de toda sesión de
  productos es **entrar al sitio, sacar los 20 sin foto y pegar la carta completa aquí, cada bloque de 10
  dentro de su bloque de código** (botón «Copiar»); **jamás** escondido en un archivo, ni «el bloque 2 está en
  tal archivo», ni «dime y te lo pego» — el `.md` es solo respaldo. Y **no se continúa** la tarea de la sesión
  anterior por cuenta propia.
  🔤 **LOS NOMBRES DE LOS ARCHIVOS LLEGAN AL AZAR** (orden del jefe, 2026-09-13, textual: *«ten en cuenta que
  la IA usa nombres en inglés o español… para descargar, es algo al azar: a veces inglés, a veces español»*):
  el diseñador a veces sí renombra a `producto-<slug>-<ID>.png` y a veces entrega con **el nombre en inglés
  o español de la escena** (ej. `Man_lifting_water_cooler_20260913032350.jpeg`,
  `Pharmacist_placing_medication_on…_20260913100353.jpeg`). Entonces: **el agente NUNCA espera el nombre
  exacto** — mira **qué se ve en cada foto** (y el manifiesto del jefe), la **asocia a su producto por el ID**,
  la **renombra él mismo** a `producto-<slug-del-título>-<ID>.png_<fecha>.jpeg` y recién ahí publica
  (`python __pub_productos.py l3` / el modo del grupo). **Jamás** pedirle al jefe que renombre nada.
  ⚠️ **Los productos sin foto también se ven sin sonda** en el
  editor: **`https://dechimbote.com/editaproductos.php?f=sinfoto&p=1`** (20 tarjetas por página, con su
  `📋 Copiar prompt`); la sonda queda para el contexto largo (descripción del producto y de la tienda):
  `python __ep_run.py __ep_productos_sinfoto.php __productos_sinfoto_80.json "&n=80&todo=1"` ⚠️ **`&todo=1`
  es obligatorio** (tras la limpieza del 2026-09-12 casi todas las tiendas tienen 2 productos y sin él la
  sonda devuelve **0**). Generadores `__carta_prod_gen_lote1.py` · `__carta_prod_gen_lote2.py`; **los pares
  (lo que se usa para publicar) van en `__productos_lote1_20.json`** (lote 1, ids **9357 → 9248**) y
  **`__productos_lote2_20.json`** (lote 2, ids **9318 → 9185**, 20 productos que **no repiten** el lote 1,
  16 tiendas y 15 rubros) ⏳ **40 imágenes pedidas al jefe** (las cartas ya pegadas a la IA están en
  `_ARCHIVO_HISTORICO_2026-09-14\`). ⚠️ **El rubro de la BD miente seguido** (farmacia
  927 en «Salones de belleza», polideportivo 1277 en «Centros de Esports», agente bancario 1319 en «Cajeros de
  Criptomonedas», mercado 1398 en «Tiendas de ropa», hotel 1104 en «Salones de belleza», terapias 1244 en
  «Gimnasios»): manda la **actividad real**.
- 🧺 **Si el jefe deja CARPETAS en Descargas (una por negocio) → método "pocos tokens" (2026-09-12):**
  la **ÚNICA guía que se lee es `publicar-con-pocos-tokens.md`** (no cargar el compendio). Flujo:
  `python __cola.py inventario "C:\Users\Usuario\Downloads"` → el agente **con visión** completa cada
  `D:\RELAX\__cola\<carpeta>\spec.json` (o **1 subagente por carpeta** con
  `__cola\INSTRUCCIONES_AGENTE.md`) → `python __cola_revisar.py` (calidad) → `python __cola.py preview`
  (que lo vea el jefe) → `python __publicar_cola.py crear` (una corrida: WebP, rubros nuevos, fusión por
  teléfono, borra fotos/captura de Descargas) → `python __cola_verificar.py` (HTTP). ⚠️ Consola en
  **UTF-8** (`$env:PYTHONIOENCODING='utf-8'`) · ⚠️ el motor **SALTA** las fichas cuyo **slug ya existe**
  (comprobar antes) · las fotos viven en **Descargas**, no en `__cola`. ⭐ **REGLA DE ORO AL CERRAR (orden
   del jefe, 2026-09-19): la tanda NO está terminada hasta que el jefe tenga delante LOS ENLACES de todas
   las tiendas creadas** (`https://dechimbote.com/neg/<slug>`, con su nombre, id, teléfono y nº de
   productos; las **fusiones** con el enlace de la ficha que los recibió, y diciéndole además qué quedó en
   cuarentena). Los enlaces **se escriben, no se abren** (Regla de Oro n.º 5). 🔢 **Productos: si la carpeta NO
  trae fotos de producto, INVENTAR MÁXIMO 2** (orden del jefe, 2026-09-12); con fotos de producto no hay
  tope (se crea, no se inventa). Caso real: **15 carpetas → 14 fichas**.
  ✅ **TANDA CERRADA (2026-09-12, noche): 15 fichas publicadas en una corrida** (ids **1658-1672**, 45
  productos, 0 fusiones) y **verificadas 15/15 por HTTP**; **Descargas quedó sin nada del proyecto** (las 15
  carpetas `Sesion *` —cáscaras con solo `historial.json`— y la del aviso de empleo se **borraron** el
  2026-09-12 con su OK). Estado y continuación: **`publicar-con-pocos-tokens.md` §0**.
  🆕 **TANDA 2 (2026-09-14, noche): 16 carpetas `Sesion-20260914-*` → 9 fichas nuevas** (ids **1682-1690**:
  clases de inglés, mototaxi 9/10, Se Compra Routers, El Ratoncito, Andrea Detalles, Ruluz Spa, **Juguero
  para Clarenz Trattoria** —aviso de empleo nombrado por la vacante—, Parihuelas de Madera de Pino,
  PROYECTARQ) **+ 2 fusiones por teléfono** (`alesof-detalles`, `ositos-sorpresa-paola`) **+ 1 que ya estaba
  publicada** (Misi.detalles, ficha 1658) **+ 2 DESCARTADOS por el jefe** (ProArtisan: empleo en
  Chilca/Cañete; Maíz Tostado Cocoliche: sus 4 imágenes eran **capturas de pantalla del celular** → el jefe
  ordenó borrar sus carpetas y así se hizo: sus `spec.json` quedaron en **`publicado` + `_descartado`** para
  que el publicador no las intente nunca)
  **+ 2 UNIFICADOS a la ficha que ya existía** (el jefe eligió «unirlos»: **Carsa Motos**
  `Sesion-20260914-115011` → FUSION a la ficha **1667** con 3 productos y 4 fotos; **Delta Gym**
  `Sesion-20260914-150724` → la ficha **228 `deltagym`** no tenía teléfono, así que entró por sonda con el
  flyer y 2 productos y se le puso el número del aviso 902051796). Descargas quedó **SIN NADA DEL
  PROYECTO: cero carpetas `Sesion *`** (las 14 de lo publicado y las 2 descartadas se borraron al cerrar,
  como en la tanda 1). ⚠️ **TRAMPA
  NUEVA: el motor salta la ficha por SLUG ANTES de fusionar por teléfono** → si la ficha vieja ya existe la
  fusión **no ocurre nunca**: hay que **cambiar el slug** en el `spec`; se comprueba antes con
  **`python __cola_prechequeo.py`** (404 = libre · 200 = ya existe, y valida el rubro). ⚠️ **Y NUNCA se
  saca el teléfono de una ficha leyendo su página HTML:** el número del **pie del sitio es el del
  ADMINISTRADOR** (hoy `908785164`; hasta el 2026-09-19 fue `955041690`, que ahora es solo su número
  personal) → la fusión de Delta Gym cayó primero en la ficha `administrador-de-sitio` (id 1555) y
  hubo que deshacerlo con `__sonda_delta_fix2.php`; el teléfono real se lee con **sonda**
  (`SELECT telefono, whatsapp FROM directorio_negocios WHERE slug=…`).
  ⚠️ Trampas nuevas de esa noche: el revisor marca **«CAPTURA metida en la galería»** cuando el **afiche**
  hace de fuente de verdad → **mirar la imagen** antes de tocar nada (afiche con marca/oferta/teléfono y
  **sin pestañas ni URLs** = legítimo); un **HTTP 0** en la verificación es red, no 404 → reintentar; y el
  motor **solo borra de Descargas lo que está en el `spec`**, así que los **duplicados de fotos** y las
  **capturas sobrantes** (`captura-2..9`) los borra el agente a mano al cerrar.
   🆕 **TANDA 7 (2026-09-19): 11 carpetas `Sesion *`** (6 del `Sesion-20260917-09*` + 5 del `Sesion-20260919-*`)
   **→ 7 fichas nuevas** (ids **1917-1923**: Chef Juan Silva asesorías gastronómicas · Eder Gutiérrez motos ·
   Eventos Mundo Mágico · Postresitos AbdiVane · Grupo Jacobo Inmobiliaria · alquiler en Urb. Cipreses ·
   Tati Decor) **+ 1 fusión** (`entredulces-chimbote`, 940279135 → **`entre-dulces-y-mas`**) **+ 3 en
   cuarentena**: `Sesion-20260917-092606` (**aviso de empleo** de Crédito Móvil → su sitio es `/empleos`, no
   una ficha) y `Sesion-20260919-083713` + `Sesion-20260919-084111` (**Variedades H&S / HYS: el MISMO negocio
   en dos carpetas y SIN teléfono** en ninguna de sus 10 capturas ni en su banner — solo direcciones, correo y
   redes → **el número no se inventa**: quedan en `borrador` esperando que el jefe lo dé y entonces se publican
   como **una sola** ficha `variedades-hys`). **8 de 8 con OK** en 2 lotes (`crear 5` y `crear 3`) y Descargas
   quedó **solo con esas 3 carpetas**. Herramientas del día: un **subagente con visión por carpeta**, luego
   **`__amp_check.py`** y **`__cola_prechequeo.py`** (los 11 slugs dieron **404** y los 12 rubros **200**).
   Trampas: **2 subagentes murieron a mitad** (uno ya había dejado el `spec.json` completo: se comprueba y se
   sigue, no se rehace a ciegas) · una carpeta traía **DOS negocios** (se publica el de más fotos propias) ·
   una foto **no subió** por un temporal `.in.x_NN.jpg` del FTP (`grupo-jacobo-inmobiliaria` quedó con 15 de
   16). Detalle: **`publicar-con-pocos-tokens.md` §0**.
  ⛔ **ORDEN DEL JEFE (2026-09-12, textual): «TÚ SOLO TRABAJAS CON CARPETAS DE NOMBRE `Sesion *`, ASÍ QUE
  IGNORA TODA IMAGEN FUERA DE CARPETAS».** La zona de trabajo son **las carpetas** (`Sesion-AAAAMMDD-HHMMSS\`);
  todo lo que esté en `C:\Users\Usuario\Downloads` **fuera** de una carpeta `Sesion *` **se IGNORA**: no se
  publica, no se mueve, no se borra y **no se le pregunta al jefe por ello**.
  🔇 **«IGNORAR» QUIERE DECIR «NO MENCIONAR» (aclarado por el jefe, 2026-09-12, textual):** *«desde el momento
  en que dices “están ahí” ya estás informando de su existencia, y eso ya es información… no debes mencionarlas
  ni por un caso uno ni por un caso dos… entras en conflicto con otras IA que están trabajando en
  simultáneo»*. → **no se mira, no se lista, no se cuenta, no se reporta, no se comenta y NO SE MENCIONA**
  (ni su nombre, ni que existe, ni en qué estado está): decir «ahí hay algo, pero no lo toco» **también está
  prohibido**. **Dentro** de una carpeta `Sesion *` sí se trabaja con **todo** lo que viva ahí (fotos,
  capturas, afiches). Detalle: **`publicar-con-pocos-tokens.md`** §2 (regla inviolable n.º 2), §0 y §8.
  ⚠️ **Esta orden es del flujo de CARPETAS.** El agente de **PORTADAS** es otro y tiene su propia regla
  irrevocable: trabaja **solo con los archivos sueltos `portada-*.jpeg`** de Descargas, **JAMÁS entra,
  lista ni husmea dentro de una carpeta**, y **sí** publica esas portadas y borra de Descargas las ya
  publicadas (Regla de Oro n.º 7 · `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` **§A.2**).
- 💼 **EMPLEOS Y ANUNCIOS (módulo nuevo, 2026-09-12 — el jefe lo pidió con un "dale"):** el
  **tercer tipo de contenido** del sitio: tienda · producto · **AVISO**. Un empleo **no lleva galería**
  (es texto) ni productos, y **caduca a los 30 días** (renovable de 1 clic). Página pública
  **`/empleos`** (`deploy/empleosdb.php`) con filtros por **oficio/rubro/zona/tipo** y ficha
  **`/empleo/<slug>`** (`deploy/empleo.php`, con **JSON-LD `JobPosting`** y `canonical`); bloque en el
  index **2×4 en celular / 4×4 en escritorio** (después de Destacados, y **no se pinta si está vacío**).
  Tabla **`directorio_empleos`** (auto-instalación defensiva: los `migrar_*.php` están bloqueados por el
  antivirus) · motor en `includes/helpers.php` (`empleos_instalar_tabla`, `empleo_vigente_sql`,
  `crear_empleo`, `buscar_empleos`, `empleo_card_html`, `empleos_bloque_html`…) · buscador
  `api/empleos_json.php` · **3 puertas**: el jefe o la IA (sonda `__empleos_seed.py`), el **dueño** desde
  su panel (sale `activo` y colgado de su ficha) y el **visitante** (nace `pendiente`: el jefe lo aprueba
  con **✅ Aprobar / 🗑️ Rechazar** desde el Telegram). ⛔ **La vigencia se compara con la fecha de LIMA
  que manda PHP, NUNCA con `NOW()`/`CURDATE()`** (el MySQL va en UTC: se vencerían 5 h antes).
  🖼️ **NOVEDAD DEL 2026-09-17 — EL AFICHE:** orden del jefe (*«oportunidad laboral publícalo y mantén
  la imagen en el anuncio»*) → nació **`directorio_empleos.afiche`** (ruta del cartel, ej.
  `fotos/empleo_waykis_3_mozas.webp`) con **`empleo_afiche_ruta()`/`empleo_afiche_url()`**: la tarjeta
  enseña el afiche recortado **16:10 anclado arriba**, la ficha lo muestra **completo** debajo del sueldo
  (y hay fila **«Horario»** si `horario_txt` va escrito a mano), y el afiche es el **`og:image`** de
  WhatsApp y el **`image` del `JobPosting`**. Sigue siendo **opcional** (los avisos sin afiche se pintan
  como siempre) y el **formulario público no sube archivos**. 🔑 Un afiche que llega **adjunto en el
  chat** SÍ se puede usar: vive en **`C:\Users\Usuario\.dsh\attachments\v1\objects\<2 dígitos>\<sha256>`**
  (el nombre del archivo **es** su huella) → se convierte a WebP y se sube con `__subir_uno.py`.
  🔴 **Y LA REGLA QUE MANDA HOY (orden del jefe, 2026-09-17, textual: *«indexa la imagen; si no es captura
  de pantalla, siempre indexar»*):** un aviso que llega **con una imagen que NO es captura de pantalla**
  **siempre se publica CON esa imagen** (se indexa: hosting + `afiche`), aunque el jefe no lo pida con
  esas palabras; lo **único** que nunca se sube es la **captura de pantalla**. Los avisos **13 a 21** y **23 a 30** ya
  van así (Waykis, Vanguard, almacén, jornaleros, Ecofrank, **Maderera Liz-Cielito** (id 21),
  **PERÚ CARNES** «Vendedor/a» (id **22**), **Florería Pétalos y Aroma** «Chica con experiencia en
  floristería» (id **23**) y **Cevichería - Pollería Taypa** «Mozo(a)» (id **24**), los **4 flyers** de **Mi Sabores 2 — Recreo
   Campestre**, la **librería de multiservicios de Bellamar** (id **31**), los **2 jóvenes ayudantes de la
   tienda de abarrotes** (id **32**) y los **operarios de FRIGORIFICAS PRC SAC** en **Santa** (id **33**)
   —el **primero con afiche y SIN teléfono**: su flyer no imprime ninguno y manda a presentarse en
   planta—).
  Verificación: `python __verif_empleos.py`. Detalle: **`GUIA_EMPLEOS_Y_ANUNCIOS.md`** (§1, §7, §12, §14
  y los avisos 13 a 30).
- ⛔ **REGLA INVIOLABLE DEL JEFE (2026-09-12) — CAPTURAS DE PANTALLA: NUNCA VISIBLES EN EL SITIO.**
  Una captura de pantalla **jamás** se sube al hosting ni se pone como foto, carátula o imagen de
  producto de una ficha, **por ningún motivo**: muestra **sus pestañas, marcadores y URLs** =
  información sensible. La captura (`captura-1.png`, la que trae nombre y teléfono) **solo se mira para
  leer el dato y se borra**. ⚠️ **Si una carpeta no trae ninguna `foto*` → esa ficha NO se publica**: se
  le pide una foto al jefe (usar la captura "como única imagen" está **prohibido**). Las imágenes del
  sitio son fotos, flyers, afiches y logos **del negocio** (una marca de agua de TikTok o un flyer con
  logo sí; una pantalla de celular/PC con su interfaz, no). Revisión masiva del 2026-09-12: **34 capturas
  retiradas** (6 + 25 + 3), copia local en `D:\RELAX\__capturas_retiradas\`, detector reutilizable
  (`__sonda_capturas.php` + `python __sonda_capturas.py ver|borrar <ids>`, sonda temporal que se
  autoborra del hosting).
- Sitio: **https://dechimbote.com** · Hostinger (cuenta `u196269909`) · BD `u196269909_CHIMBOTEALDIA`.
- Los productos viven en **`directorio_servicios`** (no existe `directorio_productos`).
- El MySQL del hosting va en **UTC**; el sitio usa `America/Lima` (guardar fechas desde PHP).
- Bot de Telegram: `@Jimmychimbote_bot` · chat ID del jefe `8333560284`.
- 🔔 **Avisos:** todo avisa al Telegram con `aviso('tipo', [...])` (motor en `includes/avisos.php`,
  se enciende/apaga en **Súper Admin → 📱 Telegram**). Lista completa:
  `GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md`.
  🆕 **DATOS INTELIGENTES (2026-09-15 — pedido del jefe: *«siempre dice que son robots, nunca que son
  personas… cruza información, necesito datos más inteligentes»*):** (1) la clasificación de personas se
  rehízo con `avisos_senales_persona()` — además de la sesión, el referer y la IP repetida, ahora mira
  **la huella del navegador** (cookie `cz_stats` en `directorio_stats_sesiones`: más de una sesión, más de
  una página o latidos de tiempo = persona), y el mensaje dice **por qué** («✅ Persona real: …»), así que
  una persona que escribe la dirección a mano ya NO se cuenta como robot; (2) el resumen de la hora y el de
  robots van **cruzados** (👤 personas · 📞/💬 clics de pedir de `directorio_pedidos` · 🔍 lo que buscaron ·
  🚩 reportes pendientes · 🤖 buscadores frente a la granja de IPs); (3) **🧠 INFORME INTELIGENTE** (módulo
  **`includes/informe_inteligente.php`**, avisos `informe_dia` a las 22:00 e `informe_semana` los domingos):
  la **tienda que más destaca** (visitas de personas + 3 × clics, contra el periodo anterior), **las que
  más piden llamada/WhatsApp**, **lo que buscan y no encuentran**, reportes pendientes y opiniones nuevas;
  (4) **el botón 📞 Llamar ya se mide** (`assets/js/llamadas.js` → **`api/llamada.php`** → `directorio_pedidos`
  `tipo='llamada'`, más los WhatsApp del copy que se saltaban `api/lead.php`; hizo falta ampliar el ENUM y
  añadir `idx_ip_fecha` en producción); 🚫 **Y SUS FALSOS POSITIVOS YA ESTÁN ARREGLADOS (2026-09-16, queja del
  jefe):** «🔥 PIDIERON PRECIO» y «📞 TOCARON LLAMAR» llegaban sin que nadie tocara nada —**371 avisos en 7
  días y 306 venían de IPs que aparecen UNA SOLA VEZ** (rastreadores y la granja siguiendo el enlace
  `api/lead.php`, que está escrito en el HTML de la ficha, y el JS contando el **`pointerdown`**, o sea solo
  TOCAR el botón). Ahora **solo cuenta un clic de verdad**: `assets/js/llamadas.js? v=2` (click + pointerup sin
  moverse, y pega **`&c=1`** en los enlaces de `api/lead.php`), `api/lead.php` exige **`&c=1` o
  `Sec-Fetch-User: ?1`** (sin eso el visitante va igual a WhatsApp pero el hecho queda como robot), y
  `assets/js/carrito.js? v=8` manda el pedido con `&c=1`. **Detalle, cifras y verificación:
  `GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md` §19** (sondas reutilizables `__sonda_falsos.php` y
  `__sonda_clic.php`); (5) el «📅 Resumen del día» **nunca se envió** (el panel lo encendía
  en la base y el código exigía una constante en `false`) y las **opiniones nuevas no avisaban**: ambos
  arreglados. **El catálogo pasó de 23 a 27 avisos** y el panel **📱 Telegram** tiene ahora una
  tarjeta **👤 personas frente a 🤖 robots (24 h)** con los clics de pedir/llamar y **dos enlaces
  para VER el informe sin enviarlo** (`&solo-mostrar=1&informe=dia` / `&informe=semana`). Prueba a mano del informe:
  `cron/monitoreo_sistema.php?k=ChimboteCron2026%23Jimmy&solo-mostrar=1&informe=dia`.
- 🏆 **RÉCORDS DEL SITIO + 📈 ESTADÍSTICAS = UNA SOLA PESTAÑA (2026-09-13 — pedido del jefe):** la pestaña
  **📈 Estadísticas y récords** (`superadmin.php?seccion=estadisticas`; **`?seccion=records` es un alias**)
  cuenta el sitio en 5 tramos: **🟢 en vivo · 💼 el negocio** (más vistos · más buscados · más botones de
  pedir pedidos · **soles** que mueven · el embudo · rubros con más demanda · las tiendas que nadie mira ·
  día/hora récord) **· 📈 el tráfico · 👥 las personas · 📣 para enseñar** (el **resumen listo para COPIAR**
  con un botón: el jefe no selecciona texto, Regla n.º 1). ⚠️ **El diseño que manda es el de Estadísticas**
  (`st-*` + tarjetas `sa-stat`): el partial de los récords **no trae CSS propio**, solo se añadieron los
  separadores `.st-sep`. **Un solo rango para todo** (`&rango=`: **Hoy (24 h) · 2 días (48 h) · 3 · 5 · 7 ·
  15 · 30 · 60 · 90 días · 1 año** — escalera del **2026-09-18**; el «14 días» se retiró).
  Motor **`includes/metricas.php`** · partial **`includes/vista_records_admin.php`** (bloques del negocio) ·
  dueña de la página **`includes/vista_estadisticas_admin.php`**. Trae **2 tablas nuevas**
  —**`directorio_busquedas`** (cada término buscado con sus resultados; antes solo se medían los usuarios
  CON SESIÓN) y **`directorio_pedidos`** (cada botón de pedir; antes **no se guardaba en la BD**, solo se
  avisaba al Telegram)— que se crean desde el panel (**⚙️ Crear las tablas de récords**) y un botón
  **🧺 Traer el histórico** que las siembra desde `directorio_avisos_log` (⚠️ depende del formato
  `«<término> (<N> res.)»` de ese registro). **3 enganches**: `buscar.php` (buscador),
  `includes/chatbot.php` (chat 🥷) y `api/lead.php` (todos los pedidos, después de la redirección).
  Se descartan robots y al admin. Primeros números: **138 búsquedas** y **144 pedidos** reales (411 de 553
  clics de precio eran **robots**), `pollo` lo más buscado (22), **311 tiendas activas sin una visita**.
  Guía: **`GUIA_RECORDS_DEL_SITIO.md`** · tráfico y tablas: `GUIA_ESTADISTICAS_DEL_SITIO.md`.
- 📍 **Botón "Ver tiendas cerca":** `deploy/includes/btn_cerca.php` →
  `require_once __DIR__ . '/includes/btn_cerca.php';` + `<?= btn_tiendas_cerca_html() ?>`.
- 🗑️ **LOS TABLONES SE RETIRARON DEL SITIO (orden del jefe, 2026-09-13) — NO VOLVER A PONERLOS.** Ese
  día el jefe pidió *«quitar del sitio web toda referencia a tablones»* y eligió la opción más completa:
  **(1) fuera el CHAT COMUNITARIO entero** (`chat_comunidad.php`, `api/chat_comunidad.php`,
  `includes/chat_mini.php` y `migrar_chat.php` borrados del hosting y de `deploy`; fuera su **banda** de
  arriba —la que se pintaba desde `includes/header.php`—, su **botón flotante «💬 Chat»**, su **opción de
  menú**, su **aviso de Telegram** y sus **estilos**: `/chat_comunidad.php` da **404**) y **(2) fuera la
  MENSAJERÍA PRIVADA ENTRE TIENDAS (B2B)** (sus 5 archivos —que nunca se publicaron— más la pestaña
  🗂️ Tablones y el contador «Tablones B2B» del Súper Admin, el modal de la ficha y sus 10 funciones y
  constantes `TABLON_*` de `helpers.php`). **La palabra «tablón» no existe en ninguna parte del sitio**
  (tampoco en empleos: se dice **«la página de empleos»**). ⚠️ La función del **plan** sobrevivió
  renombrada: **`reglas_plan_usuario()`** (antes `reglas_tablones_usuario()`); las constantes
  `PLAN_GRATIS`/`PLAN_PREMIUM`/`SITE_WHATSAPP_PREMIUM` **siguen**. El **único** botón flotante es 🥷 El
  ninja. Los archivos retirados están en `D:\RELAX\_ARCHIVO_RETIRADO_CHAT_Y_TABLONES_2026-09-13\`
  (**jamás se despliega desde ahí**). **Lo vivo de esos dos módulos está rescatado**: los reclamos en
  **`GUIA_RECLAMOS_DE_TIENDAS.md`** y el diseño de la portada en **`GUIA_DISENO_DEL_INDEX.md`**; la
  historia de los módulos retirados quedó en `_ARCHIVO_HISTORICO_2026-09-14\`.
- 🗝️ **Reclamo de tiendas (2026-09-10):** `/reclamar` = buscador (`reclamar.php`, regla `^reclamar/?$` en
  `.htaccess`) → `reclamar_negocio.php?slug=<slug>` = formulario → `directorio_reclamos` + aviso Telegram.
  El botón grande de la ficha es el bloque `.reclama-caja` de `negocio.php`.
  **WhatsApp del administrador: constante `ADMIN_WHATSAPP`** (`deploy/includes/helpers.php`; hoy
  **`908785164`**, el número del dueño de la página — ver 🗂️ DATOS ÚTILES; la usan el pie, la 404
  y el formulario vía `url_admin_reclamo()`). **La guía del módulo entero es `GUIA_RECLAMOS_DE_TIENDAS.md`**
  (el flujo, la tabla, el aviso, el Súper Admin, la 404 y las funciones del motor).
- 🟢 **Botones de WhatsApp (regla del jefe, 2026-09-11):** NINGÚN botón de WhatsApp abre el chat en
  blanco: todos llevan **mensaje con contexto + el ENLACE de la página exacta** y el
  **ícono oficial SVG** (`wa_icono_svg()` en PHP · `window.WA_ICONO_SVG` en JS).
  🆕 **2026-09-16 — «ES DEMASIADO TEXTO» (orden del jefe):** el enlace va **DENTRO de la frase** y ya
  **no** se escribe la línea aparte *«🔗 Página donde lo vi: \<URL\>»* en los mensajes al cliente; el
  saludo usa el **nombre corto** de la tienda (`wa_nombre_corto()`, sin el lema) y la consulta de un
  producto lleva **el enlace exacto del producto** (`/producto/<id>`), no el de la tienda. Lo resuelve
  **`wa_mensaje_con_enlace()`** (`includes/helpers.php`) y los copies NUEVOS escriben su mensaje con el
  marcador **`{URL}`**. La etiqueta vieja queda **solo** para los mensajes internos del administrador
  (reclamos, pedidos sin vendedor, aviso caducado de empleo). Toda la regla, el inventario de botones
  (hoy **15**; los 4 de tablones se retiraron el 2026-09-13), el **botón-imagen «CONSULTAR PRODUCTO»**
  (`assets/img/boton-consultar-producto.webp`, 15.6 KB, en la ficha rápida del producto) y las trampas:
  **`GUIA_BOTONES_WHATSAPP.md`** (índice en la maestra). Resumen técnico:
  `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` §8bis.
- 🔗 **Página 404 propia:** `404.php` ("No encontramos lo que buscas" + buscador + candidatos + WhatsApp
  del administrador). La usan el `ErrorDocument` del `.htaccess` **y** las fichas muertas (`negocio.php`).
- 🧨 **Pendiente:** las páginas de la arquitectura vieja que siguen en el hosting devolviendo **500**
  (`mercado.php`, `categorias.php`, `distritos.php`, `ultimos.php`, `ranking.php`, `api.php`,
  `cron_noticias.php`, `opiniones.php`, `track.php`): borrar o reescribir. Lista viva:
  `python __reclamar_14_paginas_viejas.py` → `__reclamar_paginas_viejas.txt`.
  ✅ **`noticias.php` ya salió de esa lista (2026-09-13):** se **reescribió** como la página del **módulo
  de noticias** (`/noticias` + `/noticia/<slug>`), y `cron_noticias.php` (también 500) quedó reemplazado
  por **`cron/noticias_diarias.php`**.
- 📰 **Noticias diarias (`/noticias`, módulo nuevo del 2026-09-13 — pedido del jefe):** un robot
  (`cron/noticias_diarias.php`, **Cron Job de hPanel a las 11:00 UTC = 06:00 de Chimbote**) lee los
  **RSS locales**, **abre el artículo** cuando el feed solo trae el titular, decide el **distrito**
  (Chimbote · Nuevo Chimbote · Santa) y le pide a la **API de DeepSeek** que la **reescriba en 200-500
  palabras sin inventar ni copiar**; después publica y avisa al Telegram. Dentro del texto, las palabras
  que son **rubro del directorio** quedan como **enlaces suaves en gris** a `/categoria/<slug>` (la idea
  del jefe: *«la letra es negra, el enlace podría ser gris»*). **10 es el tope, no la cuota**, y **sin
  texto de la fuente no hay noticia**. El **chatbot ya saluda con nuestra noticia** (el visitante se queda
  en el sitio). Guía: **`GUIA_NOTICIAS_DIARIAS.md`**. ⏳ **Falta poner el Cron Job en hPanel.**
  🆕 **Y las noticias se ofrecen en 5 sitios más** (pedido del jefe, 2026-09-13): opción **📰 Noticias de
  Chimbote** en el **menú hamburguesa**, **botón-tarjeta** en `panel.php` (todos los usuarios con sesión)
  y en el menú del **Súper Admin**, y el **slide de 4 fichas rápidas al azar** (blanco + rojo oscuro;
  el morado es un cambio de una línea) al final de la **página de rubro** (`categoria.php`) y del
  **buscador** (`buscar.php`) — bloque reutilizable `includes/noticias_slide.php` (`noticias_slide_html()`).
  ⚠️ El botón y el slide **no se pintan si no hay noticias publicadas**. Detalle: guía §10bis.
  📅 **La fecha va en CORTO (`13/09/2026`)** en todo lo visible (listado, ficha, otras noticias y slide)
  — el formato en palabras (*«lunes 1 de mayo del 2025»*) ocupaba mucho espacio (orden del jefe,
  2026-09-13) y quedó **solo** para el JSON del chatbot, que lo dice hablando.
  📆 **El listado de `/noticias` va AGRUPADO POR DÍAS** (cabecera `13/09/2026 · Hoy · 3 noticias`, con
  «Hoy»/«Ayer» según la fecha de Lima) y las fichas muestran **solo la fecha**; se **pagina por días**
  (`NOTICIAS_DIAS_POR_PAGINA` = 7) para que un día no quede partido. Guía §10ter.
- 🏷️ **LAS «FRASES DE UNIÓN» DEL RUBRO (tabla `directorio_categoria_claves`, 2026-09-13):** las palabras
  **reales** que la gente escribe y que llevan a un rubro («clavos» → Ferreterías, «paracetamol» →
  Farmacias). Es **el mismo diccionario** que usan **El caminante** (buscador predictivo), **las noticias**
  (enlaces suaves en gris) y ahora **el buscador del chat** (`chatbot_buscar.php`) y **la página del
  buscador** (`buscar.php`, `categoria_por_clave_texto()`): si la búsqueda por texto no encuentra nada, se
  muestran las tiendas del rubro. Cargadas el 2026-09-13: Ferreterías **11 → 251** y Farmacias **6 → 146**
  (total **1 057 → 1 437**) **y esa misma noche TODOS los rubros con tiendas** (orden del jefe: «frases
  para el buscador en todos los aspectos»): **87 rubros** repartidos en 6 lotes paralelos + los
  electrodomésticos → **3 320 claves en 122 rubros**. Los encargos/validaciones quedaron en
  `__claves_brief_1..6.json`, `__claves_lote_1..6.json`, `__claves_lotes_todas.json` y el
  generador/validador **`__gen_claves_lotes.py`** (normaliza, quita repetidas y choques, y escribe la
  sonda `__claves_seed2.php`). ⚠️ **Regla: si un rubro no encuentra algo que vende, lo que falta es la frase
  en esa tabla, NO código**; y las frases se escriben **sin tildes**, en minúsculas y de 1 o 2 palabras.
  ⚠️ Al añadir frases hay que mirar `NOTICIAS_ENLACE_NUNCA` (`config_noticias.php`), que veta las que en un
  diario significan otra cosa («lima», «cadena»); y **una palabra no debería estar en el rubro genérico y
  en el dueño de la palabra a la vez** (se quitaron «helado» de Bodegas y Pastelerías, «pescado» de
  Restaurantes y «aspiradora» de Grifos, que es el aspirado del car wash). Cuando dos rubros se pelean una
  palabra, gana el que **más negocios tiene** (desempate en `chatbot_busqueda_rubro()`, antes era la suerte).
  Detalle: `GUIA_CHATBOT_DEEPSEEK.md` **§2nonies c-bis**.
  🆕 **Y el 2026-09-14 se les sumó la JERGA DE LA CALLE** (pedido del jefe: *«vamos a comprar unos
  puchitos… la programación quizá no sepa traducir puchitos como cigarrillos»*): **23 claves nuevas**
  (3348 → **3371**) → Bodegas/Minimarkets `puchitos, puchito, puchos, pucho, cigarrillos, cigarrillo,
  cigarro, cigarros, tabaco, chelas, chela, jugo, jugos, refresco, rehidratante` y Mercados y Ferias
  `pina, pinas, papaya, sandia, melon, uva, naranja, limon`. Sonda que las cargó: `__claves_jerga.php`
  + `python __sonda_jerga.py go` (rollback: `DELETE … WHERE id > 3422`). **`puchitos` pasó de página
  vacía a 24 bodegas sin una sola línea de código de IA: era DATO.**
  📊 **Estado hoy: 3 506 claves en 122 rubros activos** (ver el bullet de RUBROS, abajo).
- 🏷️ **LA LEY DE LOS RUBROS Y LA RECLASIFICACIÓN DEL DIRECTORIO (2026-09-14, aprobada por el jefe):** el
  jefe pidió 25-40 rubros «cuanto más específico mejor» y se ejecutó: **12 rubros nuevos** (Barberías
  **65**, Motos/Scooters/Bicicletas **24**, Contabilidad y Trámites **6**, Ropa Deportiva **6**,
  Eventos: DJ/Animación/Shows **6**, Servicio Técnico **7**, Lavanderías **3**, Mudanzas y Fletes **3**,
  Estampados y Sublimados **3**, Electrodomésticos y Línea Blanca **2**, Importaciones y Catálogos **2**,
  Internet/Cable/Telefonía **1**), **2 renombrados** (Tortillerías y Anticuchos → **Comida al Paso,
  Ambulante y Nocturna**; Bancos y Agentes → **Bancos, Agentes y Pagos**), **7 vacíos llenados**
  (Imprentas y Publicidad 0→**15**, Cevicherías 0→**26**, Pollerías 0→**12**, Chifas 0→**9**, Lavado de
  Autos 0→**7**, Heladerías y Juguerías 0→**3**, Seguridad y Vigilancia 0→1, Confección de Uniformes
  0→1), **12 duplicados vacíos retirados** (Menús y Bodegones, Panaderías y Pastelerías, Ferreterías y
  Materiales, Grifos y Lubricentros, Talleres de Mecánica, Hospedajes y Cuartos, Transporte
  Interprovincial, Turismo y Agencias de Viaje, Locutorios y Pagos, Puntos de Pago Hidrandina,
  Restaurantes Campestres, Sastrerías y Confecciones) y **≈202 negocios movidos a su rubro específico**.
  **Las 4 reglas que mandan de ahora en adelante:** ① **un rubro es una palabra que la gente BUSCA**, no
  una actividad; ② **regla del 3+**: sin 3 negocios el rubro NO se crea y sus palabras van como claves
  al rubro padre (por eso Zumba, Taxi y Belleza a Domicilio **no** existen todavía: «zumba» responde con
  gimnasios y «taxi» con transporte); ③ **una palabra = un rubro** (se quitaron 22 del genérico: pollo,
  ceviche, chifa, mariscos, tamal, anticucho… si está en Restaurantes y en Cevicherías, gana
  Restaurantes y el rubro fino no sirve); ④ **la ganancia está en MOVER tiendas**, no en crear rubros.
  Además se **estrenó el rubro múltiple**: `directorio_negocio_rubros` pasó de **0 a 10 filas** (Xtreme
  Sport vive en Uniformes **y** Estampados sin duplicar rubros). Rubros activos **122**, vacíos **35 →
  18**, claves **3 506**. ⚠️ **Todo esto es DATO, no archivos**: se hizo con sondas temporales que se
  borran del hosting (plantilla reutilizable **`__rubros_migrar.php`** + `python __sonda_run.py
  __rubros_migrar.php <clave> [go]`; sin `go` es simulacro). ⚠️ Trampas: `uq_cat_clave` es única (no se
  pueden pasar claves con un UPDATE a secas), `directorio_negocio_rubros.creado_en` es NOT NULL, y **los
  movimientos se filtran por NOMBRE, nunca por descripción** (son plantillas automáticas: «%moto%»
  movió 69 mecánicos por la palabra «**motores**» y «%danza%» trajo mudanzas). Ley completa y receta:
  **`GUIA_RUBROS_Y_CLASIFICACION.md`**.
- 🧹 **LA LIMPIEZA DE LA BÚSQUEDA (2026-09-14, mando del jefe):** el buscador **ya no busca las palabras
  que solo ACOMPAÑAN** una búsqueda — «comprar», «buscar», «dónde hay», «quién tiene», «quién hace»,
  «sabes que quiero», «precio de», «me puedes recomendar»… (138 frases, 60 conectores, 10 muletillas).
  `comprar clavos` busca **clavos**; `dónde hay cerveza` = `quién tiene cerveza` = `sabes que quiero
  comprar cerveza` = **cerveza**. Motor y diccionario **único**:
  **`deploy/includes/busqueda_limpieza.php`** (lo manda al navegador como `window.CHIMBOTE_LIMPIEZA` desde
  `includes/footer.php`) + **`deploy/assets/js/buscador_limpieza.js`** (el mismo motor en JS; su respaldo
  se comprueba con `node __limpieza_prueba.js`). Se limpia en `buscar.php`, `api/sugerir.php`,
  `buscador_fuzzy.js` y `buscador_voz.js` (el dictado dejó su lista propia). La escalera de `buscar.php`
  es: **frase → todas las palabras → jerga local → rubro por frases de unión → rubro que acompaña (si el
  texto dio menos de 4 y la palabra es de un rubro)**. Si la limpieza deja la búsqueda vacía («comprar» a
  secas) se busca **tal cual** (marca `vacio`, y entonces NO se va a ningún rubro). Se le dice al
  visitante en una línea lo que se entendió, con enlace a `&literal=1`. Detalle: `GUIA_BUSCADOR_FUZZY.md`
  **§4.8**.
- 🎨 **EL COPY DEL BUSCADOR (2026-09-13, orden del jefe: «mejora el copywriting, hazlo más bonito,
  amigable»):** el chat no repite la misma frase: **cada caso tiene sus frases y se sortea una**
  (`chatbot_busqueda_frase()` en `chatbot_buscar.php`). Casos: **servicio** («Estos técnicos te pueden
  ayudar 🔧»), **con ubicación** («Estas te quedan cerquita 👇»), **rubro con más tiendas de las que se
  muestran** («Tengo **56** opciones de ferreterías 😃 mira estas»), **lo de siempre** («Sí, estos son los
  resultados de «pollería» en la ciudad»), **solo productos** («Justo tengo esto 👀»), **el rubro**
  («Tienes más en el rubro [Ferreterías] 👀»), **el buscador** («Y hay más en [el buscador] 👀») y **una
  sola pregunta final** («¿Te digo cuáles te quedan más cerca? Activa tu ubicación 📍»). El número real
  sale de `chatbot_busqueda_rubro()` (sin consulta extra) y solo se dice si las tiendas salieron del
  rubro. La página `buscar.php` también: el vacío es «Uy, todavía no tengo nada de eso» (con la invitación
  a preguntarle a **El ninja**) y la nota de las frases de unión empieza con 😉. Detalle:
  `GUIA_CHATBOT_DEEPSEEK.md` **§2nonies c-quater**.
- 🎩 **EL ANFITRIÓN DEL CHAT (2026-09-13, noche — pedido del jefe):** el chat **no solo contesta: ofrece
  negocios en el momento justo**, de manera inteligente e indirecta (*«es el momento perfecto para
  refrescarte con una bebida helada, mira estos negocios»*). Dos caminos: **MOMENTO** (el visitante
  cuenta que hace calor, que tiene hambre, que se le pinchó una llanta, que es un cumpleaños…) y
  **FOTO** (de la respuesta del modelo se sacan las palabras que son **frases de unión** y sale el rubro:
  un letrero de «reparación de laptops y formateos» → **Informática / Celulares**, con los **técnicos**
  primero). Vive en **`deploy/includes/chatbot_ofrecer.php`** y se engancha en un **envoltorio** al final
  de `chatbot.php` (`chatbot_responder()` → `chatbot_responder_base()` + `chatbot_ofrecer_aplicar()`),
  así vale para todos los caminos de respuesta sin tocar el motor. **Local: no gasta tokens.** Candados:
  una vez por conversación, nunca encima de una búsqueda, nada si el chat está bloqueado o si el rubro
  tiene menos de 2 negocios. Los momentos y sus términos (medidos contra la base) están en
  `chatbot_ofrecer_momentos()`. Detalle: `GUIA_CHATBOT_DEEPSEEK.md` **§2decies**.
- 🔧 **LAS BÚSQUEDAS DE SERVICIO DEL CHAT («reparar mi celular» → los TÉCNICOS del rubro, 2026-09-13):**
  el visitante escribe el **verbo** («reparar», «formatear», «arreglar») y las fichas están con el
  **sustantivo** («Reparación de pantalla», «Servicio técnico de celulares»). El buscador del chat traduce
  uno en otro (`chatbot_busqueda_servicios()`), busca **dentro del rubro** y **en los títulos de los
  productos**, y ordena en dos niveles: **1.º los que lo dicen en su NOMBRE** («Servicio técnico de
  celulares Julinho», «Microtech Service» = *los técnicos*), **2.º los que tienen el servicio publicado**,
  3.º el resto del rubro. Los servicios publicados también salen antes que los demás productos.
  ⚠️ Un sustantivo de servicio **suelto** NO se busca como tienda: «reparacion» a secas lleva a las
  **llanterías** (su rubro es «Reparación de llantas»); solo sirve para ordenar. Detalle:
  `GUIA_CHATBOT_DEEPSEEK.md` **§2nonies c-ter**.
- 🛠️ **EL MAESTRO — CONSTRUCTOR DE TIENDAS CON IA (módulo nuevo, 2026-09-14 — pedido del jefe):** la página
  `/crear-tienda` (`crear_tienda_ia.php`; **ya no exige sesión para empezar**: la cuenta la crea el propio
  asistente al publicar, con el WhatsApp del dueño como usuario) donde el dueño **arma su tienda
  conversando**. 📸🆕 **VERSIÓN 6 (2026-09-15, noche) — «8 FOTOS Y CONFIRMA ANTES DE PUBLICAR»:** el saludo
  dice *«Crea tu tienda con nosotros 🚀 Mándame 8 fotos…»*, **ese botón abre la galería del celular** (en ese
  paso **no hay botón de cámara**; el selector del celular igual deja tomar la foto) y el paso dice **qué
  fotos quiere** («afuera 🏪 · dentro 🛋️ · tus productos 📦 · tus máquinas 🧰 · tu personal 👥 · tu tarjeta 💳
  · tu folleto 📄»). **Mínimo 8 fotos** (estricto) y **UNA sola llamada de visión a 1600 px**
  (`tienda_ia_ia_arranque()` + `tienda_ia_leer_ficha()`) saca de ahí **el nombre del letrero, el rubro, el
  teléfono del aviso, la dirección, si es local o delivery y lo que vende**; se le confirma **con un toque**
  (nombre y rubro real del directorio). 🔴 **ANTES DE PUBLICAR se confirman el nombre Y el número**, y **si
  ese número ya es de otra tienda el asistente se detiene y pregunta** (paso `tel_ocupado`: *«Ese número ya
  tiene la tienda «X». ¿Es tuya?»*) **sin publicar ni tocar nada** de la tienda que ya existía (orden del
  jefe: *«se puede dañar una tienda que ya exista»*; probado con un cebo real en `__tia_prueba12.php`). Con
  el WhatsApp **la tienda YA SE PUBLICA — pero sin decírselo**: la conversación sigue `en_curso`, lo que
  falta (**cómo vende · la dirección · la zona · el horario · las redes**) se pregunta después y cada
  respuesta **actualiza la tienda en vivo** (`tienda_ia_actualizar_silenciosa()`), y **el enlace, su usuario
  y su clave se le muestran al final, como estreno** (`tienda_ia_revelar()`); los botones dicen
  **`📝 Cambiar algún dato`**, nunca «editar». **Los campos de `registrar_negocio.php` están cubiertos**:
  nombre · rubro · **los 5 tipos de ubicación** (fisica · ambulante · domicilio · nacional · mayorista) ·
  **dirección** · zona/GPS · teléfono/WhatsApp · horario · **Facebook, Instagram y TikTok** · descripción
  (la escribe la IA). Paso a paso en el **§2bis** de la guía. **Lo anterior sigue vivo**
  (pasos de producto, modo `?modo=producto` y conversaciones viejas): nombre de la tienda (aclarando que **no son
  los productos**), de qué trata, **fotos de la tienda** que la IA MIRA y comenta, cómo vende (ambulante 🛵 /
  delivery 🏠 / local 🏪), zona por GPS, WhatsApp y horario → y **publica la tienda**; después le ofrece
  **crear su primer producto** (felicitación, **eliminar con un clic** o crear otro) y **a los 2 productos** le
  ofrece el **WhatsApp del administrador (hoy `908785164` · `TIENDA_IA_WHATSAPP_MAS` = `ADMIN_WHATSAPP`)
  con el mensaje ya escrito** para más productos.
  **El servidor manda los pasos y la IA solo escribe** (cada paso tiene guion local de respaldo: la conversación
  nunca se traba). ⚡ **Los textos son CORTOS Y DIRECTOS** (orden del jefe: *«me aburriste… minimalista, rápido, sin
  tanto detalle»*): 1 o 2 líneas por mensaje y la IA con UNA frase de máximo 15 palabras. En cada paso de foto hay **dos botones: 📷 Cámara y 🖼️ Galería**. **La cuenta la crea él**:
  usuario = **el WhatsApp del dueño** y contraseña generada de **3 letras + 1 número (sin O ni 0)**, que se le
  muestra una vez con un botón para guardarla en su propio WhatsApp; **se entra con el número de teléfono**
  (login.php dice «Correo o número de teléfono»). Tiene **clave PROPIA de DeepSeek con visión** (`includes/config_tienda_ia.php`, cuenta aparte del
  chat 🥷 **para poder medir el consumo**: Súper Admin → **🛠️ El maestro (tiendas IA)** con embudo, tokens, costo por
  tienda y saldo), **2 tablas** (`directorio_ia_tiendas`, `directorio_ia_tiendas_log`, se crean solas), **2 opciones
  nuevas en el chat 🥷** («Crear mi primera tienda» y «Ya tengo tienda: subir un primer producto» →
  `/crear-tienda?modo=producto`) y botón naranja en el panel del dueño. ⚠️ **Lo que hay que saber:** el
  **razonamiento va apagado** (`reasoning_effort: none`: medido, gasta 12× menos, tarda la mitad y deja de contestar
  vacío); **al modelo no se le pide JSON**; **la foto de un letrero NO es una captura** (0 falsos positivos con fotos
  reales) y la captura se **avisa una vez y después manda el dueño**; el gasto se mide **desde el log de llamadas**.
  Costo real: **US$ 0,000106** por llamada de texto · **US$ 0,000137** con foto → **≈ US$ 0,002 por tienda**.
  Guía: **`GUIA_CONSTRUCTOR_DE_TIENDAS.md`**.
- 🔬 **Leer datos de producción sin phpMyAdmin (el MySQL remoto está cerrado):** sonda temporal por FTP
  (`python __sonda404.py`, plantilla en `D:\RELAX\__sonda404.php`, **nunca dentro de `deploy/`**); se borra
  del servidor al terminar. Ver `GUIA_DESPLIEGUE_Y_ENTORNO.md` §3.1 y, para diagnosticar 404/500,
  `GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md` §9.1.
- Cron del monitoreo (hPanel, tipo **Personalizado**): `0 * * * *` →
  `/usr/bin/php /home/u196269909/domains/dechimbote.com/public_html/cron/monitoreo_sistema.php`.

## 📚 GUÍAS DEL PROYECTO (índice completo en `GUIA_MAESTRA_CHIMBOTE_XYZ.md`)

**Base:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` · `REGLAS_DE_ORO_PROYECTO.md` · `GUIA_DESPLIEGUE_Y_ENTORNO.md` ·
`GUIA_CRONICA_Y_TRABAJO_DE_SESION.md` (flujo de trabajo, **qué se escribe y dónde — solo la guía del
módulo, sin crónicas —** y cómo verificar **sin descargar el sitio entero**)

**Módulos:** **`GUIA_CONSTRUCTOR_DE_TIENDAS.md`** 🛠️ (el constructor de tiendas con IA: la página privada `/crear-tienda`, «El maestro», su clave propia y su pestaña de consumo) · **`GUIA_OPINIONES_ANONIMAS.md`** 💬 (las opiniones anónimas de la ficha: las dos pestañas
Descripción/Opiniones, el botón 🚩 Reportar, el panel **Reclamos de opiniones** con 🗑️/🙈 y la siembra de
3 opiniones con contexto en las últimas 100 tiendas) · **`publicando a los amigos de jimmy.md`** 🤝 (los servicios de los amigos del jefe: flujo
completo + **colores y botones de WhatsApp dentro del copy**; leer SOLO esa cuando él lo pida por ese
nombre) · `GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md` · **`GUIA_NOTICIAS_DIARIAS.md`** (noticias locales
automáticas: `/noticias` + `/noticia/<slug>`, robot a las 06:00 y **enlaces suaves en gris** a los rubros) ·
`GUIA_EMPLEOS_Y_ANUNCIOS.md` (empleos y anuncios:
`/empleos`) · **`GUIA_RECLAMOS_DE_TIENDAS.md`** (🗝️ reclamo de tiendas: `/reclamar`, la tabla, el aviso, el
Súper Admin, la 404 y las funciones) · **`GUIA_DISENO_DEL_INDEX.md`** (🏠 la portada: los bloques en su
orden real, la cabecera hamburguesa y los ayudantes del index) · `GUIA_PUBLICIDAD_Y_BANNERS.md` ·
`GUIA_CERCA_DE_MI_Y_GEOBUSQUEDA.md` · `GUIA_HUBSPOT_CRM.md` · `GUIA_METABASE_BI.md` ·
`GUIA_CAMINANTE_WEB.md` · `GUIA_ESTADISTICAS_DEL_SITIO.md` · **`GUIA_RECORDS_DEL_SITIO.md`** (🏆 récords
del sitio: más vistos, más buscados, más pedidos, dinero, embudo, rubros y **resumen copiable** para
inversionistas) · `GUIA_SUPERADMIN_PANEL.md` ·
`GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` · `GUIA_PLANTILLAS_FICHA_Y_COLORES.md` ·
`GUIA_IMAGENES_Y_OPTIMIZACION.md` (todo lo que suba o muestre imágenes: compresión en el
navegador, versiones de 300/800/1600 px, `srcset` y zoom de galería) ·
`GUIA_BUSCADOR_FUZZY.md` (buscador con Fuse.js: tolera errores de tipeo) ·
**`GUIA_RUBROS_Y_CLASIFICACION.md`** (🏷️ **la ley de los rubros**: cuándo un rubro merece existir, la
regla del 3+, una palabra = un rubro, rubros extra, subcategorías, y la receta para mover tiendas con
sonda temporal) ·
`GUIA_BUSCADOR_VOZ.md` (micrófono 🎙️ gratis con la Web Speech API: dictado y limpieza) ·
`GUIA_PANEL_DUENO_Y_CAPTURA_RAPIDA.md` (panel del dueño: rejilla de fotos con la **cámara y vista previa
instantánea** + dictado 🎙️ de la descripción; `captura_rapida.js` y `dictado_voz.js`)

**Historial:** desde el **2026-09-14** esa práctica **terminó** por orden del jefe (**no se crean crónicas
por sesión ni guías por cliente**). Todo lo viejo se movió a **`_ARCHIVO_HISTORICO_2026-09-14\`**
(`cronicas_de_sesion\` · `cartas_ia_imagenes\` · `actas_de_verificacion\` · `traspasos_de_tanda\` ·
`pc_del_jefe_dictado_y_zcode\` · `_archivos_anteriores\`): **es historial, no fuente de verdad — no se lee
ni se busca ahí para trabajar.**
