# 💾 LA BASE DE DATOS — respaldo para leer tablas y valores

> **Fecha del respaldo:** **2026-09-21 23:49** · **Base:** `u196269909_CHIMBOTEALDIA` (dechimbote.com)
> **Decisión del jefe (2026-09-21):** subir la base **tal cual** al repositorio, aunque sea público.

## Qué hay en esta carpeta

| Archivo | Qué es |
|---|---|
| **`backup_20260921_234904.sql.gz`** (27,9 MB) | **El respaldo completo**: la estructura y **los datos de todas las filas** de las 58 tablas y las 7 vistas. Va comprimido (gzip). |
| **`ESQUEMA.sql`** (72 KB) | **La estructura en texto**, para leer sin bajar nada: la lista de tablas con **cuántas filas y cuántas columnas** tiene cada una, y el `CREATE` de cada tabla con todas sus columnas. |

## De dónde salió

Lo armó **el propio sitio**, con la tarea de respaldo que ya existía:

```text
https://dechimbote.com/cron_runner.php?tarea=backup&k=<CLAVE_DE_CRON_RUNNER>
```

Eso escribe `__backups/backup_<fecha>.sql.gz` en el hosting (guarda las últimas 14). Después el archivo se
bajó **por FTP** y se trajo aquí. El esquema legible lo saca la herramienta **`D:\RELAX\__bd_esquema.py`**.

> ⚠️ Antes de esto, el respaldo automático **estuvo caído**: la copia anterior era del **2026-09-10**.

## Qué trae (el sitio en números)

**58 tablas · 161 918 filas**, entre ellas:

| Tabla | Filas | Qué guarda |
|---|---|---|
| `directorio_servicios` | 28 384 | Los productos y servicios |
| `directorio_producto_fotos` | 22 316 | Las fotos de cada producto |
| `directorio_avisos_log` | 20 721 | El registro de avisos del bot de Telegram |
| `directorio_stats_eventos` | 18 147 | Las estadísticas (eventos) |
| `directorio_categoria_claves` | 16 774 | Las palabras clave de los rubros |
| `directorio_stats_sesiones` | 16 166 | Las sesiones de los visitantes |
| `directorio_fotos` | 11 013 | Las fotos (portadas) de las tiendas |
| `directorio_vistas` | 9 640 | Lo que ve cada visitante |
| `directorio_opiniones` | 6 257 | Las opiniones de las tiendas |
| `directorio_negocios` | 5 300 | **Las tiendas** |
| `directorio_usuarios` | 89 | Los usuarios (con sus claves cifradas) |

Y **7 vistas** (`vista_negocios_activos`, `vista_productos_destacados`, `vista_negocio_ficha_completa`…),
que se calculan solas a partir de las tablas.

## Cómo se lee

- **En GitHub (sin bajar nada):** abre **`ESQUEMA.sql`** — se ve como texto, con el resumen de todas las tablas.
- **Los datos:** baja el `.sql.gz`, descomprímelo (7-Zip, o `tar -xzf`) y ábrelo con un editor de texto; o
  cárgalo en un MySQL/MariaDB local con *phpMyAdmin* → *Importar*.

## ⚠️ Tres cosas que hay que saber

1. **ESTE REPOSITORIO ES PÚBLICO.** Aquí están los datos reales de las **5 300 tiendas** (nombres, teléfonos,
   WhatsApp, direcciones, correos), la tabla **`directorio_usuarios`** con las claves cifradas, los pedidos y
   las estadísticas. Además, el histórico de visitas dejó escrita **una clave viva** dentro de
   `directorio_avisos_log` (la del instalador de HubSpot). Fue decisión del jefe subirlo así.
2. **El respaldo del sitio vuelca también las VISTAS como si fueran tablas** (su tarea pide `SHOW TABLES` y ahí
   salen las vistas). Son **23 de los 27,9 MB**: `vista_alianzas_sugeridas` llegó al tope de **500 000 filas**
   y las demás repiten datos que ya están en las tablas. Al restaurar, esas líneas `INSERT INTO vista_…`
   **hay que saltarlas** (no se pueden ejecutar) y dejar que cada vista se rehaga con su `CREATE`.
3. **Las claves del sitio (FTP, base, APIs) NO están aquí**: viven solo en los `config*.php` de la computadora
   del jefe, y en el respaldo del código van tapadas con `PON_AQUI…`. Lo único que se coló es la clave del
   instalador de HubSpot, dentro del registro de visitas (punto 1).

## 🔄 Cómo se actualiza esta carpeta

```text
1) Disparar el respaldo del sitio:  cron_runner.php?tarea=backup&k=<CLAVE>
2) Bajarlo por FTP desde __backups/  a  D:\RELAX\_BASE_DE_DATOS\
3) Regenerar el esquema:            python D:\RELAX\__bd_esquema.py
4) Copiar el .sql.gz y el ESQUEMA.sql aquí y subir con git (commit + push)
```
