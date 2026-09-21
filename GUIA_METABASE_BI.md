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


# GUÍA — METABASE Y BUSINESS INTELLIGENCE · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** antes de tocar Metabase, antes de pedirle algo al jefe por hPanel, o cuando haya que crear/ajustar un dashboard con SQL sobre la base real.
> **Archivos que toca:** `D:\RELAX\metabase\` (`ABRIR_METABASE.cmd`, `metabase.jar`, `jre21\`, `data\`). No toca el código del sitio.
> **Estado:** PARCIAL — Metabase está instalado y encendido en la PC del jefe; **falta la conexión a la base de producción**, que solo puede habilitarse desde hPanel.

## 0) LO ESENCIAL EN 30 SEGUNDOS

1. **Metabase corre en la PC, no en el hosting.** Se enciende con doble clic en `D:\RELAX\metabase\ABRIR_METABASE.cmd` y se abre en `http://localhost:3000` (versión **v0.53.4.1**, health `{"status":"ok"}`).
2. **No hace falta Docker ni instalar Java.** Se usa `metabase.jar` + un **JRE 21 portátil** dentro de la misma carpeta.
3. **El usuario MySQL de solo lectura NO se puede crear por SQL.** El usuario del sitio solo manda dentro de su propia base. Se crea desde **hPanel → Bases de datos → ⋮ → Cambiar permisos** (dejar solo **SELECT**).
4. **Lo que falta lo hace el jefe y nadie más:** crear el usuario `u196269909_metabase`, asignarlo a `u196269909_CHIMBOTEALDIA`, dejarle solo SELECT y abrir **Remote MySQL** para su IP pública.
5. **Las consultas de la guía original fallarían tal cual**: usan tablas y columnas que no existen en producción. Abajo están las **corregidas y completas** (§8), que son el material más valioso de esta guía.
6. **Nunca** dar permisos de escritura al usuario de Metabase y **nunca** exponer el puerto 3000 a internet.

## 1) ESTADO ACTUAL DE METABASE EN LA PC

- Instalado y encendido: health `{"status":"ok"}`, versión **v0.53.4.1**.
- Se abre en `http://localhost:3000` (solo local).
- **Pendiente:** la conexión a la base de producción, que depende del usuario MySQL creado en hPanel (§4).
- Los dashboards y las cuentas del panel se guardan en la base interna H2, en la carpeta `data\`.
- El `.cmd` busca solo el Java portátil (21 primero), fija `MB_DB_FILE` en `data\`, puerto **3000** y zona horaria **America/Lima**. **No requiere permisos de administrador.**
- Uso: doble clic → esperar el mensaje `Metabase Initialization COMPLETE` → abrir `http://localhost:3000`.
- Apagar: cerrar la ventana (Ctrl+C).
- Si algún día se quiere usar Postgres/MySQL propio para el panel, se cambia con `MB_DB_TYPE`.

## 2) EL USUARIO DE SOLO LECTURA: POR QUÉ NO SE PUEDE CREAR POR SQL

Comprobado en el servidor ejecutando sentencias como el usuario del sitio (`u196269909_ALDIACHIMBOTE@localhost`):

```
SHOW GRANTS FOR CURRENT_USER()
  GRANT USAGE ON *.* ... WITH MAX_STATEMENT_TIME 120
  GRANT ALL PRIVILEGES ON `u196269909_CHIMBOTEALDIA`.* TO `u196269909_ALDIACHIMBOTE`@`localhost`

CREATE USER 'u196269909_metabase'@'%' ...   → 1227 Access denied (falta CREATE USER)
GRANT SELECT ON ...directorio_negocios ...  → 1142 GRANT command denied
FLUSH PRIVILEGES                            → 1227 Access denied (falta RELOAD)
SHOW GRANTS FOR 'u196269909_metabase'@'%'   → 1044 Access denied to database 'mysql'
```

**Motivo:** el usuario del sitio solo tiene permisos **dentro de su base**. Crear usuarios y repartir permisos es competencia del panel (hPanel) o de soporte. Por eso **el plan B (ticket) no es necesario** mientras hPanel muestre *Cambiar permisos*.

Además, el acceso remoto está cerrado y ese usuario es `@localhost`: una conexión remota con él responde `(1045, "Access denied for user 'u196269909_ALDIACHIMBOTE'@'190.108.93.134'")`.

## 3) POR QUÉ NO HACEN FALTA DOCKER NI JAVA INSTALADO

| Guía original | Realidad medida en la PC | Solución aplicada |
|---|---|---|
| `docker run … metabase/metabase` | **Docker NO está instalado** | Se usa `metabase.jar` (no necesita Docker) |
| (no lo menciona) | **Java NO está instalado** | JRE portátil dentro de `D:\RELAX\metabase\jre21` |
| (no lo menciona) | El JRE 17 que se bajó antes **no sirve** | Metabase 0.53 usa `java.util.SequencedCollection` → **exige Java 21** |

Archivos de `D:\RELAX\metabase\`:

| Archivo | Qué es |
|---|---|
| `metabase.jar` (467 MB) | El programa (v0.53.4.1) |
| `jre21\jdk-21.0.12.1+1-jre\` | Java 21 portátil (Temurin) — el que se usa |
| `jre\jdk-17.0.20.1+1-jre\` | Java 17 (inservible para esta versión; se puede borrar) |
| `data\metabase.db*` | La base interna del panel (cuentas, dashboards, consultas) |
| `ABRIR_METABASE.cmd` | Doble clic para encenderlo |

## 4) DATOS DE LA CONEXIÓN (formulario de Metabase)

| Campo | Valor |
|---|---|
| Database type | **MySQL** |
| Display name | `DeChimbote.com Producción` |
| Host | `82.25.67.35` (o `dechimbote.com`) |
| Port | `3306` |
| Database name | `u196269909_CHIMBOTEALDIA` |
| Username | `u196269909_metabase` |
| Password | **la que el jefe definió en hPanel.** No se guarda en ningún archivo de este proyecto y no debe escribirse en esta guía |
| SSL | **dejar apagado** la primera vez (el MySQL compartido de Hostinger no siempre acepta SSL en remoto). Si pide SSL, activarlo y añadir `useSSL=true&requireSSL=true` |

Ajustes recomendados en Metabase (**Admin → Databases → Sync**): sincronización **diaria** (no cada hora, para no cargar el hosting compartido) y **desactivar** el escaneo de valores de campo (field values). La base es pequeña: no hay tablas de millones de filas.

## 5) LA BASE REAL: LO QUE EXISTE Y LO QUE LA GUÍA ORIGINAL INVENTABA

Las consultas de la guía original **fallarían tal cual**, porque usan columnas y tablas que no existen.

| La guía original dice | Realidad en `u196269909_CHIMBOTEALDIA` |
|---|---|
| `n.es_premium` (0/1 o 'Gratis') | **No existe.** El plan viviría en `directorio_usuarios.plan`, pero **esa columna tampoco existe aún** (migración de Tablones pendiente). Lo único real hoy: `directorio_negocio_pagos` → `directorio_pagos` (**0 filas**) y `directorio_negocios.destacado` |
| `n.activo = 1` | **No existe** `activo`: es `estado ENUM('pendiente','activo','inactivo','rechazado')` |
| `n.fecha_creacion` | **No existe**: es `creado_en` (DATETIME, en **UTC**) |
| `u.whatsapp` (dueño) | **No existe** en usuarios (tiene `telefono`). El WhatsApp está en **`directorio_negocios.whatsapp`** |
| `directorio_stats_eventos` / `directorio_stats_sesiones` | ✅ **SÍ EXISTEN**: el módulo de Estadísticas está desplegado y midiendo (comprobado en vivo el 2026-09-10, ver `GUIA_ESTADISTICAS_DEL_SITIO.md`). Ojo al escribir consultas: su `tipo` es `pv`/`salida` (no `solicitar_precio`) y **no tiene `negocio_id`** |
| `e.tipo = 'solicitar_precio'` | El lead real está en **`directorio_avisos_log`** con `tipo = 'lead_precio'` (sistema de avisos, ya en producción) |
| `directorio_producto_fotos` | Existe y su clave es **`producto_id`** (apunta a `directorio_servicios`, no a un negocio). *(verificar el número de filas: un corte decía 0, pero la galería de productos está en producción y escribe ahí — probablemente el 0 era de un corte anterior)* |
| `directorio_distritos` | ✅ Existe (`id`, `nombre`, `slug`, `visible`) |
| `… * 10 AS ingresos_proyectados_soles` | Hoy daría **0**: no hay ninguna fila de pago registrada |

Tablas y vistas útiles que **sí** existen y la guía original no usaba: `vista_negocios_populares`, `vista_negocio_ficha_completa` (41 columnas con categoría, distrito, zona, `total_productos`, `total_fotos`, `total_opiniones`), `directorio_avisos_log`, `directorio_banner_stats`.

## 6) VOLUMEN REAL DE DATOS MEDIDO EN PRODUCCIÓN

Medido con `SELECT COUNT(*)` sobre la base viva:

| Tabla | Filas | Comentario |
|---|---|---|
| `directorio_negocios` | **1.532** | todas `activo`; **1.531 sin dueño** (directorio de lugares, no clientes) |
| `directorio_usuarios` | **9** | 7 cliente + 2 admin |
| `directorio_servicios` | **9.437** | los productos |
| `directorio_vistas` | **1.252** | `negocio_id`, `producto_id`, `usuario_id`, `ip`, `fecha` → **la señal de demanda real** |
| `directorio_opiniones` | **2.561** | importadas (no hay formulario en producción) |
| `directorio_negocio_pagos` | **0** | por eso "premium/ingresos" hoy es 0 |
| `directorio_avisos_log` | (activo) | `tipo`, `estado`, `negocio_id`, `usuario_id`, `creado_en` → leads y búsquedas sin resultado |
| `directorio_historial_busqueda` | (activo) | `usuario_id`, `termino`, `categoria_id`, `distrito_id`, `fecha` (solo usuarios logueados) |

## 7) LO QUE SOLO PUEDE HACER EL JEFE EN HPANEL

1. **Crear el usuario MySQL** `u196269909_metabase` (hPanel → Sitios web → dechimbote.com → Administrar → **Bases de datos** → crear usuario).
2. **Asignarlo** a la base `u196269909_CHIMBOTEALDIA`.
3. **⋮ → Cambiar permisos** → dejar **solo SELECT** → Actualizar.
   ⚠️ **NUNCA** tocar los permisos de `u196269909_ALDIACHIMBOTE` (el usuario del sitio): rompería la web.
4. **Remote MySQL** → añadir la IP del jefe → **Create**. IP pública hoy: **190.108.93.134** (si reinicia el router, hay que actualizarla).
5. En Metabase: crear su cuenta de admin y añadir la base con los datos de §4.

*(Solo si hPanel no mostrara "Cambiar permisos": abrir ticket con el texto de §10.)*

## 8) CONSULTAS SQL NATIVAS CORREGIDAS PARA LOS DASHBOARDS

> Todas usan **solo** tablas y columnas que existen hoy. El MySQL del hosting va en **UTC**; para mostrar horas de Perú usar `CONVERT_TZ(x,'UTC','America/Lima')`.

### 8.1 Dashboard 1 — 🔥 Demanda real y a quién contactar (30 días)

```sql
SELECT
    n.nombre                                   AS negocio,
    cat.nombre                                 AS rubro,
    d.nombre                                   AS distrito,
    COALESCE(NULLIF(n.whatsapp,''), n.telefono) AS contacto,
    CASE WHEN n.dueno_id IS NULL THEN 'Sin dueño reclamado' ELSE CONCAT('u', n.dueno_id) END AS dueno,
    u.email                                    AS email_dueno,
    COUNT(DISTINCT v.id)                       AS vistas_30d,
    COUNT(DISTINCT CASE WHEN a.tipo = 'lead_precio' THEN a.id END) AS pidieron_precio,
    COUNT(DISTINCT o.id)                       AS opiniones,
    n.vistas_count                             AS vistas_historicas
FROM directorio_negocios n
LEFT JOIN directorio_categorias  cat ON cat.id = n.categoria_id
LEFT JOIN directorio_distritos   d   ON d.id  = n.distrito_id
LEFT JOIN directorio_usuarios    u   ON u.id  = n.dueno_id
LEFT JOIN directorio_vistas      v   ON v.negocio_id = n.id AND v.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
LEFT JOIN directorio_avisos_log  a   ON a.negocio_id = n.id AND a.tipo = 'lead_precio'
                                    AND a.creado_en >= DATE_SUB(NOW(), INTERVAL 30 DAY)
LEFT JOIN directorio_opiniones   o   ON o.negocio_id = n.id
WHERE n.estado = 'activo'
GROUP BY n.id, cat.nombre, d.nombre, n.whatsapp, n.telefono, n.dueno_id, u.email
HAVING vistas_30d > 0 OR pidieron_precio > 0
ORDER BY pidieron_precio DESC, vistas_30d DESC
LIMIT 50;
```

### 8.2 Dashboard 2 — 📊 Crecimiento y calidad del directorio (mes a mes)

```sql
SELECT
    DATE_FORMAT(n.creado_en, '%Y-%m')                        AS mes,
    COUNT(*)                                                 AS tiendas_creadas,
    SUM(n.estado = 'activo')                                 AS activas,
    SUM(COALESCE(n.telefono,'')  <> '')                      AS con_telefono,
    SUM(COALESCE(n.whatsapp,'')  <> '')                      AS con_whatsapp,
    SUM(n.lat IS NOT NULL AND n.lng IS NOT NULL)             AS con_gps,
    SUM(n.dueno_id IS NOT NULL)                              AS con_dueno,
    SUM(n.destacado = 1)                                     AS destacadas
FROM directorio_negocios n
GROUP BY DATE_FORMAT(n.creado_en, '%Y-%m')
ORDER BY mes DESC
LIMIT 24;
```

### 8.3 Dashboard 2-bis — 💰 Planes y pagos (hoy 0: se llenará solo)

```sql
SELECT
    p.codigo                        AS plan_codigo,
    p.nombre                        AS plan,
    COUNT(np.negocio_id)            AS negocios,
    COUNT(np.negocio_id) * 10       AS soles_proyectados_mes
FROM directorio_pagos p
LEFT JOIN directorio_negocio_pagos np ON np.pago_id = p.id
GROUP BY p.id, p.codigo, p.nombre
ORDER BY negocios DESC;
```

### 8.4 Dashboard 3 — 📍 Mapa por distrito

```sql
SELECT
    COALESCE(d.nombre, 'Sin distrito')                                   AS distrito,
    COUNT(*)                                                             AS tiendas,
    SUM(n.lat IS NOT NULL AND n.lng IS NOT NULL)                         AS con_gps,
    SUM(COALESCE(n.whatsapp,'') <> '')                                   AS con_whatsapp,
    ROUND(AVG(NULLIF(n.rating, 0)), 2)                                   AS rating_medio,
    SUM(n.vistas_count)                                                  AS vistas,
    SUM(EXISTS (SELECT 1 FROM directorio_servicios s
                 WHERE s.negocio_id = n.id AND s.activo = 1))            AS con_productos
FROM directorio_negocios n
LEFT JOIN directorio_distritos d ON d.id = n.distrito_id
WHERE n.estado = 'activo'
GROUP BY COALESCE(d.nombre, 'Sin distrito')
ORDER BY tiendas DESC;
```

### 8.5 Dashboard 4 — 📍 Mapa real con puntos (visualización *Map* de Metabase)

```sql
SELECT n.nombre, n.lat AS latitude, n.lng AS longitude,
       cat.nombre AS rubro, d.nombre AS distrito, n.vistas_count
FROM directorio_negocios n
LEFT JOIN directorio_categorias cat ON cat.id = n.categoria_id
LEFT JOIN directorio_distritos  d   ON d.id  = n.distrito_id
WHERE n.estado = 'activo' AND n.lat IS NOT NULL AND n.lng IS NOT NULL
ORDER BY n.vistas_count DESC
LIMIT 2000;
```

### 8.6 Dashboard 5 — 🕳️ Oportunidades: qué busca la gente y no encuentra

```sql
SELECT a.clave AS termino_buscado, COUNT(*) AS veces, MAX(a.creado_en) AS ultima_vez
FROM directorio_avisos_log a
WHERE a.tipo = 'busqueda_vacia' AND a.es_bot = 0
GROUP BY a.clave
ORDER BY veces DESC
LIMIT 50;
```

## 9) CHECKLIST DE VERIFICACIÓN (cuando ya haya conexión)

- [ ] Metabase arranca solo con doble clic en `ABRIR_METABASE.cmd`.
- [ ] `http://localhost:3000/api/health` responde `{"status":"ok"}`.
- [ ] La conexión dice **"Connection successful"** (si falla: probar sin SSL; revisar la IP en Remote MySQL).
- [ ] Las consultas de §8 corren sin error (si alguna falla, comparar columnas con §5).
- [ ] **Prueba negativa de seguridad:** en el editor SQL de Metabase ejecutar
      `DELETE FROM directorio_negocios LIMIT 1;` → **debe fallar** con
      `command denied to user 'u196269909_metabase'`. Si **no** falla, los permisos están mal: parar y corregir.
- [ ] `SHOW GRANTS FOR 'u196269909_metabase'@'%';` (por soporte o desde Metabase) → solo `SELECT`.
- [ ] La web sigue igual: `https://dechimbote.com` responde 200 y el usuario del sitio **no** se tocó.

## 10) PLAN B: TICKET A SOPORTE DE HOSTINGER

Solo si hPanel **no** dejara cambiar los permisos. Texto listo para copiar y pegar en el ticket:

```text
Asunto: Usuario MySQL de SOLO LECTURA y acceso remoto para dechimbote.com

Hola equipo de soporte,

Necesito conectar una herramienta externa de Business Intelligence (Metabase)
a mi base de datos u196269909_CHIMBOTEALDIA del dominio dechimbote.com. Por
seguridad no puedo usar el usuario principal del sitio.

Por favor:
1. Crear (o dejar listo) el usuario MySQL: u196269909_metabase
2. Darle UNICAMENTE permiso SELECT sobre la base u196269909_CHIMBOTEALDIA
   (sin INSERT, UPDATE, DELETE, DROP, CREATE ni ALTER).
3. Habilitar el Acceso remoto a la base de datos (Remote MySQL) para ese
   usuario desde mi IP publica: 190.108.93.134

Importante: NO modifiquen los permisos del usuario actual del sitio
(u196269909_ALDIACHIMBOTE), esa cuenta debe quedar igual.

Gracias.
```

## 11) MANTENIMIENTO DEL PANEL

- **Actualizar Metabase:** bajar el jar nuevo a `D:\RELAX\metabase\metabase.jar` (mismo nombre) y reiniciar. En esta PC no hay `docker pull`.
- **Cambió la IP pública:** actualizarla en hPanel → Remote MySQL (y avisar al robot).
- **Respaldar el panel:** copiar la carpeta `D:\RELAX\metabase\data` (ahí viven las cuentas y los dashboards). Las consultas también quedan documentadas en esta guía.
- **Seguridad:** el puerto 3000 es solo local. Para verlo desde el celular haría falta un proxy inverso con autenticación (o VPN); **nunca** abrir el puerto tal cual.
- **Nunca** dar al usuario de Metabase permisos de escritura: la prueba negativa de §9 es la que garantiza que el sitio no puede ser dañado desde el panel.

---

> **Guías base del proyecto:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (qué es el sitio, mapa de archivos,
> estado real de cada módulo y qué hacer después) · `REGLAS_DE_ORO_PROYECTO.md` (reglas del jefe,
> canónicas) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar sin romper el sitio).


_Última revisión: 2026-09-10 (consolidación de guías)._
