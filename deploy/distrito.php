<?php
/**
 * distrito.php — Página de distrito (ARREGLADA 2026-09-09).
 * Antes este archivo era de la versión VIEJA y daba error 500 en el sitio real.
 * Ahora redirige a la búsqueda filtrada por distrito (/buscar.php?dist=slug),
 * que es la que sí funciona y ya incluye a Coishco (habilitado como visible).
 */
require_once __DIR__ . '/config.php';

$slug = trim($_GET['slug'] ?? '');
header('Location: ' . url('buscar.php' . ($slug !== '' ? '?dist=' . urlencode($slug) : '')), true, 301);
exit;
