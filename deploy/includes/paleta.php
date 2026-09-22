<?php
/**
 * includes/paleta.php — 🎨 LA PALETA DE COLORES DE LAS TARJETAS Y LOS BOTONES
 * ==========================================================================
 * Orden del jefe (2026-09-21): *«las tarjetas sepáralas por colores»*. Aquí vive la lista de tonos que
 * usan las tarjetas del sitio (área Nosotros y documentación oficial).
 *
 * Cada tono trae TRES cosas:
 *   · `tk`  → el color fuerte: el filete de la tarjeta, su título y sus números.
 *   · `tkt` → el fondo teñido de la tarjeta (muy claro, para que el texto se lea cómodo).
 *   · `a` / `b` / `c` → los tres del **botón 3D** (`.b3d`, que vive en `assets/css/components.css`):
 *     `a` es la luz de arriba, `b` el color base y `c` el canto oscuro de abajo.
 *
 * Las listas rotan por estos tonos (con `$i % 8`), así dos tarjetas vecinas nunca se ven iguales.
 * Lo usan `includes/nosotros_area.php` (área Nosotros) e `includes/doc_vista.php` (documentación).
 */

if (!function_exists('paleta')) {

    /** Devuelve el tono que le toca a la posición $i (rota por los ocho de la lista). */
    function paleta($i = 0) {
        static $paleta = [
            ['tk' => '#6d071a', 'tkt' => '#fdf2f4', 'a' => '#9c1030', 'b' => '#6d071a', 'c' => '#3d040f'], // granate (la marca)
            ['tk' => '#c2410c', 'tkt' => '#fff5ed', 'a' => '#f59e0b', 'b' => '#c2410c', 'c' => '#7c2d12'], // naranja
            ['tk' => '#b45309', 'tkt' => '#fffaeb', 'a' => '#fbbf24', 'b' => '#b45309', 'c' => '#78350f'], // ámbar (oro)
            ['tk' => '#15803d', 'tkt' => '#f2fdf5', 'a' => '#22c55e', 'b' => '#15803d', 'c' => '#0b5227'], // verde
            ['tk' => '#1d4ed8', 'tkt' => '#f0f6ff', 'a' => '#3b82f6', 'b' => '#1d4ed8', 'c' => '#122e7c'], // azul
            ['tk' => '#6d28d9', 'tkt' => '#f6f3ff', 'a' => '#8b5cf6', 'b' => '#6d28d9', 'c' => '#42188c'], // violeta
            ['tk' => '#0f766e', 'tkt' => '#effcfa', 'a' => '#14b8a6', 'b' => '#0f766e', 'c' => '#084c47'], // turquesa
            ['tk' => '#be185d', 'tkt' => '#fdf2f8', 'a' => '#ec4899', 'b' => '#be185d', 'c' => '#7c0b3c'], // rosa
        ];
        $n = count($paleta);
        $i = (int)$i;
        if ($i < 0) $i = 0;
        return $paleta[$i % $n];
    }

    /** El estilo en línea que le da su color a una tarjeta (`--tk` y `--tkt`). */
    function paleta_tono($i = 0) {
        $t = paleta($i);
        return '--tk:' . $t['tk'] . ';--tkt:' . $t['tkt'];
    }

    /** El estilo en línea que le da su color 3D a un botón (`--b3a`, `--b3b` y `--b3c`). */
    function paleta_tono_boton($i = 0) {
        $t = paleta($i);
        return '--b3a:' . $t['a'] . ';--b3b:' . $t['b'] . ';--b3c:' . $t['c'];
    }
}
