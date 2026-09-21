# mi-sitio-web — copia de seguridad privada de DeChimbote.com

Aquí vive una **copia de seguridad** del sitio **https://dechimbote.com** (marketplace de Chimbote y la
provincia del Santa). El sitio de verdad se trabaja en `D:\RELAX\deploy`; esta carpeta es solo el respaldo,
para tener el historial de todo lo que se ha hecho y poder volver atrás si algo se rompe.

## Qué hay dentro

| Carpeta | Qué es |
|---|---|
| `deploy/` | El código del sitio tal como está publicado (PHP, CSS, JS, imágenes del sitio). |
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

## 🔄 Cómo se actualiza esta copia

```bat
python D:\mi-sitio-web\__sync_backup.py subir
```

Eso copia el sitio de `D:\RELAX\deploy`, copia las guías, tapa las claves, revisa que no haya quedado
ninguna y sube los cambios a GitHub. Si solo se quiere armar la copia sin subirla:
`python __sync_backup.py`.

## 📅 Historial

El historial completo de cambios está en la pestaña **Commits** de este repositorio: cada subida queda con
su fecha.
