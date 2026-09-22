# mi-sitio-web — copia de seguridad de DeChimbote.com

Aquí vive una **copia de seguridad** del sitio **https://dechimbote.com** (marketplace de Chimbote y la
provincia del Santa). El sitio de verdad se trabaja en `D:\RELAX\deploy`; esta carpeta es solo el respaldo,
para tener el historial de todo lo que se ha hecho y poder volver atrás si algo se rompe.

> ⚠️ **ESTE REPOSITORIO ES PÚBLICO** (comprobado el 2026-09-21: `private: false`). Lo ve cualquiera.
> El jefe lo dejó así a propósito ese día, incluso sabiendo que aquí está la base de datos con los datos
> reales de las tiendas.

## Qué hay dentro

| Carpeta | Qué es |
|---|---|
| `deploy/` | El código del sitio tal como está publicado (PHP, CSS, JS, imágenes del sitio). |
| `base_de_datos/` | 💾 **El respaldo de la base de datos** (`.sql.gz`) + **`ESQUEMA.sql`** legible. Ver `base_de_datos/LEEME.md`. |
| `*.md` | Las guías y apuntes del proyecto (el "cómo se hace" de cada módulo). |

No se copian las carpetas `cache/` ni `logs/` (son temporales del servidor).

## 🔑 Las claves NO están aquí

Por seguridad, **todas las contraseñas y claves quedaron reemplazadas por un texto de relleno**
(`PON_AQUI_LA_CLAVE`, `PON_AQUI_TU_CLAVE_DEEPSEEK`, …). Las claves de verdad viven solo en la
computadora del dueño:

- Base de datos y FTP: en `D:\RELAX\deploy\config.php` y en el archivo de seguridad del FTP.
- Claves de las APIs (DeepSeek, Google, Telegram, HubSpot): en sus archivos `config_*.php`.

**Para restaurar el sitio** hay que volver a poner esas claves en los mismos archivos (están marcadas con
`PON_AQUI…`) y volver a subir el código por FTP.

⚠️ **Ojo: el respaldo de la BASE DE DATOS (`base_de_datos/`) NO va tapado.** Es la base tal cual, con sus
datos (tiendas, usuarios, opiniones, estadísticas). En su histórico quedó escrita, además, la clave del
instalador de HubSpot. Se subió así por decisión del jefe el 2026-09-21.

## 💾 La base de datos

En **`base_de_datos/`** están el respaldo completo (`.sql.gz`, 27,9 MB) y un **`ESQUEMA.sql`** de texto que se
lee directo en GitHub: la lista de las **58 tablas** con sus filas y columnas, y el `CREATE` de cada una.
Lo detalla **`base_de_datos/LEEME.md`** (de dónde sale, cómo se lee, cómo se restaura y las trampas).

## 🔄 Cómo se actualiza esta copia

```bat
python D:\mi-sitio-web\__sync_backup.py subir
```

Eso copia el sitio de `D:\RELAX\deploy`, copia las guías, tapa las claves, revisa que no haya quedado
ninguna y sube los cambios a GitHub. Si solo se quiere armar la copia sin subirla:
`python __sync_backup.py`.

⚠️ **Ese comando NO toca la base de datos** (ni la carpeta `base_de_datos/`): la base se respalda aparte,
con la receta de `base_de_datos/LEEME.md`.

## 📅 Historial

El historial completo de cambios está en la pestaña **Commits** de este repositorio: cada subida queda con
su fecha.
