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


# GUÍA — RESPALDO DEL SITIO EN GITHUB · dechimbote.com

> **Para qué sirve:** guardar una **copia de seguridad** del sitio (`D:\RELAX\deploy`) y de **todas las guías
> `.md`** en un repositorio de GitHub, para tener **historial** de todo lo que se ha hecho y poder **volver
> atrás** si algo se rompe.
> **En una línea:** `python D:\mi-sitio-web\__sync_backup.py subir` — y ya.

---

## 1. 📍 DÓNDE VIVE

| Dónde | Qué es |
|---|---|
| **`D:\RELAX\deploy`** | El **sitio de verdad** (lo único que se edita y se sube por FTP). El respaldo **solo lee de aquí: no lo toca ni lo cambia**. |
| **`D:\mi-sitio-web`** | El **clon de Git** (carpeta `D:\mi-sitio-web\.git`). Aquí se arma la copia tapada y desde aquí se sube. |
| **`D:\mi-sitio-web\deploy`** | La copia del sitio **con las claves tapadas**. Es lo que se ve en GitHub. |
| **`D:\mi-sitio-web\*.md`** | Las guías del proyecto, copiadas de la raíz de `D:\RELAX`. |
| **`D:\mi-sitio-web\base_de_datos\`** | 💾 **El respaldo de la base** (`.sql.gz`) + **`ESQUEMA.sql`** legible + `LEEME.md`. **No lo toca `__sync_backup.py`**: se sube aparte (§8). |
| **`https://github.com/jimlogam/chimbotede`** | El repositorio (rama **`main`**). |
| **`D:\mi-sitio-web\__sync_backup.py`** | **La herramienta** (16,7 KB). Es la única que hay que correr. |

---

## 2. ▶️ EL COMANDO ÚNICO

```powershell
python D:\mi-sitio-web\__sync_backup.py subir
```

Otros modos útiles:

| Modo | Qué hace |
|---|---|
| `python __sync_backup.py` (sin nada) | Arma la copia y la revisa, **pero NO sube nada** (para mirar antes). |
| `python __sync_backup.py revisar` | Solo revisa la copia que ya está armada (no vuelve a copiar). |
| `python __sync_backup.py detalle` | Lista, una por una, **las claves que se van a tapar** (archivo, línea y nombre). |
| `python __sync_backup.py subir` | Copia + tapa + revisa + **commit y push** a GitHub. |

---

## 3. 🔧 QUÉ HACE, PASO A PASO

1. **Copia el sitio:** reemplaza `D:\mi-sitio-web\deploy` **entero** con lo que hay en `D:\RELAX\deploy`.
2. **Copia las guías:** los `.md` de la raíz de `D:\RELAX` (hoy **247**).
3. **Tapa todas las claves** (ver §4).
4. **Revisión de claves:** recorre la copia y, si encuentra un valor conocido o algo con forma de clave,
   **avisa y se detiene sin subir**.
5. **Revisión de daños:** compara la copia con los originales **línea por línea**: cualquier línea que haya
   cambiado tiene que llevar `PON_AQUI…` (o sea, ser una clave tapada). Si cambió algo más, **avisa y no sube**:
   eso sería un daño, no un respaldo.
6. **Commit y push** a `origin main` (autor: `Jimmy Lopez <jimlogam@users.noreply.github.com>`, mensaje
   `Copia de seguridad del sitio: AAAA-MM-DD HH:MM`). Si no hay nada nuevo, dice *«la copia de GitHub ya está
   al día»* y no hace commit.

### Lo que NO se copia (a propósito)

| Fuera | Por qué |
|---|---|
| `cache/`, `logs/`, `__pycache__/` | Temporales del servidor, no son código (también están en el `.gitignore` del repo). |
| `*.log`, `Thumbs.db`, `desktop.ini`, `.DS_Store` | Basura del sistema. |
| `.git`, `.idea`, `.vscode` | Cosas de las herramientas, no del sitio. |

⚠️ **`fuse.min.js` NO se toca nunca** (librería ya comprimida: cambiarle una letra rompería el buscador).

---

## 4. 🔑 LAS CLAVES: CÓMO SE TAPAN

Las claves de verdad **viven solo en la computadora del jefe** (`D:\RELAX\deploy\config*.php` y los
`includes\config_*.php`). En el respaldo **nunca** van escritas: quedan como texto de relleno.

| Cómo se tapa | Ejemplo |
|---|---|
| **Valores conocidos** (lista fija de 8) | clave del FTP `Samsung…` → `PON_AQUI_LA_CLAVE_DEL_FTP` · clave de la base · las 2 claves de DeepSeek · la de Google · el token de HubSpot · el token del obrero y la clave de las sondas |
| **Por su forma** (aunque cambien mañana) | `sk-…` (DeepSeek) · `GOCSPX-…` (Google) · `pat-…` (HubSpot) · `123456789:AA…` (bot de Telegram) · `aldia-…` (clave interna) |
| **Por el nombre de la variable** | cualquier asignación que se llame `KEY`, `TOKEN`, `SECRET`, `PASS`, `PASSWORD` o `CLAVE` (en PHP, JS, JSON, HTML, `.htaccess`…) |

- **Lo que NO se tapa** aunque lo parezca: nombres de columna, cabeceras HTTP, abecedarios, `KEYWORDS`,
  `CLAVE_TABLA` y los guardados del navegador (si se taparan, se rompe el código).
- **Comprobado el 2026-09-21:** `detalle` encontró **33 claves en el código** (base, HubSpot, cron, migradores,
  DeepSeek, Google, Telegram, monitoreo, noticias…) y la corrida tapó **93 en 49 archivos** (contando las que
  aparecen escritas dentro de las guías). Las dos revisiones dieron **✅**.

---

## 5. ✅ CÓMO SE COMPRUEBA QUE QUEDÓ SUBIDO

```powershell
git -C D:\mi-sitio-web log --oneline -3          # el último commit tiene que ser el de hoy
git -C D:\mi-sitio-web status --short            # vacío = no quedó nada pendiente
```

En GitHub: la pestaña **Commits** del repositorio. Y si se quiere comprobar un archivo concreto, en
**`https://github.com/jimlogam/chimbotede/blob/main/deploy/<ruta>`** (el tamaño tiene que coincidir con el local).

---

## 6. 🔴 LO QUE HAY QUE SABER ANTES DE CONFIAR EN ESTE RESPALDO

1. **EL REPOSITORIO ES PÚBLICO** (verificado el **2026-09-21** con la API de GitHub: `private: false`), aunque
   el `README.md` de dentro diga «copia de seguridad privada». Decisión del jefe ese día: *«sube ya, tal cual»*.
   **Qué se ve:** el código del sitio, las 247 guías, y datos como la cuenta de hosting, el usuario y el host del
   FTP y el nombre/usuario de la base MySQL. **Qué NO se ve:** ninguna contraseña (van tapadas, §4).
   Si algún día se quiere **privado**: en GitHub → *Settings* → *General* → *Danger Zone* → *Change visibility*.
2. **Las credenciales de subida ya están guardadas** en el Administrador de credenciales de Windows (usuario
   `jimlogam`): `git -C D:\mi-sitio-web ls-remote origin` funciona **sin pedir clave**. Si algún día pide clave,
   es que la credencial se borró: hay que volver a iniciar sesión con la cuenta del jefe.
3. **La carpeta `deploy` se reemplaza entera**: si algo se borra en `D:\RELAX\deploy`, desaparece del respaldo
   nuevo — **pero sigue vivo en los commits viejos** (ahí se recupera: *History* del archivo en GitHub).
4. **Los archivos temporales de las sondas no van** ni tienen por qué ir: el sitio de verdad es `D:\RELAX\deploy`.
5. **Nada de esto toca el sitio publicado**: es una copia hacia GitHub, no un despliegue. Para publicar se sigue
   usando el flujo de siempre (`php -l` → `__subir_uno.py` → comprobar), ver `GUIA_DESPLIEGUE_Y_ENTORNO.md`.

---

## 7. ♻️ CÓMO SE RESTAURA EL SITIO CON ESTE RESPALDO

1. Bajar el repositorio (o el archivo que haga falta) desde GitHub.
2. Volver a poner las claves de verdad en los sitios marcados con **`PON_AQUI…`** (los mismos archivos que
   están en la computadora del jefe: `config.php`, `config_hubspot.php`, `includes\config_*.php`, `helpers.php`…).
   Con las claves tapadas **la web no funciona**: se ve la página de error de la base.
3. Subir el código por FTP como siempre (`GUIA_DESPLIEGUE_Y_ENTORNO.md`).

> ⚠️ **Las fotos del sitio NO están en el respaldo**: solo se guarda el **código** y las guías. Las imágenes
> viven en el hosting, en `fotos/`.

---

## 8. 💾 LA BASE DE DATOS TAMBIÉN VA (2026-09-21, pedido del jefe)

> *«La base de datos también debe estar ahí para poder leer tablas y lo que sea necesario, valores,
> elementos, etc., todo.»*

**Dónde queda:** **`D:\mi-sitio-web\base_de_datos\`** → en GitHub, **`base_de_datos/`**.

| Archivo | Qué es |
|---|---|
| **`backup_<fecha>.sql.gz`** | El respaldo completo que **arma el propio sitio**: estructura + datos de todas las filas. |
| **`ESQUEMA.sql`** | La **estructura en texto** (tablas, filas, columnas y el `CREATE` de cada una) para leerlo directo en GitHub. |
| **`LEEME.md`** | Explica de dónde sale, cómo se lee, cómo se restaura y las trampas. |

### La receta (4 pasos)

```text
1) Disparar el respaldo del sitio:
   https://dechimbote.com/cron_runner.php?tarea=backup&k=<CLAVE_DE_CRON_RUNNER>   (la # se escribe %23)
2) Bajarlo por FTP:  /__backups/backup_<fecha>.sql.gz  →  D:\RELAX\_BASE_DE_DATOS\
3) Sacar el esquema legible:   python D:\RELAX\__bd_esquema.py
4) Copiar el .sql.gz y el ESQUEMA.sql a D:\mi-sitio-web\base_de_datos\ y subir con git (add · commit · push)
```

- La clave del cron es `CRON_KEY` de `deploy/cron_runner.php` (⛔ **en las URLs la `#` tiene que ir como
  `%23`**, si no el navegador corta la dirección y da «Acceso denegado»).
- El sitio guarda **las últimas 14** copias en `__backups/` y las rota solo.
- 🛠️ **`D:\RELAX\__bd_esquema.py`** es la herramienta que lee el `.sql.gz` y escribe el `ESQUEMA.sql`
  (uso: `python __bd_esquema.py [archivo.sql.gz] [salida.sql]`).
- ⚠️ **La base se sube aparte, con `git`**: `__sync_backup.py subir` solo copia `deploy\` y las guías `.md`,
  así que **nunca sube ni actualiza la base** (eso es a mano, con la receta de arriba).
- ⚠️ **Y ojo con esto:** el sincronizador, al tapar claves, **recorre TODA la carpeta del clon** (incluida
  `base_de_datos\`) y lee el `.gz` como si fuera texto. Hasta hoy **no le ha hecho nada** (el dump va comprimido
  y ningún valor coincide con las claves que busca), pero lo prudente sería **dejar la base fuera de su camino**
  (una línea en `NO_COPIAR`). **El jefe decidió el 2026-09-21 no tocar el sincronizador**: queda anotado aquí
  como riesgo conocido — si algún día el dump apareciera dañado después de un respaldo, mirar por aquí primero.

### Lo que el jefe decidió ese día (y hay que respetar)

1. **La base va al repositorio aunque sea PÚBLICO** (datos reales de las 5 300 tiendas, la tabla de usuarios
   con claves cifradas, opiniones, pedidos y estadísticas). Se le avisó antes de subir.
2. **No se cambia `cron/tasks/backup.php`**: el dump sube **tal cual**, con su defecto (§ abajo).
3. **La clave que quedó escrita dentro del dump** (la del instalador de HubSpot, en el histórico de visitas)
   **se sube tal cual**, sin taparla. Se le avisó antes de subir.
4. **El esquema legible sí**: se agregó `ESQUEMA.sql`.

### 🔴 El defecto del respaldo del sitio (no se tocó, por orden del jefe)

`cron/tasks/backup.php` pide **`SHOW TABLES`**, y ahí **también salen las 7 VISTAS**: las vuelca como si
fueran tablas, con sus filas dentro. En el dump del **2026-09-21** eso fue **23 de los 27,9 MB**, y
`vista_alianzas_sugeridas` llegó al **tope de 500 000 filas** (se cortó ahí).

**Consecuencia al restaurar:** las líneas `INSERT INTO vista_…` **no se pueden ejecutar** (MySQL no deja
insertar en una vista así): hay que **saltarlas** y dejar que cada vista se rehaga con su `CREATE`.
**Arreglo pendiente si el jefe lo pide:** cambiar en `backup.php` el `SHOW TABLES` por
`SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'` y volcar las vistas solo con su `CREATE VIEW`.

### Lo que trae el respaldo del 2026-09-21 (el sitio en números)

**58 tablas · 161 918 filas · 7 vistas.** Las más grandes: `directorio_servicios` 28 384 (productos) ·
`directorio_producto_fotos` 22 316 · `directorio_avisos_log` 20 721 · `directorio_stats_eventos` 18 147 ·
`directorio_categoria_claves` 16 774 · `directorio_stats_sesiones` 16 166 · `directorio_fotos` 11 013 ·
`directorio_vistas` 9 640 · `directorio_opiniones` 6 257 · **`directorio_negocios` 5 300 (las tiendas)** ·
`directorio_usuarios` 89.

### ⚠️ Dato importante: el respaldo automático de la base estuvo CAÍDO

La copia anterior a esta era del **2026-09-10** (2,3 MB): la tarea se dispara con el **Cron Job de hPanel** y
tras la mudanza del dominio el trabajo quedó apuntando a la ruta vieja. **Se dispara a mano** con la URL del
paso 1 (y esa misma noche, a las 23:49, apareció **una segunda copia sola**, así que el cron parece estar
vivo otra vez). Si el jefe quiere el respaldo de la base al día, hay que **correr la receta de arriba**
cuando se pida.

---

## 9. 📋 REGISTRO DE RESPALDOS (una fila por subida)

| Fecha | Commit | Qué entró | Claves tapadas |
|---|---|---|---|
| 2026-09-21 | `0ce5f1d` | **Primera copia completa** (sitio + guías), con las claves ya tapadas | ✅ (según la herramienta) |
| **2026-09-21 23:34** | **`9ad4d8d`** | **53 archivos** (31 nuevos + 22 modificados). **Nuevos:** las cartas `CARTA_IA_PRODUCTOS_BANCO9_*` a `BANCO13_*` (25), `CARTA_IA_PRODUCTOS_NUEVA_IA_HOSTING.md`, `GUIA_IA_NUEVA_1_CREAR_LAS_IMAGENES.md`, `GUIA_IA_NUEVA_2_SUBIR_Y_ASIGNAR_AL_HOSTING.md`, `__pr_BRIEF_CATALOGO.md` y **`deploy/includes/paleta.php`**. **Modificados:** `AGENTS.md`, `GUIA_DISENO_DEL_INDEX.md`, `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md`, `GUIA_SUPERADMIN_PANEL.md`, `revisando cada tienda final.md` y en el sitio `assets/css/components.css`, `como-se-usa.php`, `empleo.php`, `explorer.php`, `mi-tienda.php`, `novedades.php`, `panel.php`, `precios.php`, `productos.php`, `superadmin.php` e `includes/` (`doc_pagina.php`, `doc_vista.php`, `header.php`, `muro.php`, `nosotros_area.php`, `panel_reco.php`, `pedidos_sin_vendedor.php`) | **93 en 49 archivos** · ✅ ninguna quedó escrita · ✅ todo lo demás igual letra por letra |
| **2026-09-21 23:49** | (la subida de la base) | 💾 **LA BASE DE DATOS (pedido del jefe):** `base_de_datos/backup_20260921_234904.sql.gz` (**27,9 MB** · 58 tablas · 161 918 filas), **`base_de_datos/ESQUEMA.sql`** (72 KB, legible), su **`LEEME.md`** y la actualización del `README.md` del repositorio | La base **va SIN tapar** (decisión del jefe): es el dump del sitio tal cual |

---

## 10. 🧠 LO APRENDIDO (para no repetir errores)

- **2026-09-21:** el repositorio decía «privada» en su `README.md` **pero es público**. Se avisó al jefe antes de
  subir y él decidió subir igual; queda anotado en §6.1 para que nadie dé por hecho que es privado.
- **2026-09-21:** el número de guías que dice `AGENTS.md` («43 archivos `.md`») está **viejo**: la copia de hoy
  subió **247**. El respaldo cuenta las guías por sí mismo, no de una lista escrita a mano.
- **2026-09-21:** el respaldo **no gasta cuota de nadie ni toca el hosting**: es solo copiar a GitHub.
- **2026-09-21:** **el respaldo automático de la base llevaba 11 días caído** (el Cron Job de hPanel quedó con
  la ruta vieja tras la mudanza del dominio). El respaldo del sitio se ve **a simple vista**: la carpeta
  `__backups/` del hosting dice la fecha de la última copia. **Conviene mirarla antes de confiar en que hay base.**
- **2026-09-21:** **el dump pesa 4 veces más de lo que debería** porque la tarea vuelca las vistas
  (`SHOW TABLES` las incluye): 23 de 27,9 MB son vistas repetidas. Se avisó al jefe y **él decidió no tocar
  el respaldo del sitio**; si algún día lo pide, el arreglo está escrito en §8.
- **2026-09-21:** **en las URLs de las sondas y del cron la `#` se escribe `%23`** (la clave del cron es
  `ChimboteCron2026#Jimmy`): sin eso el navegador corta la dirección y el sitio responde «Acceso denegado».
- **2026-09-21:** el respaldo de la base **NO se tapa** (a diferencia del código) y además trae dentro
  **una clave viva** escrita en el histórico de visitas. Antes de subir cualquier dump hay que **decirle al jefe
  qué va a quedar público**, aunque ya haya elegido repositorio público.
