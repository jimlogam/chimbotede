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


# GUÍA DEL ARNÉS: AVISOS SONOROS Y PLUGINS (`dsh-sounds`)

> **Qué es:** el arnés (la aplicación del agente, DeepSeek Harness) **avisa con un sonido** cuando
> termina una tarea, cuando pregunta algo, cuando entra un mensaje a la cola, cuando hay error o se
> va el internet, y cuando se cierra la sesión. Nació el **2026-09-19** de un pedido del jefe:
> *«cuando acabe una tarea quiero que haga algún sonido para yo enterarme que ya ha acabado y está
> esperando que yo le dé otra orden, y también un sonido para cuando haya alguna pregunta… tienes
> seis sonidos, úsalos, a cada uno asígnale una función a tu criterio»*.
>
> **Cuándo leer esta guía:** cuando haya que **cambiar, apagar o reparar los sonidos**, cuando haya
> que **agregar otro plugin al arnés**, o cuando el jefe diga *«ya no suena»* / *«quiero otro sonido
> para…»*.

---

## 1) LOS SEIS SONIDOS Y SU FUNCIÓN (lo que suena hoy)

Los seis MP3 los dejó el jefe en `C:\Users\Usuario\Downloads` (regla de oro n.º 8: Descargas es la
única zona de trabajo). Se **copiaron** al plugin y **se borraron de Descargas**.

| # | Sonido (archivo original) | 🎯 Cuándo suena | Interruptor en Ajustes |
|---|---|---|---|
| 1 | `piuw.mp3` | 🟢 **Empieza a procesar**: le llegó una orden nueva en la sesión que está viendo | `start` |
| 2 | `cuak-sound-effect.mp3` | 📥 **Mensaje en cola**: se encoló un mensaje mientras el agente todavía trabaja | `queued` |
| 3 | `rizz-sound-effect.mp3` | ❓ **Te está preguntando**: pide una respuesta o una aprobación y espera por usted | `question` |
| 4 | `undertakers-bell_2UwFCIe.mp3` | 🔔 **Terminó la tarea** (el aviso principal) y 🔔 **terminó otra sesión** | `done` · `doneOther` |
| 5 | `roblox-death-sound_1.mp3` | 🔴 **Error**: falla el turno, se corta la conexión con el arnés o el navegador se queda sin red | `error` |
| 6 | `michael-jackson-hee-hee.mp3` | 🏁 **Se cerró la sesión**: la cierran, la borran o usted detiene el trabajo a mano | `end` |

**Reglas de convivencia que ya están puestas** (para que no sea un escándalo de ruido):

- 🔇 **Si la ventana tiene el foco** (usted está escribiendo o mirando esta pestaña), el aviso
  «terminó» **se calla**: usted ya lo está viendo. Los importantes (pregunta, error) **sí suenan**.
- 🔔 **Un turno que termina en ERROR no hace sonar la campana**: suena el sonido de error, no los dos.
- ❓ Un turno que termina **pidiendo una respuesta** suena «pregunta» y **no** la campana.
- 📥 Si al terminar quedan **mensajes en cola**, **no** suena la campana: el siguiente turno arranca
  solo y el trabajo no ha acabado.
- 🚫 **Al abrir la página no suena nada** (la primera lectura solo apunta el estado).
- 🚫 El **sondeo interno** (cada 800 ms) **no duplica** avisos: el antirrebote es **por evento**, así
  que dos avisos **distintos** con pocos segundos de diferencia **sí suenan los dos** (dos tareas que
  acaban seguidas, dos errores distintos, dos cortes de internet).
- ⚠️ **El navegador no deja sonar hasta el primer clic** en la página (política de autoplay). Como el
  jefe da clic siempre, en la práctica no se nota; por eso la pestaña **debe tener volumen**.

---

## 2) DÓNDE VIVE (y por qué ahí)

```text
C:\Users\Usuario\.dsh\plugins\dsh-sounds\      ← el código fuente del plugin (paquete @dsh-external/dsh-sounds)
├── assets\                                    ← los 6 MP3 originales (211 182 bytes en total)
├── lib\
│   ├── index.mjs                              ← mitad Node (host): VACÍA a propósito (todo pasa en el navegador)
│   ├── client.template.js                     ← LA FUENTE que se edita (con el marcador __AUDIO_MAP__)
│   └── client.js                              ← GENERADO (~294 KB): panel + vigilancia + los 6 MP3 en base64
├── cordis.patch.yml                           ← la fila que lo monta en el árbol del arnés
└── package.json                               ← paquete + dsh.bundle.patch + dsh.client{platform:web}
```

- El plugin **se instala como dependencia del perfil web**: `C:\Users\Usuario\.dsh\profiles\web\package.json`
  lleva `"@dsh-external/dsh-sounds": "file:C:/Users/Usuario/.dsh/plugins/dsh-sounds"` y su nombre en
  `dsh.profile.bundles`.
- ⚠️ **El audio va EMBEBIDO** en `client.js` como *data URI* base64 (no se sirve por HTTP ni depende de
  rutas): así el plugin es autosuficiente y se entrega en **una sola petición**. Cuesta ~294 KB por
  carga de página, una vez.
- **Los ajustes del jefe** (qué suena y qué no) viven en el **`localStorage` del navegador**, bajo la
  clave `dsh-sounds.settings.v1`. No tocan la base del sitio ni el `settings.yaml` del arnés.

---

## 3) CÓMO SE USA (el panel)

**Ajustes → Avisos sonoros** (esta es la sección propia del plugin):

- **Interruptor general** `Avisos activados`: apaga **todo** sin perder la configuración.
- **Una fila por sonido**, con emoji, explicación, el **nombre del archivo**, un botón **▶ Probar** y su
  interruptor. Apagar una fila solo apaga ese aviso.
- Arriba hay **🔔 Probar el de «terminó»** y **❓ Probar el de pregunta** para oírlos de un toque.
- **Para cambiar un sonido por otro**: se reemplaza el MP3 en `assets\` con el **mismo nombre**, se
  regenera `client.js` (§5) y se refresca la página.

---

## 4) LO QUE HAY QUE SABER ANTES DE TOCARLO (aprendido el 2026-09-19)

1. 🔴 **Un plugin NUEVO exige reiniciar el arnés.** El árbol de plugins se compone **una sola vez**, al
   arrancar el proceso `dsh web`. Por eso, tras instalar el plugin, hay que **cerrar y volver a levantar
   el arnés**; la conversación **no se pierde** (todo va a disco) y se retoma **desde la barra lateral**
   (⚠️ **no existe `--resume`** en el perfil web; el turno que estaba en curso se cierra, no continúa).
2. 🟢 **Cambiar solo el navegador NO exige reiniciar.** El bundle se sirve **del disco en cada petición**
   con `cache-control: no-cache`, y hay un vigilante (HMR) que mira los archivos cada 500 ms:
   **editar `lib/client.js` + refrescar la página basta**. Cambiar `lib/index.mjs` (host) **sí** exige
   reiniciar.
3. ⚠️ **Trampa del vínculo (hard link):** el archivo que se sirve es
   `profiles\web\node_modules\@dsh-external\dsh-sounds\lib\client.js`, **vinculado (hard link)** con
   `plugins\dsh-sounds\lib\client.js` (comprobado con `fsutil hardlink list`). Un guardado que
   **reemplace** el archivo puede romper el vínculo y dejar la copia servida vieja: después de
   regenerar, comprobar el vínculo o copiar el archivo a las dos rutas.
4. ⚠️ **El generador de `client.js` tiene dos trampas ya resueltas** (por eso se usa el script y no un
   editor de texto): (a) el base64 puede contener `$&`, que `str.replace` lee como **patrón de
   reemplazo** y mete el marcador dentro del audio → se sustituye con `split`/`join`; (b) el marcador
   `__AUDIO_MAP__` **también aparece en un comentario** del encabezado → se sustituye **solo la última**
   aparición. Y las claves del mapa van **entre comillas** (`'piuw.mp3'`), porque el nombre del archivo
   lleva punto y como clave suelta no es JavaScript válido.
5. 🔴 **La trampa que más tiempo costó:** la fila del catálogo busca su audio **por el nombre del
   archivo** (`AUDIO[row.file]`). Si el generador escribe las claves con otro formato
   (`piuw_mp3` en vez de `piuw.mp3`) **el plugin no suena nunca y no da ningún error**. Si algún día
   «no suena nada», **lo primero que se mira es eso**.
6. 🔌 **Si el árbol se rompe**, el arnés no arranca. Remedio en 10 segundos: quitar el nombre del
   plugin de `dsh.profile.bundles` en `C:\Users\Usuario\.dsh\profiles\web\package.json`
   (hay copia previa: `package.json.bak-dsh-sounds`).

---

## 5) LA RECETA: REGENERAR `client.js` (tras cambiar la plantilla o los MP3)

```text
cd D:\RELAX
python __gen_client_sounds.py            ← simulacro: dice qué haría, no escribe nada
python __gen_client_sounds.py go         ← escribe C:\Users\Usuario\.dsh\plugins\dsh-sounds\lib\client.js
node --check "C:\Users\Usuario\.dsh\plugins\dsh-sounds\lib\client.js"
```

- `__gen_client_sounds.py` (en `D:\RELAX`) es el **único generador**: lee los 6 MP3 de `assets\`, los
  pasa a base64 y sustituye el marcador en `client.template.js`.
- Después del `go`: **refrescar la página** (el bundle se sirve fresco) y comprobar el **vínculo** (§4.3).
- **Para instalar el plugin en el perfil** (solo la primera vez o si se reinstala):

```text
node "C:\Users\Usuario\AppData\Roaming\npm\node_modules\@deepseek-ai\dsh\lib\bin.js" plugin --profile web add "file:C:/Users/Usuario/.dsh/plugins/dsh-sounds"
```

  Ese comando hace `pnpm add` en el perfil **y** mete el nombre en `dsh.profile.bundles` solo (lo
  reconcilia el propio arnés). Después: **reiniciar el arnés**.

---

## 6) CÓMO SE PROBÓ (20 de 20) — la prueba existe y se vuelve a correr

`python __prueba_sonidos.py` (en `D:\RELAX`). Carga el **bundle real** en un navegador simulado y
comprueba las 20 situaciones: que **no suene al abrir**, que cada aviso suene **en su momento**, que el
sondeo **no duplique**, que la campana **no suene** cuando hay pregunta pendiente / cola / error / foco,
que **dos avisos distintos sí suenen los dos**, que el **interruptor general** deje todo en silencio y
que **apagar una fila** no apague las demás.

> Si se toca la lógica de `client.template.js`, **se corre esta prueba antes de dar nada por bueno**
> (ya encontró 3 fallos reales que a ojo no se ven: la campana sonando junto al error, el corte de
> internet repetido y el aviso de arranque repetido).

---

## 7) EL ARNÉS POR DENTRO (lo que se aprendió y sirve para cualquier plugin)

- **Qué es:** el arnés es la aplicación del agente (`@deepseek-ai/dsh`, npx + `~/.dsh`). El perfil web
  vive en **`C:\Users\Usuario\.dsh\profiles\web\`**; su árbol de plugins se arma con
  `dsh.profile.bundles` (paquete.json) **más** el `cordis.patch.yml` de cada bundle.
- **Dónde está el estado:** sesiones, ajustes y credenciales en **`C:\Users\Usuario\.dsh\`**
  (`sessions\`, `settings.yaml`, `.credentials.yaml`, `plugins\`, `profiles\`). Por eso **reiniciar es
  seguro**: el historial de la conversación es *append-only* en `sessions\--<carpeta>--\<id>\session.jsonl.zstd`.
- **Cómo se sirve el navegador de un plugin:** el host lo publica en **`/plugins/<paquete>/client.js`**
  leyéndolo del disco en cada petición (sin caché), y el bundle de cliente se escribe en el formato
  **CJS del cargador**: `window.__ModuleLoader__.load({ id, factory })`, con el plugin exportando
  `{ name, inject, apply(ctx) }` y React vía `require('react')`.
- **Cómo se lee el estado desde el navegador:** `ctx.get('sessions')` da el servicio de sesiones;
  `sessions.list` (con `.subscribe()` y `.getSnapshot()`) trae las filas y **cuál está en pantalla**
  (`current`), y `sessions.binding(id).session.getSnapshot()` trae la conversación de esa sesión con
  **`running`**, **`pending`** (preguntas y aprobaciones esperando), **`queue`** (mensajes en cola) y
  **`lastAgentError`** — que es todo lo que el plugin necesita para saber cuándo avisar.
- **Otros plugins instalados hoy** (para no pisarlos): `@dsh-external/dsh-usage` (panel de consumo) y
  `@caob23/dsh-browser-control` (el puente con la extensión del navegador).
- 📖 Guía de despliegue y entorno del **sitio** (FTP, hosting, PowerShell): `GUIA_DESPLIEGUE_Y_ENTORNO.md`.
