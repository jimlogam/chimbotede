<?php
/**
 * includes/negocio_borrar.php — 🗑️ BORRAR UNA TIENDA CON TODO LO SUYO (sin dejar basura)
 * ==========================================================================================
 * Lo usan el PANEL DEL DUEÑO (`panel.php`, con su contraseña — orden del jefe, 2026-09-16) y el
 * editor del Súper Admin (`editatiendas.php`). Antes cada uno tenía su propia lista de `DELETE` y
 * era cuestión de tiempo que a una se le olvidara una tabla nueva.
 *
 * Qué borra (en este orden, que es el que respetan las llaves foráneas):
 *   1. Las **imágenes** de la tienda y de sus productos: se APUNTAN las rutas ANTES de borrar las
 *      filas (después ya no hay de dónde sacarlas) y al final se borran los archivos del hosting
 *      (WebP + versiones de 300/800 px), para no dejar fotos huérfanas ocupando espacio.
 *   2. Lo que cuelga de sus **productos** (`directorio_producto_fotos`, `directorio_vistas`).
 *   3. **Todas las tablas que apuntan a la tienda por `negocio_id`** — se le preguntan a la base
 *      (`INFORMATION_SCHEMA`), no a una lista escrita a mano: así una tabla nueva (opiniones,
 *      cobertura, rubros, prompts, avisos de empleo…) **no se queda con filas huérfanas** sin que
 *      nadie se acuerde de tocar este archivo. Si la base no dejara consultar el esquema, se usa la
 *      lista de respaldo.
 *   4. La fila de `directorio_negocios`.
 *   5. Las **cachés** (el buscador fuzzy y `cache/negocios.json`).
 *
 * ⚠️ Es IRREVERSIBLE. Quien llame a esto tiene que haber comprobado **antes** que la tienda es de
 * quien la manda a borrar (y, en el panel, que escribió bien su contraseña).
 *
 * @return array ['ok'=>bool, 'error'?=>string, 'nombre'=>string, 'archivos'=>int, 'filas'=>int]
 */

if (!function_exists('negocio_borrar_completo')) {

    function negocio_borrar_completo($negocio_id) {
        $nid = (int)$negocio_id;
        if ($nid <= 0) return ['ok' => false, 'error' => 'Falta la tienda.'];

        $pdo = db();

        // 1) El nombre (para el mensaje) y las rutas de TODAS sus imágenes, ANTES de borrar nada.
        $nombre = '';
        try {
            $st = $pdo->prepare("SELECT nombre FROM directorio_negocios WHERE id = ? LIMIT 1");
            $st->execute([$nid]);
            $nombre = trim((string)($st->fetchColumn() ?: ''));
        } catch (Throwable $e) { /* sigue: si la tabla falla, no hay nada que borrar */ }
        if ($nombre === '') return ['ok' => false, 'error' => 'Esa tienda ya no existe.'];

        $rutas = [];
        foreach ([
            "SELECT ruta FROM directorio_fotos WHERE negocio_id = ?",
            "SELECT pf.ruta FROM directorio_producto_fotos pf
               INNER JOIN directorio_servicios sv ON sv.id = pf.producto_id
              WHERE sv.negocio_id = ?",
            "SELECT ruta FROM directorio_negocio_imagenes_ant WHERE negocio_id = ?",
        ] as $sql) {
            try {
                $st = $pdo->prepare($sql);
                $st->execute([$nid]);
                foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $f) {
                    $r = trim((string)($f['ruta'] ?? ''));
                    if ($r !== '') $rutas[] = $r;
                }
            } catch (Throwable $e) { /* la tabla puede no existir en una instalación vieja */ }
        }

        // 2) Lo que cuelga de sus productos (antes de borrar los productos).
        $borradas = 0;
        foreach ([
            "DELETE FROM directorio_producto_fotos WHERE producto_id IN
                (SELECT id FROM directorio_servicios WHERE negocio_id = ?)",
            "DELETE FROM directorio_vistas WHERE producto_id IN
                (SELECT id FROM directorio_servicios WHERE negocio_id = ?)",
        ] as $sql) {
            try { $st = $pdo->prepare($sql); $st->execute([$nid]); $borradas += $st->rowCount(); }
            catch (Throwable $e) {}
        }

        // 3) Todas las tablas que apuntan a la tienda por `negocio_id` (preguntadas a la base).
        $tablas = [];
        try {
            $tablas = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'negocio_id'")
                         ->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $e) { $tablas = []; }
        if (!$tablas) {
            // Respaldo (la lista que usaba el editor del Súper Admin, más las que nacieron después).
            $tablas = ['directorio_servicios', 'directorio_fotos', 'directorio_opiniones',
                       'directorio_vistas', 'directorio_mensajes', 'directorio_negocio_pagos',
                       'directorio_reclamos', 'directorio_negocio_rubros', 'directorio_negocio_cobertura',
                       'directorio_negocio_prompts', 'directorio_negocio_imagenes_ant', 'directorio_empleos'];
        }
        foreach ($tablas as $t) {
            $t = preg_replace('/[^A-Za-z0-9_]/', '', (string)$t);
            if ($t === '' || $t === 'directorio_negocios') continue;   // la tienda va al final
            try { $st = $pdo->prepare("DELETE FROM `{$t}` WHERE negocio_id = ?"); $st->execute([$nid]); $borradas += $st->rowCount(); }
            catch (Throwable $e) { /* una tabla con llave foránea rara no debe frenar el borrado */ }
        }

        // 4) La tienda.
        try {
            $pdo->prepare("DELETE FROM directorio_negocios WHERE id = ?")->execute([$nid]);
        } catch (Throwable $e) {
            error_log('negocio_borrar_completo (' . $nid . '): ' . $e->getMessage());
            return ['ok' => false, 'error' => 'No se pudo borrar la tienda: ' . $e->getMessage()];
        }

        // 5) Los archivos de las imágenes, ya sin filas que los usen.
        $archivos = 0;
        foreach (array_unique(array_filter($rutas, 'strlen')) as $r) {
            try { $archivos += (int)img_borrar($r); } catch (Throwable $e) {}
        }

        // 6) Las cachés (el buscador tenía esa tienda dentro).
        @unlink(__DIR__ . '/../cache/negocios.json');
        if (!function_exists('fuzzy_olvidar_cache') && is_file(__DIR__ . '/fuzzy_cache.php')) {
            require_once __DIR__ . '/fuzzy_cache.php';
        }
        if (function_exists('fuzzy_olvidar_cache')) {
            try { fuzzy_olvidar_cache(); } catch (Throwable $e) {}
        }

        return ['ok' => true, 'nombre' => $nombre, 'archivos' => $archivos, 'filas' => $borradas];
    }
}
