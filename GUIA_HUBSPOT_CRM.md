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


# GUÍA — HUBSPOT CRM: INTEGRACIÓN, PROPIEDADES Y CARGA MASIVA · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** antes de tocar cualquier cosa de HubSpot (motor, enganches, permisos, propiedades o cargas masivas).
>
> 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni renombra nada a mano, así que **no se hace respaldo del archivo vivo** y **no se compara el local con el hosting** (ni md5, ni tamaños). Despliegue = `php -l` → subir solo lo modificado → verificar por HTTP. Las menciones a md5/respaldos que queden en este archivo son **históricas** y no son pasos del flujo.
>
> **Archivos que toca:** `D:\RELAX\deploy\config_hubspot.php`, `D:\RELAX\deploy\includes\helpers_hubspot.php`, `D:\RELAX\deploy\hubspot_instalar.php` (instalador, NO subido) y los enganches `registrar_negocio.php`, `guardar_asistente.php`, `caminante/subir.php`, `includes/helpers.php`, `api/lead.php`.
> **Estado:** EN PRODUCCIÓN (el sitio sincroniza contactos, suma `lead_score` y la base ya está cargada como empresas).

---

## 0) LO ESENCIAL EN 30 SEGUNDOS

- La integración **funciona en producción**: el sitio sincroniza dueños de negocios con HubSpot, suma `lead_score` cuando alguien pide precio y avisa al jefe por Telegram al cruzar 3 pedidos.
- La base completa (**1.532 tiendas**) está cargada en el CRM como **empresas**, más **5 contactos**. Carga hecha el **2026-09-10**, idempotente y verificada.
- Portal (Hub ID): **`52005472`**. Token: app privada `pat-na1-261d0f32-…-d69c92a963f7`, el valor completo vive en `D:\RELAX\deploy\config_hubspot.php`.
- El motor es **`includes/helpers_hubspot.php`**. Los enganches son bloques `try/catch` de ~6 líneas que llaman a funciones ya probadas: **nunca pueden romper la web**.
- Las herramientas manuales (instalador, cargador, diagnóstico) **no están en el servidor**: se borraron a propósito porque llevan claves dentro.
- Dato crítico: las "casi 1.500 tiendas" **no son clientes registrados**, son fichas de un **directorio geográfico** cargadas el 31/08. Ver sección 9.

| Qué | Estado |
|---|---|
| Token de HubSpot | ✅ Funciona (lee y escribe contactos y empresas) |
| Motor de sincronización | ✅ En producción, probado de punta a punta |
| Enganches en el sitio | ✅ Activos (registro de negocio y clic en "pedir precio") |
| Carga masiva de la base | ✅ 1.532 empresas + 5 contactos |
| Scripts temporales en el servidor | ✅ Borrados (no queda ninguno) |

---

## 1) PORTAL, TOKEN Y DÓNDE ESTÁN LAS CREDENCIALES

| Dato | Valor |
|---|---|
| **Portal (Hub ID)** | `52005472` |
| **Token (private app / service key)** | `pat-na1-261d0f32-…-d69c92a963f7` — el completo está en `D:\RELAX\deploy\config_hubspot.php` |
| URL de la API | `https://api.hubapi.com` |
| Objeto Empresas (objectTypeId) | `0-2` |
| Objeto Contactos (objectTypeId) | `0-1` |
| Credenciales FTP | `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` → host `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp` |

**Reglas del token:**

- **NUNCA regenerarlo** sin avisar: `config_hubspot.php` guarda el token en texto plano y el sitio lo usa. Si se regenera, hay que actualizar ese archivo y volver a subirlo.
- Al **añadir scopes** el token sigue siendo válido: no hay que regenerar nada.
- El token **NO se expone al frontend**: solo lo usa PHP en el servidor.
- `config_hubspot.php` no es accesible por web (devuelve **0 bytes** porque PHP lo ejecuta). Verificado.

---

## 2) ARCHIVOS DEL SITIO: EL MOTOR Y LOS ENGANCHES QUE DISPARAN CADA ENVÍO

### En producción (`D:\RELAX\deploy`)

| Archivo | Qué hace |
|---|---|
| `config_hubspot.php` | Guarda el token y define `hubspot_configurado()`. Lo carga el helper. |
| `includes/helpers_hubspot.php` | **El motor completo.** Toda la lógica de HubSpot. |
| `registrar_negocio.php` | **Enganche:** al registrar un negocio → sincroniza el contacto del dueño. |
| `guardar_asistente.php` | **Enganche:** negocio creado con el asistente → sincroniza, y lo hace **fuera de la transacción** (si HubSpot falla, el negocio ya quedó guardado). |
| `caminante/subir.php` | **Enganche:** tienda capturada con dueño → sincroniza (solo si la tienda nació con dueño). |
| `includes/helpers.php` | **Enganche** dentro de `caminante_autoreclamar()`: al reclamar una tienda del Caminante se sincroniza el contacto. |
| `api/lead.php` | **Enganche:** clic en "pedir precio" → **+1 al `lead_score`**. El trabajo va **después** de `fastcgi_finish_request()`, así el visitante no espera nada. |

Los enganches son bloques `try/catch` de ~6 líneas que llaman a funciones ya probadas. **Nunca** pueden romper la web: si HubSpot falla, el sitio sigue igual.

### Herramientas manuales (a propósito NO están en el servidor)

| Archivo | Para qué |
|---|---|
| `D:\RELAX\deploy\hubspot_instalar.php` | Crea las propiedades personalizadas y prueba la integración. **No está subido.** Clave: `PON_AQUI_LA_CLAVE_INTERNA` |
| `D:\RELAX\backup\herramientas_hubspot_2026-09-10\__hubspot_cargar.php` | Cargador masivo por lotes (upsert). Clave: `PON_AQUI_LA_CLAVE_INTERNA` |
| `D:\RELAX\backup\herramientas_hubspot_2026-09-10\__hubspot_diag.php` | Radiografía de la base. Clave: `PON_AQUI_LA_CLAVE_INTERNA` |
| `D:\RELAX\backup\herramientas_hubspot_2026-09-10\__vigilar_y_cargar.ps1` | Vigilante que espera el permiso y carga solo. |
| `D:\RELAX\backup\herramientas_hubspot_2026-09-10\__carga_hubspot.log` | Log de la carga del 2026-09-10. |

⚠️ Las herramientas se borraron del servidor porque **llevan claves dentro**. Si hay que usarlas: se suben, se usan y **se borran otra vez**. Nunca dejarlas en `deploy` (el script viejo `verificar_y_subir.py` sube TODO el deploy de golpe).

---

## 3) FUNCIONES DISPONIBLES EN `includes/helpers_hubspot.php`

| Función | Para qué |
|---|---|
| `hubspot_sincronizar($email,$negocio,$wsp,$score,$plan,$distrito,$rubro)` | Crea o actualiza el contacto del dueño. |
| `hubspot_sumar_clic_solicitar($email)` | **+1 al lead score. Solo el score.** |
| `hubspot_actualizar_plan($email,$plan)` | `Gratis` / `Premium` / `Vencido`. |
| `hubspot_sync_negocio_nuevo($negocio_id)` | Enganche 1: usa los datos reales de la BD. |
| `hubspot_sync_clic_precio($negocio_id)` | Enganche 2 + aviso de lead caliente. |
| `hubspot_datos_negocio($negocio_id)` | Negocio + email y plan del dueño (1 consulta). |
| `hubspot_contacto_por_email($email)` | Busca con `?idProperty=email`. |
| `hubspot_escribir($metodo,$ruta,$props)` | Escritura a prueba de propiedades inexistentes. |
| `hubspot_api($metodo,$ruta,$cuerpo)` | Llamada cruda; errores traducidos al español. |
| `hubspot_verificar_lead_caliente($email,$score,$negocio)` | Avisa al jefe por Telegram. |
| `hubspot_circuito_abierto()` / `hubspot_circuito_marcar()` | Cortacircuitos de permisos. |

---

## 4) LAS CINCO REGLAS DE DISEÑO QUE NO SE DEBEN ROMPER

1. **Nunca rompe la web.** Todo en `try/catch`. Timeout 5 s, connect timeout 3 s. Si HubSpot se cae, el sitio funciona igual.
2. **Nunca degrada a nadie.** Sumar un clic toca **solo** `lead_score`. El plan solo lo cambia `hubspot_actualizar_plan()` — es el **único** sitio del código que escribe `estado_plan`.
3. **Idempotente de verdad.** Busca por email (`idProperty=email`): si existe `PATCH`, si no `POST`. Si hay carrera y devuelve **409**, reintenta como `PATCH`.
4. **A prueba de propiedades inexistentes.** Si HubSpot rechaza una propiedad (400 *"Property X does not exist"*), se marca, se omite y se reintenta con el resto. **Un lead no se pierde porque falte `rubro`.**
5. **Cortacircuitos.** Si el token no tiene permisos (401 / 403 MISSING_SCOPES), se apunta en `logs/hubspot_circuito.txt` y **se deja de llamar a HubSpot 10 minutos**. Así la web no pierde tiempo por un token mal configurado. El instalador borra la marca al entrar.

---

## 5) PERMISOS (SCOPES): LOS QUE HAY, LOS QUE NO Y LA LECCIÓN APRENDIDA

**La verdad la dice la API, no la pantalla de HubSpot.** Cómo comprobarlo: sección 10.

### Activos y funcionando

```text
crm.objects.contacts.read          crm.objects.contacts.write
crm.schemas.contacts.read          crm.schemas.contacts.write
crm.objects.companies.read         crm.objects.companies.write
crm.schemas.companies.read         crm.schemas.companies.write
crm.objects.companies.highly_sensitive.read
```

### NO los tiene (y hoy no hacen falta)

```text
deals (negocios)  ·  tickets  ·  products
```

### Lección aprendida (importante)

El asistente de IA de HubSpot dijo en dos ocasiones que los scopes estaban puestos cuando **no lo estaban**. La prueba objetiva siempre fue la API:

```text
05:10 UTC -> 403 MISSING_SCOPES (evidencia cruda guardada)
05:31 UTC -> HTTP 200 (contactos)
06:05 UTC -> HTTP 200 (empresas, tras activar la casilla de ESCRITURA)
```

El bloqueo de contactos ya está **resuelto** (los 4 scopes `crm.objects.contacts.*` y `crm.schemas.contacts.*` se activaron). El bloqueo de empresas también: faltaba **exactamente la casilla de ESCRITURA de Empresas** (`crm.objects.companies.write`) y el jefe la activó a las ~06:05 UTC. El token **no se regeneró**, así que `config_hubspot.php` sigue siendo válido.

**Pista para futuras sesiones:** si lee empresas pero no las escribe, y puede leer el *esquema* de empresas pero no escribirlo, entonces falta exactamente la casilla **"Editar/Escribir"** de Empresas. Ver → ya está; Editar → falta.

---

## 6) PROPIEDADES PERSONALIZADAS CREADAS EN HUBSPOT

### En CONTACTOS

| Propiedad | Tipo | Notas |
|---|---|---|
| `chimbote_id` | string, **única** (`hasUniqueValue`) | Clave `u<id_usuario>`. Permite sincronizar **sin email**. |
| `lead_score` | number | Cuántas veces pidieron precio. |
| `estado_plan` | enumeration | `Gratis` / `Premium` / `Vencido`. |
| `distrito` | string | Distrito del negocio. |
| `rubro` | string | Categoría del negocio. |

### En EMPRESAS

| Propiedad | Tipo | Notas |
|---|---|---|
| `chimbote_negocio_id` | string, **única** (`hasUniqueValue`) | Clave `n<id_negocio>`. Es la clave del upsert. |
| `rubro` | string | Categoría. |
| `chimbote_estado` | string | Estado en el sitio (`activo`, `pendiente`…). |
| `chimbote_lat` | number | Latitud. |
| `chimbote_lng` | number | Longitud. |

⚠️ **`hs_latitude` y `hs_longitude` son de SOLO LECTURA en HubSpot.** No se pueden escribir. Por eso se crearon `chimbote_lat` / `chimbote_lng`.

### Propiedades estándar que se usan

- Contactos: `email`, `firstname`, `lastname`, `phone`, `company`, `website`.
- Empresas: `name`, `phone`, `city`, `address`, `description`.

---

## 7) AVISO DE LEAD CALIENTE POR TELEGRAM

Al cruzar **3 pedidos de precio por primera vez** (no en cada clic posterior) se llama a `notificar_jefe()` y llega un aviso al Telegram del jefe. La condición es `new_score >= 3 && prev_score < 3`; por eso `hubspot_verificar_lead_caliente()` recibe también el `prev_score`.

El `lead_score` es simplemente **cuántas veces pidieron precio** ese negocio/contacto. Solo sube con `hubspot_sumar_clic_solicitar()`.

---

## 8) CARGA MASIVA DEL 2026-09-10: 1.532 EMPRESAS Y 5 CONTACTOS

### Qué se subió y con qué clave

| Objeto | Cantidad | Clave de identidad |
|---|---|---|
| Empresas (tiendas) | **1.532** | `chimbote_negocio_id = n<id>` |
| Contactos (personas) | **5** | `chimbote_id = u<id_usuario>` |

- **Decisión del jefe:** las tiendas van a **EMPRESAS** (no a contactos). Las personas van a CONTACTOS.
- Se subieron en **16 lotes de 100** (el último de 32) usando `batch/upsert`.
- **Idempotente probado:** re-ejecutar el lote 0 dejó el total en 1.532 (no duplicó).

### Criterio del jefe (respetarlo siempre)

> "No solamente voy a trabajar con usuarios con Gmail, también con usuarios que tengan WhatsApp, o que al menos hayan registrado una tienda y aunque todavía no hayan agregado email ni WhatsApp, igual tengo que tener con ellos una fila en el CRM."

Por eso **nunca se filtra por email**: entra todo el que exista, tenga datos o no. Y el jefe **NO trabaja con WhatsApp masivo** (dicho por él): no insistir con ese tema.

### Cómo se resolvió el problema de los contactos sin email

HubSpot deduplica por email, así que sin email se duplicaría en cada corrida. La solución fue una **propiedad única propia** usada como `idProperty` del upsert:

1. `chimbote_id` en Contactos con `hasUniqueValue = true` → clave `u<id_usuario>`.
2. `chimbote_negocio_id` en Empresas con `hasUniqueValue = true` → clave `n<id_negocio>`.
3. La carga usa **`batch/upsert`** por esa clave: idempotente de verdad.

**Probado antes de usarlo:** HubSpot acepta un contacto **sin email y sin teléfono**, y se puede buscar por `?idProperty=chimbote_id`.

### Mapeo aplicado (empresa de ejemplo, leída de HubSpot)

```json
{"name":"Hospital Regional Eleazar Guzmán Barrón","chimbote_negocio_id":"n2",
 "phone":"+51934274553","city":"Nuevo Chimbote","address":"Nuevo Chimbote",
 "rubro":"Clínicas / Salud","chimbote_estado":"activo",
 "chimbote_lat":"-9.1180000","chimbote_lng":"-78.5260000"}
```

### Contactos cargados y contactos excluidos

- Entraron **5**: "la dulzura de patty", "Mistio", "Fiebre", "veterinaria dias" y "administrador de sitio". Todos con **email generado por el sistema** (`cliente13XX@dechimbote.com`), no real.
- **Excluidos:** 2 administradores (incluido `jimmylopez483@gmail.com`), la cuenta interna `admin@dechimbote.com` y el usuario de prueba `notif…@test.com`.
- Cuando un dueño ponga su email real, **el upsert actualiza la misma fila, no crea otra**.

### Advertencia dada al jefe sobre estos datos

De los 1.532, **474 tienen teléfono** sacado de fuentes públicas. Subirlos al CRM como directorio organizado: bien. Usarlos para **WhatsApp masivo**: no — WhatsApp banea números por envíos no solicitados y la Ley 29733 (Perú) exige consentimiento. Camino recomendado: que cada dueño **reclame su tienda** (ahí hay consentimiento y datos frescos) y contactar de a uno.

---

## 9) RADIOGRAFÍA REAL DE LA BASE: POR QUÉ "1.500 TIENDAS" NO SON CLIENTES

El jefe pidió pasar "casi 1500 tiendas de clientes registrados". La medición real (2026-09-10):

| Dato | Valor |
|---|---|
| Tiendas en `directorio_negocios` | **1.532** (todas estado `activo`) |
| Tiendas **con dueño** | **1** |
| Tiendas **sin dueño** | **1.531** (cargadas el 31/08) |
| Usuarios en `directorio_usuarios` | **9** (7 cliente + 2 admin) |
| Productos en `directorio_servicios` | **9.437** |
| Tiendas con WhatsApp | 472 |
| Tiendas con teléfono | 473 |
| Tiendas con teléfono o WhatsApp | **474** |
| Tiendas con rubro | 1.527 |
| Tiendas con distrito | 1.532 |
| Tiendas con coordenadas | 1.474 |

**Conclusión:** no era una cartera de clientes, era un **directorio geográfico de lugares**: "Mercado Modelo de Chimbote", "Hospital Regional Eleazar Guzmán", "MegaPlaza Chimbote", "Plaza de Armas", "Centro Cívico de Nuevo Chimbote". Nadie se registró: no hay persona detrás. Muy valioso como base de prospección, pero no son clientes.

---

## 10) CÓMO VERIFICAR QUE TODO FUNCIONA (COMANDOS Y URLS)

### a) ¿El token tiene permisos?

```powershell
$tok='<TOKEN_COMPLETO_DE_config_hubspot.php>'
$hh=@{Authorization="Bearer $tok"; 'Content-Type'='application/json'}
Invoke-RestMethod -Method Post -Uri 'https://api.hubapi.com/crm/v3/objects/companies' `
  -Headers $hh -Body (@{properties=@{name='PRUEBA BORRAR'}} | ConvertTo-Json) -TimeoutSec 20
```

Si responde con un `id`, hay permiso (¡y **borrar** esa empresa de prueba!).

### b) ¿Cuántas tiendas nuestras hay en HubSpot?

```powershell
$b = @{filterGroups=@(@{filters=@(@{propertyName='chimbote_negocio_id'; operator='HAS_PROPERTY'})}); limit=1} | ConvertTo-Json -Depth 6
(Invoke-RestMethod -Method Post -Uri 'https://api.hubapi.com/crm/v3/objects/companies/search' -Headers $hh -Body $b).total
```

Debe dar **1532**. Lo mismo con `chimbote_id` en `/contacts/search` → **5**.

### c) Verlo con los ojos en la interfaz de HubSpot

```text
Empresas:  https://app.hubspot.com/contacts/52005472/objects/0-2/views/all/list
Contactos: https://app.hubspot.com/contacts/52005472/objects/0-1/views/all/list
```

### d) Verificación por HTTP del sitio

| URL | Esperado |
|---|---|
| `https://dechimbote.com/index.php` | 200 |
| `https://dechimbote.com/registrar_negocio.php` | 302 (login) |
| `https://dechimbote.com/guardar_asistente.php` | 401 (pide sesión) |
| `https://dechimbote.com/caminante/subir.php` | 405 |
| `https://dechimbote.com/api/lead.php?n=0` | 302 |
| `https://dechimbote.com/includes/helpers_hubspot.php` | 403 (bloqueado) |
| `https://dechimbote.com/config_hubspot.php` | 200 con **0 bytes** (no filtra el token) |

Sin errores 500. El token **NO se filtra**: `config_hubspot.php` se ejecuta (solo define constantes) y devuelve cuerpo vacío. `hubspot_instalar.php` ya está borrado del servidor (**HTTP 404**).

⚠️ **No llamar a `api/lead.php?n=<id real>`** para probar: dispara un aviso real al Telegram del jefe y suma un clic al score. Usar `n=0`.

---

## 11) CÓMO ENTRAR TIENDAS NUEVAS AL CRM: AUTOMÁTICO Y MANUAL

### Automático (ya funciona, sin hacer nada)

- Un dueño registra un negocio → el enganche sincroniza el contacto solo.
- Alguien pide precio en una ficha → +1 al `lead_score` (y a los 3, aviso a Telegram).
- Una tienda del Caminante se reclama → se sincroniza el contacto.

### Manual (carga masiva, solo si se agregan muchas tiendas de golpe)

1. Subir `__hubspot_cargar.php` desde `D:\RELAX\backup\herramientas_hubspot_2026-09-10\`.
2. `?key=PON_AQUI_LA_CLAVE_INTERNA&dry=1` → **simular siempre primero** y revisar el mapeo.
3. `?key=…&props=1` → crea las propiedades que falten.
4. `?key=…&objeto=empresas&desde=0`, luego `desde=100`, `200`… hasta que `enviados < 100`.
5. `?key=…&objeto=contactos&desde=0`.
6. **Borrar el archivo del servidor** al terminar.
7. Verificar con las búsquedas de la sección 10.

El cargador es **idempotente**: se puede repetir sin duplicar.

---

## 12) ERRORES YA COMETIDOS QUE NO SE DEBEN REPETIR

| Error | Qué pasó | Solución aplicada |
|---|---|---|
| `hs_latitude` / `hs_longitude` en el payload | **El lote entero falló** (son de solo lectura) | Usar `chimbote_lat` / `chimbote_lng` |
| Consultar `directorio_usuarios.plan` | **La columna NO existe** (verificar): la consulta reventaba y el `try/catch` lo escondía → **el enganche estaba muerto en producción sin dar error** | `hubspot_datos_negocio()` reintenta sin esa columna; verificado en vivo |
| `description` con HTML crudo | Las descripciones del directorio traen `<h3>`, `<p>`… | `limpiar_descripcion()`: texto plano |
| Contactos basura | Entraban el admin y un usuario de prueba | `email_basura()` + excluir tipo `admin` |
| Buscar contacto en `/contacts/{email}` | HubSpot v3 busca por **ID interno**, no por email | Añadir `?idProperty=email` |
| Actualizar con `POST` | En v3 se actualiza con **`PATCH` por ID** | `PATCH /contacts/{id}` |
| Creer que `POST` es idempotente | Devuelve **409** si el contacto ya existe | Buscar primero; usar upsert |
| Sumar clic y enviar `estado_plan` | **Degradaba a Premium** a los Gratis en cada clic | Sumar clic toca **solo** `lead_score` |
| Creer que el CRM estaba vacío | El instalador imprimía "0 contactos" leyendo `data.total`, que la API no siempre devuelve | Quitado ese mensaje |
| Creer que "1500 tiendas = 1500 clientes" | Son fichas de un directorio sin dueño | Ver sección 9 |
| Creer que faltaban permisos de empresas | El formato de los JSON en PowerShell hacía fallar la llamada | Era un fallo de *escaping*, no de HubSpot: usar `Invoke-RestMethod` + `ConvertTo-Json` |

---

## 13) TRAMPAS DEL ENTORNO (POWERSHELL, FTP Y PHP)

### PowerShell

- **La ejecución de archivos `.ps1` está DESHABILITADA** en la máquina del jefe. `& script.ps1` falla con *"la ejecución de scripts está deshabilitada"*. ✅ Solución: `Invoke-Expression (Get-Content 'ruta.ps1' -Raw)`.
- **`pwsh` no está en el PATH** de los procesos hijos. No sirve `pwsh -File …`.
- **`curl.exe -d '{"json":"…"}'` con comillas simples rompe el JSON** en PowerShell (error *"Invalid input JSON"*). ✅ Usar `Invoke-RestMethod` con `ConvertTo-Json`.

### FTP

- Host correcto: **`ftp.dechimbote.com`** (cuenta `u196269909.dechimboteftp`, del 2026-09-15; la IP `82.25.67.35` es la misma máquina). Datos en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json`.
- **La raíz viva es `/`**: el FTP **entra** en `/public_html`, que es una **copia vieja anidada** (subir ahí no cambia la web). Los scripts hacen `cwd('/')` + comprobación de la marca `assets/css/carrito.css` (`GUIA_DESPLIEGUE_Y_ENTORNO.md` §11.1).
- **`SIZE` falla con "not allowed in ASCII mode"**: hay que hacer `ftp.voidcmd('TYPE I')` antes de consultar tamaños. Ese error **no** significa que el archivo no exista.
- `D:\RELAX\verificar_y_subir.py` **NO USARLO**: sube TODO el deploy de golpe y lleva la contraseña escrita dentro.
- **Despliegue (3 pasos, 🔓 regla del 2026-09-12):** `php -l` → subir **solo lo modificado**
  (`python D:\RELAX\__subir_uno.py <ruta relativa>`, un archivo por vez) → **verificar por HTTP** (200 o 302,
  nunca 500). **Sin respaldo del archivo vivo y sin comparar local ↔ hosting** (ni md5, ni tamaños): el jefe
  nunca toca el hosting, así que `D:\RELAX\deploy` **es la verdad**.

### PHP / servidor

- PHP **8.3.33** en producción (el XAMPP local es 8.2.12): probar sintaxis en local **no** garantiza el comportamiento en el hosting.
- La **base de datos es `localhost`**: no se llega desde fuera. Todo lo que toque la BD tiene que ser un script **en el servidor**.
- `display_errors` está en **`0`**: un error fatal **no se ve**. Envolver todo en `try/catch` y devolver mensajes propios.
- El MySQL va en **UTC**; el sitio usa `America/Lima`.
- La tabla de usuarios es `directorio_usuarios` y el dueño se enlaza por `directorio_negocios.dueno_id`. Los productos viven en `directorio_servicios` (**no existe** `directorio_productos`).

---

## 14) HERRAMIENTAS MANUALES Y RESPALDOS (los respaldos son 🗄️ HISTÓRICOS)

- **Respaldos previos al cambio 🗄️ HISTÓRICOS (no se usan):** `D:\RELAX\backup\antes_hubspot_2026-09-10` (los archivos vivos tal como estaban antes de HubSpot, guardados en aquella sesión: hoy **no** se respalda el archivo vivo): `registrar_negocio.php` 12.948 B, `guardar_asistente.php` 7.784 B, `includes/helpers.php` 52.947 B, `api/lead.php` 1.647 B, `caminante/subir.php` 9.169 B. En esa carpeta **no hay respaldo de `config_hubspot.php` ni de `helpers_hubspot.php`** (son nuevos).
- **Copias de las herramientas manuales** (fuera del deploy, porque llevan claves): `D:\RELAX\backup\herramientas_hubspot_2026-09-10\` con `__hubspot_cargar.php`, `__hubspot_diag.php`, `__vigilar_y_cargar.ps1` y `__carga_hubspot.log`.
- **`hubspot_instalar.php`** está borrado del servidor, pero la copia local sigue en `D:\RELAX\deploy\hubspot_instalar.php` por si hay que re-probar: se resube en segundos.
- **Total de archivos tocados en el sitio:** 8 (`config_hubspot.php`, `includes/helpers_hubspot.php`, `includes/helpers.php`, `registrar_negocio.php`, `guardar_asistente.php`, `caminante/subir.php`, `api/lead.php` y `hubspot_instalar.php`, este último ya fuera del servidor).
- Al desplegar **se comprobó entonces** md5 local = md5 servidor en los archivos subidos (y antes de pisar nada se comparó tamaño y md5 con lo que había en el servidor, que tenía archivos con fecha de otra sesión, ~10 minutos antes) **(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA: no se respalda el vivo ni se compara local ↔ hosting)**.

---

## 15) ROLLBACK: CÓMO DEJAR EL SITIO COMO ANTES DE HUBSPOT

> 🗄️ **Nota (regla del 2026-09-12):** `D:\RELAX\backup\antes_hubspot_2026-09-10` es **archivo histórico** y
> **no** es parte del flujo de despliegue: solo se usa si de verdad hay que revertir a mano. Para subir
> cualquier archivo: `python D:\RELAX\__subir_uno.py <ruta relativa>` (un archivo por vez, sin respaldar el vivo).

1. Volver a subir los 5 archivos de `D:\RELAX\backup\antes_hubspot_2026-09-10` (`registrar_negocio.php`, `guardar_asistente.php`, `includes/helpers.php`, `api/lead.php`, `caminante/subir.php`).
2. Borrar del servidor `config_hubspot.php`, `includes/helpers_hubspot.php` y `hubspot_instalar.php`.
3. El sitio queda igual que antes: los enganches están aislados y nada más llama a HubSpot.

---

## 16) ANTECEDENTE: LA GUÍA DE HUBSPOT QUE ENTREGÓ QWEN NO APLICABA

El jefe trajo una guía de HubSpot hecha por Qwen, partida en 10 archivos. Proponía tocar archivos que no son los que crean negocios (`registro.php`, `crear_negocio.php`, `productos.php`), creía que había que inventar el botón "Solicitar precio" y el motor (ambos ya existían, aunque nadie los llamaba), y traía 4 errores graves: buscar el contacto por `/contacts/{email}` sin `?idProperty=email`, actualizar con `POST` en vez de `PATCH` por ID, decir que era idempotente cuando `POST` devuelve 409, y **forzar `estado_plan='Gratis'` en cada clic** (degradaba a Premium). Se descartó y se reescribió el motor desde cero contra los archivos reales del hosting compartido.

---

## 17) PENDIENTES

1. **Asociar contacto ↔ empresa** en HubSpot (los 5 contactos aún no están asociados a su tienda). Cobra sentido cuando los dueños empiecen a reclamar sus fichas.
2. **Prueba con tráfico real:** que una persona registre un negocio (o pida precio en una ficha cuyo dueño tenga email) y ver en HubSpot que el contacto aparece y el score sube. El motor está probado al 100% y los enganches están vivos, pero ese tramo aún no se ha ejercitado con datos reales.
3. **Descripciones de las 1.532 fichas:** son **plantillas generadas** ("somos tu mejor alternativa", todas con la misma estructura). Si algún día se quieren usar para marketing, hay que reescribirlas.

**Ya resuelto (no volver a abrirlo):** permisos de contactos (activados el 2026-09-10, `GET /contacts` → 200); permisos de escritura de empresas (activados ~06:05 UTC, tras detectar que faltaba la casilla de escritura); instalador corrido (las 4 propiedades de contactos ya existían); prueba de punta a punta superada contra HubSpot real (contacto ID `247555284400`, `lead_score` 0→1→2 y el plan **siguió** en Premium); carga masiva completada (1.532 + 5, idempotente); scripts temporales borrados del servidor (404); contacto de prueba `test@dechimbote.com` borrado del CRM (**verificar**: la crónica del día lo dejaba a propósito para que el jefe lo viera, la guía de configuración posterior dice que ya se borró; si aparece otro contacto de prueba, borrarlo).

---

---

> **Guías base del proyecto:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (qué es el sitio, mapa de archivos,
> estado real de cada módulo y qué hacer después) · `REGLAS_DE_ORO_PROYECTO.md` (reglas del jefe,
> canónicas) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar sin romper el sitio).


_Última revisión: 2026-09-10 (consolidación de las guías de HubSpot)._
