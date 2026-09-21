<?php
/**
 * sesion.php — Estado de sesión + catálogos para Caminante.
 * Caminante vive en el mismo dominio (dechimbote.com) y usa la misma cookie de
 * sesión, así que aquí le decimos a la app:
 *   - si el usuario está logueado (para asignarle la tienda al guardar),
 *   - si acaba de entrar y tiene tiendas "reclamables" creadas sin sesión → se
 *     asignan automáticamente (aparecen en su panel como suyas),
 *   - el token CSRF para el guardado seguro,
 *   - la lista de rubros, sus SUBCATEGORÍAS y sus PALABRAS CLAVE, más los distritos.
 *
 * 🆕 2026-09-10 — El buscador de rubros ahora es de verdad predictivo:
 *   Antes solo buscaba dentro de 60 rubros de tienda, así que "pollo", "zapatilla",
 *   "ceviche", "hidrandina" o "municipalidad" NO devolvían nada (y el propio campo
 *   ponía "pollo" de ejemplo). Ahora se envían:
 *     · `claves`   : palabras reales que llevan a un rubro (tabla directorio_categoria_claves)
 *     · `subcategorias` : "Pollerías", "Cevicherías", "Chifas", "Menús / Bodegones"…
 *   El rubro del negocio se sigue guardando por CATEGORÍA; la subcategoría viaja aparte
 *   en `subcategoria_id` (la columna existe en directorio_negocios desde siempre).
 * Respuesta JSON.
 */
require_once __DIR__ . '/../config.php'; // config + helpers del sitio

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    iniciar_sesion();
    $u = usuario_actual();

    // Reclamación automática: si esta sesión creó tiendas sin loguearse y ahora
    // ya hay cuenta, se asignan (dueno_id) y se devuelven en 'asignadas'.
    $asignadas = caminante_autoreclamar();

    $claves = obtener_claves_categorias();

    $categorias = [];
    foreach (obtener_categorias() as $c) {
        $categorias[] = [
            'id'     => (int)$c['id'],
            'nombre' => $c['nombre'],
            'icono'  => (string)($c['icono'] ?? ''),
            'claves' => $claves[(int)$c['id']] ?? [],
        ];
    }

    $subcategorias = [];
    foreach (obtener_subcategorias() as $s) {
        $subcategorias[] = [
            'id'           => (int)$s['id'],
            'categoria_id' => (int)$s['categoria_id'],
            'nombre'       => $s['nombre'],
            'slug'         => (string)$s['slug'],
            'claves'       => $claves[(int)$s['categoria_id']] ?? [],
        ];
    }

    $distritos = [];
    foreach (obtener_distritos_visibles() as $d) {
        $distritos[] = ['id' => (int)$d['id'], 'nombre' => $d['nombre'], 'slug' => (string)($d['slug'] ?? '')];
    }

    echo json_encode([
        'ok' => true,
        'logueado' => (bool)$u,
        'usuario' => $u ? (string)$u['nombre'] : '',
        'tipo' => $u ? (string)($u['tipo'] ?? '') : '',
        'url_login' => url('login.php?redirect=' . rawurlencode('/caminante/')),
        'csrf' => csrf_token(),
        'categorias' => $categorias,
        'subcategorias' => $subcategorias,
        'distritos' => $distritos,
        'asignadas' => $asignadas,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server_error', 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
