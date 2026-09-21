# CONTRATO TÉCNICO — TABLÓN DE EMPLEOS (aprobado por el jefe, "dale", 2026-09-12)

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> Documento de trabajo (fuente única de nombres). La guía definitiva se escribe al final del módulo.
> Decisiones del jefe: 3 tipos · el visitante particular publica con aprobación previa ·
> 30 días renovables de 1 clic · bloque del index después de Destacados y oculto si está vacío ·
> `/empleos` (archivo `empleosdb.php`) y `/empleo/<slug>` · sin imágenes (el aviso es texto).

## 1) TABLA NUEVA: `directorio_empleos`

Se crea por **auto-instalación defensiva** (patrón que estrenó el chat comunitario en
`chat_comunidad.php:57-87` — 🗑️ ese archivo **se retiró del sitio el 2026-09-13**; el patrón sigue vivo en
banners, prompts de productos y este módulo): `CREATE TABLE IF NOT
EXISTS` + comprobación en `information_schema.columns`, **solo cuando hay admin/dueño**. Los
`migrar_*.php` NO sirven: el antivirus del hosting les devuelve 404.

```sql
CREATE TABLE IF NOT EXISTS directorio_empleos (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug              VARCHAR(160) NOT NULL,
  tipo              ENUM('ofrezco','busco','anuncio') NOT NULL DEFAULT 'ofrezco',
  titulo            VARCHAR(150) NOT NULL,
  entidad           VARCHAR(120) NULL,
  negocio_id        INT UNSIGNED NULL,
  dueno_id          INT UNSIGNED NULL,
  categoria_id      INT UNSIGNED NULL,
  oficio_slug       VARCHAR(40)  NULL,
  distrito_id       INT UNSIGNED NULL,
  ciudad_txt        VARCHAR(80)  NULL,
  telefono          VARCHAR(20)  NULL,
  whatsapp          VARCHAR(20)  NULL,
  sueldo_txt        VARCHAR(80)  NULL,
  jornada           VARCHAR(60)  NULL,
  duracion          VARCHAR(60)  NULL,
  requisitos        VARCHAR(500) NULL,
  descripcion       TEXT         NULL,
  estado            ENUM('pendiente','activo','pausado','vencido','rechazado') NOT NULL DEFAULT 'pendiente',
  destacado         TINYINT(1)   NOT NULL DEFAULT 0,
  vistas            INT UNSIGNED NOT NULL DEFAULT 0,
  wa_clicks         INT UNSIGNED NOT NULL DEFAULT 0,
  token             CHAR(32)     NOT NULL,
  ip_hash           CHAR(40)     NULL,
  publicado_en      DATETIME     NULL,
  disponible_hasta  DATE         NULL,
  renovado_en       DATETIME     NULL,
  creado_en         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en    DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_empleo_slug (slug),
  KEY idx_vig (estado, disponible_hasta),
  KEY idx_tipo_dist (tipo, distrito_id),
  KEY idx_oficio (oficio_slug),
  KEY idx_cat (categoria_id),
  KEY idx_negocio (negocio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

⛔ **REGLA DE FECHAS (trampa del proyecto):** el MySQL del hosting va en **UTC** y el sitio en
**America/Lima**. La vigencia **NUNCA** se compara con `NOW()`/`CURDATE()`: se compara con la fecha de Lima
que manda PHP (`date('Y-m-d')`) como parámetro, igual que `sql_producto_vigente()` (`helpers.php:650`).

## 2) OFICIOS (lista propia; los rubros del directorio NO son puestos de trabajo)

`construccion` 🧱 · `cocina` 🍳 · `atencion-ventas` 🛍️ · `transporte` 🛵 · `limpieza` 🧹 ·
`seguridad` 🛡️ · `tecnicos` 🔧 · `salud` 🩺 · `educacion` 📚 · `administracion` 💼 · `campo` 🌾 · `otros` 📌

## 3) HELPERS NUEVOS (`deploy/includes/helpers.php`, al final del archivo)

| Función | Qué hace |
|---|---|
| `empleo_oficios()` | array `slug => ['nombre','icono']` |
| `empleo_oficio_nombre($slug)` | nombre + icono de un oficio |
| `empleos_instalar_tabla()` | CREATE TABLE IF NOT EXISTS defensivo (+ añade columnas que falten) |
| `empleo_vigente_sql($alias)` | devuelve `[sql, params]` con `estado='activo' AND (disponible_hasta IS NULL OR disponible_hasta >= ?)` |
| `empleo_slug_unico($titulo)` | slug ASCII corto y único |
| `crear_empleo($datos)` | INSERT + `aviso('empleo', …)`; devuelve `['id','token','slug']` |
| `empleo_por_slug($slug)` / `empleo_por_token($token)` | lectura |
| `empleo_zonas_txt($e)` | "Coishco" / "Huarmey (otras ciudades)" |
| `empleo_card_html($e)` | **la tarjeta simple** (sin foto, sin precio, sin productos) |
| `empleos_destacados($limite)` | para el bloque del index |
| `buscar_empleos($f)` | listado con filtros + total + paginación |
| `contar_empleos_por_oficio()` / `contar_empleos_por_zona()` | chips con número |
| `empleos_bloque_html($limite = 16)` | el bloque del index (devuelve '' si no hay avisos) |
| `empleo_renovar($token, $dias = 30)` | +30 días desde hoy (fecha Lima) |
| `empleo_marcar($token, $estado)` | aprobar / rechazar / pausar (moderación) |
| `empleo_url($slug)` | `url('empleo/' . $slug)` |

## 4) ARCHIVOS

| Archivo | Qué es |
|---|---|
| `deploy/empleosdb.php` | **La página de empleos** (URL `/empleos`): filtros, buscador, paginación 24, formulario público (antes se llamaba «el tablón de empleos»; el sitio ya no usa esa palabra) |
| `deploy/empleo.php` | **ficha del aviso** (URL `/empleo/<slug>`): JSON-LD `JobPosting`, WhatsApp, reportar |
| `deploy/api/empleos_json.php` | JSON plano para Fuse.js (patrón `api/negocios_json.php`, caché 1 h) |
| `deploy/assets/css/components.css` | bloque nuevo al final: `.grid-empleos`, `.card-empleo*` |
| `deploy/index.php` | bloque de empleos después de Destacados y antes de Más vistos |
| `deploy/panel.php` | botón "💼 Ofrecer empleo" (lado dueño) |
| `deploy/negocio.php` | sección "💼 Este negocio busca personal" (si hay avisos) |
| `deploy/includes/avisos.php` | tipo `empleo` con botones ✅ Aprobar / 🗑️ Rechazar |
| `deploy/includes/header.php` | soporte de `$canonical_url` + bump de versión del CSS |
| `deploy/sitemap.php` | `/empleos` junto a la línea 42 + los avisos activos |
| `deploy/.htaccess` | `^empleos/?$` y `^empleo/([a-z0-9\-]+)/?$` |

## 5) REJILLA EXACTA (pedido del jefe)

`.grid-empleos`: **2 columnas** (móvil) → **4 columnas** desde 900 px. El bloque del index pinta **16
tarjetas** y el CSS oculta de la **9ª** en adelante por debajo de 900 px → **2×4 en móvil (8)** y
**4×4 en escritorio (16)**, sin JS. Las tarjetas ocultas siguen en el HTML (Google las indexa).

## 6) REGLAS QUE NO SE NEGOCIAN

1. **Sin imágenes**: el aviso es texto (regla del jefe, 2026-09-12). Nunca una captura de pantalla.
2. **Botón de WhatsApp** siempre con `wa_icono_svg()` + mensaje de contexto + `wa_linea_origen()`.
3. **Nunca se inventa sueldo ni edad**: lo que diga el aviso; si no lo dice, "A convenir".
4. **Nada de `NOW()`/`CURDATE()`** para vigencia (MySQL en UTC).
5. **Móvil primero**: fuentes ≥16 px, botones ≥44 px.
6. Texto de usuario SIEMPRE con `e()` al pintar y `htmlspecialchars` en JSON.
