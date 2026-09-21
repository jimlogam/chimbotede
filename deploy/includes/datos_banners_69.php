<?php
/**
 * datos_banners_69.php — Los 69 banners de la campaña de necesidades (imagen -> configuración).
 * =============================================================================================
 * Fuente: D:\desorden\chimboteweb\__banners_69\MAPEO.md (transcripción con visión de cada imagen)
 * La vigencia inicial es INFINITA (fecha_fin = NULL). Se programa por banner desde el panel.
 * Las franjas son decisión de marketing (franjas de mayor consumo):
 *   24 = todo el día · manana,tarde,noche según el caso.
 * Los 7 sociales (aura) y 4 "DECIDIR" (sin rubro claro) se importan INACTIVOS (activo=0)
 * para no ensuciar la rotación; se activan desde el panel cuando tengan tema asignado.
 * rubros: vacío = libre en todo el sitio (por defecto); el panel permite restringir.
 *
 * Devuelve array de filas: [archivo, titulo, tema, franjas, activo]
 */
return [

    ['assets/uploads/banners/banner-01.webp', '¿Fiebre a medianoche?',            'farmacias',     '24',              1],
    ['assets/uploads/banners/banner-02.webp', '¿Te quedaste sin gasolina?',       'grifos',        '24',              1],
    ['assets/uploads/banners/banner-03.webp', '¿Se te apagó la cocina?',          'gas',           '24',              1],
    ['assets/uploads/banners/banner-04.webp', '¿Llanta en el suelo y apurado?',   'mecanicos',     'manana,tarde',    1],
    ['assets/uploads/banners/banner-05.webp', '¿Mueres de hambre?',               'restaurantes',  'tarde,noche',     1],
    ['assets/uploads/banners/banner-06.webp', '¿Tu engreído se rasca sin parar?', 'veterinarias',  'manana,tarde',    1],
    ['assets/uploads/banners/banner-07.webp', '¿La refri vacía otra vez?',        'supermercados', '24',              1],
    ['assets/uploads/banners/banner-08.webp', '¿Tu máquina no da más?',           'informatica',   'manana,tarde',    1],
    ['assets/uploads/banners/banner-09.webp', '¿Problemas con papeles o contratos?', 'abogados',  'manana,tarde',    1],
    ['assets/uploads/banners/banner-10.webp', '¿Vives en una caja de zapatos?',   '',              'manana,tarde',    0], // DECIDIR: melamina
    ['assets/uploads/banners/banner-11.webp', '¿Papeles que no entiendes?',       'abogados',      'manana,tarde',    1],
    ['assets/uploads/banners/banner-12.webp', '¿Sin cancha para el partido?',     'deportes',      'tarde,noche',     1],
    ['assets/uploads/banners/banner-13.webp', '¿Impresora atascada otra vez?',    'informatica',   'manana,tarde',    1],
    ['assets/uploads/banners/banner-14.webp', '¿Criar al ratón o botarlo?',       '',              'manana',          0], // DECIDIR: fumigación
    ['assets/uploads/banners/banner-15.webp', '¿Un grano te arruinó la cita?',    'farmacias',     '24',              1],
    ['assets/uploads/banners/banner-16.webp', '¿La invitaste y no te alcanza?',   'restaurantes',  'tarde,noche',     1],
    ['assets/uploads/banners/banner-17.webp', '¿Escondiendo las uñas otra vez?',  'belleza',       'tarde',           1],
    ['assets/uploads/banners/banner-18.webp', '¿Sonreír con la boca cerrada?',    'dentistas',     'manana,tarde',    1],
    ['assets/uploads/banners/banner-19.webp', '¿Llamar por teléfono te da ansiedad?', '',         'tarde',           0], // DECIDIR: bienestar
    ['assets/uploads/banners/banner-20.webp', '¿El único disfrazado en la reunión?', 'ropa',      'tarde',           1],
    ['assets/uploads/banners/banner-21.webp', '¿Comer solo te hace sudar frío?',  'restaurantes',  'tarde,noche',     1],
    ['assets/uploads/banners/banner-22.webp', '¿Te quedaste en blanco otra vez?', 'educacion',     'manana,tarde',    1],
    ['assets/uploads/banners/banner-23.webp', '¿Tapando la mancha con la mochila?', 'ropa',       'tarde',           1],
    ['assets/uploads/banners/banner-24.webp', 'Papá pidió pollo a la brasa',      'restaurantes',  'tarde,noche',     1],
    ['assets/uploads/banners/banner-25.webp', 'Estudiar en mancha en las escaleras', 'educacion', 'manana,tarde',    1],
    ['assets/uploads/banners/banner-26.webp', '¿Short en la exposición final?',   'educacion',     'manana,tarde',    1],
    ['assets/uploads/banners/banner-27.webp', '¿Te comiste las papas en el camino?', 'restaurantes', 'tarde,noche',   1],
    ['assets/uploads/banners/banner-28.webp', 'Traer la pesca fresca a casa',     'restaurantes',  'manana,tarde',    1],
    ['assets/uploads/banners/banner-29.webp', '¿Le robas el pellejito del pollo?', 'restaurantes', 'tarde,noche',     1],
    ['assets/uploads/banners/banner-30.webp', '¿Todo el colectivo huele a tu pescado?', 'restaurantes', 'tarde',      1],
    ['assets/uploads/banners/banner-31.webp', 'Ayudar a cruzar la pista a la abuela', '',         '',                0], // SOCIAL
    ['assets/uploads/banners/banner-32.webp', 'Ceder el asiento en el colectivo', '',             '',                0], // SOCIAL
    ['assets/uploads/banners/banner-33.webp', 'Dejar agua fresca para los callejeritos', 'veterinarias', 'manana,tarde', 1],
    ['assets/uploads/banners/banner-34.webp', '¿Aguantar el estornudo con la tijera cerca?', 'belleza', 'tarde',       1],
    ['assets/uploads/banners/banner-35.webp', '¿El inodoro se rebeló en casa ajena?', 'gasfiteros', 'manana,tarde',   1],
    ['assets/uploads/banners/banner-36.webp', '¿Se te vació el caldo en el camino?', 'restaurantes', 'manana',        1],
    ['assets/uploads/banners/banner-37.webp', 'Salvar a un amigo del papelón',    '',             '',                0], // SOCIAL
    ['assets/uploads/banners/banner-38.webp', 'Avisar a tiempo la llanta baja',   'mecanicos',     'manana,tarde',    1],
    ['assets/uploads/banners/banner-39.webp', 'Atrapar el vaso antes del desastre', '',           '',                0], // SOCIAL
    ['assets/uploads/banners/banner-40.webp', 'Frenar y ceder el paso al peatón', 'mecanicos',     'manana,tarde',    1],
    ['assets/uploads/banners/banner-41.webp', '¿Te besó el tubo de escape?',      'mecanicos',     'manana,tarde',    1],
    ['assets/uploads/banners/banner-42.webp', '¿Se te rompió la bolsa en la calle?', 'supermercados', '24',           1],
    ['assets/uploads/banners/banner-43.webp', '¿Te pegaron la grasa a la espalda?', 'restaurantes', 'tarde,noche',    1],
    ['assets/uploads/banners/banner-44.webp', 'La torta llegó entera y perfecta', 'restaurantes',  'tarde,noche',     1],
    ['assets/uploads/banners/banner-45.webp', 'Resolver con chaufa en diez minutos', 'restaurantes', 'tarde,noche',   1],
    ['assets/uploads/banners/banner-46.webp', 'Prender el carbón al primer intento', 'restaurantes', 'tarde,noche',   1],
    ['assets/uploads/banners/banner-47.webp', 'Cosechar tus propios ingredientes', 'supermercados', 'manana,tarde',   1],
    ['assets/uploads/banners/banner-48.webp', '¿Dijiste "yo invito" y no te alcanza?', 'restaurantes', 'tarde,noche', 1],
    ['assets/uploads/banners/banner-49.webp', 'Llegar con la fuente de ceviche',  'restaurantes',  'tarde',           1],
    ['assets/uploads/banners/banner-50.webp', '¿Short en la exposición final?',   'educacion',     'manana,tarde',    1],
    ['assets/uploads/banners/banner-51.webp', '¿No pasar el dato del delivery?',  'restaurantes',  'tarde,noche',     1],
    ['assets/uploads/banners/banner-52.webp', '¿3 días y no reparas el caño?',    'gasfiteros',    'manana,tarde',    1],
    ['assets/uploads/banners/banner-53.webp', '¿Silencio incómodo en tu primera cita?', 'restaurantes', 'tarde,noche',1],
    ['assets/uploads/banners/banner-54.webp', '¿Saliste haciendo el ridículo en TikTok?', 'informatica', 'manana,tarde', 1],
    ['assets/uploads/banners/banner-55.webp', '¿El viento te arruinó en un segundo?', '',          '',                0], // SOCIAL
    ['assets/uploads/banners/banner-56.webp', '¿Te dejaron con la mano estirada?', 'deportes',     'tarde,noche',     1],
    ['assets/uploads/banners/banner-57.webp', '¿Tarjeta rechazada frente a todos?', 'restaurantes', 'tarde,noche',   1],
    ['assets/uploads/banners/banner-58.webp', '¿Tu cadena te pintó el cuello de verde?', 'joyas',  'tarde',           1],
    ['assets/uploads/banners/banner-59.webp', '¿Tus fotos parecen de hace diez años?', 'informatica', 'manana,tarde', 1],
    ['assets/uploads/banners/banner-60.webp', '¿Cansado de vestir como NPC?',     'ropa',          'tarde',           1],
    ['assets/uploads/banners/banner-61.webp', '¿Cuándo empieza tu glow up?',      'gimnasios',     'manana,noche',    1],
    ['assets/uploads/banners/banner-62.webp', '¿Seguro que el chicle te salva?',  'dentistas',     'manana,tarde',    1],
    ['assets/uploads/banners/banner-63.webp', '¿Tu suegra llegó sin avisar?',     '',              '',                0], // SOCIAL
    ['assets/uploads/banners/banner-64.webp', '¿Otra vez saliste a "caminar"?',   'restaurantes',  'tarde,noche',     1],
    ['assets/uploads/banners/banner-65.webp', '¿Pánico de manejar en la pista?',  'manejo',        'manana,tarde',    1],
    ['assets/uploads/banners/banner-66.webp', '¿Fiesta elegante y sin traje?',    'ropa',          'tarde',           1],
    ['assets/uploads/banners/banner-67.webp', '¿Mudanza de pesadilla?',           '',              'manana,tarde',    0], // DECIDIR: mudanzas
    ['assets/uploads/banners/banner-68.webp', '¡Vende tu casa vieja sin arruinarte!', 'inmobiliarias', 'manana,tarde', 1],
    ['assets/uploads/banners/banner-69.webp', 'Soluciones mágicas... verdadera magia', '',        '',                0], // SOCIAL

];
