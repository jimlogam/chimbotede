# BRIEF — CATÁLOGO DE UN RUBRO PARA SUBIR LAS TIENDAS A 5 PRODUCTOS (`__pr_cfg_<slug>.json`)

> **Contexto (no lo repitas, solo úsalo):** en **dechimbote.com** hay un **banco de imágenes**: cada imagen es
> la foto canónica de **UN NOMBRE de producto dentro de UN RUBRO** y vive en `fotos/banco/…` del hosting.
> El jefe ordenó **subir a 5 los productos de todas las tiendas** que hoy tienen menos de 5, **rubro por
> rubro**, y que cada producto nuevo **lleve la imagen del banco**. Se crean **solo productos nuevos**: no se
> borra, no se pisa y no se toca ninguna foto que ya exista.
>
> Tu trabajo es escribir **UN archivo**: `D:\RELAX\__pr_cfg_<slug>.json`. Nada más. No toques ningún otro
> archivo del proyecto.

## 1. Lo que tienes que leer

* **`D:\RELAX\__pr_banco_<slug>.txt`** → las imágenes que el banco **YA TIENE** de ese rubro, una por línea:
  `codigo | nombre | cuántas fichas ya la usan`. **Esos nombres son los productos con foto.**
* `D:\RELAX\GUIA_PRECIOS_DE_PRODUCTOS.md` (opcional, para el rasero de precios).

## 2. El archivo que tienes que escribir

Exactamente esta forma (JSON válido, UTF-8, sin comentarios):

```json
{
  "slug": "<slug>",
  "rubro": "<nombre EXACTO del rubro en la base, te lo doy yo>",
  "pistas": [["<tag>", ["fragmento de texto en minúsculas", "otro fragmento"]], ...],
  "prioridad": {"<tag>": ["<tag>", "<tag>", "gen"], "gen": ["gen"]},
  "items": [
    {"codigo": "C-123", "nombre": "Ceviche mixto", "precio": 28.0, "unidad": "por plato",
     "descripcion": "…", "tags": ["mariscos", "gen"]},
    {"codigo": null, "nombre": "Chicha morada (jarra de 1 litro)", "precio": 12.0, "unidad": "por jarra",
     "descripcion": "…", "tags": ["bebidas", "gen"]}
  ]
}
```

### 2.1 `items` — el catálogo del rubro

* **TODAS las líneas del `__pr_banco_<slug>.txt` van como ítem, con su `codigo` y su nombre COPIADO TAL
  CUAL** (sin corregir mayúsculas, sin traducir, sin acortar). Si el nombre del banco está en minúsculas
  («jugo fruta», «desayuno»), **respétalo tal cual**: ese nombre es la clave del banco.
* **Además, agrega ítems PROPUESTOS (`"codigo": null`)** — productos o servicios **reales y típicos de ese
  rubro en Chimbote** que el banco todavía no tiene (son la lista de compras para el diseñador). Pon entre
  **10 y 30 propuestos**, los más vendidos primero, sin repetir ni parecerse a los que ya tienen código.
  El total del catálogo (con código + propuestos) tiene que ser **de 25 a 90 ítems**.
* **`precio`** (número, soles de Chimbote, sin símbolo): el precio típico del mercado. Rasero del proyecto:
  `GUIA_PRECIOS_DE_PRODUCTOS.md` §3 — servicios y oficios: consulta legal 50 · asesoría 150 · diseño 50 ·
  fotografía 250 · mantenimiento mensual 350 · academia pensión 200 · taller infantil 30 por sesión ·
  habitación 350 (amoblada 450) · cancha 60 por hora · sala de eventos 50 por hora · pasaje 70 · flete 150 ·
  delivery 5 · herramientas 45 · material de construcción 35 · ropa 55 · zapatillas 90 · abarrotes 5 ·
  bebidas 7 · lácteos 8 · snacks 2 · carnes 14 por kg · frutas 4.50 por kg · limpieza 10 · llanta 220 ·
  lubricantes 45 · muebles a medida 800 · puertas 450. **En restaurantes**: menú del día 12-16 · ceviche
  25-32 · chicharrón de pescado 28 · jalea mixta 35 · cuarto de pollo 22-25 · pollo entero 60-70 ·
  chicha morada (jarra) 12 · gaseosa 5-7 · cerveza 12-15 · postre 8-12 · desayuno 8-12.
* **`unidad`** (texto corto, **máximo 30 caracteres**): `por unidad`, `por plato`, `por jarra`, `por vaso`,
  `por kilo`, `por litro`, `por botella`, `por caja`, `por bolsa`, `por metro`, `por m2`, `por docena`,
  `por ciento`, `por juego`, `por paquete`, `por servicio`, `por sesión`, `por hora`, `por día`, `por noche`,
  `por mes`, `por persona`, `por tramo`, `por viaje`, `por página`.
* **`descripcion`**: **máximo 400 caracteres**, en **español del Perú**, 1 o 2 frases que digan **qué se
  lleva el cliente** (qué incluye, de qué está hecho, para qué sirve). Reglas duras:
  ⛔ **no** menciones el nombre de ninguna tienda, ⛔ **no** pongas precios ni teléfonos dentro del texto,
  ⛔ **no** prometas nada que no se pueda cumplir (nada de «delivery gratis», «el mejor de Chimbote»,
  «garantía de por vida»), ⛔ **no** uses inglés y ⛔ **no** inventes marcas.
  ✅ Escribe como habla un negocio peruano que atiende bien: directo y concreto.
* **`tags`**: 1 a 3 etiquetas **de tu propia lista** (ver abajo). Cada ítem que **cualquier** tienda de ese
  rubro podría vender lleva la etiqueta **`gen`** (es lo que se usa para rellenar).

### 2.2 `pistas` — cómo se detecta de qué es la tienda

Lista de pares `[tag, [fragmentos]]`. Los fragmentos son **trozos de texto en minúsculas** que aparecen en
el **nombre o la descripción de la tienda** (por ejemplo `"cevicher"`, `"marisco"`, `"chifa"`, `"parrilla"`,
`"polleria"`, `"cafeteria"`, `"menú del día"`, `"panader"`, `"pasteler"`, `"gimnasio"`, `"barber"`).
Sirven para saber si la tienda es una cevichería, una chifa, una pollería, etc., y darle **primero** lo suyo.
Reglas: **mínimo 4 y máximo 12 etiquetas**; los fragmentos van **sin tildes cuando sea posible** y en
minúsculas; una misma etiqueta puede tener varios fragmentos; los fragmentos **no llevan `^` ni `$`**.
Ojo: son **expresiones regulares sencillas**, así que usa trozos seguros (`cevicher`, `polleria|pollería`…).

### 2.3 `prioridad` — el orden en que se reparten

Para **cada** etiqueta de `pistas` (y para `gen`), la lista de etiquetas que se miran **en ese orden**,
empezando por la suya y **terminando siempre en `gen`**. Ejemplo:

```json
"prioridad": {
  "mariscos": ["mariscos", "criollo", "bebidas", "gen"],
  "pollo":    ["pollo", "criollo", "bebidas", "gen"],
  "gen":      ["gen", "criollo", "bebidas"]
}
```

## 3. Cómo se revisa tu archivo (hazlo tú antes de terminar)

Corre esto y **arréglalo hasta que salga limpio** (0 problemas):

```powershell
$env:PYTHONIOENCODING='utf-8'; cd D:\RELAX; python __pr_check_cfg.py <slug>
```

El revisor comprueba: JSON válido · que estén **todos** los códigos del banco de ese rubro · que los nombres
con código sean **idénticos** al banco · `precio > 0` · `unidad` ≤ 30 · `descripcion` ≤ 400 · que cada ítem
tenga `tags` y que **todas** las etiquetas usadas existan en `pistas`/`prioridad` · que exista la etiqueta
`gen` · que haya ítems con `gen` · que `prioridad` tenga todas las etiquetas y termine en `gen`.
Si algo no te cuadra, arréglalo en tu archivo y vuelve a correrlo.

## 4. Lo que NO se hace

* ⛔ No tocar ningún archivo que no sea `D:\RELAX\__pr_cfg_<slug>.json`.
* ⛔ No subir nada al hosting, no tocar la base de datos, no correr sondas.
* ⛔ No escribir en inglés y no inventar nombres de negocios.
* ⛔ No cambiar los nombres de los ítems que tienen código.
