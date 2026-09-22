-- ============================================================
--  ESQUEMA DE LA BASE DE DATOS  ·  dechimbote.com
-- ============================================================
--  Sacado del respaldo: backup_20260921_234904.sql.gz
--  Tamaño del respaldo: 27.9 MB (comprimido)
--  Contenido: 58 tablas (161 918 filas) y 7 vistas (552 663 filas).
--
--  ⚠️ ESTE ARCHIVO ES SOLO LA ESTRUCTURA. Los DATOS (el contenido de cada
--     fila) están en el .sql.gz que va al lado. Aquí se lee, sin bajar nada,
--     qué tablas hay, qué columnas tiene cada una y cuántas filas guarda.
--
--  ⚠️ OJO AL RESTAURAR: el respaldo del sitio (`cron/tasks/backup.php`) pide
--     SHOW TABLES, así que volcó también las 7 VISTAS como si fueran tablas,
--     con sus filas dentro (la de `vista_alianzas_sugeridas` llegó al tope de
--     500 000). Esas líneas `INSERT INTO vista_…` NO se pueden ejecutar: al
--     restaurar hay que saltarlas y dejar que cada vista se rehaga con su CREATE.
-- ============================================================

-- ------------------------------------------------------------
--  RESUMEN
-- ------------------------------------------------------------

-- TABLAS                                                   FILAS  COLUMNAS
-- directorio_afinidades                                      942         5
-- directorio_alianzas                                          0         7
-- directorio_avisos_config                                    22         3
-- directorio_avisos_log                                   20 721        12
-- directorio_banner_stats                                    104         4
-- directorio_banners                                           8        16
-- directorio_busquedas                                     3 250        10
-- directorio_cancion_claves                                    5         8
-- directorio_canciones                                       101        18
-- directorio_categoria_claves                             16 774         3
-- directorio_categorias                                      139         8
-- directorio_chat_comunidad                                    1         5
-- directorio_chatbot_ajustes                                  15         3
-- directorio_claves_pedidas                                    0        11
-- directorio_distritos                                         9         4
-- directorio_empleos                                          30        37
-- directorio_explorer_comentarios                              0        12
-- directorio_explorer_likes                                    5         9
-- directorio_fotos                                        11 013         6
-- directorio_historial_busqueda                              154         6
-- directorio_historias                                       145         5
-- directorio_ia_tiendas                                       20        18
-- directorio_ia_tiendas_log                                  179        14
-- directorio_invitacion_claves                                68         5
-- directorio_invitaciones                                     80         5
-- directorio_mensajes                                          0         7
-- directorio_negocio_cobertura                               325         4
-- directorio_negocio_imagenes_ant                            314         4
-- directorio_negocio_pagos                                     0         4
-- directorio_negocio_prompts                                 100         8
-- directorio_negocio_rubros                                   20         4
-- directorio_negocio_telefonos                                 0         6
-- directorio_negocios                                      5 300        42
-- directorio_noticias                                         16        18
-- directorio_opiniones                                     6 257        10
-- directorio_opiniones_reportes                                2        10
-- directorio_pagos                                             6         5
-- directorio_paletas                                           4         7
-- directorio_pedidos                                         419        13
-- directorio_pedidos_busqueda                                202        21
-- directorio_pedidos_propuestas                                0        11
-- directorio_plantillas                                        3         5
-- directorio_postulantes                                       7        13
-- directorio_producto_fotos                               22 316         6
-- directorio_producto_imagenes_ant                            46         4
-- directorio_producto_prompts                                320         8
-- directorio_publicaciones_fijadas                             0         6
-- directorio_reclamos                                          1        11
-- directorio_reportes                                          4        11
-- directorio_servicios                                    28 384        12
-- directorio_sesiones                                          2         7
-- directorio_stats_eventos                                18 147         9
-- directorio_stats_sesiones                               16 166        14
-- directorio_subcategorias                                    28         5
-- directorio_telegram_subs                                     0        15
-- directorio_usuarios                                         89        14
-- directorio_vistas                                        9 640         6
-- directorio_zonas                                            15         4

-- VISTAS (se calculan solas, no son tablas)                FILAS
-- vista_alianzas_sugeridas                               500 000   ← tope del respaldo: siguió contando y se cortó
-- vista_negocio_ficha_completa                             5 300
-- vista_negocios_activos                                   5 299
-- vista_negocios_con_pagos                                 5 299
-- vista_negocios_populares                                 5 299
-- vista_productos_destacados                              27 794
-- vista_recomendaciones_usuario                            3 672

-- ============================================================
--  TABLAS (58) — estructura real
-- ============================================================

-- ------------------------------------------------------------
-- TABLA: directorio_afinidades — 942 filas · 5 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_afinidades` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoria_origen_id` int(10) unsigned NOT NULL,
  `categoria_complementaria_id` int(10) unsigned NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_par` (`categoria_origen_id`,`categoria_complementaria_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2207 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_alianzas — 0 filas · 7 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_alianzas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_a_id` int(10) unsigned NOT NULL,
  `negocio_b_id` int(10) unsigned NOT NULL,
  `estado` enum('pendiente','aceptada','rechazada','cancelada') NOT NULL DEFAULT 'pendiente',
  `propuesta_por` bigint(20) unsigned NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `respuesta_fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_par` (`negocio_a_id`,`negocio_b_id`),
  KEY `idx_estado` (`estado`),
  KEY `fk_ali_b` (`negocio_b_id`),
  KEY `fk_ali_usr` (`propuesta_por`),
  CONSTRAINT `fk_ali_a` FOREIGN KEY (`negocio_a_id`) REFERENCES `directorio_negocios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ali_b` FOREIGN KEY (`negocio_b_id`) REFERENCES `directorio_negocios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ali_usr` FOREIGN KEY (`propuesta_por`) REFERENCES `directorio_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_avisos_config — 22 filas · 3 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_avisos_config` (
  `tipo` varchar(30) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `actualizado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_avisos_log — 20 721 filas · 12 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_avisos_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` varchar(30) NOT NULL,
  `estado` enum('enviado','agrupado','duplicado','silencio','apagado','robot') NOT NULL DEFAULT 'enviado',
  `clave` varchar(120) DEFAULT NULL,
  `resumen` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `es_bot` tinyint(1) NOT NULL DEFAULT 0,
  `bot` varchar(40) DEFAULT NULL,
  `negocio_id` int(10) unsigned DEFAULT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `creado_en` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_estado_fecha` (`estado`,`creado_en`),
  KEY `idx_clave` (`clave`,`estado`,`creado_en`),
  KEY `idx_tipo_fecha` (`tipo`,`creado_en`),
  KEY `idx_bot` (`es_bot`,`creado_en`),
  KEY `idx_negocio` (`negocio_id`,`creado_en`),
  KEY `idx_ip_fecha` (`ip`,`creado_en`)
) ENGINE=InnoDB AUTO_INCREMENT=20764 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_banner_stats — 104 filas · 4 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_banner_stats` (
  `banner_id` int(10) unsigned NOT NULL,
  `fecha` date NOT NULL,
  `impresiones` int(10) unsigned NOT NULL DEFAULT 0,
  `clics` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`banner_id`,`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_banners — 8 filas · 16 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_banners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(190) NOT NULL,
  `texto` varchar(255) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `tema` varchar(120) DEFAULT NULL COMMENT 'necesidad -> negocios del modal',
  `busqueda` varchar(190) DEFAULT NULL,
  `rubros` varchar(255) DEFAULT NULL COMMENT 'csv ids categoria permitidas; NULL=libre',
  `franjas` varchar(60) NOT NULL DEFAULT '24' COMMENT 'csv manana,tarde,noche | 24',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL COMMENT 'NULL = infinito',
  `enlace` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `impresiones` int(10) unsigned NOT NULL DEFAULT 0,
  `clics` int(10) unsigned NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_act` (`activo`),
  KEY `idx_vigencia` (`fecha_inicio`,`fecha_fin`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_busquedas — 3 250 filas · 10 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_busquedas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `termino` varchar(120) NOT NULL,
  `norm` varchar(120) NOT NULL,
  `origen` varchar(12) NOT NULL DEFAULT 'web',
  `resultados` smallint(5) unsigned DEFAULT NULL,
  `categoria_id` int(10) unsigned DEFAULT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `dispositivo` varchar(12) NOT NULL DEFAULT '',
  `ip` varchar(45) DEFAULT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mb_fecha` (`fecha`),
  KEY `idx_mb_norm` (`norm`,`fecha`),
  KEY `idx_mb_vacias` (`resultados`,`fecha`),
  KEY `idx_mb_cat` (`categoria_id`,`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=3251 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_cancion_claves — 5 filas · 8 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_cancion_claves` (
  `n` tinyint(3) unsigned NOT NULL,
  `etiqueta` varchar(40) DEFAULT NULL,
  `saldo` int(11) DEFAULT NULL,
  `agotada` tinyint(1) NOT NULL DEFAULT 0,
  `usadas` int(10) unsigned NOT NULL DEFAULT 0,
  `comprobado_en` datetime DEFAULT NULL,
  `aviso_en` datetime DEFAULT NULL,
  `latido_en` datetime DEFAULT NULL,
  PRIMARY KEY (`n`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_canciones — 101 filas · 18 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_canciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_id` int(10) unsigned NOT NULL,
  `nombre` varchar(160) NOT NULL DEFAULT '',
  `rubro` varchar(120) NOT NULL DEFAULT '',
  `distrito` varchar(80) NOT NULL DEFAULT '',
  `estado` enum('pendiente','generando','listo','error') NOT NULL DEFAULT 'pendiente',
  `clave_n` tinyint(3) unsigned DEFAULT NULL,
  `task_id` varchar(64) DEFAULT NULL,
  `modelo` varchar(32) DEFAULT NULL,
  `prompt` text DEFAULT NULL,
  `letra` text DEFAULT NULL,
  `ruta` varchar(255) DEFAULT NULL,
  `duracion` decimal(6,3) DEFAULT NULL,
  `bytes` int(10) unsigned DEFAULT NULL,
  `error` varchar(255) DEFAULT NULL,
  `intentos` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL,
  `actualizado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_negocio` (`negocio_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_creado` (`creado_en`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_categoria_claves — 16 774 filas · 3 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_categoria_claves` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` int(10) unsigned NOT NULL,
  `clave` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_clave` (`categoria_id`,`clave`),
  KEY `idx_clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=29990 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_categorias — 139 filas · 8 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_categorias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(10) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_chat_comunidad — 1 filas · 5 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_chat_comunidad` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `alias` varchar(10) NOT NULL,
  `texto` varchar(500) NOT NULL,
  `creado_en` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_chatbot_ajustes — 15 filas · 3 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_chatbot_ajustes` (
  `clave` varchar(40) NOT NULL,
  `valor` text DEFAULT NULL,
  `actualizado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_claves_pedidas — 0 filas · 11 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_claves_pedidas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `entrada` varchar(120) NOT NULL,
  `entrada_norm` varchar(120) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `nombre` varchar(160) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'pendiente',
  `atendido_por` int(11) DEFAULT NULL,
  `atendido_en` datetime DEFAULT NULL,
  `creado_en` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_estado` (`estado`,`creado_en`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_norm` (`entrada_norm`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_distritos — 9 filas · 4 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_distritos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_empleos — 30 filas · 37 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_empleos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(160) NOT NULL,
  `tipo` enum('ofrezco','busco','anuncio') NOT NULL DEFAULT 'ofrezco',
  `titulo` varchar(150) NOT NULL,
  `entidad` varchar(120) DEFAULT NULL,
  `negocio_id` int(10) unsigned DEFAULT NULL,
  `dueno_id` int(10) unsigned DEFAULT NULL,
  `categoria_id` int(10) unsigned DEFAULT NULL,
  `oficio_slug` varchar(40) DEFAULT NULL,
  `distrito_id` int(10) unsigned DEFAULT NULL,
  `ciudad_txt` varchar(80) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `sueldo_txt` varchar(80) DEFAULT NULL,
  `jornada` varchar(60) DEFAULT NULL,
  `duracion` varchar(60) DEFAULT NULL,
  `requisitos` varchar(500) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('pendiente','activo','pausado','vencido','rechazado') NOT NULL DEFAULT 'pendiente',
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `vistas` int(10) unsigned NOT NULL DEFAULT 0,
  `wa_clicks` int(10) unsigned NOT NULL DEFAULT 0,
  `token` char(32) NOT NULL,
  `ip_hash` char(40) DEFAULT NULL,
  `publicado_en` datetime DEFAULT NULL,
  `disponible_hasta` date DEFAULT NULL,
  `renovado_en` datetime DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime DEFAULT NULL,
  `horario_txt` varchar(80) DEFAULT NULL,
  `edad_min` tinyint(3) unsigned DEFAULT NULL,
  `edad_max` tinyint(3) unsigned DEFAULT NULL,
  `nivel_formativo` varchar(30) DEFAULT NULL,
  `experiencia` varchar(30) DEFAULT NULL,
  `sueldo_periodo` enum('mensual','quincenal','semanal','diario','hora','convenir') DEFAULT NULL,
  `sueldo_monto` decimal(8,2) DEFAULT NULL,
  `afiche` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_empleo_slug` (`slug`),
  KEY `idx_vig` (`estado`,`disponible_hasta`),
  KEY `idx_tipo_dist` (`tipo`,`distrito_id`),
  KEY `idx_oficio` (`oficio_slug`),
  KEY `idx_cat` (`categoria_id`),
  KEY `idx_negocio` (`negocio_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_explorer_comentarios — 0 filas · 12 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_explorer_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_tipo` char(1) NOT NULL DEFAULT 't',
  `post_id` int(10) unsigned NOT NULL,
  `negocio_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned DEFAULT NULL,
  `autor` varchar(60) NOT NULL DEFAULT 'Visitante',
  `texto` text NOT NULL,
  `visitante` varchar(64) NOT NULL DEFAULT '',
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `estado` varchar(12) NOT NULL DEFAULT 'aprobado',
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_exp_com_post` (`post_tipo`,`post_id`,`id`),
  KEY `idx_exp_com_neg` (`negocio_id`,`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_explorer_likes — 5 filas · 9 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_explorer_likes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_tipo` char(1) NOT NULL DEFAULT 't',
  `post_id` int(10) unsigned NOT NULL,
  `negocio_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned DEFAULT NULL,
  `visitante` varchar(64) NOT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_exp_like` (`post_tipo`,`post_id`,`visitante`),
  KEY `idx_exp_like_post` (`post_tipo`,`post_id`),
  KEY `idx_exp_like_neg` (`negocio_id`,`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_fotos — 11 013 filas · 6 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_fotos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_id` int(10) unsigned NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 1,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_negocio_orden` (`negocio_id`,`orden`),
  CONSTRAINT `fk_foto_negocio` FOREIGN KEY (`negocio_id`) REFERENCES `directorio_negocios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11570 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_historial_busqueda — 154 filas · 6 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_historial_busqueda` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `termino` varchar(255) DEFAULT NULL,
  `categoria_id` int(10) unsigned DEFAULT NULL,
  `distrito_id` int(10) unsigned DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario_fecha` (`usuario_id`,`fecha` DESC),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_distrito` (`distrito_id`),
  KEY `idx_rec` (`usuario_id`,`categoria_id`,`distrito_id`,`fecha` DESC),
  CONSTRAINT `fk_hb_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `directorio_categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hb_distrito` FOREIGN KEY (`distrito_id`) REFERENCES `directorio_distritos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hb_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `directorio_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=198 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_historias — 145 filas · 5 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_historias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_historia_producto` (`producto_id`),
  KEY `idx_historia_orden` (`activo`,`orden`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_ia_tiendas — 20 filas · 18 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_ia_tiendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `visitante` varchar(64) DEFAULT NULL,
  `modo` varchar(20) NOT NULL DEFAULT 'nueva',
  `negocio_id` int(11) DEFAULT NULL,
  `paso` varchar(40) NOT NULL DEFAULT 'nombre',
  `estado` varchar(20) NOT NULL DEFAULT 'en_curso',
  `datos` mediumtext DEFAULT NULL,
  `chat` mediumtext DEFAULT NULL,
  `llamadas` int(11) NOT NULL DEFAULT 0,
  `tokens_in` int(11) NOT NULL DEFAULT 0,
  `tokens_out` int(11) NOT NULL DEFAULT 0,
  `cache_hit` int(11) NOT NULL DEFAULT 0,
  `costo_usd` decimal(12,6) NOT NULL DEFAULT 0.000000,
  `visto_en` datetime DEFAULT NULL,
  `creado_en` datetime NOT NULL,
  `actualizado_en` datetime NOT NULL,
  `publicado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_paso` (`paso`),
  KEY `idx_visitante` (`visitante`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_ia_tiendas_log — 179 filas · 14 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_ia_tiendas_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sesion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `paso` varchar(40) DEFAULT NULL,
  `modelo` varchar(60) DEFAULT NULL,
  `con_imagen` tinyint(1) NOT NULL DEFAULT 0,
  `ok` tinyint(1) NOT NULL DEFAULT 1,
  `error` varchar(40) DEFAULT NULL,
  `tokens_in` int(11) NOT NULL DEFAULT 0,
  `tokens_out` int(11) NOT NULL DEFAULT 0,
  `cache_hit` int(11) NOT NULL DEFAULT 0,
  `ms` int(11) NOT NULL DEFAULT 0,
  `costo_usd` decimal(12,6) NOT NULL DEFAULT 0.000000,
  `creado_en` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sesion` (`sesion_id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_fecha` (`creado_en`)
) ENGINE=InnoDB AUTO_INCREMENT=501 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_invitacion_claves — 68 filas · 5 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_invitacion_claves` (
  `negocio_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `usuario` varchar(120) NOT NULL,
  `clave` varchar(12) NOT NULL,
  `creado_en` datetime NOT NULL,
  PRIMARY KEY (`negocio_id`),
  KEY `idx_dic_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_invitaciones — 80 filas · 5 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_invitaciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_id` int(10) unsigned NOT NULL,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `canal` varchar(12) NOT NULL DEFAULT 'whatsapp',
  `enviado_en` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dinv_negocio` (`negocio_id`,`enviado_en`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_mensajes — 0 filas · 7 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_mensajes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_id` int(10) unsigned NOT NULL,
  `de_usuario_id` bigint(20) unsigned NOT NULL,
  `para_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `texto` text NOT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_negocio_fecha` (`negocio_id`,`fecha` DESC),
  KEY `idx_de` (`de_usuario_id`),
  KEY `idx_para` (`para_usuario_id`),
  KEY `idx_leido` (`leido`),
  KEY `idx_negocio_leido` (`negocio_id`,`leido`,`fecha` DESC),
  CONSTRAINT `fk_msg_de` FOREIGN KEY (`de_usuario_id`) REFERENCES `directorio_usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_negocio` FOREIGN KEY (`negocio_id`) REFERENCES `directorio_negocios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_para` FOREIGN KEY (`para_usuario_id`) REFERENCES `directorio_usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_negocio_cobertura — 325 filas · 4 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_negocio_cobertura` (
  `negocio_id` int(10) unsigned NOT NULL,
  `distrito_id` int(10) unsigned NOT NULL,
  `es_todos` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`negocio_id`,`distrito_id`),
  KEY `idx_cobertura_distrito` (`distrito_id`),
  CONSTRAINT `fk_cob_distrito` FOREIGN KEY (`distrito_id`) REFERENCES `directorio_distritos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cob_negocio` FOREIGN KEY (`negocio_id`) REFERENCES `directorio_negocios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_negocio_imagenes_ant — 314 filas · 4 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_negocio_imagenes_ant` (
  `negocio_id` int(10) unsigned NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `ruta_nueva` varchar(255) DEFAULT NULL,
  `creado_en` datetime NOT NULL,
  PRIMARY KEY (`negocio_id`),
  KEY `idx_creado` (`creado_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_negocio_pagos — 0 filas · 4 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_negocio_pagos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_id` int(10) unsigned NOT NULL,
  `pago_id` tinyint(3) unsigned NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_negocio_pago` (`negocio_id`,`pago_id`),
  KEY `idx_pago` (`pago_id`),
  CONSTRAINT `fk_np_negocio` FOREIGN KEY (`negocio_id`) REFERENCES `directorio_negocios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_np_pago` FOREIGN KEY (`pago_id`) REFERENCES `directorio_pagos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_negocio_prompts — 100 filas · 8 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_negocio_prompts` (
  `negocio_id` int(10) unsigned NOT NULL,
  `prompt` text NOT NULL,
  `origen` enum('asistente','jefe') NOT NULL DEFAULT 'asistente',
  `lote` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rubro` varchar(120) DEFAULT NULL,
  `negocio` varchar(180) DEFAULT NULL,
  `creado_en` datetime NOT NULL,
  `actualizado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`negocio_id`),
  KEY `idx_origen` (`origen`),
  KEY `idx_lote` (`lote`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_negocio_rubros — 20 filas · 4 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_negocio_rubros` (
  `negocio_id` int(10) unsigned NOT NULL,
  `categoria_id` int(10) unsigned NOT NULL,
  `orden` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL,
  PRIMARY KEY (`negocio_id`,`categoria_id`),
  KEY `idx_cat` (`categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_negocio_telefonos — 0 filas · 6 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_negocio_telefonos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_id` int(10) unsigned NOT NULL,
  `numero` varchar(40) NOT NULL,
  `tipo` enum('ambos','llamada','whatsapp','ventas','atencion','mayorista','pedidos','otros') NOT NULL DEFAULT 'ambos',
  `orden` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tel_negocio` (`negocio_id`,`orden`),
  CONSTRAINT `fk_tel_negocio` FOREIGN KEY (`negocio_id`) REFERENCES `directorio_negocios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_negocios — 5 300 filas · 42 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_negocios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(180) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `categoria_id` int(10) unsigned DEFAULT NULL,
  `subcategoria_id` int(10) unsigned DEFAULT NULL,
  `distrito_id` int(10) unsigned DEFAULT NULL,
  `zona_id` int(10) unsigned DEFAULT NULL,
  `ubicacion_tipo` enum('fisica','ambulante','nacional','mayorista','domicilio') NOT NULL DEFAULT 'fisica',
  `direccion` varchar(255) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `telefono` varchar(40) DEFAULT NULL,
  `whatsapp` varchar(40) DEFAULT NULL,
  `horario` varchar(255) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `descripcion` longtext DEFAULT NULL,
  `web` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `tiktok` varchar(255) DEFAULT NULL,
  `plantilla_id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `paleta_id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `delivery` tinyint(1) NOT NULL DEFAULT 0,
  `recojo` tinyint(1) NOT NULL DEFAULT 0,
  `anticipacion` varchar(60) DEFAULT NULL,
  `descuento` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `minimo_personas` smallint(5) unsigned NOT NULL DEFAULT 0,
  `dueno_id` bigint(20) unsigned DEFAULT NULL,
  `estado` enum('pendiente','activo','inactivo','rechazado') NOT NULL DEFAULT 'activo',
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
  `vistas_count` int(10) unsigned NOT NULL DEFAULT 0,
  `vistas_reset_fecha` date NOT NULL DEFAULT curdate(),
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ruc` varchar(20) DEFAULT NULL COMMENT 'RUC del negocio (11 dígitos), opcional',
  `email` varchar(120) DEFAULT NULL COMMENT 'Correo público del negocio (no el de la cuenta)',
  `youtube` varchar(255) DEFAULT NULL COMMENT 'Canal de YouTube',
  `twitter` varchar(255) DEFAULT NULL COMMENT 'X (Twitter)',
  `telegram` varchar(255) DEFAULT NULL COMMENT 'Telegram',
  `linkedin` varchar(255) DEFAULT NULL COMMENT 'LinkedIn',
  `revisado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_distrito` (`distrito_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_destacado` (`destacado`),
  KEY `idx_ubicacion_tipo` (`ubicacion_tipo`),
  KEY `idx_dueno` (`dueno_id`),
  KEY `idx_vistas` (`vistas_count` DESC),
  KEY `idx_coor` (`lat`,`lng`),
  KEY `fk_negocio_subcat` (`subcategoria_id`),
  KEY `fk_negocio_zona` (`zona_id`),
  KEY `fk_negocio_plantilla` (`plantilla_id`),
  KEY `fk_negocio_paleta` (`paleta_id`),
  KEY `idx_nombre_trgm` (`nombre`(60)),
  KEY `idx_filtro_portada` (`estado`,`categoria_id`,`distrito_id`,`destacado`),
  KEY `idx_ranking_vistas` (`estado`,`vistas_count` DESC,`rating` DESC),
  CONSTRAINT `fk_negocio_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `directorio_categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_negocio_distrito` FOREIGN KEY (`distrito_id`) REFERENCES `directorio_distritos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_negocio_dueno` FOREIGN KEY (`dueno_id`) REFERENCES `directorio_usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_negocio_paleta` FOREIGN KEY (`paleta_id`) REFERENCES `directorio_paletas` (`id`),
  CONSTRAINT `fk_negocio_plantilla` FOREIGN KEY (`plantilla_id`) REFERENCES `directorio_plantillas` (`id`),
  CONSTRAINT `fk_negocio_subcat` FOREIGN KEY (`subcategoria_id`) REFERENCES `directorio_subcategorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_negocio_zona` FOREIGN KEY (`zona_id`) REFERENCES `directorio_zonas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5503 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_noticias — 16 filas · 18 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_noticias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(190) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `entradilla` varchar(400) DEFAULT NULL,
  `cuerpo` mediumtext NOT NULL,
  `palabras` smallint(5) unsigned NOT NULL DEFAULT 0,
  `distrito` varchar(30) NOT NULL,
  `zona` varchar(80) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `fuente_nombre` varchar(120) DEFAULT NULL,
  `fuente_web` varchar(255) DEFAULT NULL,
  `fuente_enlace` varchar(700) DEFAULT NULL,
  `rubros` varchar(255) DEFAULT NULL,
  `huella` char(32) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'publicado',
  `vistas` int(10) unsigned NOT NULL DEFAULT 0,
  `creada_en` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_noti_slug` (`slug`),
  UNIQUE KEY `uq_noti_huella` (`huella`),
  KEY `idx_noti_fecha` (`fecha`,`hora`),
  KEY `idx_noti_distrito` (`distrito`,`fecha`),
  KEY `idx_noti_estado` (`estado`,`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_opiniones — 6 257 filas · 10 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_opiniones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_id` int(10) unsigned NOT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `autor` varchar(80) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `texto` text DEFAULT NULL,
  `respuesta` text DEFAULT NULL,
  `fuente` varchar(30) DEFAULT NULL,
  `estado` varchar(12) NOT NULL DEFAULT 'aprobada',
  PRIMARY KEY (`id`),
  KEY `idx_negocio_fecha` (`negocio_id`,`fecha` DESC),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_opiniones_estado` (`estado`),
  CONSTRAINT `fk_opi_negocio` FOREIGN KEY (`negocio_id`) REFERENCES `directorio_negocios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_opi_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `directorio_usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12953 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_opiniones_reportes — 2 filas · 10 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_opiniones_reportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opinion_id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL DEFAULT 0,
  `motivo` varchar(80) NOT NULL DEFAULT '',
  `texto` varchar(1000) NOT NULL DEFAULT '',
  `ip` varchar(45) NOT NULL DEFAULT '',
  `estado` enum('pendiente','opinion_borrada','ignorado') NOT NULL DEFAULT 'pendiente',
  `creado_en` datetime NOT NULL,
  `atendido_por` int(11) DEFAULT NULL,
  `atendido_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_opiniones_rep_estado` (`estado`,`creado_en`),
  KEY `idx_opiniones_rep_opinion` (`opinion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_pagos — 6 filas · 5 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_pagos` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `icono` varchar(10) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_paletas — 4 filas · 7 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_paletas` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `color_primario` varchar(7) NOT NULL,
  `color_acento` varchar(7) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_pedidos — 419 filas · 13 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_pedidos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned DEFAULT NULL,
  `tipo` enum('clic','consulta','pedido','llamada') NOT NULL DEFAULT 'clic',
  `origen` varchar(12) NOT NULL DEFAULT 'ficha',
  `productos_n` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `total` decimal(10,2) DEFAULT NULL,
  `descuento_pct` tinyint(3) unsigned DEFAULT NULL,
  `detalle` varchar(255) DEFAULT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `dispositivo` varchar(12) NOT NULL DEFAULT '',
  `ip` varchar(45) DEFAULT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mp_fecha` (`fecha`),
  KEY `idx_mp_neg` (`negocio_id`,`fecha`),
  KEY `idx_mp_prod` (`producto_id`,`fecha`),
  KEY `idx_mp_tipo` (`tipo`,`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=427 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_pedidos_busqueda — 202 filas · 21 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_pedidos_busqueda` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(160) NOT NULL,
  `termino` varchar(120) NOT NULL,
  `norm` varchar(120) NOT NULL,
  `tipo` enum('sin_vendedor','pocos') NOT NULL DEFAULT 'sin_vendedor',
  `resultados` tinyint(3) unsigned DEFAULT NULL,
  `distrito_id` int(10) unsigned DEFAULT NULL,
  `rubro_id` int(10) unsigned DEFAULT NULL,
  `buscado_n` int(10) unsigned NOT NULL DEFAULT 1,
  `propuestas_n` int(10) unsigned NOT NULL DEFAULT 0,
  `whatsapp` varchar(20) DEFAULT NULL,
  `aviso_comprador` varchar(160) DEFAULT NULL,
  `estado` enum('abierto','conseguido','oculto') NOT NULL DEFAULT 'abierto',
  `token` char(32) NOT NULL,
  `ip_hash` char(40) DEFAULT NULL,
  `dispositivo` varchar(12) NOT NULL DEFAULT '',
  `primera_vez` datetime NOT NULL,
  `ultima_vez` datetime NOT NULL,
  `conseguido_en` datetime DEFAULT NULL,
  `origen` varchar(12) NOT NULL DEFAULT 'buscador',
  `creado_en` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pedido_slug` (`slug`),
  KEY `idx_norm` (`norm`,`estado`),
  KEY `idx_estado` (`estado`,`ultima_vez`),
  KEY `idx_token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_pedidos_propuestas — 0 filas · 11 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_pedidos_propuestas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int(10) unsigned NOT NULL,
  `negocio_id` int(10) unsigned DEFAULT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `nombre` varchar(80) NOT NULL,
  `whatsapp` varchar(20) NOT NULL,
  `mensaje` varchar(500) DEFAULT NULL,
  `precio_txt` varchar(60) DEFAULT NULL,
  `estado` enum('visible','oculto') NOT NULL DEFAULT 'visible',
  `ip` varchar(45) DEFAULT NULL,
  `creado_en` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pedido` (`pedido_id`,`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_plantillas — 3 filas · 5 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_plantillas` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(2) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_postulantes — 7 filas · 13 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_postulantes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_real` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(40) DEFAULT NULL,
  `redes` varchar(255) DEFAULT NULL,
  `info` varchar(500) DEFAULT NULL,
  `sueldo_solicitado` varchar(20) DEFAULT NULL,
  `jornada` varchar(40) DEFAULT NULL,
  `movilidad` enum('si','no') DEFAULT NULL,
  `segundo_idioma` varchar(120) DEFAULT NULL,
  `formacion` varchar(255) DEFAULT NULL,
  `estado` enum('nuevo','en_revision','contactado','descartado') NOT NULL DEFAULT 'nuevo',
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_producto_fotos — 22 316 filas · 6 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_producto_fotos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int(10) unsigned NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_producto` (`producto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24761 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_producto_imagenes_ant — 46 filas · 4 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_producto_imagenes_ant` (
  `producto_id` int(10) unsigned NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `ruta_nueva` varchar(255) DEFAULT NULL,
  `creado_en` datetime NOT NULL,
  PRIMARY KEY (`producto_id`),
  KEY `idx_creado` (`creado_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_producto_prompts — 320 filas · 8 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_producto_prompts` (
  `producto_id` int(10) unsigned NOT NULL,
  `prompt` text NOT NULL,
  `origen` enum('asistente','jefe') NOT NULL DEFAULT 'asistente',
  `lote` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rubro` varchar(120) DEFAULT NULL,
  `negocio` varchar(180) DEFAULT NULL,
  `creado_en` datetime NOT NULL,
  `actualizado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`producto_id`),
  KEY `idx_origen` (`origen`),
  KEY `idx_lote` (`lote`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_publicaciones_fijadas — 0 filas · 6 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_publicaciones_fijadas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_id` int(10) unsigned NOT NULL,
  `tipo` enum('producto','foto','opinion') NOT NULL,
  `referencia_id` int(10) unsigned NOT NULL,
  `orden` tinyint(4) NOT NULL DEFAULT 1,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_negocio_orden` (`negocio_id`,`orden`),
  KEY `idx_negocio` (`negocio_id`),
  CONSTRAINT `fk_pf_negocio` FOREIGN KEY (`negocio_id`) REFERENCES `directorio_negocios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_reclamos — 1 filas · 11 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_reclamos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_id` int(10) unsigned NOT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(40) DEFAULT NULL,
  `explicacion` text DEFAULT NULL,
  `estado` enum('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  `atendido_por` bigint(20) unsigned DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `atendido_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_negocio` (`negocio_id`),
  KEY `idx_estado` (`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_reportes — 4 filas · 11 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_reportes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `negocio_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned DEFAULT NULL,
  `motivo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `estado` enum('pendiente','revisado','resuelto','ignorado') NOT NULL DEFAULT 'pendiente',
  `atendido_por` bigint(20) unsigned DEFAULT NULL,
  `atendido_en` datetime DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_negocio` (`negocio_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_servicios — 28 384 filas · 12 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_servicios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_id` int(10) unsigned NOT NULL,
  `titulo` varchar(180) NOT NULL,
  `tipo_producto` enum('fisico','virtual') NOT NULL DEFAULT 'fisico',
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unidad` varchar(50) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `disponible_hasta` date DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_negocio` (`negocio_id`),
  KEY `idx_activo` (`activo`),
  KEY `idx_destacado` (`destacado`),
  KEY `idx_titulo_trgm` (`titulo`(60)),
  KEY `idx_disponible` (`disponible_hasta`),
  CONSTRAINT `fk_serv_negocio` FOREIGN KEY (`negocio_id`) REFERENCES `directorio_negocios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34422 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_sesiones — 2 filas · 7 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_sesiones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token` (`token`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_ses_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `directorio_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_stats_eventos — 18 147 filas · 9 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_stats_eventos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sesion_id` bigint(20) unsigned DEFAULT NULL,
  `cookie` char(32) NOT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `tipo` varchar(10) NOT NULL DEFAULT 'pv',
  `pagina` varchar(190) NOT NULL DEFAULT '',
  `tipo_pagina` varchar(24) NOT NULL DEFAULT 'otro',
  `ref_dominio` varchar(120) NOT NULL DEFAULT '',
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ev_fecha` (`fecha`),
  KEY `idx_ev_sesion` (`sesion_id`),
  KEY `idx_ev_tipo_pagina` (`tipo_pagina`,`fecha`),
  KEY `idx_ev_pagina` (`pagina`(64)),
  KEY `idx_ev_busqueda` (`tipo`,`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=18148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_stats_sesiones — 16 166 filas · 14 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_stats_sesiones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cookie` char(32) NOT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `inicio` datetime NOT NULL,
  `ultimo_activo` datetime NOT NULL,
  `segundos` int(10) unsigned NOT NULL DEFAULT 0,
  `paginas` int(10) unsigned NOT NULL DEFAULT 0,
  `entrada` varchar(190) NOT NULL DEFAULT '',
  `salida` varchar(190) NOT NULL DEFAULT '',
  `pagina_actual` varchar(190) NOT NULL DEFAULT '',
  `ref_dominio` varchar(120) NOT NULL DEFAULT '',
  `ref_url` varchar(500) NOT NULL DEFAULT '',
  `dispositivo` varchar(12) NOT NULL DEFAULT 'desktop',
  `es_bot` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_stats_cookie` (`cookie`),
  KEY `idx_stats_usr` (`usuario_id`),
  KEY `idx_stats_ultimo` (`ultimo_activo`),
  KEY `idx_stats_inicio` (`inicio`),
  KEY `idx_stats_ref` (`ref_dominio`)
) ENGINE=InnoDB AUTO_INCREMENT=16169 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_subcategorias — 28 filas · 5 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_subcategorias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` int(10) unsigned NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cat_slug` (`categoria_id`,`slug`),
  KEY `idx_categoria` (`categoria_id`),
  CONSTRAINT `fk_subcat_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `directorio_categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_telegram_subs — 0 filas · 15 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_telegram_subs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` char(16) NOT NULL,
  `chat_id` varchar(32) DEFAULT NULL,
  `nombre` varchar(80) DEFAULT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `negocio_id` int(10) unsigned DEFAULT NULL,
  `rubro_id` int(10) unsigned DEFAULT NULL,
  `distrito_id` int(10) unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 0,
  `avisos_n` smallint(5) unsigned NOT NULL DEFAULT 0,
  `avisos_fecha` date DEFAULT NULL,
  `ultimo_pedido_id` int(10) unsigned DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `creado_en` datetime NOT NULL,
  `activado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tg_codigo` (`codigo`),
  KEY `idx_tg_chat` (`chat_id`),
  KEY `idx_tg_interes` (`activo`,`rubro_id`,`distrito_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_usuarios — 89 filas · 14 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_usuarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `tipo` enum('cliente','dueno','admin') NOT NULL DEFAULT 'cliente',
  `avatar` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_login` datetime DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `chat_alias` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`),
  UNIQUE KEY `uniq_google_id` (`google_id`),
  UNIQUE KEY `uq_chat_alias` (`chat_alias`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB AUTO_INCREMENT=224 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_vistas — 9 640 filas · 6 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_vistas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `negocio_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned DEFAULT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_negocio` (`negocio_id`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_fecha` (`fecha`),
  CONSTRAINT `fk_vis_negocio` FOREIGN KEY (`negocio_id`) REFERENCES `directorio_negocios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9648 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- TABLA: directorio_zonas — 15 filas · 4 columnas
-- ------------------------------------------------------------
CREATE TABLE `directorio_zonas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `distrito_id` int(10) unsigned NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_distrito_slug` (`distrito_id`,`slug`),
  KEY `idx_distrito` (`distrito_id`),
  CONSTRAINT `fk_zona_distrito` FOREIGN KEY (`distrito_id`) REFERENCES `directorio_distritos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------

-- ============================================================
--  VISTAS (7) — se calculan solas a partir de las tablas
-- ============================================================

-- ------------------------------------------------------------
-- VISTA: vista_alianzas_sugeridas — 500 000 filas volcadas · se calcula sola (no tiene columnas propias)
-- ------------------------------------------------------------
CREATE ALGORITHM=UNDEFINED DEFINER=`u196269909_ALDIACHIMBOTE`@`127.0.0.1` SQL SECURITY DEFINER VIEW `vista_alianzas_sugeridas` AS select `a`.`id` AS `negocio_a_id`,`a`.`nombre` AS `negocio_a_nombre`,`a`.`slug` AS `negocio_a_slug`,`b`.`id` AS `negocio_b_id`,`b`.`nombre` AS `negocio_b_nombre`,`b`.`slug` AS `negocio_b_slug`,`c`.`nombre` AS `categoria_comun`,round(6371 * acos(least(1.0,cos(radians(`a`.`lat`)) * cos(radians(`b`.`lat`)) * cos(radians(`b`.`lng`) - radians(`a`.`lng`)) + sin(radians(`a`.`lat`)) * sin(radians(`b`.`lat`)))),2) AS `distancia_km` from ((`directorio_negocios` `a` join `directorio_negocios` `b` on(`a`.`categoria_id` = `b`.`categoria_id` and `a`.`id` < `b`.`id` and `a`.`estado` = 'activo' and `b`.`estado` = 'activo' and `a`.`ubicacion_tipo` = 'fisica' and `b`.`ubicacion_tipo` = 'fisica' and `a`.`lat` is not null and `b`.`lat` is not null)) left join `directorio_categorias` `c` on(`c`.`id` = `a`.`categoria_id`)) having `distancia_km` <= 5.0 order by round(6371 * acos(least(1.0,cos(radians(`a`.`lat`)) * cos(radians(`b`.`lat`)) * cos(radians(`b`.`lng`) - radians(`a`.`lng`)) + sin(radians(`a`.`lat`)) * sin(radians(`b`.`lat`)))),2);


-- ------------------------------------------------

-- ------------------------------------------------------------
-- VISTA: vista_negocio_ficha_completa — 5 300 filas volcadas · se calcula sola (no tiene columnas propias)
-- ------------------------------------------------------------
CREATE ALGORITHM=UNDEFINED DEFINER=`u196269909_ALDIACHIMBOTE`@`127.0.0.1` SQL SECURITY DEFINER VIEW `vista_negocio_ficha_completa` AS select `n`.`id` AS `id`,`n`.`nombre` AS `nombre`,`n`.`slug` AS `slug`,`n`.`categoria_id` AS `categoria_id`,`n`.`subcategoria_id` AS `subcategoria_id`,`n`.`distrito_id` AS `distrito_id`,`n`.`zona_id` AS `zona_id`,`n`.`ubicacion_tipo` AS `ubicacion_tipo`,`n`.`direccion` AS `direccion`,`n`.`referencia` AS `referencia`,`n`.`telefono` AS `telefono`,`n`.`whatsapp` AS `whatsapp`,`n`.`horario` AS `horario`,`n`.`lat` AS `lat`,`n`.`lng` AS `lng`,`n`.`descripcion` AS `descripcion`,`n`.`web` AS `web`,`n`.`facebook` AS `facebook`,`n`.`instagram` AS `instagram`,`n`.`tiktok` AS `tiktok`,`n`.`plantilla_id` AS `plantilla_id`,`n`.`paleta_id` AS `paleta_id`,`n`.`delivery` AS `delivery`,`n`.`dueno_id` AS `dueno_id`,`n`.`estado` AS `estado`,`n`.`destacado` AS `destacado`,`n`.`rating` AS `rating`,`n`.`vistas_count` AS `vistas_count`,`n`.`vistas_reset_fecha` AS `vistas_reset_fecha`,`n`.`creado_en` AS `creado_en`,`n`.`actualizado_en` AS `actualizado_en`,`c`.`nombre` AS `categoria_nombre`,`c`.`slug` AS `categoria_slug`,`c`.`icono` AS `categoria_icono`,`c`.`color` AS `categoria_color`,`d`.`nombre` AS `distrito_nombre`,`d`.`slug` AS `distrito_slug`,`z`.`nombre` AS `zona_nombre`,`z`.`slug` AS `zona_slug`,`p`.`codigo` AS `plantilla_codigo`,`p`.`nombre` AS `plantilla_nombre`,`pa`.`codigo` AS `paleta_codigo`,`pa`.`nombre` AS `paleta_nombre`,`pa`.`color_primario` AS `color_primario`,`pa`.`color_acento` AS `color_acento`,(select count(0) from `directorio_servicios` `s` where `s`.`negocio_id` = `n`.`id` and `s`.`activo` = 1) AS `total_productos`,(select count(0) from `directorio_fotos` `f` where `f`.`negocio_id` = `n`.`id`) AS `total_fotos`,(select count(0) from `directorio_opiniones` `o` where `o`.`negocio_id` = `n`.`id`) AS `total_opiniones`,(select group_concat(concat(`pago`.`codigo`,':',`pago`.`nombre`) separator '|') from (`directorio_negocio_pagos` `np` join `directorio_pagos` `pago` on(`pago`.`id` = `np`.`pago_id`)) where `np`.`negocio_id` = `n`.`id`) AS `pagos_csv` from (((((`directorio_negocios` `n` left join `directorio_categorias` `c` on(`c`.`id` = `n`.`categoria_id`)) left join `directorio_distritos` `d` on(`d`.`id` = `n`.`distrito_id`)) left join `directorio_zonas` `z` on(`z`.`id` = `n`.`zona_id`)) left join `directorio_plantillas` `p` on(`p`.`id` = `n`.`plantilla_id`)) left join `directorio_paletas` `pa` on(`pa`.`id` = `n`.`paleta_id`));


-- ------------------------------------------------

-- ------------------------------------------------------------
-- VISTA: vista_negocios_activos — 5 299 filas volcadas · se calcula sola (no tiene columnas propias)
-- ------------------------------------------------------------
CREATE ALGORITHM=UNDEFINED DEFINER=`u196269909_ALDIACHIMBOTE`@`127.0.0.1` SQL SECURITY DEFINER VIEW `vista_negocios_activos` AS select `n`.`id` AS `id`,`n`.`nombre` AS `nombre`,`n`.`slug` AS `slug`,`n`.`direccion` AS `direccion`,`n`.`referencia` AS `referencia`,`n`.`telefono` AS `telefono`,`n`.`whatsapp` AS `whatsapp`,`n`.`horario` AS `horario`,`n`.`lat` AS `lat`,`n`.`lng` AS `lng`,`n`.`descripcion` AS `descripcion`,`n`.`web` AS `web`,`n`.`facebook` AS `facebook`,`n`.`instagram` AS `instagram`,`n`.`tiktok` AS `tiktok`,`n`.`ubicacion_tipo` AS `ubicacion_tipo`,`n`.`delivery` AS `delivery`,`n`.`rating` AS `rating`,`n`.`destacado` AS `destacado`,`n`.`vistas_count` AS `vistas_count`,`n`.`creado_en` AS `creado_en`,`c`.`id` AS `categoria_id`,`c`.`nombre` AS `categoria_nombre`,`c`.`slug` AS `categoria_slug`,`c`.`icono` AS `categoria_icono`,`d`.`id` AS `distrito_id`,`d`.`nombre` AS `distrito_nombre`,`d`.`slug` AS `distrito_slug`,`z`.`id` AS `zona_id`,`z`.`nombre` AS `zona_nombre`,`p`.`codigo` AS `plantilla_codigo`,`pa`.`codigo` AS `paleta_codigo`,`pa`.`color_primario` AS `color_primario`,`pa`.`color_acento` AS `color_acento` from (((((`directorio_negocios` `n` left join `directorio_categorias` `c` on(`c`.`id` = `n`.`categoria_id`)) left join `directorio_distritos` `d` on(`d`.`id` = `n`.`distrito_id`)) left join `directorio_zonas` `z` on(`z`.`id` = `n`.`zona_id`)) left join `directorio_plantillas` `p` on(`p`.`id` = `n`.`plantilla_id`)) left join `directorio_paletas` `pa` on(`pa`.`id` = `n`.`paleta_id`)) where `n`.`estado` = 'activo';


-- ------------------------------------------------

-- ------------------------------------------------------------
-- VISTA: vista_negocios_con_pagos — 5 299 filas volcadas · se calcula sola (no tiene columnas propias)
-- ------------------------------------------------------------
CREATE ALGORITHM=UNDEFINED DEFINER=`u196269909_ALDIACHIMBOTE`@`127.0.0.1` SQL SECURITY DEFINER VIEW `vista_negocios_con_pagos` AS select `n`.`id` AS `id`,`n`.`nombre` AS `nombre`,`n`.`slug` AS `slug`,group_concat(concat(`pago`.`codigo`,':',`pago`.`nombre`,':',ifnull(`pago`.`icono`,'')) order by `pago`.`codigo` ASC separator '|') AS `pagos_csv` from ((`directorio_negocios` `n` left join `directorio_negocio_pagos` `np` on(`np`.`negocio_id` = `n`.`id`)) left join `directorio_pagos` `pago` on(`pago`.`id` = `np`.`pago_id`)) where `n`.`estado` = 'activo' group by `n`.`id`,`n`.`nombre`,`n`.`slug`;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- VISTA: vista_negocios_populares — 5 299 filas volcadas · se calcula sola (no tiene columnas propias)
-- ------------------------------------------------------------
CREATE ALGORITHM=UNDEFINED DEFINER=`u196269909_ALDIACHIMBOTE`@`127.0.0.1` SQL SECURITY DEFINER VIEW `vista_negocios_populares` AS select `n`.`id` AS `id`,`n`.`nombre` AS `nombre`,`n`.`slug` AS `slug`,`c`.`icono` AS `categoria_icono`,`c`.`nombre` AS `categoria_nombre`,`n`.`rating` AS `rating`,`n`.`vistas_count` AS `vistas_count`,`n`.`vistas_reset_fecha` AS `vistas_reset_fecha` from (`directorio_negocios` `n` left join `directorio_categorias` `c` on(`c`.`id` = `n`.`categoria_id`)) where `n`.`estado` = 'activo' order by `n`.`vistas_count` desc,`n`.`rating` desc,`n`.`creado_en` desc;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- VISTA: vista_productos_destacados — 27 794 filas volcadas · se calcula sola (no tiene columnas propias)
-- ------------------------------------------------------------
CREATE ALGORITHM=UNDEFINED DEFINER=`u196269909_ALDIACHIMBOTE`@`127.0.0.1` SQL SECURITY DEFINER VIEW `vista_productos_destacados` AS select `s`.`id` AS `id`,`s`.`titulo` AS `titulo`,`s`.`descripcion` AS `descripcion`,`s`.`precio` AS `precio`,`s`.`unidad` AS `unidad`,`s`.`imagen` AS `imagen`,`s`.`destacado` AS `destacado`,`n`.`id` AS `negocio_id`,`n`.`nombre` AS `negocio_nombre`,`n`.`slug` AS `negocio_slug`,`c`.`id` AS `categoria_id`,`c`.`nombre` AS `categoria_nombre`,`c`.`icono` AS `categoria_icono` from ((`directorio_servicios` `s` join `directorio_negocios` `n` on(`n`.`id` = `s`.`negocio_id`)) left join `directorio_categorias` `c` on(`c`.`id` = `n`.`categoria_id`)) where `s`.`activo` = 1 and `n`.`estado` = 'activo' order by `s`.`destacado` desc,`n`.`vistas_count` desc;


-- ------------------------------------------------

-- ------------------------------------------------------------
-- VISTA: vista_recomendaciones_usuario — 3 672 filas volcadas · se calcula sola (no tiene columnas propias)
-- ------------------------------------------------------------
CREATE ALGORITHM=UNDEFINED DEFINER=`u196269909_ALDIACHIMBOTE`@`127.0.0.1` SQL SECURITY DEFINER VIEW `vista_recomendaciones_usuario` AS select `n`.`id` AS `id`,`n`.`nombre` AS `nombre`,`n`.`slug` AS `slug`,`n`.`rating` AS `rating`,`n`.`vistas_count` AS `vistas_count`,`c`.`id` AS `categoria_id`,`c`.`nombre` AS `categoria_nombre`,`c`.`icono` AS `categoria_icono`,`h`.`termino` AS `termino_buscado`,`h`.`fecha` AS `fecha_busqueda` from ((`directorio_historial_busqueda` `h` join `directorio_negocios` `n` on(`n`.`categoria_id` = `h`.`categoria_id` or `n`.`distrito_id` = `h`.`distrito_id`)) left join `directorio_categorias` `c` on(`c`.`id` = `n`.`categoria_id`)) where `n`.`estado` = 'activo' and `h`.`fecha` >= current_timestamp() - interval 7 day order by `h`.`fecha` desc,`n`.`vistas_count` desc;
