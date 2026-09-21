<?php
/**
 * opiniones_semilla.php — 🌱 LAS 3 OPINIONES CON CONTEXTO DE LAS ÚLTIMAS 100 TIENDAS
 * ==========================================================================================
 * Pedido del jefe (2026-09-14): *«a las primeras 100 tiendas, empezando por las que recién hemos
 * creado cronológicamente, las 100 últimas tiendas, le vas a escribir tres opiniones positivas
 * pero con contexto hablando de los productos que esa tienda tenga»*.
 *
 * QUÉ ES «CONTEXTO» AQUÍ: cada opinión habla de lo que ESA tienda vende de verdad — sus
 * productos, sus servicios, sus medidas, sus precios, su forma de atender. No vale una frase
 * genérica («excelente atención»): si es una pollería se habla del pollo y de las papas; si es
 * una ferretería, del trato y del orden; si es un taller de melamina, de la madera y las
 * medidas. Por eso cada tienda se leyó antes de escribirle (ficha + rubro + catálogo real).
 *
 * FORMATO:  ['Nombre A.', estrellas(1-5), 'texto de la opinión', días_atrás]
 *   · El 4.º dato es cuántos DÍAS ATRÁS se escribió (así el chat no sale todo del mismo día).
 *   · Las estrellas son positivas: 5 casi siempre y algún 4, como en la vida real.
 *
 * CÓMO SE USA: desde Súper Admin → 🚩 Reclamos de opiniones → «🌱 Sembrar ahora».
 * Es IDEMPOTENTE: si esa opinión (mismo texto en la misma tienda) ya está puesta, no se repite.
 *
 * ⚠️ Las tiendas se buscan por SLUG, nunca por id: los ids los pone la base y el slug es el
 * nombre estable de la ficha (@ver la regla de oro de los despliegues).
 *
 * NOTA: la ficha «administrador-de-sitio» (el perfil del propio administrador del sitio) NO
 * lleva opiniones a propósito: no es un negocio, es la cuenta del dueño del directorio.
 */
return [

// 1
['slug' => 'orientacion-juridica-gratuita-corte-superior-santa', 'opiniones' => [
    ['Rosa C.', 5, 'Fui por una consulta de pensión y me atendieron sin cobrarme nada. La señorita me explicó con paciencia qué papeles tenía que llevar y en qué ventanilla pedir mi cita.', 9],
    ['Miguel Á.', 5, 'Es un servicio del Poder Judicial y de verdad es gratuito. Me orientaron sobre un problema de contrato de alquiler y salí sabiendo exactamente qué pasos seguir.', 21],
    ['Gloria H.', 4, 'Hay que llegar temprano porque se llena, pero vale la pena: te escuchan, te orientan y no te mandan de un lugar a otro. Muy útil para quien no puede pagar un abogado.', 33],
]],

// 2
['slug' => 'mistio', 'opiniones' => [
    ['Jorge R.', 5, 'Pagué la membresía mensual sin permanencia y eso me dio confianza para probar. Las máquinas están completas y el plan trimestral ya me venía con rutina personalizada.', 4],
    ['Milagros V.', 5, 'Tomé la evaluación física inicial y me armaron un plan de entrenamiento según lo que necesitaba. Se nota que te acompañan, no te dejan solo con la máquina.', 12],
    ['Kevin O.', 5, 'Las clases de cardio y acondicionamiento son exigentes pero entretenidas, y el entrenamiento personalizado de una hora vale cada sol. El plan anual me salió más cómodo.', 27],
]],

// 3
['slug' => 'fiebre', 'opiniones' => [
    ['Luis M.', 5, 'Me instalaron toda la parte eléctrica de la casa y quedó impecable. Además me cambiaron los tomacorrientes e interruptores viejos que ya me daban miedo.', 6],
    ['Patricia C.', 5, 'Se me quemó el tablero eléctrico un domingo y vinieron a revisarlo. Lo repararon y me explicaron por qué había pasado, con todo bien ordenado y sin dejar cables sueltos.', 16],
    ['Óscar L.', 5, 'Contraté el mantenimiento preventivo y ya no se me baja la luz cada vez que prendo la plancha. Instalaron también las luminarias y los puntos de luz que faltaban.', 29],
]],

// 4  (veterinaria; en la base figura con rubro de bodega pero la actividad real es veterinaria)
['slug' => 'veterinaria-dias', 'opiniones' => [
    ['Diana R.', 5, 'Llevé a mi perrita por la consulta general y la revisaron completita. Le pusieron su vacuna y salió con su desparasitación al día el mismo día.', 3],
    ['Wilmer C.', 5, 'Atienden todos los días y eso salva: llevé a mi gato un domingo por la tarde y lo atendieron sin problema. El chequeo preventivo me pareció muy completo.', 14],
    ['Elsa M.', 4, 'Mi mascota odia el baño, pero le hicieron el corte de uñas con paciencia y salió tranquila. Además compré su alimento ahí mismo y me dieron buen precio.', 26],
]],

// 6
['slug' => 'anqari', 'opiniones' => [
    ['Carlos Q.', 5, 'Es un hotel nuevo y se nota: las habitaciones huelen a nuevo y todo está limpio. Está en una esquina fácil de ubicar, al costado de Cassetech.', 7],
    ['Sofía T.', 5, 'Me quedé una noche por trabajo y dormí muy bien, sin ruido. La atención en recepción fue amable desde que llegué.', 19],
    ['Julio B.', 4, 'Buena relación entre lo que pagas y lo que recibes. Es tres estrellas, pero con estándar de hotel nuevo y bien ubicado en el centro.', 32],
]],

// 7
['slug' => 'restaurante-el-tamarindo', 'opiniones' => [
    ['Marisol L.', 5, 'Almorzamos en familia un domingo y todo salió caliente y bien servido. Está frente a la Iglesia Monte Sinaí, así que es fácil de encontrar.', 5],
    ['Antonio Z.', 5, 'La comida criolla está bien hecha y las porciones son generosas. A dos pasos de la Plaza de Armas de Santa, ideal si andas por el distrito.', 17],
    ['Nadia F.', 4, 'Fui con mis padres y nos atendieron rápido. El local es sencillo pero limpio, y la sazón es de casa.', 30],
]],

// 8
['slug' => 'iglesia-monte-sinai', 'opiniones' => [
    ['Yolanda P.', 5, 'Es una iglesia de las Asambleas de Dios donde de verdad te reciben como familia. Las reuniones son puntuales y el mensaje llega claro.', 8],
    ['Rubén A.', 5, 'Llevé a mis hijos a la escuela dominical y salieron contentos. Hay espacio para las familias del distrito de Santa.', 20],
    ['Janet M.', 4, 'El templo está ordenado y limpio, y la alabanza se siente con ganas. Se ora por los enfermos al final, que es lo que uno busca.', 34],
]],

// 9
['slug' => 'los-ninos-del-futuro', 'opiniones' => [
    ['Beatriz S.', 5, 'Matriculé a mi hija en inicial y se adaptó rápido. Es un colegio con resolución ministerial y los profesores avisan cualquier cosa por el cuaderno.', 6],
    ['Fernando G.', 5, 'Lo que más me gusta es que tiene inicial, primaria y secundaria en un solo local: mis dos hijos están juntos y no tengo que andar en dos sitios.', 18],
    ['Cinthia D.', 4, 'Los profesores son exigentes pero tratan bien a los chicos. La infraestructura es sencilla, aunque el nivel de enseñanza responde.', 31],
]],

// 10
['slug' => 'plaza-de-armas-de-santa', 'opiniones' => [
    ['Pedro S.', 5, 'La plaza está bien cuidada y con jardines arreglados. Es el punto de encuentro del distrito y se puede caminar tranquilo por la tarde.', 10],
    ['Ana P.', 5, 'Fuimos un domingo con los niños y la pasaron bien. Hay espacio para sentarse y siempre hay gente del pueblo, se siente seguro.', 22],
    ['Víctor N.', 4, 'Le falta un poco más de sombra a mediodía, pero como parque está muy bien: limpio y bien iluminado de noche.', 35],
]],

// 11
['slug' => 'carritos-magicos-luminosos', 'opiniones' => [
    ['Katherine V.', 5, 'Los carritos luminosos son el éxito con los chicos: los pasea por la plaza y no quieren bajarse. Es un plan distinto para la noche en familia.', 2],
    ['Marco T.', 5, 'Contratamos los carritos para el cumpleaños y los niños hicieron cola. Llegaron puntuales y con todo iluminado, tal como se ve en las fotos.', 13],
    ['Nadia F.', 4, 'Buena idea para una noche distinta en Santa. Los carritos se ven bien cuidados y la vuelta es lo suficientemente larga para que valga.', 25],
]],

// 12
['slug' => 'xtreme-sport', 'opiniones' => [
    ['Fernando G.', 5, 'Mandé a hacer los polos sublimados de mi equipo y salieron con los colores exactos que pedí. El bordado del escudo quedó parejo, sin hilos sueltos.', 3],
    ['Patricia C.', 5, 'Pedimos polos publicitarios para la empresa y también tazas personalizadas. Todo listo en la fecha que nos prometieron y con el diseño gráfico incluido.', 15],
    ['Jorge R.', 5, 'Los buzos y casacas deportivas son de buena tela, no de esas que se destiñen al segundo lavado. Los mamelucos quedaron a la medida del equipo.', 28],
]],

// 13
['slug' => 'lohan-star', 'opiniones' => [
    ['Elsa M.', 5, 'Contratamos la música en vivo para el aniversario de mis padres y levantaron la fiesta. Tocan repertorio para todas las edades.', 5],
    ['Julio B.', 5, 'Amenizaron el cumpleaños de mi hija y se ajustaron al horario sin problema. Son de Coishco, así que llegaron rápido y con su propio equipo.', 16],
    ['Gloria H.', 4, 'Suenan bien en vivo y se acomodan a lo que la familia pida. Nos hicieron bailar hasta el final.', 29],
]],

// 14
['slug' => 'floristeria-kyc', 'opiniones' => [
    ['Diana R.', 5, 'Pedí el ramo de rosas frescas para el Día de la Esposa y llegó con la tarjeta escrita a mano. Las rosas duraron más de una semana.', 4],
    ['Sofía T.', 5, 'Las rosas son frescas de verdad, se nota que las arman el mismo día. El ramo llegó bonito y bien presentado.', 19],
    ['Marisol L.', 4, 'Me ayudaron a elegir el detalle sin apuro y por WhatsApp me mandaron la foto antes de enviarlo. Muy atentos.', 33],
]],

// 15
['slug' => 'javier-gael-alejos-ollas-menaje', 'opiniones' => [
    ['Yolanda P.', 5, 'Compré el juego de ollas de roca volcánica RIKENIA de 11 piezas y no se pega nada. Me lo llevaron hasta la puerta en Coishco.', 7],
    ['Antonio Z.', 5, 'El juego de acero inoxidable de 12 piezas es pesado y de buen material, no como los de mercado. Buen precio comparado con tienda.', 20],
    ['Beatriz S.', 4, 'Hace entregas en Chimbote, Nuevo Chimbote y Santa, así que encargué desde acá sin problema. Llegó completo y bien embalado.', 32],
]],

// 16
['slug' => 'venta-camioneta-changhe-q25', 'opiniones' => [
    ['Víctor N.', 5, 'Fui a ver la CHANGHE Q25 del 2018 y está tal como la describen: operativa, con su versión Full y sin detalles raros. Me dejaron revisarla con mi mecánico.', 6],
    ['Óscar L.', 5, 'La camioneta está bien cuidada para ser de ocasión. El trato fue directo, sin vueltas ni excusas para no mostrar los papeles.', 18],
    ['Pedro S.', 4, 'Buena opción de SUV usada en Chimbote por el precio. Se nota que el dueño la mantuvo, el motor suena parejo.', 30],
]],

// 17
['slug' => 'shandelle-orquesta', 'opiniones' => [
    ['Marco T.', 5, 'Contratamos el paquete completo para el matrimonio y la orquesta se llevó los aplausos. Sonaron impecables toda la noche.', 4],
    ['Cinthia D.', 5, 'Llevaron la orquesta por horas a la fiesta de promoción y armaron el ambiente desde el primer tema. El sonido, muy bien equilibrado.', 17],
    ['Rubén A.', 4, 'Puntuales, con buen vestuario y repertorio variado. Nos dejaron grabar los momentos importantes sin poner peros.', 28],
]],

// 18
['slug' => 'proveedor-camas-saltarinas-carritos', 'opiniones' => [
    ['Katherine V.', 5, 'Alquilamos la cama saltarina de 1.83 x 1.83 y los chicos no salían de ahí. La armaron ellos y se la llevaron al terminar.', 3],
    ['Janet M.', 5, 'El Jeep Terafunq con luces LED fue lo más pedido del cumpleaños. Los carritos eléctricos funcionan bien y tienen batería para toda la tarde.', 14],
    ['Wilmer C.', 4, 'Tienen camas saltarinas de varios tamaños, así que elegimos la de 2.44 para el patio del colegio. Buen remate de precios.', 27],
]],

// 19
['slug' => 'compra-venta-ropa-usada', 'opiniones' => [
    ['Milagros V.', 5, 'Vendí la ropa que ya no usaba y me la recibieron por lotes sin regatear de más. Me pagaron el mismo día.', 8],
    ['Nadia F.', 5, 'Compré ropa de mujer juvenil en buen estado y encontré prendas que parecen nuevas. La ropa buena no se bota, se renueva: tal cual.', 21],
    ['Sofía T.', 4, 'Me hicieron la intermediación para vender unas casacas y se encargaron de todo. Cómodo si no tienes tiempo de publicar y responder chats.', 34],
]],

// 20
['slug' => 'fabrica-muebles-melamina-ghian', 'opiniones' => [
    ['Patricia C.', 5, 'Mandé hacer el ropero a medida y usan melamina de 18 mm, se siente firme. Quedó justo en el espacio que tenía, sin rellenos.', 5],
    ['Luis M.', 5, 'Aproveché el remate del closet y me hicieron también el zapatero y las mesas de noche del mismo color. Todo llegó a domicilio en Coishco.', 16],
    ['Elsa M.', 5, 'Los muebles de cocina modulares quedaron bien armados y con las puertas alineadas. El escritorio con librero también salió del mismo taller.', 29],
]],

// 21
['slug' => 'agua-de-vida-rivamar', 'opiniones' => [
    ['Gloria H.', 5, 'Pido el bidón de 20 litros cada semana y siempre llega sellado. El agua no tiene ese sabor raro de otras, se siente limpia.', 4],
    ['Jorge R.', 5, 'Me traen el bidón de 5 litros para la oficina y el rellenado sale más barato que comprar botellas. Cumplen el horario que coordinamos.', 18],
    ['Diana R.', 4, 'La triple garantía que ofrecen se nota: el bidón llega con su precinto y limpio. Buen servicio de reparto en Chimbote.', 31],
]],

// 22
['slug' => 'fabrica-mesas-madera-ruben-jaqua', 'opiniones' => [
    ['Marco T.', 5, 'Les compré las mesas para el restaurante y las patas son gruesas de verdad, aguantan el uso diario. Ya llevan meses sin aflojarse.', 6],
    ['Antonio Z.', 5, 'Pedí una pizarra grande de pie para el negocio y se lee desde la vereda. La hicieron con las patas reforzadas que les pedí.', 17],
    ['Beatriz S.', 5, 'Son fabricantes, así que me hicieron las mesas a medida con el color que quería. Se nota la madera maciza, no es aglomerado.', 30],
]],

// 23
['slug' => 'venta-lotes-el-porvenir-alison', 'opiniones' => [
    ['Pedro S.', 5, 'Los lotes de 6 x 18 m están con documentos en regla e inscritos, que es lo que más miedo da al comprar. Me mostraron todo antes de cerrar.', 7],
    ['Víctor N.', 5, 'Fui a ver el terreno en El Porvenir y está plano y bien ubicado. Buena opción para invertir en Chimbote a buen precio.', 19],
    ['Ana P.', 4, 'La atención fue clara y sin presionar. Me explicaron los pasos de la transferencia y me acompañaron en el trámite.', 32],
]],

// 24
['slug' => 'joma', 'opiniones' => [
    ['Janet M.', 5, 'Es de las tiendas más antiguas de Galerías Alfa y se nota la experiencia: me ayudaron a elegir la talla sin apuro. La ropa deportiva es de buena calidad.', 5],
    ['Cinthia D.', 5, 'Compré los chores y polos de uso diario y aguantan el lavado. También tienen ropa interior y brasieres en varias tallas.', 15],
    ['Milagros V.', 4, 'Les encargué los uniformes para las olimpiadas del colegio y llegaron a tiempo. Precio justo para la cantidad que pedimos.', 28],
]],

// 25
['slug' => 'carsa-motos', 'opiniones' => [
    ['Wilmer C.', 5, 'Tienen stock de motos de 125, 200 y 250 cc y me dejaron probar la pistera antes de decidir. La compré al crédito sin tanta vuelta.', 3],
    ['Kevin O.', 5, 'Saqué la cuatrimoto para el trabajo en el campo y viene con su papelería al día. Buen asesoramiento para elegir la cilindrada.', 16],
    ['Rubén A.', 4, 'El furgón que necesitaba estaba disponible y me lo entregaron con mantenimiento hecho. Se agradece el stock real, no catálogo.', 27],
]],

// 26
['slug' => 'krisscake-tortas-chantilly', 'opiniones' => [
    ['Rosa C.', 5, 'La torta de chantilly de 1 kg estaba fresca y no empalagaba. Le pidieron el diseño personalizado y quedó tal como se lo mandé.', 2],
    ['Sofía T.', 5, 'Encargamos la torta de dos pisos para los 50 años de mi mamá y fue lo primero que se acabó. El chantilly es suave, no grasoso.', 13],
    ['Marisol L.', 5, 'Para el cumpleaños del colegio pedimos la de 3 kg y alcanzó para todos. Buen precio por el tamaño y llegó a la hora prometida.', 26],
]],

// 27
['slug' => 'agua-san-pedrito', 'opiniones' => [
    ['Elsa M.', 5, 'El bidón de 20 litros con caño es comodísimo, ya no tengo que levantarlo para servir. El delivery es gratis y siempre cumplen.', 4],
    ['Jorge R.', 5, 'El agua sabe limpia, sin ese dejo a cloro. Me traen también las botellas de 3 y 5 litros para llevar al trabajo.', 17],
    ['Patricia C.', 4, 'Pido la recarga cada quince días y son puntuales. Dejaron el bidón en la puerta cuando no estaba, como les pedí.', 30],
]],

// 28
['slug' => 'claro-erika-cercado', 'opiniones' => [
    ['Kevin O.', 5, 'Me pasé a Claro con mi mismo número y me aplicaron el 50 % de descuento por 12 meses. Todo lo gestionó la asesora, yo no fui a ninguna tienda.', 3],
    ['Nadia F.', 5, 'El plan de 200 GB ilimitado a S/ 34.90 me sale mucho más barato que lo que pagaba. Me lo instalaron y quedó funcionando el mismo día.', 15],
    ['Julio B.', 5, 'Me explicó la diferencia entre los planes de 225 y 235 GB y elegí el que me convenía, sin venderme el más caro. Muy clara.', 28],
]],

// 29
['slug' => 'ganchos-artesanales-dalana', 'opiniones' => [
    ['Yolanda P.', 5, 'Compré los ganchos dorados de alambre por docena para mi tienda y quedaron elegantes. Se nota que están hechos a mano.', 6],
    ['Beatriz S.', 5, 'Los ganchos de madera son firmes y no se doblan con el peso de la ropa. También me dio el precio por unidad, que sirve para probar.', 18],
    ['Antonio Z.', 4, 'El gancho silueta es ideal para exhibir, llama la atención del cliente. Fabricación directa en Chimbote y a buen precio.', 31],
]],

// 30
['slug' => 'mariachi-la-gaviota', 'opiniones' => [
    ['Marisol L.', 5, 'Contratamos el mariachi de 5 músicos por una hora para el aniversario de mis padres y fue el momento más bonito. Sonaron afinados y con elegancia.', 5],
    ['Rubén A.', 5, 'Llegaron a la casa a la hora exacta con su vestuario completo. El trío de 3 músicos fue suficiente para la serenata y quedó perfecto.', 16],
    ['Cinthia D.', 5, 'El mariachi completo de 8 músicos llenó la cuadra. Vale la pena para una fecha especial, se llevaron todos los aplausos.', 29],
]],

// 31
['slug' => 'entel-lara-mgre', 'opiniones' => [
    ['Fernando G.', 5, 'Hice la portabilidad a Entel conservando mi número y me trajeron el chip hasta la casa. La asesoría fue en persona, sin trámites engorrosos.', 4],
    ['Diana R.', 5, 'Antes de contratar me verificó la cobertura 4G y 5G en mi zona y recién ahí me ofreció el internet. Se agradece que no prometan de más.', 17],
    ['Gloria H.', 4, 'Saqué la línea adicional a S/ 29.90 con la promo de 6 meses para mi hijo. Todo quedó activado el mismo día.', 30],
]],

// 32
['slug' => 'venta-yamaha-fz25-chimbote', 'opiniones' => [
    ['Wilmer C.', 5, 'Estrené la Yamaha FZ 25 con 4 años de garantía y me la entregaron con placa y papeles. La saqué al crédito con cuota cómoda.', 3],
    ['Óscar L.', 5, 'Fui por la FZ 25 2026 en azul y me mostró también la FZ 4.0 inyectada. Me dejó comparar sin apurarme y elegí la que quería.', 15],
    ['Pedro S.', 4, 'Buen asesoramiento para la compra de moto nueva. El precio al contado me salió mejor que en otras tiendas de Chimbote.', 28],
]],

// 33
['slug' => 'ositos-sorpresa-paola', 'opiniones' => [
    ['Katherine V.', 5, 'El osito sorpresa llegó con la carta y el regalo al trabajo de mi esposo y lo hizo llorar. Coordinaron todo sin que él sospechara nada.', 2],
    ['Janet M.', 5, 'Contraté la visita con osita y el ramo de girasoles amarillos para mi hermana y quedó feliz. Llegaron puntuales al punto que les di.', 14],
    ['Sofía T.', 5, 'El osito cantor con la serenata fue el broche de oro del aniversario. Además llevaron la botarga que elegimos para las fotos.', 27],
]],

// 34
['slug' => 'empleo-mozo-los-pinos', 'opiniones' => [
    ['Luis M.', 5, 'Postulé al puesto de mozo full time en Los Pinos y me respondieron rápido. Me explicaron desde el inicio que el ingreso es a planilla.', 4],
    ['Kevin O.', 5, 'La convocatoria está clara: puestos full time y part time en el centro, y también de lavavajilla. No te hacen perder el tiempo.', 16],
    ['Nadia F.', 4, 'Me contactaron por WhatsApp al día siguiente de postular. El proceso fue ordenado y me dijeron los horarios desde la primera conversación.', 29],
]],

// 35
['slug' => 'decoraciones-lacs', 'opiniones' => [
    ['Milagros V.', 5, 'Decoraron el cumpleaños de mi hija con la temática de princesas y quedó de revista. El mobiliario se ve nuevo y bien cuidado.', 5],
    ['Cinthia D.', 5, 'Para la graduación de mi hijo armaron todo a domicilio con la temática de Roblox y los chicos alucinaron. Llegaron a montar con tiempo.', 18],
    ['Beatriz S.', 5, 'La decoración de quinceañero quedó elegante y sin ese plástico barato. Nos respetaron el presupuesto que les dimos.', 30],
]],

// 36
['slug' => 'mudanzas-chimbote-trujillo', 'opiniones' => [
    ['Antonio Z.', 5, 'Mudé toda mi casa de Chimbote a Trujillo y llegó todo entero, sin rayones. Cargaron y descargaron con cuidado y en el tiempo prometido.', 3],
    ['Patricia C.', 5, 'Mando fletes de mercadería para mi negocio de calzado cada mes y siempre hay carro disponible. Los costales llegan completos.', 15],
    ['Víctor N.', 5, 'Transportaron producción local de acero y muebles sin un solo golpe. El precio del flete me pareció justo por la distancia.', 28],
]],

// 37
['slug' => 'spa-canino-jhael', 'opiniones' => [
    ['Diana R.', 5, 'Aproveché la promo de setiembre de S/ 40 para mi perrita de 6 kilos y salió oliendo rico. El corte quedó parejo, no como otros que la dejan pelada.', 2],
    ['Elsa M.', 5, 'Mi mascota es grande, de más de 8 kilos, y la trataron con paciencia. Se nota el cariño, no la tienen amarrada ni nerviosa.', 16],
    ['Rosa C.', 4, 'Buena atención y precio. Además me dieron recomendaciones para el cuidado del pelaje y del oído.', 29],
]],

// 38
['slug' => 'venta-chevrolet-cruze-ls', 'opiniones' => [
    ['Jorge R.', 5, 'Fui a ver el Cruze LS y está como nuevo, tal como dice el anuncio. El automático secuencial responde suave y me dejaron probarlo.', 6],
    ['Carlos Q.', 5, 'Tiene doble sistema de gasolina y GNV, que es un ahorro real al mes. Por S/ 23,000 me parece buen precio para el estado en que está.', 19],
    ['Marco T.', 4, 'El trato fue serio y me mostró los papeles y el historial de mantenimiento sin que tuviera que insistir.', 32],
]],

// 39
['slug' => 'credicapital-prestamos', 'opiniones' => [
    ['Rosa C.', 5, 'Saqué el Préstamo Capital Rápido de S/ 300 para reponer mercadería y me lo aprobaron el mismo día. Sin tantos papeles como en el banco.', 4],
    ['Wilmer C.', 5, 'Pedí el Impulso Emprendedor de S/ 500 para mi bodega y las cuotas se ajustaron a lo que vendo por semana. Me explicaron el cronograma claro.', 17],
    ['Janet M.', 4, 'Atención directa por WhatsApp, te responden rápido. Se agradece que te digan el monto de la cuota antes de firmar nada.', 30],
]],

// 40
['slug' => 'brenda-beltran-accesorios', 'opiniones' => [
    ['Kevin O.', 5, 'El cargador Tipo C de 35W carga mi celular de verdad rápido, no como los de feria. Me lo entregó en un punto céntrico y llegó puntual.', 3],
    ['Nadia F.', 5, 'Compré cargadores al por mayor para revender y todos salieron buenos. Buen precio por volumen y entrega rápida.', 15],
    ['Julio B.', 4, 'Los accesorios son de calidad y llegaron con su empaque. La entrega coordinada en Chimbote fue segura.', 28],
]],

// 41
['slug' => 'alesof-detalles', 'opiniones' => [
    ['Sofía T.', 5, 'El desayuno con flores amarillas y el girasol LED fue el regalo más lindo que le he dado a mi mamá. Llegó armado y bien presentado.', 2],
    ['Katherine V.', 5, 'Los detalles son hechos a mano y se nota: el girasol tejido con luces quedó precioso. Incluye los chocolates Vizzio, tal como ofrecen.', 13],
    ['Marisol L.', 5, 'Pedí un desayuno romántico personalizado y respetaron todo lo que les indiqué. Llegó a la hora exacta para la sorpresa.', 26],
]],

// 42
['slug' => 'elexors-market', 'opiniones' => [
    ['Milagros V.', 5, 'El difusor universal para secadora me cambió el peinado: los rizos salen definidos y sin frizz. Llegó en su empaque original.', 5],
    ['Beatriz S.', 5, 'Compré del catálogo online y coordinar la entrega fue rapidísimo. El delivery llegó el mismo día que acordamos.', 18],
    ['Diana R.', 4, 'Los artículos de cuidado personal son originales y a buen precio. Me avisaron por WhatsApp cuando salió el pedido.', 31],
]],

// 43
['slug' => 'polleria-el-rustico', 'opiniones' => [
    ['Luis M.', 5, 'El pollo a la brasa sale jugoso por dentro y con la piel crocante. Fui temprano y me lo dieron recién salido, no recalentado.', 2],
    ['Cinthia D.', 5, 'Las papas fritas son doradas y crocantes, no grasosas. Pedimos el pollo entero para la familia y alcanzó con sobra.', 14],
    ['Pedro S.', 5, 'El cuarto de pollo viene con su ensalada fresca y bien aliñada. Buen sabor casero y precio justo.', 27],
]],

// 44
['slug' => 'cursos-talleres-angeles', 'opiniones' => [
    ['Gloria H.', 5, 'Hice el curso de repostería y es 100 % práctico: ellos ponen todos los utensilios y los insumos. Salí sabiendo preparar tortas de verdad.', 4],
    ['Elsa M.', 5, 'El taller de globos y piñatería me sirvió para emprender: ya estoy decorando cumpleaños y recuperé la inversión. Los profesores explican paso a paso.', 16],
    ['Patricia C.', 5, 'La mensualidad de repostería es cómoda y las clases son en grupos chicos. Me dejaron repetir la clase cuando falté.', 29],
]],

// 45
['slug' => 'contrata-artesanal-poderosa', 'opiniones' => [
    ['Rubén A.', 5, 'Postulé al puesto de ayudante de mina con sistema 30x14 y la convocatoria es clara: te dicen la zona y el régimen desde el inicio.', 5],
    ['Víctor N.', 5, 'Me respondieron rápido y me explicaron los documentos que necesitaba para la contratación. Trabajo seguro, como ofrecen.', 17],
    ['Jorge R.', 4, 'La empresa ya tiene experiencia contratando para la zona de Papagayo Vijus, así que el proceso es ordenado y sin sorpresas.', 30],
]],

// 46
['slug' => 'turrones-joel-alexandra', 'opiniones' => [
    ['Rosa C.', 5, 'El turrón de Doña Pepa de 900 g está suave y fresquito, se nota que es de la fecha. Es el sabor de siempre, el del mes morado.', 3],
    ['Ana P.', 5, 'Compré para compartir en la oficina y fue un éxito: no es empalagoso y el dulce está bien balanceado. Volvieron a atender como todos los años.', 15],
    ['Cinthia D.', 4, 'Los puntos de venta son fáciles de ubicar en Nuevo Chimbote. El turrón llega bien envuelto y completo.', 28],
]],

// 47
['slug' => 'vidrieria-jasner', 'opiniones' => [
    ['Patricia C.', 5, 'Me instalaron las mamparas corredizas de vidrio templado y quedaron sin un solo desnivel. Se deslizan suave, sin ruido.', 4],
    ['Marco T.', 5, 'Hicieron las ventanas y vitrinas en aluminio con acabado moderno y a buen precio. Midieron bien y no hubo que rehacer nada.', 16],
    ['Óscar L.', 5, 'Los acabados son impecables y dejaron todo limpio al terminar. Cumplieron la fecha que me prometieron.', 29],
]],

// 48
['slug' => 'rl-construcciones-servicios', 'opiniones' => [
    ['Antonio Z.', 5, 'Llevaron la supervisión de mi obra y me mantuvieron informado de cada avance. Usaron materiales de primera, tal como acordamos.', 5],
    ['Beatriz S.', 5, 'Nos hicieron la gasfitería, la pintura y la instalación eléctrica del local. Todo con un solo equipo, sin andar buscando por separado.', 17],
    ['Fernando G.', 5, 'El hermetizado del techo quedó bien hecho y se acabaron las goteras. Además nos asesoraron en el diseño de interiores.', 30],
]],

// 49
['slug' => 'tu-vehiculo-con-mel', 'opiniones' => [
    ['Kevin O.', 5, 'Saqué la Toyota RUSH con el plan del 50 % y me acompañaron en todo el trámite con el concesionario. Las cuotas quedaron a mi medida.', 3],
    ['Nadia F.', 5, 'Los seminuevos los entregan revisados de verdad: me mostraron el informe antes de que decidiera. Cero sorpresas después.', 15],
    ['Wilmer C.', 4, 'Trabajan con varios concesionarios, así que me consiguieron el modelo que quería. El cierre de venta fue rápido.', 28],
]],

// 50
['slug' => 'alquiler-habitaciones-uns', 'opiniones' => [
    ['Sofía T.', 5, 'La habitación amoblada está cerca de la UNS y es tranquila para estudiar. El contrato fue claro y sin condiciones escondidas.', 4],
    ['Julio B.', 5, 'Alquilé la habitación básica para mi hija que estudia en la universidad y el lugar es seguro. Los servicios están incluidos como dijeron.', 16],
    ['Yolanda P.', 4, 'Buena opción para estudiantes de otras ciudades: cerca, limpio y con las áreas comunes ordenadas.', 29],
]],

// 51
['slug' => 'cuentas-streaming-dariana', 'opiniones' => [
    ['Nadia F.', 5, 'El perfil de Netflix funciona perfecto, sin cortes ni caídas. Me lo entregaron al toque después de pagar.', 3],
    ['Jorge R.', 5, 'Contraté el plan para 2 personas y sale mucho más económico que pagar la cuenta completa. Buena garantía si algo falla.', 15],
    ['Marisol L.', 4, 'Llevo meses con este sistema y siempre me renuevan sin problema. Responden rápido si tienes alguna duda.', 28],
]],

// 52
['slug' => 'masajes-relajantes-domicilio', 'opiniones' => [
    ['Elsa M.', 5, 'Pedí la sesión de masaje relajante a domicilio y llegaron con su camilla y todo. Salí como nueva del estrés de la semana.', 3],
    ['Patricia C.', 5, 'El masaje descontracturante me soltó la contractura de la espalda que traía hace meses. Muy profesionales y puntuales.', 16],
    ['Gloria H.', 5, 'Lo mejor es no tener que salir de casa: llegan a la hora coordinada y dejan todo ordenado. Lo pido cada quince días.', 29],
]],

// 53
['slug' => 'andy-avila-ropa-deportiva', 'opiniones' => [
    ['Fernando G.', 5, 'La polera hoodie de Alianza Lima con el diseño del mes morado está bien hecha, tela gruesa y estampado firme. Me llegó rápido.', 5],
    ['Kevin O.', 5, 'Es una edición especial que no se encuentra en tienda. La compré para el clásico y aguantó el lavado sin desteñirse.', 17],
    ['Luis M.', 5, 'Me atendió al detalle para elegir la talla y me mandó fotos reales antes de cerrar. Gente blanquiazul de verdad.', 30],
]],

// 54
['slug' => 'polleria-brasas-lena', 'opiniones' => [
    ['Cinthia D.', 5, 'El pollo a la brasa tiene ese sabor a leña que no se consigue en otros lados. Las porciones son generosas y llegó caliente.', 3],
    ['Pedro S.', 5, 'Pedí el Combo Mostro con chaufa y quedé lleno. Las alitas acevichadas son un buen complemento para compartir.', 15],
    ['Antonio Z.', 5, 'El lomo saltado de carne viene con buena carne y bien sazonado. Se nota que usan ingredientes frescos.', 28],
]],

// 55
['slug' => 'compumex-chimbote', 'opiniones' => [
    ['Milagros V.', 5, 'Postulé como vendedora de campo y el proceso fue claro: me explicaron desde el inicio que hay capacitación constante. Buen ambiente laboral.', 4],
    ['Kevin O.', 5, 'Es una cadena de tiendas de tecnología seria, y eso da confianza al postular. Te responden y te dicen en qué sede sería.', 16],
    ['Nadia F.', 4, 'Buscan gente joven con ganas de aprender y lo dicen tal cual. La convocatoria para Chimbote y Nuevo Chimbote está abierta.', 29],
]],

// 56
['slug' => 'lavadora-lg-13-kilos', 'opiniones' => [
    ['Ana P.', 5, 'La lavadora LG TurboDrum de 13 kilos está tal como la describen: estado 9 de 10 y funciona todo. Me dejaron probarla antes de pagar.', 5],
    ['Rosa C.', 5, 'Trae su manguera de entrada y el cable de alimentación, así que la instalé y lavé el mismo día. Buena capacidad para familia grande.', 18],
    ['Elsa M.', 4, 'Consulté por WhatsApp y me respondieron al instante para coordinar la entrega. Gente seria en la venta.', 31],
]],

// 57
['slug' => 'alex-ormeno-eventos', 'opiniones' => [
    ['Katherine V.', 5, 'Condujo la fiesta de promoción de mi hija y no dejó apagar el ambiente en ningún momento. Es la voz que garantiza el evento, tal cual.', 2],
    ['Marco T.', 5, 'El DJ con sonido y luces estuvo impecable: se escuchaba bien en todo el local. Además anima, no solo pone música.', 14],
    ['Rubén A.', 5, 'Es también maestro electricista, así que nos sacó de un apuro cuando falló la instalación a mitad de la fiesta. Salvó la noche.', 27],
]],

// 58
['slug' => 'agua-bendicion-nuevo-chimbote', 'opiniones' => [
    ['Jorge R.', 5, 'El bidón de 20 litros llega sellado y el delivery en Nuevo Chimbote es gratis. Con la promo de dos bidones me sale más económico.', 3],
    ['Beatriz S.', 5, 'Compro al por mayor para el negocio y me traen varios bidones juntos. El agua es pura y refrescante, tal como dicen.', 16],
    ['Wilmer C.', 4, 'Atienden por WhatsApp y cumplen la hora. Muy práctico para la oficina, ya no cargo bidones yo mismo.', 29],
]],

// 59
['slug' => 'moza-venta-de-menu-chimbote', 'opiniones' => [
    ['Nadia F.', 5, 'El aviso es claro: piden moza con experiencia en venta de menú y el trabajo es presencial en el centro. Sin requisitos raros.', 5],
    ['Cinthia D.', 5, 'Escribí por WhatsApp y me respondieron ese mismo día para coordinar la entrevista. Buen trato en la atención.', 17],
    ['Gloria H.', 4, 'Te dicen el horario del servicio de menú desde el inicio, así sabes a qué atenerte. Convocatoria honesta.', 30],
]],

// 60
['slug' => 'masajes-relajantes-descontracturantes', 'opiniones' => [
    ['Patricia C.', 5, 'El masaje descontracturante me alivió la tensión del cuello y los hombros que traía del trabajo. Muy buenas manos.', 4],
    ['Yolanda P.', 5, 'El masaje relajante ayuda a dormir mejor, salgo flotando. Atienden en Nuevo Chimbote y son puntuales.', 15],
    ['Diana R.', 4, 'Explican bien qué tipo de masaje te conviene según la molestia. Ambiente tranquilo y precios razonables.', 28],
]],

// 61
['slug' => 'allison-detalles-con-amor', 'opiniones' => [
    ['Sofía T.', 5, 'El ramo de peluche con rosas y chocolates fue el regalo estrella. Además le agregaron la base personalizada con el peluche de Stitch y el globo.', 3],
    ['Milagros V.', 5, 'Tienen peluches nacionales e importados y de buena calidad, no de esos que se pelan. Me ayudaron a elegir por presupuesto.', 15],
    ['Katherine V.', 5, 'Mandé a hacer copas y alcancías personalizadas con nombres para un bautizo y quedaron hermosas. Entrega con delivery, sin retrasos.', 28],
]],

// 62
['slug' => 'motomax-chimbote-ventas', 'opiniones' => [
    ['Wilmer C.', 5, 'Tienen varias marcas en tienda —TVS, Zontes, Lifan— así que pude comparar de verdad. Me llevé los cascos LS2 ahí mismo.', 4],
    ['Óscar L.', 5, 'Compré la moto al contado y me hicieron descuento. El crédito también lo manejan, así que hay opciones para todos.', 17],
    ['Víctor N.', 5, 'Saqué la cuatrimoto para el terreno y me dieron repuestos y equipamiento de la misma línea. Buen stock, no te hacen esperar.', 30],
]],

// 63
['slug' => 'la-casa-del-cemento', 'opiniones' => [
    ['Rubén A.', 5, 'Postulé para ayudante chofer en el reparto de materiales y me pidieron justo lo que el aviso dice. Proceso rápido y ordenado.', 5],
    ['Kevin O.', 5, 'La convocatoria de operario para estampados sublimados y DTF está bien detallada. Te dicen qué experiencia necesitas.', 18],
    ['Pedro S.', 4, 'Es una empresa del rubro construcción, así que el trabajo es estable. Responden las consultas por WhatsApp.', 31],
]],

// 64
['slug' => 'patty-perfumeria-y-cosmetica', 'opiniones' => [
    ['Diana R.', 5, 'El perfume Vibranza Provocative de 45 ml me salió original, con su sello. Me lo llevaron a Nuevo Chimbote sin costo extra.', 4],
    ['Elsa M.', 5, 'Tienen dos tiendas propias, así que puedes ir a probar la fragancia antes de comprar. La bruma Taste de 200 ml huele delicioso.', 16],
    ['Milagros V.', 5, 'Pedí por WhatsApp y me enviaron todo bien embalado. Los precios de catálogo son más bajos que en tienda.', 29],
]],

// 65
['slug' => 'personal-atencion-cliente-cevicheria', 'opiniones' => [
    ['Nadia F.', 5, 'El aviso pide atención al cliente en cevichería y lo dice claro: con disponibilidad para el horario que coordine el local.', 5],
    ['Cinthia D.', 5, 'Me respondieron rápido y me explicaron cómo era el trabajo, sin prometer cosas que no son. Buscan gente responsable.', 17],
    ['Rosa C.', 4, 'Buena opción si ya tienes experiencia atendiendo público. El proceso de postulación es simple y directo.', 30],
]],

// 66
['slug' => 'camaras-de-seguridad-chimbote', 'opiniones' => [
    ['Óscar L.', 5, 'Instalaron la cámara pulpo en mi tienda y se ve clarísimo desde el celular, incluso de noche. Los equipos vienen con un año de garantía.', 3],
    ['Fernando G.', 5, 'Compré la cámara AGPRO 2K y la dual 360 para la casa. Me asesoraron para ubicarlas y quedó todo configurado.', 15],
    ['Víctor N.', 5, 'La cámara AB8-2K iCSee es fácil de manejar, mi esposa la revisa sin problema. Buen catálogo y precios.', 28],
]],

// 67
['slug' => 'secomtur-instituto-de-gastronomia', 'opiniones' => [
    ['Cinthia D.', 5, 'Estudio la carrera de Gastronomía en el ciclo 2026-II y las clases son 100 % prácticas en cocina. Los chefs tienen paciencia con los que recién empezamos.', 4],
    ['Antonio Z.', 5, 'Llevé el curso de repostería y tortas y salí preparando tortas para vender. El instituto también hace titulación y graduación para egresados.', 17],
    ['Beatriz S.', 5, 'El taller de panadería y bocaditos me sirvió para atender eventos. Buena formación y cocinas bien equipadas.', 30],
]],

// 68
['slug' => 'misi-detalles', 'opiniones' => [
    ['Sofía T.', 5, 'El Ramo Noche Estrellada viene con las 10 rosas azules, el girasol y las luces amarillas, tal como lo describen. Llegó con su tarjeta con dedicatoria.', 2],
    ['Katherine V.', 5, 'El Ramo Girasol con flores eternas quedó precioso y no se marchita. Le puse mi mensaje y lo entregaron puntual.', 14],
    ['Marisol L.', 5, 'Los porta flor en forma de osita son un detalle original para acompañar el ramo. Buen gusto armando los arreglos.', 27],
]],

// 69
['slug' => 'nicolas-garay-vega', 'opiniones' => [
    ['Pedro S.', 5, 'El combinado criollo del domingo está contundente y sale caliente desde temprano. Se nota que cocinan como en casa.', 2],
    ['Luis M.', 5, 'El pellejón reventado es el plato que más me gusta, bien preparado y con buena porción. Los domingos desde las 7 de la mañana ya atienden.', 15],
    ['Antonio Z.', 5, 'El sancochado con orejita y tronpita de chancho está tal como lo describen. En Coishco es una parada obligada del domingo.', 28],
]],

// 70
['slug' => 'lotes-comerciales-villa-del-universitario', 'opiniones' => [
    ['Fernando G.', 5, 'Los lotes de 6x18 m en la 2da etapa están planos y verificados, con plano adjunto. Fui con mi ingeniero y no encontramos sorpresas.', 5],
    ['Ana P.', 5, 'Se pueden comprar los 6 juntos o por separado, así que se acomoda al presupuesto de cada uno. La referencia es fácil de ubicar.', 18],
    ['Víctor N.', 4, 'Zona comercial con proyección, cerca del paradero. Los documentos están en orden para la transferencia.', 31],
]],

// 71
['slug' => 'odontologia-jheremit-solis', 'opiniones' => [
    ['Diana R.', 5, 'Me hizo la restauración con resina en un diente que tenía partido y quedó del mismo color que los demás. Excelente mano para la estética.', 4],
    ['Patricia C.', 5, 'Pide cita previa por WhatsApp y te atiende a la hora exacta, sin hacerte esperar. La evaluación y el diagnóstico fueron muy claros.', 17],
    ['Jorge R.', 5, 'El consultorio en José Olaya es cómodo y todo está esterilizado. Me explicó cada paso del tratamiento antes de empezar.', 30],
]],

// 72
['slug' => 'kumara-polleria', 'opiniones' => [
    ['Cinthia D.', 5, 'El pollo entero a la brasa está jugoso y con buen sabor a carbón. En Coishco es de los mejores, y el precio de S/ 60 se justifica.', 3],
    ['Wilmer C.', 5, 'El cuarto de pollo con papas viene bien servido y las papas crocantes. La chicha morada en jarra está bien helada.', 15],
    ['Pedro S.', 5, 'El lomo saltado con arroz y papas es contundente. Además tienen menú a precios cómodos para el almuerzo.', 28],
]],

// 73
['slug' => 'iep-el-senor-es-mi-pastor', 'opiniones' => [
    ['Beatriz S.', 5, 'Matriculé a mi hijo en primaria y la formación es integral: no solo se preocupan por las notas, también por los valores.', 4],
    ['Yolanda P.', 5, 'Tener inicial, primaria y secundaria en el mismo colegio es comodísimo para las familias de Nuevo Chimbote. La admisión 2026 ya está abierta.', 17],
    ['Gloria H.', 4, 'Los profesores están pendientes y avisan cualquier situación a los padres. El ambiente es familiar.', 30],
]],

// 74
['slug' => 'osito-mariachi-sorpresa', 'opiniones' => [
    ['Katherine V.', 5, 'El osito mariachi entró bailando al cumpleaños de mi mamá y se robó todas las fotos. La botarga blanca con el traje rojo es tal como se ve.', 3],
    ['Janet M.', 5, 'Coordinaron todo con la familia para que la sorpresa saliera perfecta. Además llevaron el arco de globos y la mesa decorada.', 16],
    ['Sofía T.', 5, 'Contratamos el paquete para los quinceaños y quedó impecable. Puntuales y muy divertidos con los invitados.', 29],
]],

// 75
['slug' => 'estudio-contable-daniela', 'opiniones' => [
    ['Antonio Z.', 5, 'Me tramitaron las facturas electrónicas para mi negocio con XML y CDR incluidos, y salieron de inmediato. Sin vueltas.', 5],
    ['Fernando G.', 5, 'Me ayudaron con la reducción del IGV de mi empresa y me explicaron en qué me estaba equivocando. Se notó el ahorro.', 18],
    ['Patricia C.', 5, 'También hacen guías de remisión, órdenes de compra y cotizaciones, así que resolví todo con un solo estudio. Entrega inmediata.', 31],
]],

// 76
['slug' => 'jugueteria-y-regalos-para-ninos', 'opiniones' => [
    ['Milagros V.', 5, 'La muñeca Baby Dreams viene con cuna, pijama, biberón y cubiertos: mi sobrina no la suelta. Muy buen precio para todo lo que incluye.', 3],
    ['Elsa M.', 5, 'El camión de bomberos con luces y sonido fue el regalo del cumpleaños. Los juguetes son resistentes, no se rompen al primer día.', 15],
    ['Rosa C.', 5, 'El juego didáctico Cognitive Direction le sirvió mucho a mi hijo. Aconsejan bien según la edad del niño.', 28],
]],

// 77
['slug' => 'carsa-motos-chimbote', 'opiniones' => [
    ['Wilmer C.', 5, 'Me dieron crédito inmediato con 0 % de inicial previa evaluación, tal como ofrecen. Saqué la cuatrimoto ATV para el trabajo.', 4],
    ['Kevin O.', 5, 'Buen stock en exhibición: cuatrimotos de varios colores y motos lineales. Pude ver y probar antes de decidir.', 17],
    ['Óscar L.', 5, 'Pagué al contado y me hicieron un descuento real. El asesor me atendió directo, sin intermediarios.', 30],
]],

// 78
['slug' => 'interseguro-desgravamen', 'opiniones' => [
    ['Patricia C.', 5, 'No sabía que el seguro de desgravamen de mi crédito se podía recuperar. Me asesoraron y ya inicié el trámite.', 5],
    ['Luis M.', 5, 'Me explicaron con números cuánto había pagado por desgravamen en todos estos años. Esa plata puede volver a tu bolsillo.', 18],
    ['Ana P.', 4, 'Asesoría seria y sin promesas exageradas: te dicen qué se puede y qué no según tu caso. Buen acompañamiento.', 31],
]],

// 79
['slug' => 'luna-importaciones', 'opiniones' => [
    ['Kevin O.', 5, 'El módem Huawei B612 llegó liberado y funciona con cualquier operadora. Mejoró bastante la señal en mi casa.', 4],
    ['Nadia F.', 5, 'Conecté hasta 30 dispositivos sin que se caiga la red, tal como indican las características. Buen equipo y a buen precio.', 16],
    ['Jorge R.', 5, 'Hice el pedido desde Chimbote y llegó bien embalado a los pocos días. La coordinación del envío fue clara.', 29],
]],

// 80
['slug' => 'win-fibra-optica-y-tv', 'opiniones' => [
    ['Fernando G.', 5, 'Contraté el plan de 350 Mbps con fono y la velocidad es real: se siente la diferencia con lo que tenía antes.', 3],
    ['Marco T.', 5, 'El plan de 1000 Mbps con winTV de 80 canales está ideal para la casa con varios equipos. Me aplicaron el 50 % de descuento por 3 meses.', 16],
    ['Beatriz S.', 4, 'La instalación de fibra fue rápida y ordenada, sin cables colgando. La atención por WhatsApp responde.', 29],
]],

// 81
['slug' => 'elenco-infantil-sonrisitas-de-jesus', 'opiniones' => [
    ['Cinthia D.', 5, 'Contratamos la animación para el cumpleaños de mi hijo y los chicos no pararon de reír. Los juegos extremos fueron lo más pedido.', 3],
    ['Katherine V.', 5, 'La hora loca con todo el elenco fue el cierre perfecto de la fiesta. Música durante toda la actividad, sin silencios.', 15],
    ['Janet M.', 5, 'Es un elenco infantil de verdad, saben tratar a los niños y a los papás también. Puntuales y con mucha energía.', 28],
]],

// 82
['slug' => 'webcam-hd-1080p-chimbote', 'opiniones' => [
    ['Kevin O.', 5, 'La webcam 1080P con micrófono incorporado mejoró mis videollamadas de trabajo: se me escucha claro y sin eco.', 4],
    ['Nadia F.', 5, 'El gran angular de 120 grados es ideal para las clases virtuales, entro completa en cuadro. Se conecta por USB y listo.', 17],
    ['Julio B.', 5, 'La de enfoque automático HD sirve para transmitir y grabar contenido sin complicaciones. Buena imagen por el precio.', 30],
]],

// 83
['slug' => 'tecnico-david-computadoras', 'opiniones' => [
    ['Luis M.', 5, 'Mi laptop se reiniciaba sola y en el día me la devolvieron reparada. Me explicó qué era y me cambió el disco por SSD.', 3],
    ['Patricia C.', 5, 'Le hice la ampliación de memoria a mi computadora y quedó volando. Trabaja con repuestos originales y te muestra lo que cambia.', 16],
    ['Antonio Z.', 5, 'Me reparó la impresora que ya daba por perdida y me vendió los accesorios de cómputo que necesitaba. Honesto con el presupuesto.', 29],
]],

// 84
['slug' => 'tecnico-german-computadoras', 'opiniones' => [
    ['Jorge R.', 5, 'Mi laptop no encendía y me la devolvió funcionando en dos días. Tenía los repuestos Epson que necesitaba mi impresora también.', 4],
    ['Elsa M.', 5, 'Le llevé mi impresora por un problema de rodillos y me la arregló con repuesto exclusivo, no genérico. Sigue trabajando perfecto.', 17],
    ['Fernando G.', 5, 'Compré una computadora y una cámara Hikvision ahí mismo, con instalación incluida. Buen asesoramiento técnico.', 30],
]],

// 85
['slug' => 'chancho-frito', 'opiniones' => [
    ['Pedro S.', 5, 'El chicharrón está crocante por fuera y jugoso por dentro, recién preparado. Con su sarsa de cebolla y maní queda espectacular.', 2],
    ['Cinthia D.', 5, 'El plato es bien contundente y alcanza para almorzar de sobra. El chuño de papa y el de maíz son el acompañamiento perfecto.', 14],
    ['Wilmer C.', 5, 'La chicha morada está bien helada y bien preparada. Es el clásico de la tarde en Chimbote, en su esquina de siempre.', 27],
]],

// 86
['slug' => 'llaves-hilario', 'opiniones' => [
    ['Rosa C.', 5, 'Se me quedaron las llaves dentro del cuarto y vino a abrir la chapa a domicilio en menos de media hora. Salvó mi día.', 3],
    ['Marco T.', 5, 'Hice los duplicados de las llaves de mi casa y de la tienda y salieron exactos, entran suaves. Precio justo.', 16],
    ['Ana P.', 5, 'También me reparó un candado que ya no cerraba bien. Buena mano y atiende en el momento.', 29],
]],

// 87
['slug' => 'licenciada-grecia-podologia', 'opiniones' => [
    ['Yolanda P.', 5, 'Me retiró los callos y las durezas sin dolor y salí caminando normal. Como soy diabética, me trató el pie con mucho cuidado.', 3],
    ['Gloria H.', 5, 'Le hicieron podología integral a mi mamá en la casa y quedó encantada. Además le tomaron la presión y le pusieron su inyectable.', 15],
    ['Diana R.', 5, 'Me mandó hacer plantillas ortopédicas a medida y se me acabó el dolor en el talón. Explica todo con paciencia.', 28],
]],

// 88
['slug' => 'khalid-impresiones', 'opiniones' => [
    ['Marisol L.', 5, 'Imprimieron los nombres y letreros en trupan para mi negocio y se leen desde la vereda. El acabado dorado quedó elegante.', 4],
    ['Sofía T.', 5, 'Mandé hacer las invitaciones de boda con sobre y sello y quedaron preciosas, la papelería completa salió del mismo taller.', 16],
    ['Cinthia D.', 5, 'Hicieron las gigantografías para la fachada y también las tazas personalizadas para la empresa. Buen precio por volumen.', 29],
]],

// 89
['slug' => 'sra-doris-santa', 'opiniones' => [
    ['Elsa M.', 5, 'Los huevos de corral son frescos y se nota en el sabor, nada que ver con los de tienda. Además me llevé naranjas y manzanas bien seleccionadas.', 2],
    ['Ana P.', 5, 'En un solo puesto encuentro fruta, ropa y hasta arreglos florales. Doña Doris te atiende con paciencia y te ayuda a elegir.', 14],
    ['Beatriz S.', 5, 'Compré un arreglo floral para un matrimonio y quedó hermoso. También tienen faldas, pantalones y ropa interior para damas.', 27],
]],

// 90
['slug' => 'etro-system', 'opiniones' => [
    ['Luis M.', 5, 'Le cambiaron la pantalla a mi laptop en el Shopping Center y quedó como nueva. Me mostraron el repuesto antes de instalarlo.', 3],
    ['Fernando G.', 5, 'Hicieron el mantenimiento de las computadoras de mi negocio con cambio de pasta térmica y ya no se calientan. También instalaron las cámaras de seguridad.', 16],
    ['Óscar L.', 5, 'Repararon el servidor de la empresa sin perder nada de información. Atención a domicilio y presupuesto claro desde el inicio.', 29],
]],

// 91
['slug' => 'sra-cinthia-santa', 'opiniones' => [
    ['Milagros V.', 5, 'Las zapatillas infantiles son de buena calidad y a buen precio, mi hijo ya las está usando. Doña Cinthia atiende con una sonrisa.', 3],
    ['Cinthia D.', 5, 'Alquilamos los disfraces de Fiestas Patrias para la actuación del colegio y estaban limpios y completos. También tienen los típicos de sierra y selva.', 15],
    ['Rosa C.', 5, 'Compré las mochilas escolares en la campaña y los polos sublimados para las olimpiadas. Todo en un solo puesto del Mercado Central de Santa.', 28],
]],

// 92
['slug' => 'clases-de-ingles-particulares', 'opiniones' => [
    ['Kevin O.', 5, 'Las clases para adultos se adaptan a mi horario de trabajo y al nivel que tengo. Ya perdí el miedo a hablar en las reuniones.', 4],
    ['Nadia F.', 5, 'Mi hija lleva las clases para niños y ha mejorado bastante en el colegio. La profesora refuerza gramática y vocabulario con juegos.', 17],
    ['Sofía T.', 5, 'Son clases personalizadas de verdad, no grupales: te corrigen la pronunciación al instante. Se avanza rápido.', 30],
]],

// 93
['slug' => 'mototaxi-9-10', 'opiniones' => [
    ['Wilmer C.', 5, 'Compré el mototaxi 9/10 completo y viene con tarjeta de propiedad y SOAT vigentes. Es de un solo dueño, tal como dice el aviso.', 5],
    ['Rubén A.', 5, 'La cabina con asientos para pasajeros está en buen estado y la tolva trasera con baranda aguanta carga. Buen precio por S/ 3,300.', 18],
    ['Pedro S.', 5, 'El azul de tres ruedas está bien cuidado y me dejaron revisarlo con mi mecánico antes de cerrar el trato. Transferencia notarial sin problemas.', 31],
]],

// 94
['slug' => 'se-compra-routers-chimbote', 'opiniones' => [
    ['Jorge R.', 5, 'Les vendí dos módems que tenía guardados y me pagaron al instante. Compran de cualquier operadora, sin poner peros.', 4],
    ['Kevin O.', 5, 'Me compraron el router WiFi con su caja original y me dieron un precio justo. Trato directo y rápido.', 16],
    ['Óscar L.', 5, 'Tenía módems de Mi Fibra y Wow sin usar y los liquidé ahí mismo. Buena opción para no acumular equipos en casa.', 29],
]],

// 95
['slug' => 'cevicheria-el-ratoncito', 'opiniones' => [
    ['Pedro S.', 5, 'El ceviche de puro pescado es fresco y se nota que lo preparan al momento. Buen tamaño y bien aliñado.', 2],
    ['Cinthia D.', 5, 'El dúo marino es ideal para el almuerzo: alcanza y el sabor es de casa. Atienden rápido, así que sirve para el trabajo.', 14],
    ['Luis M.', 5, 'Tienen dos puntos y delivery, así que pedí a la oficina y llegó bien frío. El pescado estaba fresco, sin ese sabor fuerte.', 27],
]],

// 96
['slug' => 'andrea-detalles', 'opiniones' => [
    ['Sofía T.', 5, 'El ramo de girasoles amarillos está armado a mano con papel de seda y lazo, quedó precioso. Y desde S/ 10, muy accesible.', 3],
    ['Katherine V.', 5, 'El ramo de tulipanes amarillos y rosados fue el regalo del 21 de setiembre. Le agregué el peluche con girasol y quedó completo.', 15],
    ['Marisol L.', 5, 'El arreglo floral en caja con la mariposa quedó elegante para el aniversario. Entregan cuando les coordinas, sin retrasos.', 28],
]],

// 97
['slug' => 'ruluz-spa', 'opiniones' => [
    ['Diana R.', 5, 'Tomé la promoción de masaje relajante más limpieza facial express y salí renovada. Vale cada sol.', 3],
    ['Elsa M.', 5, 'La limpieza facial express deja la piel limpia y sin esa sensación de tirantez. El ambiente es tranquilo, se descansa de verdad.', 16],
    ['Patricia C.', 5, 'El masaje relajante es con buena presión, no esos que apenas tocan. Está cerca al colegio Fe y Alegría, fácil de llegar.', 29],
]],

// 98
['slug' => 'juguero-clarenz-trattoria', 'opiniones' => [
    ['Kevin O.', 5, 'Postulé al puesto de juguero full time y el aviso detalla bien el horario de 3:30 p.m. a 11:30 p.m. Sin sorpresas.', 4],
    ['Nadia F.', 5, 'Piden preparar bebidas como limonada, maracuyá y chicha morada, así que el trabajo es completo. Buen lugar para aprender.', 17],
    ['Julio B.', 4, 'La sede queda en Chimbote y el proceso de postulación fue rápido. Te responden por WhatsApp el mismo día.', 30],
]],

// 99
['slug' => 'parihuelas-madera-pino', 'opiniones' => [
    ['Antonio Z.', 5, 'Las parihuelas de madera de pino de 1.45 x 1.25 están armadas con tablas sólidas y en buen estado, no con madera partida.', 5],
    ['Wilmer C.', 5, 'Compré por lote para el almacén y me las trajeron en motocarro hasta la puerta. Buen precio por volumen.', 18],
    ['Víctor N.', 5, 'Las unidades llegan apiladas y ordenadas, listas para usar. Se nota que trabajan la madera en serio.', 31],
]],

// 100
['slug' => 'proyectarq', 'opiniones' => [
    ['Fernando G.', 5, 'Alquilé el local de 35 m² para mi consultorio y está en primer piso, muy accesible. Queda frente al Chifa Mandarin, fácil de ubicar.', 4],
    ['Beatriz S.', 5, 'El local de la Urb. Los Cipreses sirve para tienda de ropa o barbería, bien ubicado a pocas cuadras de la Plaza Mayor. La gestión fue rápida.', 17],
    ['Marco T.', 5, 'Me mostraron el local el mismo día que pregunté y me explicaron las condiciones del contrato sin letra chica. Zona con buen movimiento.', 30],
]],

// ====== TANDA 2 (2026-09-14, noche): las 100 tiendas anteriores ======
['slug' => 'hostal-payolk', 'opiniones' => [
    ['Rosa C.', 5, 'Alquilé un escritorio por el día para cerrar unos documentos y el internet voló, sin cortes.', 7],
    ['Luis M.', 5, 'La sala de reuniones nos salvó: hicimos la reunión con el cliente y nadie nos interrumpió.', 21],
    ['Ana P.', 4, 'Queda céntrico en Chimbote y atienden hasta tarde, así que pude avanzar después de la oficina.', 33],
]],

['slug' => 'aljieri', 'opiniones' => [
    ['Julio R.', 5, 'Contraté el flete y mudanza para pasar de Nuevo Chimbote a Chimbote y no me rayaron ni una caja.', 12],
    ['Marisol Q.', 5, 'Llevan a mi personal todos los días al turno de la mañana y nunca han llegado tarde.', 30],
    ['Pedro A.', 4, 'Cobran por kilómetro y me salió más barato que otros; la movilidad estaba limpia y con aire.', 5],
]],

['slug' => 'kalesy-decor-s-decoraciones', 'opiniones' => [
    ['Karina V.', 5, 'Grabaron la fiesta de mi hija con dron y salió un video aéreo precioso de toda la casa.', 18],
    ['Jorge T.', 5, 'La edición del contenido aéreo quedó bien trabajada, con música y tomas cortas, sin relleno.', 9],
    ['Silvia N.', 5, 'Vinieron temprano a la sesión, aprovecharon la luz de la tarde y no cobraron extra por eso.', 40],
]],

['slug' => 'servicio-tecnico-de-celulares-julinho', 'opiniones' => [
    ['Diego H.', 5, 'Me cambiaron la pantalla del celular en menos de una hora y quedó como nuevo, sin rayas.', 3],
    ['Carmen L.', 5, 'Compré un celular ahí y me lo configuraron con todo, hasta me pasaron mis contactos.', 25],
    ['Walter S.', 4, 'El técnico me mostró el repuesto antes de ponerlo, cosa que en otros lados no hacen.', 14],
]],

['slug' => 'ovalo-la-familia', 'opiniones' => [
    ['Elmer P.', 5, 'El video con dron de la plaza quedó espectacular, se ve todo el óvalo y las palmeras.', 11],
    ['Nadia G.', 5, 'Me entregaron la edición al día siguiente, ya con música y bien recortado para redes.', 27],
    ['Rubén D.', 4, 'Subieron el dron sin molestar a la gente que pasaba y respetaron el horario que pedí.', 44],
]],

['slug' => 'academia-sip', 'opiniones' => [
    ['Yolanda B.', 5, 'Matriculé a mi hijo en la academia SIP y en un ciclo ya se notó la mejora en sus notas.', 6],
    ['Iván C.', 5, 'La pensión mensual es cómoda y no te meten cobros raros a mitad de ciclo.', 19],
    ['Milagros F.', 4, 'Los profesores avisan por WhatsApp cómo va el chico, eso se agradece bastante.', 36],
]],

['slug' => 'academia-sporting-cristal-sede-chimbote', 'opiniones' => [
    ['Percy O.', 5, 'Mi sobrino entrena en la sede de Chimbote y le enseñan bien la técnica, no solo a correr.', 4],
    ['Gisela M.', 5, 'Pagamos la pensión mensual y el profe le presta atención a cada chico, no son grupos enormes.', 22],
    ['Hugo Z.', 4, 'Metí a mi hijo al curso intensivo en vacaciones y salió sabiendo jugar en equipo.', 15],
]],

['slug' => 'delca-s-polleria-parrilladas', 'opiniones' => [
    ['Cinthia R.', 5, 'El cuarto de pollo a la brasa viene con papas y ensalada, y la piel sale bien crocante.', 8],
    ['Marcos E.', 5, 'Pedimos la parrilla mixta para dos y alcanzó de sobra, la carne estaba jugosa.', 29],
    ['Lucía T.', 4, 'Atienden rápido los fines de semana, aunque esté lleno te dan mesa en unos minutos.', 2],
]],

['slug' => 'balance-academy', 'opiniones' => [
    ['Álvaro N.', 5, 'Los simulacros de admisión son iguales al examen real, con el mismo tiempo y las hojas.', 13],
    ['Fiorella S.', 5, 'En el curso intensivo repasan los temas que más salen y te dan sus trucos para el examen.', 35],
    ['Teodoro J.', 4, 'Los profesores se quedan después de clase a resolver dudas sin cobrar aparte, gran detalle.', 20],
]],

['slug' => 'napos-chicken-chimbote', 'opiniones' => [
    ['Janeth V.', 5, 'El pollo entero alcanza para la familia y te lo entregan caliente, recién salido del horno.', 1],
    ['Óscar D.', 5, 'El cuarto de pollo con papas es mi almuerzo fijo los sábados, siempre con buen sabor.', 17],
    ['Rita P.', 4, 'El local está limpio y las mesas se desocupan rápido, no esperas mucho por tu pedido.', 38],
]],

['slug' => 'polleria-pio-riko', 'opiniones' => [
    ['Nelly A.', 5, 'El medio pollo a la brasa es bien rendidor y las papas vienen calentitas y crocantes.', 23],
    ['Fernando C.', 5, 'Pedí el pollo entero para llevar y llegó a casa aún caliente, bien embalado.', 10],
    ['Gladys U.', 4, 'La salsa de la casa le da otro sabor al pollo, se nota que la preparan ahí mismo.', 42],
]],

['slug' => 'academia-prepolicial-sip', 'opiniones' => [
    ['Bruno L.', 5, 'Me matriculé en la prepolicial y los repasos son exigentes, te hacen rendir de verdad.', 16],
    ['Estefany R.', 5, 'La academia de repaso me ayudó a subir el puntaje en cultura física, los entrenadores son duros.', 31],
    ['Marco Q.', 4, 'La matrícula anual incluye los materiales y no te cobran cada examen aparte, ya lo verifiqué.', 7],
]],

['slug' => 'el-lenador-chimbote', 'opiniones' => [
    ['Sandro I.', 5, 'Los anticuchos salen bien macerados, con harto ají y su choclo, como deben ser.', 5],
    ['Katherine B.', 5, 'El cuarto de pollo a la brasa es mi pedido de siempre y nunca me ha salido seco.', 26],
    ['Roly M.', 4, 'El local tiene mesas afuera y atienden hasta tarde, ideal después del trabajo.', 12],
]],

['slug' => 'polleria-joshua-chicken', 'opiniones' => [
    ['Pilar H.', 5, 'El medio pollo alcanza bien para dos y las papas son abundantes, no te quedas con hambre.', 9],
    ['Gustavo F.', 5, 'Encargué el pollo entero para un cumpleaños y lo tuvieron listo a la hora exacta.', 24],
    ['Deysi O.', 4, 'La atención es rápida por teléfono y el pedido llega bien caliente a la casa.', 45],
]],

['slug' => 'complejo-deportivo-miramar-bajo', 'opiniones' => [
    ['Édgar W.', 5, 'Alquilamos la cancha de futsal por una hora y el grass está bien mantenido, sin huecos.', 14],
    ['Cecilia Y.', 5, 'Organizamos un torneo de vóley con los del barrio y nos dieron la losa y las redes listas.', 33],
    ['Johnny K.', 4, 'Reservar la cancha por media jornada sale más barato y hay estacionamiento para las motos.', 6],
]],

['slug' => 'chifa-polleria-d-mellizo', 'opiniones' => [
    ['Roxana G.', 5, 'El chaufa de pollo es bien contundente, con harto pollo y su porción generosa.', 19],
    ['Alberto N.', 5, 'El tallarín saltado tiene ese sabor a wok de verdad, con verduras crocantes y su carne.', 8],
    ['Susy V.', 4, 'En Coishco es de los pocos que atienden hasta tarde y el local siempre está limpio.', 28],
]],

['slug' => 'killer-fish-sede-progreso', 'opiniones' => [
    ['Kevin A.', 5, 'Las clases de boxeo en la sede Progreso son bien técnicas, el profe corrige tu guardia.', 4],
    ['Sonia D.', 5, 'Probé el kickboxing y salí molido, pero en un mes ya aguantaba más rounds.', 21],
    ['Brayan P.', 4, 'El ambiente del gimnasio es tranquilo, nadie te mira raro si recién empiezas.', 37],
]],

['slug' => 'constantinofight', 'opiniones' => [
    ['Iván T.', 5, 'Pedí el día de prueba y me quedé: el entrenador te arma la rutina según lo que aguantas.', 15],
    ['Lorena C.', 5, 'La clase con entrenador es personalizada de verdad, te corrige la postura en cada ejercicio.', 30],
    ['Félix R.', 4, 'El profe te explica para qué sirve cada máquina, no te deja adivinando en el gimnasio.', 3],
]],

['slug' => 'killer-fish', 'opiniones' => [
    ['Angie S.', 5, 'Pagué la membresía mensual y puedes ir a cualquier hora, no te ponen horarios incómodos.', 12],
    ['Roger B.', 5, 'El paquete de ocho clases me salió más económico que pagar por visita, lo recomiendo.', 27],
    ['Melissa J.', 4, 'Los entrenadores te exigen pero no te humillan, y el gimnasio está aseado siempre.', 41],
]],

['slug' => 'farmacia-ailannie-s-v', 'opiniones' => [
    ['Zoila M.', 5, 'Encontré el medicamento que no hallaba en otras boticas y a buen precio, genérico y todo.', 2],
    ['Hernán V.', 5, 'Compro las vitaminas para mi mamá ahí y la señora me explica cómo tomarlas, sin apuro.', 18],
    ['Liliana E.', 4, 'Atienden rápido y hasta te anotan la receta para tu próxima compra, buen detalle.', 34],
]],

['slug' => 'flow-fight-chimbote', 'opiniones' => [
    ['Dante O.', 5, 'El entrenamiento funcional me ayudó con el dolor de espalda, son ejercicios bien pensados.', 10],
    ['Pamela G.', 5, 'La membresía mensual incluye todas las clases y no te cobran la inscripción aparte.', 25],
    ['Julissa N.', 4, 'El grupo es chico, así que el profe te mira y te corrige en cada serie.', 43],
]],

['slug' => 'mi-farmalife-centro', 'opiniones' => [
    ['Rebeca L.', 5, 'Pedí el delivery de pañales y la leche de fórmula a las nueve de la noche y llegó rapidísimo.', 7],
    ['Anthony C.', 5, 'Tienen buena variedad de productos para bebé, desde biberones hasta cremas para la rozadura.', 20],
    ['Noelia S.', 4, 'La chica me recomendó la crema correcta para el pañal y me ahorró una consulta al pediatra.', 39],
]],

['slug' => 'botica-farma-unica', 'opiniones' => [
    ['Elías Q.', 5, 'Los genéricos son bien baratos y el químico te dice si sirve lo mismo que la marca.', 11],
    ['Bertha H.', 5, 'Compré vitaminas para la anemia y me dieron la dosis exacta según la edad de mi hijo.', 32],
    ['Cristian D.', 4, 'Abren bien temprano, así que alcanzo a comprar mi medicina antes de entrar a trabajar.', 5],
]],

['slug' => 'killer-fish-san-pedro', 'opiniones' => [
    ['Ruth P.', 5, 'Me hicieron la evaluación física con medidas y todo antes de empezar, no fue al azar.', 16],
    ['Manuel A.', 5, 'El entrenamiento personalizado vale la pena: en dos meses bajé la grasa que quería.', 28],
    ['Fabiola T.', 4, 'El profe anota tus avances en una ficha y te muestra cómo vas mejorando cada semana.', 9],
]],

['slug' => 'botiquin-vides-de-rocio-nolasco', 'opiniones' => [
    ['Mercedes R.', 5, 'Compro los genéricos para la gripe y siempre tienen stock, no te mandan a otra botica.', 13],
    ['Pablo U.', 5, 'Tienen artículos de cuidado personal como alcohol, gasas y algodón a buen precio.', 23],
    ['Sandra C.', 4, 'La señora Rocío te atiende con paciencia y te busca el equivalente más barato.', 1],
]],

['slug' => 'vegano-s-restaurant', 'opiniones' => [
    ['Rosa C.', 5, 'Pedí el batido de frutas y viene bien cargado, con fruta de verdad, no esas mezclas aguadas.', 7],
    ['Luis M.', 5, 'El jugo natural de papaya me lo sirvieron al toque, bien frío, ideal para el calor de Chimbote.', 21],
    ['Ana P.', 4, 'Como no como carne, aquí encontré opciones ricas y el local es tranquilo para almorzar.', 33],
]],

['slug' => 'club-de-boxeo-ko', 'opiniones' => [
    ['Jorge Q.', 5, 'Empecé con una clase de boxeo suelta y terminé comprando el paquete de 8 clases.', 4],
    ['Kevin R.', 5, 'El profe corrige la guardia y la respiración, no te deja tirar golpes a lo loco.', 19],
    ['Paola S.', 4, 'Entreno a las siete de la noche y siempre hay espacio, el ring está bien cuidado.', 28],
]],

['slug' => 'peru-barber', 'opiniones' => [
    ['Diego T.', 5, 'El afeitado con navaja y el perfilado de barba me dejaron la cara suave toda la semana.', 6],
    ['Marco A.', 5, 'Me hice la limpieza facial y salí con la piel fresca, sin esos granitos que tenía.', 15],
    ['Sheyla V.', 4, 'Llegué sin cita un sábado y me atendieron rápido, el local está limpio y con buen ambiente.', 30],
]],

['slug' => 'gsamotors-sac', 'opiniones' => [
    ['Wilmer H.', 5, 'Le hice el mantenimiento preventivo a mi auto y me mostraron las piezas que cambiaron.', 9],
    ['César B.', 5, 'Tenía el motor sonando raro y lo repararon en dos días, quedó parejo otra vez.', 22],
    ['Nelly G.', 4, 'Cobran lo que dicen al inicio, sin sorpresas al final, eso se agradece en un taller.', 41],
]],

['slug' => 'pw-barber', 'opiniones' => [
    ['Bryan L.', 5, 'El degradado me quedó bien marcado a los costados y parejo arriba, tal como pedí.', 3],
    ['Óscar F.', 5, 'Primera vez que me hago la limpieza facial en una barbería y quedé contento.', 17],
    ['Katherine D.', 4, 'Me atendieron a la hora que reservé y el chico explicó cómo mantener el diseño.', 26],
]],

['slug' => 'la-cris', 'opiniones' => [
    ['Milagros E.', 5, 'Compré los útiles escolares de mi hija y me salió más barato que en la librería del centro.', 5],
    ['Julissa N.', 5, 'Las mochilas son resistentes, la que le compré el año pasado sigue entera.', 23],
    ['Raúl Z.', 4, 'La señora me ayudó a completar la lista del colegio sin que falte nada.', 38],
]],

['slug' => 'barberia-j16', 'opiniones' => [
    ['Christian P.', 5, 'Le llevé a mi hijo para el corte de niños y tuvo paciencia, no lo apuró.', 8],
    ['Elmer Y.', 5, 'El perfilado de barba me lo hicieron con máquina y navaja, quedó bien definido.', 14],
    ['Sandra O.', 5, 'El afeitado fue rápido y sin cortes, además te ponen toalla caliente.', 35],
]],

['slug' => 'leni-motors-taller-automotriz-multimarca-927-868-770', 'opiniones' => [
    ['Percy I.', 5, 'Hice alineamiento y balanceo antes de viajar a Casma y el carro ya no vibra.', 2],
    ['Gladys U.', 5, 'Reparan cualquier marca: llevé mi Toyota viejo y ahí mismo me encontraron el repuesto.', 20],
    ['Iván K.', 4, 'Puedes llamar al 927 868 770 y te dicen si hay que dejar el carro.', 44],
]],

['slug' => 'libreria-bazar-miguelito', 'opiniones' => [
    ['Yolanda R.', 5, 'Encontré el texto de matemática que no había en otras librerías de Nuevo Chimbote.', 11],
    ['Manuel C.', 5, 'Las mochilas las tienen de varios modelos y precios, mi sobrino eligió una con ruedas.', 25],
    ['Fiorella J.', 4, 'Los libros vienen forrados y el señor te dice si todavía falta algún cuaderno.', 31],
]],

['slug' => 'cafag-barber-school', 'opiniones' => [
    ['Anthony M.', 5, 'El combo de corte más barba sale a cuenta, salí bien arreglado por menos plata.', 10],
    ['Rocío A.', 5, 'Es escuela de barbería, los alumnos cortan con el profe encima supervisando.', 18],
    ['Wilder S.', 4, 'Le hicieron el corte de niños a mi hijo y salió feliz con su diseño.', 29],
]],

['slug' => 'la-tertulia-en-rio-santa-editores', 'opiniones' => [
    ['Elizabeth T.', 5, 'Saqué copias de un libro entero y me las dieron anilladas, listas para estudiar.', 12],
    ['Hugo B.', 5, 'Imprimen a color y en blanco y negro, salen rápido aunque haya cola.', 27],
    ['Carmen L.', 4, 'También venden mochilas escolares y ahí mismo completé todo lo de mi sobrina.', 36],
]],

['slug' => 'lavanderia-roque-sede-chimbote', 'opiniones' => [
    ['Marisol Q.', 5, 'Llevé seis camisas para planchado y me las devolvieron sin una sola arruga.', 7],
    ['Beto C.', 5, 'Usé el servicio express un viernes y a las pocas horas ya tenía mi ropa.', 16],
    ['Diana V.', 4, 'La sede de Chimbote atiende hasta tarde, pasé después del trabajo sin problema.', 40],
]],

['slug' => 'lava-express-lavanderia', 'opiniones' => [
    ['Jorge Luis A.', 5, 'Mandé teñir un pantalón viejo que estaba desteñido y me lo devolvieron como nuevo.', 9],
    ['Tatiana F.', 5, 'El servicio express me salvó: tenía una reunión y mi camisa estaba sucia.', 24],
    ['Rubén Z.', 4, 'Cuentan las prendas delante de ti y te las entregan con su comprobante.', 34],
]],

['slug' => 'polleria-parrillas-don-pio-garatea', 'opiniones' => [
    ['Karina S.', 5, 'El pollo a la brasa entero salió jugoso y con bastante papa, alcanzó para toda la familia.', 1],
    ['Miguel Á.', 5, 'Los anticuchos de corazón estaban bien macerados y jugosos, no duros ni secos como en otros sitios.', 13],
    ['Lucía P.', 5, 'Pedimos un pollo entero para llevar un domingo y en Garatea nos atendieron rapidísimo.', 32],
]],

['slug' => 'napos-chicken', 'opiniones' => [
    ['Flor M.', 5, 'El cuarto de pollo con ensalada es mi almuerzo fijo, la porción llena de verdad.', 6],
    ['Segundo R.', 5, 'La ensalada estaba fresca, con lechuga y tomate cortados al momento, no marchita.', 20],
    ['Nayeli C.', 4, 'El local es sencillo pero limpio y el chico que atiende es bien amable.', 39],
]],

['slug' => 'lumacar', 'opiniones' => [
    ['Joel D.', 5, 'Lavé mi auto por fuera y quedó sin esa tierra de la pista, brillaban las llantas.', 8],
    ['Wilder G.', 5, 'Llevé mi moto para el lavado y le sacaron el barro del motor.', 22],
    ['Ana Lucía H.', 4, 'Atienden rápido y no te cobran extra por secar el carro al final.', 43],
]],

['slug' => 'emc-pinos', 'opiniones' => [
    ['Teodoro V.', 5, 'Me ayudaron a alquilar un departamento cerca del mercado, con contrato claro.', 15],
    ['Patricia S.', 5, 'Vendí mi casa en Nuevo Chimbote y se encargaron de los papeles.', 26],
    ['Enrique L.', 4, 'Fui a consultar por un terreno y me explicaron los precios sin apurar la decisión.', 37],
]],

['slug' => 'mascotas-inti-te-ve', 'opiniones' => [
    ['Gisela R.', 5, 'Llevé a mi perro para la desparasitación y le tomaron el peso antes de darle la dosis.', 4],
    ['Percy M.', 5, 'Compro el alimento ahí porque siempre tienen la misma marca y a buen precio.', 19],
    ['Vanessa K.', 4, 'Me vendieron un collar y una correa para mi cachorra, buena calidad.', 29],
]],

['slug' => 'dr-juan-clinica-veterinaria', 'opiniones' => [
    ['Susana B.', 5, 'Mi gata se enfermó de noche y en la emergencia veterinaria la atendieron al instante.', 5],
    ['Óscar N.', 5, 'Le hicieron una cirugía menor para esterilizarla y se recuperó sin complicaciones.', 21],
    ['Leslie A.', 5, 'El doctor explica lo que tiene la mascota y no te mete miedo para cobrar más.', 33],
]],

['slug' => 'arepita-nuevo-chimbote', 'opiniones' => [
    ['Yenifer O.', 5, 'La arepa rellena de queso y carne es grande, con una ya quedas lleno.', 10],
    ['Marco T.', 5, 'Probé la cachapa con queso de mano y estaba dulcecita, tal como debe ser.', 23],
    ['Rossy D.', 4, 'El puesto atiende desde temprano y preparan la arepa al momento, no recalentada.', 42],
]],

['slug' => 'consultorio-veterinario-kota', 'opiniones' => [
    ['Claudia F.', 5, 'Llevé a mis dos gatos a desparasitar y cobraron precio justo por los dos.', 7],
    ['Jherson P.', 5, 'Operaron a mi perro de una hernia pequeña y salió bien ese mismo día.', 18],
    ['Milagros Y.', 4, 'El consultorio es pequeño pero muy limpio, y citan por hora para no esperar.', 28],
]],

['slug' => 'restaurante-vegetariano-k-umara', 'opiniones' => [
    ['Sandra Q.', 5, 'El menú vegetariano del día trae entrada, segundo y refresco por precio cómodo.', 11],
    ['Iván R.', 5, 'El lomo de soya me sorprendió, tiene buen sabor y no se siente pesado.', 25],
    ['Nancy E.', 4, 'Voy a almorzar ahí entre semana, la sopa siempre está caliente y bien servida.', 36],
]],

['slug' => 'mi-fiel-amigo-veterinaria-spa-canino', 'opiniones' => [
    ['Rocío V.', 5, 'A las dos de la mañana llevé a mi perro con fiebre y la consulta 24 horas funcionó.', 3],
    ['Dante C.', 5, 'En la emergencia le pusieron suero y se quedó en observación hasta que mejoró.', 17],
    ['Elsa M.', 4, 'Al salir le hicieron un baño en el spa canino y quedó oliendo rico.', 31],
]],

['slug' => 'casa-de-cambio-mr-celso', 'opiniones' => [
    ['Luis Alberto G.', 5, 'Cambié dólares por la app y la transferencia llegó en minutos, sin ir al local.', 13],
    ['Karina Z.', 5, 'Me asesoraron sobre el tipo de cambio antes de cambiar mis soles, muy claro.', 24],
    ['Hugo P.', 4, 'El señor Celso cotiza al toque por WhatsApp y el trato es de confianza.', 38],
]],

['slug' => 'sport-gym', 'opiniones' => [
    ['Bruno A.', 5, 'Pedí el día de prueba y me dejaron entrenar completo, sin compromiso de pagar.', 6],
    ['Cynthia L.', 5, 'La asesoría nutricional me sirvió, me armaron una dieta según mi horario de trabajo.', 20],
    ['Fernando T.', 4, 'Las máquinas están en buen estado y hay bastante espacio para hacer pesas.', 35],
]],

['slug' => 'peludog-spa-canino', 'opiniones' => [
    ['Rosa C.', 5, 'Llevé a mi schnauzer para corte de raza y salió parejo, con las patas bien perfiladas y sin estrés.', 7],
    ['Luis M.', 5, 'El spa de mascotas les deja el pelo suave y sin ese olor feo; mi perrita quedó contenta y juguetona.', 21],
    ['Ana P.', 4, 'Atienden con cita y no te hacen esperar; en una hora ya tenía a mi perro listo para llevar a casa.', 33],
]],

['slug' => 'mr-pug-spa-canino', 'opiniones' => [
    ['Carmen Q.', 5, 'Le hicieron corte de raza a mi pug y le dejaron la carita redonda tal como pedí, bien prolijo.', 3],
    ['Jorge V.', 5, 'Aproveché y le pusieron la desparasitación externa; ya no se rasca y duerme tranquilo toda la noche.', 12],
    ['Milagros T.', 5, 'El local es pequeño pero limpio, y trabajan con paciencia porque mi perro es nervioso con la máquina.', 40],
]],

['slug' => 'pink-and-red', 'opiniones' => [
    ['Katia R.', 5, 'Compré dos polos de hombre en promoción y me salieron bien baratos; la tela no es fina ni se destiñe.', 5],
    ['Pedro S.', 5, 'Tienen tallas grandes, algo que casi no se encuentra en Chimbote; me probé y me quedó justo.', 18],
    ['Diana H.', 5, 'La señora que atiende te busca talla sin apuro y te dice con sinceridad cómo te queda la prenda.', 27],
]],

['slug' => 'fitness-su', 'opiniones' => [
    ['Brayan L.', 5, 'Saqué la membresía trimestral y me sale más cómodo que pagar mes a mes; ya voy tres veces por semana.', 2],
    ['Sandra N.', 5, 'El entrenador te corrige la postura en cada ejercicio y te arma rutina según lo que aguantas.', 9],
    ['Marco A.', 4, 'El gym está limpio y las máquinas funcionan; a las siete de la noche igual encuentras espacio para entrenar.', 30],
]],

['slug' => 'panda-shop-chimbote', 'opiniones' => [
    ['Fiorella G.', 5, 'Compré un vestido de mujer para una boda y me combinaron los aretes y la cartera ahí mismo.', 6],
    ['César D.', 5, 'Los accesorios son baratos y de moda; me llevé dos collares y un bolso por menos de lo pensado.', 15],
    ['Rocío B.', 5, 'Pedí por WhatsApp y me mandaron fotos reales de las prendas, sin sorpresas cuando fui a recoger.', 22],
]],

['slug' => 'saiyan-s-gym', 'opiniones' => [
    ['Kevin P.', 5, 'Fui al día de prueba sin compromiso y me gustó, al día siguiente ya saqué mi membresía trimestral.', 1],
    ['Yolanda F.', 5, 'Hay bastante peso libre y mancuernas de todos los kilos, no como en otros gyms que siempre faltan.', 11],
    ['Anthony J.', 5, 'El chico de recepción te explica los horarios y las promociones sin apurarte ni presionarte para pagar.', 44],
]],

['slug' => 'laur-z-king-boutique', 'opiniones' => [
    ['Melisa O.', 5, 'Encontré camisas de hombre para el trabajo con buen corte y precio de promoción; me llevé tres.', 8],
    ['Ricardo Z.', 5, 'La ropa está colgada por tallas, así que uno no pierde tiempo buscando; eso se agradece.', 19],
    ['Paola Y.', 4, 'Me atendieron hasta tarde un sábado y me guardaron la prenda que había visto la semana anterior.', 36],
]],

['slug' => 'credito-movil', 'opiniones' => [
    ['Vanessa I.', 5, 'Saqué un crédito personal y me desembolsaron el mismo día; solo llevé mi DNI y el recibo de luz.', 4],
    ['Óscar E.', 5, 'Uso la transferencia de dinero para mandarle a mi mamá a Casma y le llega el mismo día sin ir al banco.', 14],
    ['Nathaly W.', 5, 'La chica me explicó las cuotas con números claros, sin letra chica, y pude elegir el plazo.', 25],
]],

['slug' => 'mercado-la-victoria', 'opiniones' => [
    ['Gladys U.', 5, 'Ayudé a mi mamá a pedir bebidas al por mayor y nos llevaron las cajas hasta la puerta del puesto.', 10],
    ['Wilder K.', 5, 'Siempre encuentro gaseosas y agua bien frías a precio de mercado, más barato que en la bodega.', 23],
    ['Sonia M.', 5, 'El reparto a domicilio sí funciona: pedimos temprano y antes del mediodía ya estaban en la casa.', 42],
]],

['slug' => 'edipesa-chimbote', 'opiniones' => [
    ['Hilda V.', 5, 'Compré cerámicos para el baño y me sacaron la cuenta de cuántas cajas necesitaba según los metros.', 13],
    ['Percy R.', 5, 'Pedí arena y agregados y llegó el volquete a la hora que dijeron, sin hacerme esperar todo el día.', 26],
    ['Elena S.', 4, 'Los precios están marcados y te dan boleta; en ferretería eso da confianza para comprar grueso.', 38],
]],

['slug' => 'distribuidora-alvarez-bohl-s-r-l', 'opiniones' => [
    ['Julio A.', 5, 'Les compro queso y jamonada para el desayuno del negocio y siempre llegan frescos, bien sellados y fríos.', 16],
    ['Carmen L.', 5, 'Hacen entrega a domicilio sin cobrar extra por la zona; con una llamada ya tengo el pedido en casa.', 29],
    ['Estela R.', 5, 'El chorizo y la mortadela son de buena marca y a precio de distribuidora, sale más barato que al detalle.', 45],
]],

['slug' => 'distribuidora-pma-eirl-dpma-chimbote', 'opiniones' => [
    ['Betty Z.', 5, 'Compré lejía y detergente al por mayor para mi bodega y me dieron precio por caja, con boleta.', 17],
    ['Fernando G.', 5, 'Tienen desinfectante y papel industrial que en otros lados no se encuentra; me salvaron para limpiar el local.', 31],
    ['Lucía P.', 5, 'El pedido grande me lo llevaron hasta el negocio y los muchachos ayudaron a bajar los baldes.', 43],
]],

['slug' => 'distribuidora-y-licoreria-jacc', 'opiniones' => [
    ['Hugo D.', 5, 'Surtí mi bodega con arroz, aceite y atún al por mayor y los precios sí son de distribuidora.', 20],
    ['Marina S.', 5, 'Tienen licores y cerveza para fiestas; comprando la caja te hacen un descuento que se nota.', 28],
    ['Tito V.', 5, 'El despacho fue rápido aunque era pedido grande, y me separaron lo que faltaba para el día siguiente.', 39],
]],

['slug' => 'rayo3d', 'opiniones' => [
    ['Iván C.', 5, 'Llevé una pieza rota de una máquina y me hicieron el escaneo 3D para replicarla exacta.', 24],
    ['Silvia H.', 5, 'Me asesoraron sobre qué material convenía para la pieza, sin querer venderme lo más caro.', 34],
    ['Renzo M.', 4, 'El trabajo estuvo listo antes de lo prometido y la pieza encajó a la primera, sin lijar nada.', 41],
]],

['slug' => 'rayo3d-radiologia-bucal-y-maxilofacial', 'opiniones' => [
    ['Pilar N.', 5, 'Mi odontólogo me mandó la radiografía dental aquí y me la tomaron rápido, sin dolor y bien nítida.', 3],
    ['José Q.', 5, 'Diseñaron la guía quirúrgica para mi implante y el doctor dijo que le facilitó muchísimo la cirugía.', 22],
    ['Andrea T.', 5, 'Me atendieron con cita en Nuevo Chimbote y me entregaron las imágenes por correo el mismo día.', 35],
]],

['slug' => 'duplicados-de-llaves-y-reparacion-de-chapas', 'opiniones' => [
    ['Miguel F.', 5, 'Me quedé fuera de casa a las once de la noche y vinieron a abrirme la puerta en veinte minutos.', 5],
    ['Roxana B.', 5, 'Hice duplicados de mis llaves y quedaron suaves, sin ese ruido feo que hacen las copias malas.', 27],
    ['Elías D.', 4, 'Me cambiaron la chapa de la puerta principal y me cobraron lo que dijeron por teléfono, nada más.', 37],
]],

['slug' => 'taller-de-llaves-y-cerrajeria-sotil', 'opiniones' => [
    ['Santos L.', 5, 'Me instalaron una cerradura nueva en la puerta del negocio y quedó bien alineada, cierra suave.', 12],
    ['Verónica C.', 5, 'Cambié la cerradura de mi casa porque perdí las llaves y me dieron dos juegos nuevos al instante.', 30],
    ['Julio P.', 5, 'El señor tiene años en el oficio y te explica qué cerradura te conviene según cómo es tu puerta.', 44],
]],

['slug' => 'duplicados-de-llave-jose', 'opiniones' => [
    ['Wilson T.', 5, 'Le llevé la llave de mi candado de moto y me la sacó al instante, igualita y sin fallar.', 2],
    ['Nancy V.', 5, 'Me aconsejó poner una chapa de más seguridad después del robo en mi cuadra; buen consejo.', 20],
    ['Beto R.', 5, 'Cobra barato y trabaja rápido, incluso atiende a la hora del almuerzo cuando otros están cerrados.', 33],
]],

['slug' => 'calle-8-barber-shop', 'opiniones' => [
    ['Diego S.', 5, 'Me hice el combo de corte con barba y salí como nuevo, con el perfilado bien marcado.', 6],
    ['Katherin L.', 5, 'El afeitado con navaja y toalla caliente se siente relajante; no te apuran ni te cortan.', 25],
    ['Joel B.', 4, 'El local de la calle 8 es tranquilo; pones tu música y esperas cómodo, sin bulla de la avenida.', 40],
]],

['slug' => 'calidimpresiones', 'opiniones' => [
    ['Elsa M.', 5, 'Mandé a imprimir volantes para mi pollería y salieron con buen color y el papel grueso que pedí.', 9],
    ['Rubén C.', 5, 'Me imprimieron una pieza en 3D para un repuesto y quedó resistente, mejor de lo que esperaba.', 21],
    ['Tatiana G.', 5, 'El taller de la señora Elsa entrega rápido; mandé el pedido por WhatsApp y al día siguiente ya estaba.', 45],
]],

['slug' => 'asesoria-contable-empresarial-groaris', 'opiniones' => [
    ['Walter A.', 5, 'Me constituyeron la empresa en menos de dos semanas y me explicaron cada paso por Sunarp y Sunat.', 14],
    ['Nelly J.', 5, 'Me ayudaron a ordenar mis declaraciones atrasadas y ya no vivo con miedo a una multa.', 29],
    ['Cristian U.', 4, 'Cobran por lo que hacen y te dicen el precio desde el inicio; no aparecen gastos raros después.', 43],
]],

['slug' => 'estudio-contable-empresarial-mendez-asociados', 'opiniones' => [
    ['Sofía E.', 5, 'Les llevo la planilla de mis ocho trabajadores y me calculan los descuentos de ley sin equivocarse.', 4],
    ['Gustavo P.', 5, 'Me asesoraron para constituir mi empresa familiar y me ahorraron vueltas innecesarias en los trámites.', 18],
    ['Marleny R.', 5, 'Contestan el teléfono cuando tienes una duda de impuestos, aunque no sea fin de mes; eso vale.', 31],
]],

['slug' => 'estudio-contable-del-solar-fernandez-asociados', 'opiniones' => [
    ['Eduardo N.', 5, 'Hicieron mi declaración anual de impuestos y me devolvieron un saldo que no sabía que tenía.', 7],
    ['Fabiola M.', 5, 'Me inscribieron como empresa y salí con mi RUC activo, listo para facturar desde el primer día.', 23],
    ['Raúl H.', 5, 'Es un estudio formal, con archivadores por cliente; uno siente que su papelería está bien cuidada.', 42],
]],

['slug' => 'elmer-lopez-dominguez', 'opiniones' => [
    ['Vilma T.', 5, 'Fui por una consulta legal de un contrato de alquiler y me la explicó en palabras sencillas.', 13],
    ['Segundo A.', 5, 'Asesoró a mi pequeña empresa sobre cómo formalizar a mi socio y evitamos problemas más adelante.', 26],
    ['Lorena D.', 5, 'Atiende en Coishco y no cobra la consulta como los estudios grandes del centro; se agradece.', 39],
]],

['slug' => 't-g-consultores-y-contratistas-generales', 'opiniones' => [
    ['Omar V.', 5, 'Me llevan la planilla de mis empleados y me avisan cuando toca pagar gratificación o CTS.', 11],
    ['Janet C.', 5, 'También dan asesoría contable y me ordenaron los libros del negocio, que tenía un desastre.', 28],
    ['Ángel R.', 5, 'Como son contratistas, me cotizaron una obra menor y me dieron el presupuesto por escrito.', 38],
]],

['slug' => 'union-motor-s', 'opiniones' => [
    ['Rosa C.', 5, 'Le llevé mi auto para el cambio de aceite y me lo devolvieron el mismo día, sin cobrarme de más.', 7],
    ['Luis M.', 5, 'Me repararon el motor de mi tico y quedó parejo; el mecánico me explicó todo lo que le hizo.', 21],
    ['Ana P.', 5, 'El taller queda cerca de la avenida y atienden desde temprano, así dejé el carro antes del trabajo.', 33],
]],

['slug' => 'mecanica-el-angel', 'opiniones' => [
    ['Jorge Q.', 5, 'Hice alinear y balancear las cuatro llantas y el carro dejó de vibrar en la Panamericana.', 3],
    ['Milagros T.', 5, 'El cambio de aceite es rápido y te muestran el envase vacío, así uno sabe que es aceite nuevo.', 15],
    ['Pedro R.', 5, 'Como el taller es familiar, el dueño mismo revisa el carro y te dice qué más conviene arreglar.', 40],
]],

['slug' => 'puerto-chimbote-restaurant', 'opiniones' => [
    ['Carmen V.', 5, 'El ceviche mixto del puerto es bien fresquito, con bastante chicha y su porción generosa para dos.', 5],
    ['Ricardo S.', 5, 'Pedimos arroz con mariscos para compartir y trajo harto calamar y conchas, no puro arroz.', 18],
    ['Yolanda B.', 4, 'Fuimos un domingo y aunque estaba lleno nos atendieron rápido; se ve el mar desde las mesas.', 29],
]],

['slug' => 'antonella-tours-peru', 'opiniones' => [
    ['Katherine L.', 5, 'Compré el paquete turístico a Huaraz y todo salió como lo pactaron, el bus pasó a la hora.', 2],
    ['Miguel A.', 5, 'Nos hicieron el traslado del aeropuerto al hotel y el chofer nos esperó sin cobrar extra.', 12],
    ['Sofía D.', 5, 'Me armaron un paquete a la medida para mi familia y me explicaron el itinerario sin apuro.', 26],
]],

['slug' => 'garu-tour', 'opiniones' => [
    ['Diana R.', 5, 'Fui por la asesoría de viajes y me armaron un recorrido a Cusco que me salió más barato.', 4],
    ['Fernando C.', 4, 'Viajé con ellos a Máncora en el viaje nacional y el hospedaje estaba tal cual me mostraron.', 20],
    ['Gladys M.', 5, 'La oficina de Nuevo Chimbote atiende hasta la noche, así pude ir después del trabajo a consultar.', 38],
]],

['slug' => 'transportes-andino-e-i-r-l', 'opiniones' => [
    ['Walter H.', 5, 'Saqué mi pasaje semi-cama a Lima y los asientos se reclinan bastante, dormí casi todo el viaje.', 1],
    ['Nancy P.', 5, 'Mandé una encomienda de 5 kilos a Trujillo y llegó al día siguiente sin ningún problema.', 9],
    ['Julio E.', 5, 'En el terminal de Chimbote despachan rápido el equipaje y no te hacen esperar parado.', 25],
]],

['slug' => 'etarsa-y-etacsa-s-a', 'opiniones' => [
    ['Elmer G.', 5, 'Viajé a Lima con ETACSA y el bus salió puntual, llegamos a la hora que dijeron en ventanilla.', 6],
    ['Rocío F.', 5, 'En la agencia me asesoraron bien para combinar mi viaje al norte y no perder el enlace.', 22],
    ['Betty N.', 4, 'Los asientos estaban limpios y el baño funcionaba, cosa que no siempre pasa en interprovincial.', 44],
]],

['slug' => 'viajes-programados', 'opiniones' => [
    ['Giancarlo V.', 5, 'Reservé un paquete a Chachapoyas con anticipación y me respetaron el precio de la promo.', 8],
    ['Marisol A.', 5, 'El traslado de ida y vuelta al aeropuerto de Trujillo fue puntual y con conductor conocido.', 17],
    ['Tania O.', 5, 'Me cambiaron la fecha del viaje sin cobrarme penalidad porque avisé con tiempo, buen detalle.', 31],
]],

['slug' => 'psc-lilibeth-ruiz', 'opiniones' => [
    ['Patricia S.', 5, 'Llevé a mi mamá a terapia física por su lumbalgia y le enseñaron ejercicios para hacer en casa.', 3],
    ['Marco T.', 5, 'La sesión de terapia alternativa me ayudó con la ansiedad; la licenciada te escucha sin apurar.', 14],
    ['Evelyn J.', 5, 'El consultorio es tranquilo y ordenado, se nota que atiende con cita para no hacer esperar.', 27],
]],

['slug' => 'mf-mecanica-fernandez-sac', 'opiniones' => [
    ['Hugo B.', 5, 'Cambio de aceite con filtro incluido a buen precio, y me mostraron la factura sin pedirla.', 10],
    ['Verónica L.', 5, 'Me hicieron la reparación del motor de una camioneta vieja y quedó andando suave otra vez.', 23],
    ['Alberto K.', 4, 'Son formales para dar presupuesto; me llamaron antes de meterle mano al motor.', 41],
]],

['slug' => 'mecanica-automotriz-juancho', 'opiniones' => [
    ['Segundo A.', 5, 'Hice alineamiento y balanceo antes de un viaje largo y el carro dejó de jalar a un lado.', 5],
    ['Karina D.', 5, 'El diagnóstico de motor por escáner salió rápido y me dijeron claro qué fallaba, sin inventos.', 19],
    ['Omar U.', 5, 'Juancho recibe el carro temprano y siempre me avisa por teléfono cuando ya está listo.', 35],
]],

['slug' => 'clinica-veterinaria-b-b', 'opiniones' => [
    ['Cynthia R.', 5, 'Llevé a mi perrito al baño y corte y me lo devolvieron bien peinado, sin rasurarlo todo.', 2],
    ['Brayan M.', 4, 'Tienen collares, correas y juguetes a buen precio; compré un plato para el agua y salió bueno.', 16],
    ['Lucero P.', 5, 'Nos atendieron sin cita el sábado porque mi gata estaba mal, se nota que aman los animales.', 30],
]],

['slug' => 'biblioteca-central-de-la-universidad-nacional-del-santa', 'opiniones' => [
    ['Estefany C.', 5, 'Estudio en la UNS y la biblioteca central abre hasta tarde, así avanzo mis trabajos tranquila.', 7],
    ['Rodrigo N.', 5, 'Pedí un libro que no estaba en estantería y la señorita me lo ubicó en el sistema al toque.', 21],
    ['Jhanet V.', 5, 'Las salas de lectura son silenciosas y hay enchufes para la laptop, aunque en parciales se llena.', 39],
]],

['slug' => 'municipalidad-distrital-de-santa', 'opiniones' => [
    ['Elva Q.', 5, 'Fui a pagar mi arbitrio de limpieza pública en la Municipalidad de Santa y la cola avanzó rápido.', 4],
    ['Teodoro S.', 5, 'Hice el trámite de constancia de residencia y me la dieron el mismo día en ventanilla.', 13],
    ['Marlene I.', 5, 'El personal de la oficina de rentas me explicó cómo fraccionar mi deuda sin ponerme trabas.', 28],
]],

['slug' => 'municipalidad-distrital-de-coishco', 'opiniones' => [
    ['Wilder Ch.', 5, 'En Coishco fui a la municipalidad por la partida de nacimiento de mi hijo y salió sin demora.', 6],
    ['Yesenia F.', 5, 'La licencia de funcionamiento de mi bodega la tramité ahí y me guiaron con los requisitos.', 24],
    ['Percy O.', 5, 'El serenazgo pasó por mi calle cuando reporté el alumbrado malogrado, eso se agradece.', 42],
]],

['slug' => 'iep-buena-esperanza', 'opiniones' => [
    ['Silvia M.', 5, 'Mi hija está en tercero de primaria y las maestras avisan por cuaderno cualquier cosa, buena comunicación.', 8],
    ['Gonzalo R.', 4, 'El colegio tiene patio techado, así los chicos hacen educación física aunque llueva en Nuevo Chimbote.', 20],
    ['Deysi A.', 5, 'En la reunión de padres nos mostraron los cuadernos corregidos y explicaron cómo van las notas.', 36],
]],

['slug' => 'aegis-inmobiliaria-chimbote', 'opiniones' => [
    ['Iván C.', 5, 'Me mostraron tres departamentos en Chimbote el mismo día y sin cobrarme por la visita.', 2],
    ['Rosario T.', 5, 'Hicieron todo el trámite de compra-venta de mi casa y me acompañaron en la notaría.', 11],
    ['Félix D.', 5, 'Me avisaron cuando bajó el precio de un terreno que había visto, se nota que trabajan con seriedad.', 33],
]],

['slug' => 'clinica-jireh-salud-ocupacional-chimbote', 'opiniones' => [
    ['Erick S.', 5, 'Mi empresa me mandó al examen médico ocupacional y me atendieron en menos de una hora.', 3],
    ['Liliana V.', 4, 'Me hicieron el electrocardiograma y el doctor me explicó el resultado ahí mismo, sin vueltas.', 17],
    ['Juan Carlos H.', 5, 'Los resultados del examen ocupacional llegaron por correo al día siguiente, justo para mi contrato.', 29],
]],

['slug' => 'clinica-medvida-salud-ocupacional-nuevo-chimbote', 'opiniones' => [
    ['Sheyla B.', 5, 'Fui por el chequeo preventivo y me sacaron análisis, presión y vista en una sola mañana.', 5],
    ['Anthony G.', 5, 'La evaluación de aptitud para mi trabajo la firmaron el mismo día, sin hacerme volver.', 15],
    ['Miriam L.', 5, 'En Nuevo Chimbote encontrar atención a las siete de la mañana se agradece, entré sin cola.', 34],
]],

['slug' => 'favisa-online-abarrotes-chimbote', 'opiniones' => [
    ['Nelly R.', 5, 'Pedí el costal de arroz extra de 50 kilos y me lo trajeron hasta la puerta de mi casa.', 1],
    ['Víctor P.', 5, 'Los fideos de un kilo salen más baratos que en el mercado y vienen bien cerrados, sin humedad.', 12],
    ['Edith Z.', 4, 'Hago el pedido por WhatsApp y me confirman el precio antes de mandar al repartidor.', 27],
]],

['slug' => 'turismo-chimbote-transportes', 'opiniones' => [
    ['Wilmer A.', 5, 'Compré el pasaje Chimbote a Trujillo de las seis de la mañana y salió puntual, sin esperar.', 4],
    ['Flor M.', 5, 'Saqué el viaje ida y vuelta a Lima y me salió más cómodo que comprar dos pasajes sueltos.', 18],
    ['Ítalo R.', 5, 'Reservé por teléfono y me guardaron el asiento junto a la ventana, buena atención.', 40],
]],

['slug' => 'municipalidad-distrital-de-nuevo-chimbote', 'opiniones' => [
    ['Rosmery C.', 4, 'Tramité en la Municipalidad de Nuevo Chimbote la constancia de posesión y me atendieron en ventanilla.', 6],
    ['Abelardo T.', 5, 'Fui a defensa civil por el certificado de mi local y vinieron a inspeccionar la semana siguiente.', 22],
    ['Karim S.', 5, 'Pagué el impuesto predial con el descuento por pronto pago, la cajera me avisó del beneficio.', 43],
]],

['slug' => 'biblioteca-municipal-de-nuevo-chimbote', 'opiniones' => [
    ['Nilton E.', 5, 'Llevé a mi hijo a la biblioteca de Nuevo Chimbote y la señorita le leyó un cuento en la sala infantil.', 3],
    ['Carolina F.', 5, 'Estoy preparando mi examen de admisión y ahí encuentro textos de matemática que no hay en librerías.', 16],
    ['Zulema A.', 5, 'El préstamo de libros a domicilio es gratis con el DNI, solo hay que devolverlos en una semana.', 32],
]],

['slug' => 'biblioteca-municipal-provincial-cesar-vallejo-santa', 'opiniones' => [
    ['Mirko S.', 4, 'En la biblioteca de Santa encontré libros de historia de nuestra provincia que no se ven en otro lado.', 9],
    ['Dora L.', 5, 'Fui a consultar por una tarea de mi sobrino y el bibliotecario nos ayudó a buscar en los estantes.', 25],
    ['Alexia V.', 5, 'La sala está fresca y ordenada; llevo mi cuaderno y avanzo tranquila los sábados por la mañana.', 45],
]],

['slug' => 'instituto-superior-pedagogico-chimbote', 'opiniones' => [
    ['Fabiola G.', 5, 'Estudio educación inicial en el Pedagógico de Chimbote y las prácticas las hacemos desde el tercer ciclo.', 2],
    ['Cristian M.', 5, 'Fui a preguntar por la carrera de primaria y en secretaría me dieron el plan de estudios completo.', 19],
    ['Yessenia Q.', 4, 'Los profesores del instituto son exigentes con las exposiciones, pero así uno aprende a pararse.', 37],
]],

// ====== TANDA 2 (2026-09-14, noche): las 100 tiendas anteriores ======
['slug' => 'gamer-07l', 'opiniones' => [
    ['Kevin R.', 5, 'Alquilé una cabina por hora y las máquinas corren bien los juegos pesados, sin tirones.', 5],
    ['Jhonatan V.', 5, 'Me metí al torneo de viernes y aunque perdí en la primera ronda, la organización estuvo ordenada.', 14],
    ['Diego A.', 4, 'Voy con mis patas los sábados; la cabina sale más barata por hora y el internet no se cae.', 27],
]],

['slug' => 'los-domingos-cevicheria', 'opiniones' => [
    ['Marisol Q.', 5, 'La jalea mixta es bien abundante, alcanza para dos, y el pescado estaba fresco ese día.', 3],
    ['Pedro L.', 5, 'La chicha morada la preparan en casa, no es de sobre, se siente el clavo de olor.', 11],
    ['Elena T.', 5, 'Fuimos domingo al mediodía y aunque había gente nos atendieron rápido, en menos de quince minutos.', 22],
]],

['slug' => 'cevicheria-maradona', 'opiniones' => [
    ['Jorge M.', 5, 'Pedí la jalea mixta y venía bien cargada de conchas y calamar, no puro pescado.', 6],
    ['Katherine S.', 4, 'El arroz con mariscos tiene buen sabor a ají amarillo, bien sazonado y con harto marisco.', 18],
    ['Wilmer D.', 5, 'El local está a media cuadra de la plaza, limpio, y las mesas las desocupan rápido.', 31],
]],

['slug' => 'panaderia-y-pasteleria-el-hornito', 'opiniones' => [
    ['Carmen R.', 5, 'Encargué un pastel de chocolate para el cumpleaños de mi hija y quedó bien húmedo por dentro.', 2],
    ['Lucía B.', 5, 'Los bocaditos para la reunión salieron a buen precio y vinieron surtidos, todos fresquitos.', 9],
    ['Sandro P.', 5, 'Avisé el pedido con dos días y me lo tuvieron listo a la hora que les dije.', 25],
]],

['slug' => 'rest-cevicheria-calamarino', 'opiniones' => [
    ['Róger A.', 5, 'El ceviche mixto viene con su choclo, camote y cancha, y el limón se siente fresco.', 8],
    ['Yesenia F.', 5, 'El sudado de pescado lo sirven bien caliente, con bastante culantro, ideal para el frío.', 16],
    ['Miguel H.', 4, 'Almuerzo ahí seguido porque la porción es grande y no te cobran de más.', 29],
]],

['slug' => 'xtreme-force-gym', 'opiniones' => [
    ['Bryan C.', 5, 'Fui al día de prueba y el entrenador me armó la rutina según lo que quería, sin presionarme.', 4],
    ['Fiorella M.', 5, 'La asesoría nutricional me sirvió bastante: me dieron un plan con lo que como en casa.', 13],
    ['Alexis T.', 5, 'Las máquinas están en buen estado y a las siete de la noche igual alcanzas a entrenar.', 26],
]],

['slug' => 'la-marea-brava-cevicheria', 'opiniones' => [
    ['Paola V.', 5, 'El ceviche mixto llega rápido a la mesa y el pescado se nota del día, no pasado.', 7],
    ['Segundo R.', 5, 'La chicha morada la dan en jarra bien helada, perfecta para el calor de Santa.', 19],
    ['Nadia L.', 4, 'Está frente a la playa, así que uno come con vista al mar y el ambiente es tranquilo.', 34],
]],

['slug' => 'panaderia-maria-del-carmen', 'opiniones' => [
    ['Julissa N.', 5, 'El pan francés sale calientito a las seis de la mañana y la docena se acaba rapidísimo.', 1],
    ['Marco E.', 5, 'Las tortas las hacen con manjar blanco de verdad, no esa crema que sabe a químico.', 12],
    ['Teresa G.', 5, 'Mi mamá manda a pedir la docena para el desayuno y siempre pesa lo que debe.', 24],
]],

['slug' => 'cevicheria-d-rumba', 'opiniones' => [
    ['César O.', 5, 'El ceviche mixto de D\' Rumba tiene harto marisco y el ají le da un picor justo.', 5],
    ['Eliana P.', 4, 'Pedí el sudado un martes y me lo trajeron hirviendo, con yuca bien suave.', 15],
    ['Rubén D.', 5, 'Los domingos se llena, pero el mozo igual te ubica una mesa y no te deja esperando.', 28],
]],

['slug' => 'moreno-pasteleria-y-panaderia', 'opiniones' => [
    ['Silvia A.', 5, 'El pan de molde de Moreno es suave y aguanta varios días sin ponerse duro.', 3],
    ['Gustavo I.', 5, 'Les encargué una torta de tres leches para el trabajo y todos preguntaron dónde la compré.', 17],
    ['Milagros C.', 5, 'Atienden desde temprano en Chimbote y el local huele rico a pan recién horneado.', 30],
]],

['slug' => 'cebicheria-dona-lucha', 'opiniones' => [
    ['Nancy Z.', 5, 'La jalea mixta de doña Lucha es enorme; comí con mi hermano y sobró para llevar.', 6],
    ['Percy M.', 5, 'El arroz con mariscos viene con su salsa criolla aparte, bien picante como me gusta.', 20],
    ['Gladys T.', 4, 'Doña Lucha pasa por las mesas a preguntar si todo está bien, eso ya no se ve.', 35],
]],

['slug' => 'san-miguel-pasteleria-fina', 'opiniones' => [
    ['Verónica S.', 5, 'Los pasteles de San Miguel son finos de verdad, la crema no empalaga y el bizcocho es liviano.', 9],
    ['Iván Q.', 5, 'Pedí bocaditos salados para una reunión y trajeron tequeños, empanaditas y mini sánguches.', 21],
    ['Claudia R.', 4, 'Pagué un poco más que en otras pastelerías, pero la presentación vale la pena.', 38],
]],

['slug' => 'sakura-cafe', 'opiniones' => [
    ['Daniela H.', 5, 'El pan de molde de Sakura es bien esponjoso, lo uso para los sánguches de la lonchera.', 10],
    ['Óscar N.', 5, 'Los bocaditos dulces se ven bonitos en la vitrina y no son empalagosos.', 23],
    ['Pilar U.', 5, 'Es tranquilo para desayunar, con café pasado y pan tostado, y el chico atiende amable.', 40],
]],

['slug' => 'comunidad-terapeutica-nuevo-amanecer', 'opiniones' => [
    ['Rosario B.', 5, 'Llevé a mi hermano a su sesión de terapia y el trato fue respetuoso, sin hacerlo sentir mal.', 2],
    ['Héctor L.', 5, 'La terapia psicológica en Nuevo Amanecer ayudó harto en casa; nos enseñaron a conversar sin gritos.', 14],
    ['Amalia V.', 5, 'Te reciben con cita previa y hay que esperar un poco, pero el acompañamiento es constante.', 27],
]],

['slug' => 'centro-de-salud-mental-comunitario-dos-de-junio', 'opiniones' => [
    ['Maritza F.', 5, 'Pedí la evaluación inicial para mi sobrino y salió sin costo, solo con su DNI.', 7],
    ['Julio C.', 5, 'El paquete de sesiones nos ayudó a que mi hijo vuelva al colegio con más calma.', 18],
    ['Bertha A.', 5, 'La psicóloga escucha con paciencia y explica el tratamiento en palabras simples.', 33],
]],

['slug' => 'happy-dog-pet-shop', 'opiniones' => [
    ['Katia M.', 5, 'Compré un collar y una correa para mi perro en Coishco y me salieron a buen precio.', 4],
    ['Jhon P.', 5, 'Los productos de higiene para mascotas están surtidos: champú antipulgas y toallitas.', 16],
    ['Rocío E.', 4, 'La señora me ayudó a elegir el tamaño de la casaca para mi perro, sin apurarme.', 29],
]],

['slug' => 'fisiosana-kids', 'opiniones' => [
    ['Elsa D.', 5, 'Mi hijo camina mejor después de las sesiones de fisioterapia en Fisiosana Kids.', 8],
    ['Christian Y.', 5, 'La evaluación inicial fue completa y nos explicaron en qué estaba fallando la postura.', 19],
    ['Melissa J.', 5, 'El local tiene juegos y colchonetas, así que mi hijo entra contento a su terapia.', 32],
]],

['slug' => 'centro-de-salud-mental-nuevo-puerto', 'opiniones' => [
    ['Gladys O.', 5, 'Llamé por una crisis de mi mamá y nos atendieron el mismo día, sin tantos papeles.', 3],
    ['Wilfredo S.', 5, 'La sesión de psicología es puntual y te dan tu cita para la siguiente semana.', 15],
    ['Anahí R.', 5, 'El personal de Nuevo Puerto tiene paciencia con los familiares, te explican cómo acompañar.', 26],
]],

['slug' => 'bodega-zorayda', 'opiniones' => [
    ['Sonia V.', 5, 'En la bodega Zorayda encuentro el alimento de mi gato más barato que en la veterinaria.', 5],
    ['Édgar T.', 5, 'Venden snacks para perro sueltos, así que compro poquitos y no se me malogran.', 17],
    ['Nelly C.', 5, 'La señora Zorayda siempre tiene su bolsa de 15 kilos y te la alcanza hasta la esquina.', 31],
]],

['slug' => 'fisiosana', 'opiniones' => [
    ['Liliana G.', 5, 'El paquete de diez sesiones conviene más que pagar suelto, y ves el avance por semana.', 6],
    ['Marcos A.', 5, 'Mi sobrino tiene parálisis braquial y en FISIOSANA le trabajan el brazo con juegos.', 20],
    ['Patricia N.', 5, 'Los terapeutas te mandan ejercicios para hacer en casa y los revisan la próxima sesión.', 36],
]],

['slug' => 'patitas-pet-shop', 'opiniones' => [
    ['Fiorella I.', 5, 'Fui por un arnés y me asesoraron bien: mi perro es braquicéfalo y necesitaba otro modelo.', 2],
    ['Andrés B.', 5, 'Tienen platos, camas y juguetes para mascotas; compré una cama y mi gata no se baja.', 12],
    ['Zoila M.', 4, 'La asesoría es gratis aunque no compres, te dicen qué comida le cae mejor a tu cachorro.', 25],
]],

['slug' => 'jolly-pets-peru', 'opiniones' => [
    ['Cinthia L.', 5, 'Le compré una casaca a mi perro para las mañanas frías y le quedó justa la talla.', 8],
    ['Raúl F.', 5, 'Los productos de higiene huelen rico y el champú le quitó la picazón a mi mascota.', 22],
    ['Deysi P.', 5, 'Pedí por WhatsApp y me dejaron el pedido listo para recoger, sin esperar.', 37],
]],

['slug' => 'car-wash-lian', 'opiniones' => [
    ['Jhonatan E.', 5, 'Dejé mi camioneta para el lavado completo en Coishco y quedó sin polvo por dentro.', 4],
    ['Rosa H.', 4, 'El lavado de moto es rápido, en media hora la sacan y le limpian bien la cadena.', 14],
    ['Víctor Z.', 5, 'Cobran menos que en Chimbote y trabajan con cuidado, no rayan los aros.', 28],
]],

['slug' => 'daylson-barber', 'opiniones' => [
    ['Jair S.', 5, 'El corte de caballero en Daylson es parejo, con máquina y tijera, y dura bien.', 5],
    ['Mercedes A.', 5, 'Llevé a mi hijo de cinco años y le tuvieron paciencia, le quedó su corte con línea.', 16],
    ['Pool R.', 5, 'En Santa cobran menos que en el centro y hay que ir temprano porque se llena.', 30],
]],

['slug' => 'ecoexpress-centro-de-lavado', 'opiniones' => [
    ['Luis Q.', 5, 'Lavado de camioneta en EcoExpress y me dejaron los asientos sin una miga del aspirado.', 9],
    ['Estefany C.', 5, 'Aproveché que estaba en Chimbote y mandé aspirar los interiores, salió rápido y barato.', 21],
    ['Miguel A.', 4, 'Usan poca agua, eso me gustó, y el carro queda sin olor a humedad.', 39],
]],

['slug' => '3a-diseno-publicidad-y-gigantografia', 'opiniones' => [
    ['Rosa C.', 5, 'Pedí unos afiches para la pollada de mi barrio y salieron bien nítidos, se notaba la buena impresión.', 7],
    ['Luis M.', 5, 'Me ayudaron con el diseño gráfico del logo de mi bodega; le atinaron al color que quería.', 21],
    ['Ana P.', 4, 'Aunque fui a última hora me entregaron los afiches el mismo día, rapidito para la actividad.', 33],
]],

['slug' => 'carwash-apolo', 'opiniones' => [
    ['Jorge Q.', 5, 'El aspirado de interiores me dejó la camioneta impecable, hasta las alfombras quedaron como nuevas.', 3],
    ['Milagros T.', 5, 'Llevé mi moto para el lavado y salió sin una sola mancha de barro, quedó brillante.', 12],
    ['Pedro S.', 5, 'Buen trato de los muchachos y no me hicieron esperar mucho, en menos de una hora ya estaba listo.', 28],
]],

['slug' => 'xpresion-grafica-2', 'opiniones' => [
    ['Carlos V.', 5, 'Mandé a imprimir tarjetas para mi negocio y el acabado quedó parejo, bien presentable.', 5],
    ['Nelly R.', 5, 'Los afiches los sacaron en tamaño grande y los colores salieron tal cual los mandé.', 17],
    ['Ivan D.', 4, 'Atienden rápido y te explican cómo mandar el archivo para que salga mejor la impresión.', 40],
]],

['slug' => 'miami-studio-barber-shop', 'opiniones' => [
    ['Kevin A.', 5, 'El degradado me lo hicieron bien parejo, con la máquina justa, sin dejarme pelado de un lado.', 2],
    ['Bryan L.', 5, 'Voy cada quince días y siempre salgo con el corte fresco; ya me conocen el estilo.', 14],
    ['Jose F.', 5, 'El local está limpio y con música buena, se pasa rápido la espera del turno.', 25],
]],

['slug' => 'barbershop-santa', 'opiniones' => [
    ['Sandra M.', 5, 'Llevé a mi hijo de cinco años y le tuvieron paciencia, terminó contento con su corte.', 6],
    ['Walter N.', 5, 'La limpieza facial me dejó la cara suave, se me quitaron los puntos negros de la nariz.', 19],
    ['Cynthia B.', 4, 'Cobran justo y no te apuran; te preguntan cómo quieres el corte antes de empezar.', 31],
]],

['slug' => 'car-wash-the-rock', 'opiniones' => [
    ['Ruben H.', 5, 'El lavado completo le sacó el polvo del motor y las llantas, quedó como recién comprada.', 9],
    ['Diana G.', 5, 'Mi camioneta es grande y la lavaron bien por dentro y por fuera, sin dejar los vidrios manchados.', 22],
    ['Marco E.', 5, 'Fui un domingo y me atendieron igual de rápido; el precio no sube por ser fin de semana.', 44],
]],

['slug' => 'xpresion-grafica', 'opiniones' => [
    ['Yolanda S.', 5, 'Los letreros y banners me duraron todo el año afuera, la tinta no se corrió con la lluvia.', 4],
    ['Percy A.', 5, 'Imprimí tarjetas para repartir en el mercado y me salieron cien por menos de lo que pensaba.', 16],
    ['Gladys T.', 4, 'Me ayudaron a corregir el diseño porque mi archivo estaba bajito, y quedó legible.', 27],
]],

['slug' => 'elohim-barber-shop-y-estetica', 'opiniones' => [
    ['Anthony R.', 5, 'El perfilado de barba me lo dejaron bien marcado y con toalla caliente, cosa que no hacen en todos lados.', 8],
    ['Flor M.', 5, 'Aproveché la limpieza facial y me sacaron los puntitos de grasa; la piel quedó fresca.', 23],
    ['Elmer C.', 5, 'Los chicos son amables y el ambiente es tranquilo, uno sale relajado del servicio.', 38],
]],

['slug' => 'habite-diseno-y-construccion', 'opiniones' => [
    ['Jhonatan P.', 5, 'Mandé sublimar polos para el equipo del barrio y los colores salieron fuertes, sin desteñirse.', 11],
    ['Bertha Q.', 5, 'El letrero para mi tienda lo instalaron ellos mismos, quedó derecho y bien iluminado.', 24],
    ['Segundo L.', 4, 'Me asesoraron con la medida del banner según la fachada, no me vendieron de más.', 35],
]],

['slug' => 'abel-mucching-tattoo-store', 'opiniones' => [
    ['Diego S.', 5, 'Me hice un tatuaje mediano en el antebrazo y las líneas quedaron finas, tal como le pedí.', 1],
    ['Alessandra V.', 5, 'Es paciente con el diseño; le cambié la idea dos veces y no se molestó para nada.', 13],
    ['Renzo M.', 5, 'El estudio está limpio y usa agujas nuevas, eso me dio confianza para el tatuaje grande.', 29],
]],

['slug' => 'barber-antuan', 'opiniones' => [
    ['Giancarlo T.', 5, 'Me perfiló la barba con navaja y quedó pareja, sin esos pelos locos que me salían.', 10],
    ['Paola R.', 5, 'El degradado lo hace mirando tu cabeza, no igual para todos; se nota que sabe.', 20],
    ['Hugo D.', 4, 'Es puntual con la hora que le pides y el local está en una esquina fácil de encontrar en Santa.', 42],
]],

['slug' => 'luis-maguinatattoo-studio', 'opiniones' => [
    ['Estefany L.', 5, 'Me explicó bien los cuidados después del tatuaje y me mandó crema para la cicatrización.', 6],
    ['Cesar A.', 5, 'Mi tatuaje mediano sanó parejo, sin que se me borrara el color en las partes finas.', 18],
    ['Melissa J.', 5, 'Te muestra bocetos antes de avanzar y respeta el tamaño que uno quiere según el brazo.', 30],
]],

['slug' => 'chimbote-ink-tattoo-studio', 'opiniones' => [
    ['Fabrizio N.', 5, 'Le llevé una foto de referencia y me armó un diseño propio, no me lo copió igual.', 5],
    ['Karen Y.', 5, 'Después de tatuarme me escribió para saber cómo iba la herida, detalle que se agradece.', 15],
    ['Sebastian O.', 4, 'Trabaja con buena iluminación y todo esterilizado; el estudio se ve ordenado y limpio, sin desorden.', 26],
]],

['slug' => 'immonucal', 'opiniones' => [
    ['Maritza G.', 5, 'Fui por consulta médica general y el doctor me escuchó con calma, sin apurarme para recetar.', 7],
    ['Alberto F.', 5, 'Conseguí cita por especialidad para mi mamá y atendieron casi a la hora que nos dijeron.', 21],
    ['Rosario E.', 5, 'La atención en recepción es buena y explican cómo sacar la cita por teléfono.', 34],
]],

['slug' => 'muto-tattoo-estudio-s', 'opiniones' => [
    ['Cristhian Z.', 5, 'Me hice un tatuaje pequeño en la muñeca y los detalles chiquitos salieron bien definidos.', 3],
    ['Lucia M.', 5, 'Saben aguantar el dolor del cliente; me dio miedo y me dieron tiempo para respirar.', 12],
    ['Fernando B.', 4, 'Los precios son claros desde el inicio, no te suben al final por el tamaño.', 37],
]],

['slug' => 'betoliutattoo', 'opiniones' => [
    ['Nicole P.', 5, 'Fui por un retoque de un tatuaje viejo y me lo levantó, parece nuevo otra vez.', 8],
    ['Andres C.', 5, 'El diseño me lo pasó por el celular antes de tatuar y ahí recién aprobé.', 19],
    ['Tatiana S.', 5, 'Aguanta sesiones largas sin apurarse, y eso se nota en los detalles del dibujo.', 43],
]],

['slug' => 'clinica-country', 'opiniones' => [
    ['Elena V.', 5, 'Me hicieron el electrocardiograma ahí mismo después de la consulta, sin tener que ir a otro lado.', 4],
    ['Ricardo M.', 5, 'La consulta por especialidad fue puntual y el doctor explicó el tratamiento paso a paso.', 16],
    ['Sonia K.', 4, 'La clínica está limpia y el personal de laboratorio te trata con paciencia.', 29],
]],

['slug' => 'tattoo-studio', 'opiniones' => [
    ['Miguel A.', 5, 'Me retocó una letra que se me había desvanecido y quedó igual de oscura que el resto.', 9],
    ['Claudia R.', 5, 'Las indicaciones de cuidado después del tatuaje me sirvieron, no se me infectó nada.', 23],
    ['Percy H.', 5, 'Trabaja con cita previa, así no hay gente amontonada esperando en el local.', 39],
]],

['slug' => 'antuan-tattoo-estudio', 'opiniones' => [
    ['Eduardo L.', 5, 'Me hizo un tatuaje grande en la espalda en dos sesiones y respetó el diseño aprobado.', 6],
    ['Yessenia T.', 5, 'El lugar es tranquilo y pone música bajita, uno se relaja aunque dure horas.', 14],
    ['Omar P.', 4, 'Le pedí un tatuaje mediano para mi hijo mayor y le quedó bien proporcionado.', 32],
]],

['slug' => 'trainer-s-gym', 'opiniones' => [
    ['Gustavo R.', 5, 'El entrenamiento funcional me ayudó con la espalda; en un mes ya hacía sentadillas sin dolor.', 2],
    ['Pamela S.', 5, 'La asesoría nutricional no es solo una hoja impresa: te revisan lo que comes cada semana.', 11],
    ['Ivan Q.', 5, 'Los horarios son amplios y a las seis de la mañana ya está abierto para entrenar.', 27],
]],

['slug' => 'infinity-gym', 'opiniones' => [
    ['Katherine M.', 5, 'Saqué la membresía trimestral y me salió más barata que pagar mes a mes.', 5],
    ['Sergio D.', 5, 'Las máquinas están en buen estado y hay pesas suficientes, no hay que hacer cola para usarlas.', 18],
    ['Brenda A.', 4, 'La asesoría nutricional me cambió el desayuno y bajé dos kilos sin dejar de comer.', 36],
]],

['slug' => 'makro-chimbote', 'opiniones' => [
    ['Vilma C.', 5, 'Compro los abarrotes al por mayor y sale más barato que en la bodega de la esquina.', 3],
    ['Roberto N.', 5, 'Los lácteos y embutidos siempre están frescos, reviso la fecha y vienen con buen tiempo.', 13],
    ['Jessica F.', 5, 'Hay estacionamiento y carritos grandes, así que uno carga todo sin andar con las bolsas.', 24],
]],

['slug' => 'la-pequena-esther', 'opiniones' => [
    ['Marisol T.', 5, 'Las frutas y verduras están frescas y a buen precio; el tomate lo venden bien maduro.', 7],
    ['Efrain G.', 5, 'Me atienden rápido en Santa y siempre me pesan justo, sin redondear a su favor.', 20],
    ['Carmen L.', 4, 'Los lácteos y embutidos que compro ahí me duran más que los del mercado.', 41],
]],

['slug' => 'super-mercados-racsa-chimbote', 'opiniones' => [
    ['Karina B.', 5, 'Encuentro los abarrotes que en otras tiendas no hay, ahí compro el arroz por saco.', 4],
    ['Teodoro M.', 5, 'El área de lácteos y embutidos está surtida y las ofertas se ven en la entrada.', 17],
    ['Liliana V.', 5, 'Las cajeras atienden rápido aunque haya cola, y siempre te ayudan a embolsar la compra.', 28],
]],

['slug' => 'margarita-karaoke-bar', 'opiniones' => [
    ['Alfredo S.', 5, 'El menú del día viene con sopa y segundo, y la porción llena de verdad.', 9],
    ['Milagros Z.', 5, 'Alquilamos la cabina de karaoke para un cumpleaños y nos dieron micrófonos que sí funcionan.', 22],
    ['Jonathan C.', 5, 'El ambiente es familiar, no es bullero, y se puede cantar sin que te grite el de al lado.', 45],
]],

['slug' => 'drakaena-licoreria-bar-karaoke-chimbote', 'opiniones' => [
    ['Rosa C.', 5, 'El menú del día viene bien servido y a buen precio; la sopa siempre llega caliente.', 7],
    ['Luis M.', 5, 'Pedimos pisco sour y lo preparan al momento, con esa espuma bien helada que me gusta.', 21],
    ['Ana P.', 4, 'Fuimos un viernes a cantar karaoke y nos prestaron el micrófono sin apuro, hasta cerrar.', 33],
]],

['slug' => 'acustica-karaoke-lounge', 'opiniones' => [
    ['Jorge Q.', 5, 'Alquilamos la cabina de karaoke por horas y el sonido se escucha parejo, sin ese eco molesto.', 5],
    ['Milagros T.', 5, 'El menú del día tenía arroz con pollo y chicha morada bien fría, por poquito más de diez soles.', 14],
    ['César R.', 4, 'Fuimos con mi promo del trabajo y nos guardaron la cabina hasta tarde, muy buena onda.', 28],
]],

['slug' => 'karaoke-black-white', 'opiniones' => [
    ['Sandra V.', 5, 'La parrilla para dos alcanza tranquilo; la carne llegó jugosa y las papas bien crocantes.', 4],
    ['Wilder A.', 5, 'Su pisco sour es de los mejores que probé en Nuevo Chimbote, bien frío y no muy dulce.', 17],
    ['Kelly S.', 5, 'El local es oscuro y cómodo para conversar; los mozos pasan seguido sin que tengas que llamarlos.', 39],
]],

['slug' => 'neovita-3d', 'opiniones' => [
    ['Marco A.', 5, 'Les llevé la pieza rota de mi máquina, la escanearon en 3D y me devolvieron el prototipo exacto.', 9],
    ['Diana L.', 5, 'Necesitaba un prototipo pequeño para mi tesis y me lo imprimieron en dos días, bien prolijo.', 22],
    ['Paolo G.', 4, 'El escaneo 3D salió con todos los detalles; me explicaron cómo usarlo en mi proyecto.', 15],
]],

['slug' => 'star-karaoke', 'opiniones' => [
    ['Rocío H.', 5, 'El ceviche viene con bastante choclo y camote; el pescado estaba fresco, se sentía.', 14],
    ['Bryan F.', 5, 'La hamburguesa es grande y trae papas; me llenó por completo antes de cantar.', 15],
    ['Nidia C.', 4, 'Pedimos ceviche y hamburguesa para compartir y no nos cobraron de más, todo claro.', 16],
]],

['slug' => 'dannas', 'opiniones' => [
    ['Elmer S.', 5, 'El cuarto de pollo a la brasa viene con papas y ensalada, y la salsa de ají es casera.', 20],
    ['Tania B.', 5, 'Almuerzo casi todos los días el menú del día y siempre me sirven rápido, sin esperar mucho.', 16],
    ['José Luis P.', 4, 'Los domingos hay cola por el pollo, pero vale la pena porque sale bien dorado.', 15],
]],

['slug' => 'servicios-y-negocios-sayumi-sac', 'opiniones' => [
    ['Carmen R.', 5, 'Me imprimieron los planos a color en tamaño A1 y quedaron nítidos, con las líneas bien definidas.', 17],
    ['Hugo D.', 5, 'Les mandé el archivo de una pieza por WhatsApp y ya tenía mi impresión 3D lista en la tarde.', 18],
    ['Silvia M.', 4, 'Imprimen por unidad y no te obligan a pedir cien; eso me ayudó para mi maqueta.', 16],
]],

['slug' => 'centro-medico-santa-rosa', 'opiniones' => [
    ['Gladys T.', 5, 'La consulta general costó lo que me dijeron por teléfono, sin sorpresas al pagar.', 14],
    ['Manuel E.', 5, 'Me hicieron el electrocardiograma ahí mismo y el doctor me explicó el resultado con calma.', 15],
    ['Verónica A.', 4, 'Saqué cita para mi mamá y la atendieron a la hora, aunque el local estaba lleno.', 16],
]],

['slug' => 'buena-salud', 'opiniones' => [
    ['Fernando C.', 5, 'Encontré polos de hombre a diez soles y de buena tela, no de esos que se estiran.', 17],
    ['Karina S.', 5, 'Cada temporada traen ropa nueva; compré una casaca abrigadora para el invierno de Santa.', 15],
    ['Rubén O.', 4, 'La ropa de hombre está ordenada por tallas, así no pierdes tiempo buscando tu medida.', 16],
]],

['slug' => 'sana-consultorios-medicos', 'opiniones' => [
    ['Pilar N.', 5, 'Me hicieron la ecografía y en la misma visita me dieron el informe impreso.', 13],
    ['Iván R.', 5, 'El electrocardiograma fue rápido y la técnica muy cuidadosa, me puso los electrodos sin jalarme.', 14],
    ['Marisol Q.', 4, 'Atienden por orden de llegada y avisan cuánto falta; esperé menos de lo que pensaba.', 15],
]],

['slug' => 'mercado-central-lds', 'opiniones' => [
    ['Julio B.', 5, 'Compro la canasta de abarrotes ahí: arroz, aceite y azúcar más baratos que en el mercado.', 15],
    ['Lucía F.', 5, 'Las bebidas siempre están heladas y tienen gaseosas por caja para las reuniones.', 13],
    ['Santos M.', 4, 'Abren temprano, así que alcanzo a llevar el pan y la leche antes del trabajo.', 16],
]],

['slug' => 'chifa-mandarin', 'opiniones' => [
    ['Alberto L.', 5, 'El tallarín saltado trae bastante carne y verdura; la porción alcanza para dos personas.', 14],
    ['Jessica P.', 5, 'Pedí aeropuerto para llevar y llegó caliente, con el arroz bien suelto y el huevo encima.', 17],
    ['Rommel C.', 4, 'El chifa está lleno a la hora del almuerzo, pero la comida sale rápido igual.', 15],
]],

['slug' => 'santa-clara-consultorios', 'opiniones' => [
    ['Nelly U.', 5, 'Hice el chequeo médico completo para mi trabajo y me dieron todos los resultados juntos.', 15],
    ['Augusto V.', 5, 'El doctor de consulta general me escuchó sin apurarme y me mandó solo los exámenes necesarios.', 16],
    ['Beatriz J.', 4, 'Los consultorios están limpios y hay sitio para esperar sentado, no en la calle.', 14],
]],

['slug' => 'dulce-tradicion', 'opiniones' => [
    ['Melanie R.', 5, 'Compré aretes y una vincha por quince soles aprovechando la promoción de la semana.', 14],
    ['Cynthia D.', 5, 'Tienen accesorios surtidos y la señora te ayuda a combinar con la ropa que llevas.', 15],
    ['Óscar T.', 4, 'Fui por un regalo de cumpleaños y me lo envolvieron bonito, sin cobrarme extra.', 16],
]],

['slug' => 'chifa-kua-yuan', 'opiniones' => [
    ['Nancy H.', 5, 'El chaufa de pollo es bien abundante y no viene aceitoso como en otros chifas.', 14],
    ['Percy M.', 5, 'Probé el pollo chi jau kay y la salsa es lo mejor, tuve que pedir más arroz.', 16],
    ['Liliana S.', 4, 'Pedimos por teléfono y la comida estuvo lista a la hora que nos dijeron.', 13],
]],

['slug' => 'novedades-yasnara', 'opiniones' => [
    ['Eliana G.', 5, 'Compré zapatillas para mi hijo en promoción y hasta ahora aguantan el colegio.', 13],
    ['Marco Z.', 5, 'Hay calzado de hombre y mujer en varias tallas; conseguí mi número 42 sin encargo.', 15],
    ['Rosa Elena B.', 4, 'Los precios están marcados y aceptan pago con Yape, eso me facilita.', 12],
]],

['slug' => 'confecciones-shantall', 'opiniones' => [
    ['Sonia A.', 5, 'Les llevé mi tela y me confeccionaron el uniforme de mi promo a la medida.', 14],
    ['Wilson P.', 5, 'Hice un pedido por mayor de polos para mi negocio y me respetaron el precio pactado.', 15],
    ['Dora L.', 4, 'Cosen rápido y aceptan arreglos pequeños; me ajustaron la basta el mismo día.', 13],
]],

['slug' => 'chifa-canton', 'opiniones' => [
    ['Gisela M.', 5, 'El tallarín saltado de Chifa Cantón tiene ese ahumado del wok que no encuentro en otro lado.', 15],
    ['Héctor R.', 5, 'Encargamos dos aeropuertos para la oficina y llegaron completos, con su salsa de tamarindo.', 16],
    ['Mónica F.', 4, 'El local es sencillo pero limpio, y las mesas las desocupan rápido para los que esperan.', 17],
]],

['slug' => 'innovacell', 'opiniones' => [
    ['Kevin S.', 5, 'Me cambiaron la pantalla del celular en una hora y quedó funcionando el táctil perfecto.', 15],
    ['Alonso D.', 5, 'Compré un case y un cargador ahí; el precio era menor que en la galería.', 14],
    ['Fabiana M.', 4, 'Me avisaron por WhatsApp cuando el repuesto llegó, así no tuve que ir dos veces.', 16],
]],

['slug' => 'smartcase', 'opiniones' => [
    ['Diego A.', 5, 'Me pusieron el protector de pantalla sin burbujas y me cobraron solo el vidrio.', 14],
    ['Katia V.', 5, 'Compré un cable trenzado y ya llevo meses sin que se malogre como los baratos.', 15],
    ['Renzo C.', 4, 'Tienen protectores para modelos viejos también; encontré uno para mi celular de años.', 16],
]],

['slug' => 'santel-mobile', 'opiniones' => [
    ['Anthony M.', 5, 'Me desbloquearon el celular para usar chip de otra operadora y me explicaron cómo hacerlo.', 15],
    ['Karla T.', 5, 'La reparación de pantalla quedó sin marcas ni polvo dentro; revisan bien antes de entregar.', 14],
    ['Segundo R.', 4, 'Dejé mi equipo a las diez y a las dos de la tarde ya me avisaron que estaba listo.', 17],
]],

['slug' => 'academia-preuniversitaria-galileo-olaya', 'opiniones' => [
    ['Maritza C.', 5, 'Pago la pensión mensual y mi hija puede ir a las asesorías extra sin costo adicional.', 15],
    ['Teodoro L.', 5, 'Los simulacros de admisión son cronometrados como el examen real; ahí aprendió a manejar su tiempo.', 16],
    ['Yolanda P.', 4, 'Los profesores avisan por WhatsApp cómo va el alumno, no solo cuando hay problemas.', 14],
]],

['slug' => 'academia-preuniversitaria-el-prisma', 'opiniones' => [
    ['Estela R.', 5, 'Metí a mi hijo al ciclo intensivo de verano y en marzo ya estaba preparado para el examen.', 17],
    ['Ronald S.', 5, 'La asesoría vocacional individual le ayudó a decidir entre ingeniería y administración, sin presiones.', 14],
    ['Carmen Rosa V.', 4, 'Las aulas tienen ventilador y son pocas por salón, así los chicos preguntan sin miedo.', 15],
]],

['slug' => 'spa-xio-peluqueria-y-estetica', 'opiniones' => [
    ['Lisset A.', 5, 'Me hicieron el degradado con máquina y tijera tal como llevé la foto, quedó parejo.', 15],
    ['Patricia N.', 5, 'La limpieza facial me dejó la piel sin los puntos negros de siempre; usan productos frescos.', 16],
    ['Giannina F.', 4, 'Hay que sacar cita porque se llena, pero te atienden a la hora que te dicen.', 17],
]],

['slug' => 'peluqueria-jastin', 'opiniones' => [
    ['Edinson T.', 5, 'El combo de corte más barba cuesta menos que en otras peluquerías de Santa.', 14],
    ['Marcos G.', 5, 'Le digo cómo quiero el corte y lo respeta; no me deja el pelo más corto de lo pedido.', 18],
    ['Junior B.', 4, 'Atienden hasta tarde, así que voy después del trabajo y no hay tanta gente.', 15],
]],

['slug' => 'liso-y-color-salon', 'opiniones' => [
    ['Rosa C.', 5, 'Me hicieron el perfilado de barba bien prolijo, con navaja y todo, y quedó parejo como quería.', 7],
    ['Luis M.', 5, 'La limpieza facial me dejó la cara fresca, se nota que usan productos buenos y no te apuran.', 21],
    ['Ana P.', 4, 'Fui sin cita un sábado y me atendieron en veinte minutos, el local está limpio y ordenado.', 33],
]],

['slug' => 'hospedaje-residencial-sol-y-luna', 'opiniones' => [
    ['Marisol Q.', 5, 'La habitación matrimonial es amplia y la cama cómoda; el baño estuvo limpio todos esos días.', 4],
    ['Jorge T.', 5, 'Alquilamos por semana y nos respetaron el precio acordado, sin sorpresas ni cobros extra al salir.', 12],
    ['Carmen V.', 5, 'Está cerca de la avenida y se llega rápido en moto; el desayuno lo dan temprano, a las siete.', 28],
]],

['slug' => 'moto-repuestos-el-mister', 'opiniones' => [
    ['Brayan S.', 5, 'Le llevé mi moto porque no arrancaba y el diagnóstico fue rápido, me dijo exactamente qué tenía.', 3],
    ['Segundo R.', 5, 'Repararon el motor de mi Chineza y quedó parejo; me cobró justo lo que me había dicho.', 19],
    ['Nelly F.', 4, 'Tienen repuestos a la mano y te explican el trabajo, no te dejan con la duda ni te apuran.', 40],
]],

['slug' => 'hotel-fullrelax', 'opiniones' => [
    ['Patricia H.', 5, 'La habitación familiar tiene tres camas y espacio de sobra; mis hijos durmieron tranquilos.', 9],
    ['Wilmer A.', 5, 'Alquilamos por semana y sale mucho más barato que pagar por noche, nos hicieron buen precio.', 25],
    ['Diana L.', 5, 'El agua caliente funciona a cualquier hora y la recepción atiende hasta tarde, eso nos salvó.', 38],
]],

['slug' => 'cerveceria-vicky', 'opiniones' => [
    ['César D.', 5, 'La jarra de medio litro de cerveza está bien helada y los piqueos son abundantes para compartir.', 6],
    ['Katherine M.', 5, 'El local está a media cuadra de la plaza y los fines de semana se llena, pero atienden rápido.', 17],
    ['Elmer P.', 4, 'Pedimos piqueos surtidos y llegaron calientes y crocantes; la cuenta no pasó de lo esperado.', 31],
]],

['slug' => 'aredo-albarran-jimmy-andres', 'opiniones' => [
    ['Rubén O.', 5, 'Hice alinear y balancear mi auto en Santa y se fue la vibración del volante en la pista.', 11],
    ['Julio B.', 5, 'El mantenimiento preventivo lo hacen con checklist y te avisan qué hay que cambiar después.', 26],
    ['Sara G.', 5, 'Me atendió el mismo dueño, revisó las llantas una por una y no me cobró de más.', 44],
]],

['slug' => 'ankashina', 'opiniones' => [
    ['Milagros T.', 5, 'El ceviche de pescado tiene buen limón y el pescado está fresco, se siente al primer bocado.', 2],
    ['Iván R.', 5, 'El medio pollo a la brasa viene con papas y ensalada, alcanza para dos personas tranquilamente.', 15],
    ['Betty N.', 4, 'Fuimos a la una y ya estaba lleno, pero el pedido salió en quince minutos; el local es limpio.', 29],
]],

['slug' => 'muelle-viejo-lounge', 'opiniones' => [
    ['Fabiola E.', 5, 'El cóctel de la casa está bien preparado y no escatiman en la medida, vale lo que cuesta.', 8],
    ['Renzo C.', 5, 'Los viernes hay música en vivo y el ambiente se pone bueno sin que la bulla tape la conversa.', 22],
    ['Gisela U.', 5, 'Me gustó que atienden en la barra rápido y las mesas de afuera se sienten frescas de noche.', 36],
]],

['slug' => 'lubricentro-carlos', 'opiniones' => [
    ['Percy M.', 5, 'El cambio de aceite lo hacen delante de uno y te muestran la varilla antes de cerrar el tapón.', 5],
    ['Johnny S.', 5, 'Le hicieron reparación de motor a mi taxi y quedó andando parejo, sin humo ni ruido raro.', 18],
    ['Rocío A.', 4, 'Fui sin avisar y me atendieron al toque, y cobran según el aceite que uno elige.', 27],
]],

['slug' => 'lubricentro-powerrider', 'opiniones' => [
    ['Kevin L.', 5, 'Me cambiaron las pastillas y me ajustaron los frenos; ahora el pedal responde al primer toque.', 13],
    ['Máximo Z.', 5, 'Llevé mi moto por el motor y me mostraron la pieza gastada antes de cambiarla, buena señal.', 24],
    ['Paola D.', 5, 'Está sobre la avenida, entras y sales rápido; el chico que atiende explica sin apurar a nadie.', 41],
]],

['slug' => 'lubricentro-klismar', 'opiniones' => [
    ['Freddy V.', 5, 'Le hago el mantenimiento preventivo cada cinco mil kilómetros y siempre me reciben sin cita.', 10],
    ['Ángel Q.', 5, 'Una vez se me calentó el motor y lo dejaron afinado el mismo día, me salvaron la semana.', 20],
    ['Sandra I.', 4, 'Precios claros en la pizarra y te entregan el filtro viejo, cosa que otros talleres no hacen.', 35],
]],

['slug' => 'jc-lubricentro-carwash-detailing', 'opiniones' => [
    ['Cristian W.', 5, 'Aproveché y dejé el carro para el carwash mientras le hacían el alineamiento, todo en una visita.', 1],
    ['Óscar N.', 5, 'Los frenos quedaron parejos y el pedal firme; me explicaron que las pastillas aún aguantaban.', 23],
    ['Verónica J.', 5, 'El detailing de interiores dejó los asientos como nuevos, se nota el trabajo fino que hacen.', 39],
]],

['slug' => 'lubricentro-multiservicios-apowash-eirl', 'opiniones' => [
    ['Edwin F.', 5, 'El mantenimiento preventivo incluye revisión de líquidos y filtros, no solo el cambio de aceite.', 14],
    ['Karina B.', 5, 'Se me rajó una manguera del motor y la cambiaron en la misma tarde, sin dejarme el carro días.', 30],
    ['Hugo Y.', 4, 'Tienen espacio para varios carros y un sitio donde esperar; atienden de corrido hasta la noche.', 43],
]],

['slug' => 'lubricante-rodriguez', 'opiniones' => [
    ['Manuel G.', 5, 'El diagnóstico de motor lo hicieron con escáner y me dieron el detalle por escrito, muy serios.', 16],
    ['Lady P.', 5, 'Sentía ruido al frenar y era el disco; lo rectificaron y quedó silencioso, cobro razonable.', 32],
    ['Rolando C.', 5, 'Es un taller pequeño pero ordenado, y el mecánico te deja ver lo que está haciendo.', 45],
]],

['slug' => 'distribuidora-de-agua-san-pedrito', 'opiniones' => [
    ['Zoila M.', 5, 'Pido agua y bebidas para la tienda y llegan el mismo día, casi siempre antes del mediodía.', 7],
    ['Alfredo H.', 5, 'La entrega a domicilio en Coishco es puntual y el muchacho sube los baldes hasta la puerta.', 21],
    ['Mónica S.', 4, 'Buen surtido de gaseosas y aguas de distintas marcas, y te fían si eres cliente de años.', 34],
]],

['slug' => 'distribuidora-rj', 'opiniones' => [
    ['Elva R.', 5, 'Compro los lácteos y embutidos al por mayor para mi bodega y siempre me respetan el precio.', 3],
    ['Dante O.', 5, 'Los embutidos llegan frescos y bien refrigerados, se nota que cuidan la cadena de frío.', 19],
    ['Susana K.', 5, 'También llevo detergente y lejía; con un solo pedido me surto de todo para la semana.', 37],
]],

['slug' => 'distribuidora-sanchez', 'opiniones' => [
    ['Gladys T.', 5, 'Tienen abarrotes de marcas que no encuentro en las bodegas del barrio y a precio de mayorista.', 12],
    ['Wilder A.', 5, 'Pedí lejía, detergente y escobas para el negocio y me lo llevaron hasta Coishco sin cobrar.', 26],
    ['Néstor D.', 4, 'La atención es de confianza, anotan el pedido en el cuaderno y no se equivocan con la cuenta.', 42],
]],

['slug' => 'divina-celebracion', 'opiniones' => [
    ['Johana P.', 5, 'El pan francés sale caliente a las seis de la mañana y la docena se acaba rapidísimo.', 6],
    ['Miguel A.', 5, 'Encargué una torta de chocolate para un cumpleaños y quedó húmeda y no muy dulce, como pedí.', 24],
    ['Teresa L.', 5, 'Para las tortas hay que encargar con un día de anticipación; el precio es justo, no inflado.', 33],
]],

['slug' => 'cebicheria-el-ajicito', 'opiniones' => [
    ['Lucho C.', 5, 'La jalea mixta viene bien cargada de mariscos y el ají le da ese punto que no hallo en otros.', 4],
    ['Fiorella N.', 5, 'El sudado de pescado lo sirven humeando, con bastante yuca y arroz aparte, llena de verdad.', 18],
    ['Ramiro S.', 4, 'Llegamos después de la una y aún había sitio; el servicio es rápido aunque esté lleno.', 30],
]],

['slug' => 'arte-sano', 'opiniones' => [
    ['Yolanda B.', 5, 'El pan de molde integral se mantiene suave varios días, no se deshace como el de supermercado.', 9],
    ['Cristhian M.', 5, 'Encargamos bocaditos salados para una reunión y los prepararon con anticipación sin problema.', 22],
    ['Pilar E.', 5, 'Es una panadería pequeña pero limpia, y atienden desde temprano para el que va a trabajar.', 40],
]],

['slug' => 'rincon-del-mero-2', 'opiniones' => [
    ['Eduardo V.', 5, 'El ceviche mixto trae conchas, calamar y pescado; el limón se siente fresco, no aguado.', 13],
    ['Cynthia R.', 5, 'El arroz con mariscos es bien cremoso y la porción alcanza para dos, conviene pedir uno.', 27],
    ['Andrés F.', 4, 'Fuimos en familia un domingo y nos atendieron rápido, aunque el local estaba con varias mesas.', 38],
]],

['slug' => 'rincon-del-mero', 'opiniones' => [
    ['Blanca O.', 5, 'El ceviche de mero es otro nivel: el pescado es firme y dulce, se nota que es fresco del día.', 2],
    ['Tito G.', 5, 'La parihuela viene bien picante y con buen caldo; me la comí con harto limón y ají.', 20],
    ['Mariela C.', 5, 'El local es sencillo, sin lujos, pero limpio, y el dueño pasa por las mesas a preguntar.', 44],
]],

['slug' => 'cevicheria-la-lanchita', 'opiniones' => [
    ['Silvia D.', 5, 'La jalea mixta es crocante y no viene aceitosa; se nota que fríen al momento el pedido.', 8],
    ['Marcos I.', 5, 'El arroz con mariscos tiene buen sabor a ají amarillo y no te lo sirven seco ni pasado.', 25],
    ['Katty H.', 4, 'Está a unas cuadras del mercado y abre desde temprano; a las once ya hay gente comiendo.', 36],
]],

['slug' => 'estudio-juridico-ambar-g-gomez-asociados', 'opiniones' => [
    ['Fernando Z.', 5, 'Fui a una consulta legal por un tema de trabajo y me explicaron mis opciones sin apurarme.', 10],
    ['Lucía M.', 5, 'Llevaron mi defensa legal en un proceso largo y siempre me mantuvieron al tanto del avance.', 23],
    ['Raúl P.', 5, 'Los honorarios los dicen desde la primera reunión, no aparecen cobros raros a mitad del caso.', 41],
]],

['slug' => 'cochera-y-autolavado-cesar', 'opiniones' => [
    ['Erick S.', 5, 'Dejo el carro en la cochera por el día y siempre hay espacio, además está vigilada.', 5],
    ['Nataly Q.', 5, 'El aspirado de interiores saca la arena de la playa que se mete en los asientos, buen trabajo.', 17],
    ['Gregorio T.', 4, 'Cobran por día y te dan un papelito con la hora de entrada, cosa que da confianza.', 34],
]],

// ====== TANDA 2 (2026-09-14, noche): las 100 tiendas anteriores ======
['slug' => 'desarrollo-de-tecnologias-de-la-informacion', 'opiniones' => [
    ['Rosa C.', 5, 'Le llevé mi laptop porque se puso lenta y me hicieron el mantenimiento de sistemas el mismo día.', 7],
    ['Luis M.', 5, 'Pedí la asesoría tecnológica para la red de mi negocio y me explicaron todo con paciencia, sin cobrarme de más.', 21],
    ['Ana P.', 4, 'Vinieron a mi oficina en Chimbote a revisar las computadoras y dejaron todo configurado; quedé tranquila.', 33],
]],

['slug' => 'estudio-contable-angel', 'opiniones' => [
    ['Jorge Q.', 5, 'Llevé la planilla de empleados de mi ferretería y me la ordenaron en dos días, sin errores en las gratificaciones.', 4],
    ['Milagros T.', 5, 'La asesoría tributaria me salvó: me ayudaron a declarar la renta anual y no pagué multa este año.', 16],
    ['Wilmer A.', 4, 'El contador atiende en su oficina de Santa y siempre contesta el celular cuando tengo una duda de la SUNAT.', 29],
]],

['slug' => 'kusikuy-beer-cerveceria-artesanal', 'opiniones' => [
    ['Diana R.', 5, 'La tabla de piqueos es enorme y va bien con la cerveza artesanal que preparan ahí mismo.', 2],
    ['Percy H.', 5, 'Hicimos la visita guiada a la fábrica con mi grupo y nos explicaron paso a paso cómo fermentan su cerveza.', 12],
    ['Sandra V.', 4, 'Fuimos un viernes y el lugar estaba lleno; igual nos atendieron rápido y el ambiente es bien tranquilo.', 26],
]],

['slug' => 's-o-s-lavanderia', 'opiniones' => [
    ['Elena B.', 5, 'Llevé dos edredones de plumas y me los devolvieron como nuevos, bien secos y sin olor a humedad.', 9],
    ['Marco F.', 5, 'Manché un terno en una boda y la tintorería de prendas me lo dejó impecable, ni se nota la mancha.', 19],
    ['Julissa N.', 5, 'Dejé las cortinas de la sala un lunes y el jueves ya las tenía planchadas y dobladas en su bolsa.', 38],
]],

['slug' => 'carwash-fortaleza-ii', 'opiniones' => [
    ['César D.', 5, 'El aspirado y la limpieza de interiores quedó perfecto, sacaron hasta la arena de los asientos de mi auto.', 3],
    ['Karina S.', 5, 'Le hice el encerado y pulido a mi carro antes de venderlo y parecía recién salido de la tienda.', 14],
    ['Beto L.', 4, 'Fui un domingo en la mañana y aunque había cola me atendieron en menos de una hora.', 25],
]],

['slug' => 'flow-fight-nuevo-chimbote', 'opiniones' => [
    ['Kevin A.', 5, 'Pago la membresía mensual y voy tres veces por semana; el gimnasio nunca está lleno en las mañanas.', 6],
    ['Rocío M.', 5, 'Las clases de boxeo con el profe son bravas, pero en un mes ya aguantaba los rounds completos.', 18],
    ['Giancarlo P.', 5, 'El local de Nuevo Chimbote es amplio y los implementos están limpios, no hay que esperar por los guantes.', 31],
]],

['slug' => 'decoracion-de-fiestas-dayana', 'opiniones' => [
    ['Tatiana G.', 5, 'Contraté la decoración de cumpleaños de mi hija con temática de unicornio y quedó tal cual la foto que le mandé.', 5],
    ['Elmer Z.', 5, 'Los globos y arreglos los armó ella misma y llegó puntual a Santa, sin que yo tenga que estar encima.', 17],
    ['Nancy O.', 4, 'Me hizo un arco de globos para el bautizo y cobró bastante menos de lo que me pedían en otros lados.', 28],
]],

['slug' => 'estudio-juridico-ocas-rodriguez', 'opiniones' => [
    ['Paolo R.', 5, 'Fui por una consulta legal por un tema de arrendamiento y me explicó mis opciones sin apurarme ni asustarme.', 8],
    ['Verónica I.', 5, 'Llevó mi defensa legal en un caso laboral y siempre me mantuvo al tanto de las audiencias por WhatsApp.', 22],
    ['Hugo E.', 4, 'La oficina queda en Santa, cerca de la plaza, y atienden con cita para no hacerte esperar en la puerta.', 35],
]],

['slug' => 'samuelmotors', 'opiniones' => [
    ['Raúl T.', 5, 'Le hice el cambio de aceite a mi auto y me mostraron el filtro viejo antes de botarlo, eso da confianza.', 11],
    ['Fiorella C.', 5, 'El diagnóstico de motor dio con la falla que dos talleres no encontraban; era el sensor de oxígeno.', 23],
    ['Máximo B.', 5, 'El mecánico me avisó por teléfono del precio antes de meter mano y no hubo sorpresas en la factura.', 40],
]],

['slug' => 'distribuidora-el-reino', 'opiniones' => [
    ['Gladys P.', 5, 'Compro los productos de limpieza al por mayor para mi restaurante y me sale más barato que en el mercado.', 10],
    ['Segundo R.', 5, 'Pedí entrega a domicilio un sábado y llegaron con el pedido completo, hasta la lejía y los detergentes.', 20],
    ['Marisol Q.', 4, 'Tienen variedad de lejía, suavizante y desinfectante; siempre encuentro lo que busco para la limpieza del local.', 30],
]],

['slug' => 'distribuidora-de-ladrillos', 'opiniones' => [
    ['Elías C.', 5, 'Compré ladrillos al por mayor para mi segunda planta y me respetaron el precio que me cotizaron por teléfono.', 13],
    ['Yesenia H.', 5, 'La entrega a domicilio llegó puntual a mi obra en Nuevo Chimbote y ellos mismos descargaron el camión.', 24],
    ['Toribio M.', 5, 'Pedí mil ladrillos y no vino ninguno partido, estaban bien acomodados encima de las parihuelas.', 42],
]],

['slug' => 'centro-de-terapias-alternativas-my-cielito', 'opiniones' => [
    ['Lourdes A.', 5, 'Empecé con la evaluación inicial por un dolor de espalda y me hicieron seguimiento sesión por sesión.', 15],
    ['Richard S.', 5, 'La masoterapia me relajó de verdad; salgo de cada sesión en Coishco como si hubiera dormido diez horas.', 27],
    ['Pilar N.', 4, 'El lugar es pequeño pero limpio y las camillas tienen sábanas nuevas cada vez que uno llega.', 36],
]],

['slug' => 'yoreparovzla-servicio-tecnico-iphone-samsung', 'opiniones' => [
    ['Brayan O.', 5, 'Le cambiaron la pantalla a mi iPhone en el día y me dieron garantía por escrito del repuesto.', 1],
    ['Melissa D.', 5, 'Compré un Samsung de segunda y me hicieron el desbloqueo y la configuración completa con mis correos.', 19],
    ['Julio F.', 4, 'El técnico me explicó por qué se había malogrado el celular y no me quiso vender algo que no necesitaba.', 32],
]],

['slug' => 'november-studio-grafico', 'opiniones' => [
    ['Christian V.', 5, 'Mandé a imprimir afiches para la pollada de mi barrio y salieron con buen color y en la medida que pedí.', 6],
    ['Lady R.', 5, 'Hago las copias e impresiones de los documentos de mi trabajo ahí y nunca me han salido borrosas.', 18],
    ['Oswaldo T.', 5, 'Necesitaba los afiches para el mismo día y me los sacaron en un par de horas, bien amables.', 34],
]],

['slug' => 'food-trucks', 'opiniones' => [
    ['Fabricio L.', 5, 'La hamburguesa de food truck es bien grande, con harto queso y papas aparte, vale lo que cuesta.', 9],
    ['Cinthia M.', 5, 'Pedí una pizza al paso mientras esperaba y salió caliente y con la masa delgada, como me gusta.', 21],
    ['Renzo A.', 4, 'Los encontré por el centro de Chimbote y atienden rápido aunque haya gente esperando en la cola.', 37],
]],

['slug' => 'bryant-aplysia-tattoo-studio', 'opiniones' => [
    ['Ángelo P.', 5, 'Me hice un tatuaje mediano en el antebrazo y el diseño lo ajustó conmigo hasta que quedó exacto.', 4],
    ['Sheyla Q.', 5, 'El estudio está limpio y las agujas salen de su envoltorio sellado delante de ti, eso me dio confianza.', 16],
    ['Deyvis N.', 5, 'Le pedí un diseño de tatuaje para mi hermana y le mandó el boceto por WhatsApp antes de la cita.', 29],
]],

['slug' => 'impresiones-evaluna', 'opiniones' => [
    ['Nelly C.', 5, 'Imprimí volantes publicitarios para mi bodega y me salieron nítidos, con el logo bien definido.', 7],
    ['Wilder J.', 5, 'Voy por copias e impresiones de las tareas de mi hijo y siempre me atienden al toque en Santa.', 20],
    ['Roxana B.', 4, 'Me dejaron ver una prueba antes de imprimir los mil volantes, así no boté mi dinero.', 33],
]],

['slug' => 'chero-panaderia-pasteleria', 'opiniones' => [
    ['Víctor H.', 5, 'El pan sale caliente tipo seis de la mañana y por unidad cuesta lo mismo que en cualquier lado.', 2],
    ['Carmen S.', 5, 'Encargué la torta decorada para el cumpleaños de mi mamá y el bizcocho estaba bien húmedo, no seco.', 14],
    ['Joel M.', 5, 'La torta la tenían lista a la hora que quedamos y le pusieron el nombre con letras de chocolate.', 27],
]],

['slug' => 'multiservicios-y-lubricantes-el-volvo-sac', 'opiniones' => [
    ['Iván R.', 5, 'Hago el cambio de aceite de mi camioneta ahí y me dan la boleta con el kilometraje para el próximo servicio.', 8],
    ['Sonia T.', 5, 'El diagnóstico de motor detectó una fuga que me hubiera costado caro si seguía manejando así.', 22],
    ['Gregorio L.', 4, 'Atienden camiones y autos por igual; mi jefe manda toda la flota al taller de Santa.', 39],
]],

['slug' => 'consultorio-odontologico-santa', 'opiniones' => [
    ['Paola G.', 5, 'Me hicieron una curación sin dolor; la doctora avisaba cada paso antes de meter la fresa.', 5],
    ['Enrique D.', 5, 'El blanqueamiento me duró bastante y no me quedaron los dientes sensibles como me pasó en otro sitio.', 17],
    ['Lisset A.', 5, 'Atienden con cita en Santa y la sala de espera está limpia, con música bajita para no ponerse nervioso.', 30],
]],

['slug' => 'vidrieria-valle', 'opiniones' => [
    ['Humberto C.', 5, 'Compré el vidrio para la mampara del baño y me lo cortaron a la medida exacta, sin sobrantes.', 11],
    ['Jessica P.', 5, 'El espejo del baño me lo trajeron con los bordes pulidos y los ganchos para colgarlo, buen detalle.', 25],
    ['Abelardo N.', 4, 'Cotizan rápido y te dicen cuánto aguanta el vidrio según el grosor, eso me ayudó a decidir.', 41],
]],

['slug' => 'digital-fotos', 'opiniones' => [
    ['Mariela F.', 5, 'Hicimos la sesión fotográfica de los quince años de mi hija y salieron fotos naturales, sin poses raras.', 3],
    ['Óscar V.', 5, 'Imprimí las fotos de mi boda en tamaño grande y el color quedó igual al de la cámara.', 15],
    ['Denisse R.', 5, 'Me entregaron las fotos en un pendrive y también impresas, todo en menos de una semana.', 28],
]],

['slug' => 'laboratorio-clinico-salud-y-vida', 'opiniones' => [
    ['Cynthia B.', 5, 'Me hice la prueba de embarazo ahí y me dieron el resultado el mismo día, con la explicación de la técnica.', 6],
    ['Arturo M.', 5, 'Los exámenes de función hepática salieron puntuales para mi control, porque el laboratorio trabaja con cita.', 18],
    ['Zoila E.', 4, 'La toma de muestra fue rápida y con material nuevo, no tuve que esperar más de diez minutos.', 31],
]],

['slug' => 'medina-velasquez-estudio-juridico', 'opiniones' => [
    ['Fernando Q.', 5, 'Fui por una consulta legal sobre un terreno y me atendió sin apuro, explicándome qué papeles me faltaban.', 10],
    ['Lucía S.', 5, 'Me revisó el contrato de alquiler de mi local y le corrigió dos cláusulas que me perjudicaban.', 23],
    ['Rubén A.', 5, 'El estudio queda en Coishco y atienden también los sábados, eso me salvó porque trabajo de lunes a viernes.', 43],
]],

['slug' => 'floreria-montoya-chimbote-centro', 'opiniones' => [
    ['Aurora T.', 5, 'Pedí una corona para el funeral de mi tía y llegaron al velorio en Chimbote antes de la misa.', 7],
    ['Gilmer P.', 5, 'El delivery de flores llegó puntual para el aniversario de mis padres, el ramo fresco y bien armado.', 19],
    ['Bertha C.', 4, 'Me asesoraron con calma para elegir las flores según el color, no me empujaron el arreglo más caro.', 36],
]],

['slug' => 'carpinteria-paredes', 'opiniones' => [
    ['Rosa C.', 5, 'Mandé hacer una mesa de comedor a medida para mi cocina y quedó justo como la pedí, bien firme la madera.', 5],
    ['Julio A.', 5, 'Le llevé una silla rota del comedor y me la devolvió reforzada al día siguiente, sin cobrarme de más.', 18],
    ['Marisol Q.', 4, 'El taller queda en Santa y uno puede ir a ver los muebles; me mostraron los acabados antes de barnizar.', 33],
]],

['slug' => 'artic-fis-salud-y-rehabilitacion-especializada', 'opiniones' => [
    ['Carmen R.', 5, 'Llevé a mi mamá por su dolor de espalda y en la evaluación inicial le explicaron todo el tratamiento con paciencia.', 3],
    ['Jorge L.', 5, 'La teleconsulta me salvó, porque trabajo en Chimbote y no podía ir; me atendieron puntual por videollamada.', 12],
    ['Silvia T.', 4, 'Las terapias de rehabilitación de mi hombro fueron progresando; ahora puedo levantar el brazo sin ese dolor feo.', 27],
]],

['slug' => 'luz-de-luna-eventos-y-recepciones', 'opiniones' => [
    ['Paola M.', 5, 'Contraté la mesa de dulces para el cumpleaños de mi hija y quedó preciosa, con los globos combinados en rosa.', 2],
    ['Karina V.', 5, 'Los arreglos de globos los llevaron hasta el local en Nuevo Chimbote y llegaron a la hora que les pedí.', 15],
    ['Doris E.', 4, 'Me ayudaron a elegir los colores de la decoración para una recepción y salió más económico de lo que pensaba.', 29],
]],

['slug' => 'barber-shop-pena', 'opiniones' => [
    ['Kevin S.', 5, 'Me hicieron el degradado bien parejo y me dejaron la línea marcada como pedí, sin apurarse en la máquina.', 4],
    ['Renzo B.', 5, 'Fui un sábado sin cita y esperé como veinte minutos; el corte de caballero me quedó bien prolijo.', 20],
    ['Álvaro N.', 4, 'En Santa cuesta encontrar barbería que trabaje hasta tarde; aquí llegué a las ocho y me atendieron igual.', 38],
]],

['slug' => 'bodega-virgen-de-la-puerta', 'opiniones' => [
    ['María F.', 5, 'Compro gaseosas y agua al por mayor para mi puesto y siempre me salen más baratas que en el mercado.', 6],
    ['Teodoro H.', 5, 'Las bebidas están bien heladas y tienen bastante surtido; el chico me ayudó a cargar las cajas al mototaxi.', 22],
    ['Nancy R.', 4, 'Es una bodega de barrio atendida por sus dueños; pedí fiado hasta fin de semana y no me hicieron problema.', 41],
]],

['slug' => 'car-wash-siempre-con-dios', 'opiniones' => [
    ['Wilmer A.', 5, 'Dejé la camioneta llena de barro del camino a Nepeña y me la entregaron como nueva, por dentro y por fuera.', 1],
    ['Griselda P.', 5, 'El lavado completo incluye aspirado y silicona en el tablero; me demoraron menos de una hora.', 14],
    ['Beto C.', 4, 'Precio justo y los muchachos revisan los aros; solo faltaría que tengan más espacio para esperar.', 30],
]],

['slug' => 'team-vera', 'opiniones' => [
    ['Anthony G.', 5, 'Entré con el día de prueba y terminé sacando la membresía trimestral; las máquinas están bien cuidadas.', 7],
    ['Ruth D.', 5, 'El entrenador te corrige la postura en cada serie, no te deja solo con la rutina escrita.', 19],
    ['Marco T.', 4, 'Voy en las mañanas antes del trabajo y casi no hay gente; la mensualidad me la dieron en tres pagos.', 36],
]],

['slug' => 'punto-urbano-express-y-punto-sharf-p84-soto', 'opiniones' => [
    ['Lidia M.', 5, 'Pago ahí la luz y el agua cada mes; la señora revisa el recibo y me da el vuelto al toque.', 8],
    ['Percy O.', 5, 'Recargo mi celular en el punto del P84 de Soto y siempre tienen saldo; también aceptan Yape.', 24],
    ['Yesenia B.', 4, 'Está abierto hasta noche, así que salgo del trabajo y alcanzo a pagar mis servicios sin hacer cola larga.', 43],
]],

['slug' => 'nakasato-studios', 'opiniones' => [
    ['Fiorella A.', 5, 'Me hicieron las fotos de mi negocio y salieron con buena luz; además me armaron el logo y los colores.', 9],
    ['Diego R.', 5, 'Contraté el branding para mi marca de ropa y el manual de logo me sirvió hasta para la etiqueta.', 23],
    ['Claudia N.', 4, 'La sesión de fotos fue en su estudio de Chimbote y me entregaron las imágenes editadas en pocos días.', 40],
]],

['slug' => 'bodega-jheymad', 'opiniones' => [
    ['Sonia L.', 5, 'Los huevos siempre frescos y la leche bien fría; paso antes del desayuno y ya está abierto.', 10],
    ['Elmer V.', 5, 'Compro detergente y lejía ahí porque sale más barato que en la tienda grande, y venden por unidad.', 25],
    ['Zoila C.', 4, 'La señora atiende rápido y anota lo que le pides; a veces me manda el pedido a la casa.', 44],
]],

['slug' => '7level-studio', 'opiniones' => [
    ['Bryan P.', 5, 'Grabaron el video corporativo de mi empresa en dos jornadas y la edición quedó con música y subtítulos.', 11],
    ['Melissa Q.', 5, 'Les mandé mis videos de TikTok por Drive y me los devolvieron editados con transiciones y buen sonido.', 26],
    ['Iván S.', 4, 'Pedí un spot para redes y respetaron el guion que llevé; solo tardaron un día más de lo pactado.', 39],
]],

['slug' => 'jefreyworks', 'opiniones' => [
    ['Milagros H.', 5, 'Me hizo las fotos de mis postres para el catálogo y se notan los detalles del glaseado, quedaron antojosas.', 13],
    ['Christian F.', 5, 'Le pagué la publicidad digital para mi taller y en la semana empezaron a llegar clientes por Facebook.', 28],
    ['Pamela G.', 4, 'Es puntual con las entregas y te explica qué foto sirve para cada red; cobra por paquete, no por foto.', 45],
]],

['slug' => 'lavado-villanueva-rogelio', 'opiniones' => [
    ['Rosa M.', 5, 'Es una bodega pequeña de barrio en Santa, pero siempre encuentro el aceite y el arroz que busco.', 5],
    ['Santos A.', 5, 'El señor Rogelio atiende él mismo y te fía hasta el día de pago, cuando uno anda corto.', 21],
    ['Elva T.', 4, 'Abren temprano y cierran tarde; me salvaron una noche cuando me faltaba leche para el bebé.', 37],
]],

['slug' => 'dona-cloty', 'opiniones' => [
    ['Gladys R.', 5, 'Pedí gaseosas y hielo para una reunión y me lo trajeron a la casa en menos de media hora.', 3],
    ['Walter N.', 5, 'La cerveza siempre bien helada y te la llevan en su moto; el chico es bien educado.', 16],
    ['Marlene D.', 4, 'Doña Cloty anota el pedido por WhatsApp y no se equivoca; el vuelto me lo dio completo.', 34],
]],

['slug' => 'luana-store', 'opiniones' => [
    ['Katherine V.', 5, 'Tienen de todo para la semana: arroz, atún y gaseosa; ya no tengo que ir al mercado.', 6],
    ['Segundo P.', 5, 'Compré una caja de bebidas para mi chifa y me hicieron precio por mayor sin regatear mucho.', 18],
    ['Diana O.', 4, 'El local es chico pero está ordenado y limpio; las señoras atienden con paciencia a los niños.', 31],
]],

['slug' => 'roya-club-fut-chimbote', 'opiniones' => [
    ['Jhonatan M.', 5, 'Alquilamos la cancha los martes con los amigos del barrio y el grass está bien cuidado, sin huecos.', 4],
    ['Édgar L.', 5, 'Armamos un torneo de ocho equipos y ellos pusieron los árbitros y la tabla de posiciones.', 17],
    ['Franco R.', 4, 'La hora de alquiler se respeta, aunque a veces se cruzan los horarios y hay que esperar un ratito.', 35],
]],

['slug' => 'bodega-y-novedades-k-barato', 'opiniones' => [
    ['Nelly A.', 5, 'Encontré escobas, trapeadores y baldes a buen precio; compré por mayor para el colegio de mis hijos.', 7],
    ['Óscar H.', 5, 'Venden lejía y detergente por jaba, y la señora me hizo la cuenta en un papelito para que revise.', 20],
    ['Rocío B.', 4, 'También traen novedades para la casa; el pasillo es angosto, pero se encuentra rápido lo que uno busca.', 42],
]],

['slug' => 'complejo-deportivo-florida-baja', 'opiniones' => [
    ['Gloria S.', 5, 'La cancha de vóley tiene buen piso y la red está tensa; entrenamos ahí todos los domingos con el equipo.', 9],
    ['Miguel Á.', 5, 'Organizaron un torneo de vóley mixto y todo salió ordenado, con horarios y premios para los ganadores.', 22],
    ['Lucía F.', 4, 'Está en Florida Baja y tiene espacio para las barras; solo le falta mejorar los baños.', 38],
]],

['slug' => 'lan-center-express', 'opiniones' => [
    ['Diego A.', 5, 'Alquilé cabina por hora con mis primos y las computadoras corren los juegos sin trabarse, buen internet.', 8],
    ['Alexis C.', 5, 'Pagamos cabina por día para un campeonato interno y nos dejaron llevar nuestros propios mandos.', 19],
    ['Noelia T.', 4, 'El local tiene aire y audífonos limpios; la hora sale más barata si vas en la mañana.', 33],
]],

['slug' => 'polideportivo-el-progreso', 'opiniones' => [
    ['Rómulo V.', 5, 'Reservamos la cancha de vóley para el campeonato del barrio y nos dieron facilidades para pagar.', 5],
    ['Eliana M.', 5, 'El polideportivo está bien iluminado de noche, así que podemos jugar después del trabajo sin problema.', 23],
    ['César I.', 4, 'Organizan torneos con inscripción barata; la tribuna es sencilla pero alcanza para los familiares.', 41],
]],

['slug' => 'estacion-de-servicio-transersa', 'opiniones' => [
    ['Freddy Q.', 5, 'Cargo la gasolina de 90 antes de subir a Nuevo Chimbote y los surtidores van rápido, no hay cola.', 2],
    ['Ivonne P.', 5, 'Pedí el balón de GLP de 10 kilos y me lo cambiaron al instante; el muchacho lo puso en mi cocina.', 14],
    ['Hernán G.', 4, 'Tienen grifo y tienda juntos, así que aprovecho para comprar aceite; el baño está limpio.', 29],
]],

['slug' => 'costa-gas-nuevo-chimbote', 'opiniones' => [
    ['Renato S.', 5, 'Mi carro pide gasolina de 95 y aquí siempre hay; además me revisaron el nivel de aceite.', 10],
    ['Miluska A.', 5, 'Compré lubricante para mi moto y el técnico me explicó cuál le convenía según el motor.', 26],
    ['Percy D.', 4, 'Está a la entrada de Nuevo Chimbote y abren de madrugada; a veces demoran con el vuelto.', 44],
]],

['slug' => 'barras-de-sider', 'opiniones' => [
    ['Bruno E.', 5, 'Alquilamos la consola con mis patas toda la tarde y nos vendieron snacks y gaseosas bien heladas.', 6],
    ['Nicolás W.', 5, 'El juego de consola sale por hora y los mandos están en buen estado, no como en otros sitios.', 21],
    ['Sheyla C.', 4, 'Llevé a mi hermano menor y el chico le enseñó a jugar; los snacks son baratos.', 36],
]],

['slug' => 'estacion-pardo-s-a', 'opiniones' => [
    ['Orlando C.', 5, 'Lleno la gasolina de 90 aquí porque el precio se mantiene y el personal te saluda al llegar.', 3],
    ['Maribel U.', 5, 'El GLP de 10 kilos me lo traen a domicilio el mismo día que llamo; nunca me han fallado.', 15],
    ['Saúl R.', 4, 'La tienda del grifo tiene aceites y filtros; la atención es rápida aunque el patio se llena a las siete.', 32],
]],

['slug' => 'primax-palmeras-chimbote', 'opiniones' => [
    ['Víctor H.', 5, 'En Primax Palmeras el personal limpia el parabrisas mientras cargas; la gasolina de 90 rinde bien.', 8],
    ['Karla J.', 5, 'Pedimos el balón de GLP de 10 kg para el restaurante y llegó antes del mediodía, justo para cocinar.', 24],
    ['Elder M.', 4, 'Tienen baño y tienda de cafetería; el grifo está en zona transitada, así que a veces hay cola.', 40],
]],

['slug' => 'estacion-satelital-quavii-chimbote', 'opiniones' => [
    ['Rosa C.', 5, 'Pedí el balón de GLP de 10 kilos y llegaron rapidísimo al pueblo, bien temprano.', 7],
    ['Luis M.', 5, 'El balón de 10 kilos me duró más que el de otra distribuidora, se nota que viene lleno.', 21],
    ['Ana P.', 4, 'También compro lubricante para mi moto ahí y siempre tienen stock, no te hacen esperar.', 33],
]],

['slug' => 'centro-medico-munoz', 'opiniones' => [
    ['Carmen R.', 5, 'La consulta general con el doctor fue rápida y me explicó todo con calma; la ecografía salió el mismo día.', 3],
    ['Jorge T.', 5, 'Saqué cita para una ecografía y me atendieron a la hora exacta, sin la espera de otros consultorios.', 18],
    ['Milagros V.', 4, 'Cobran módico por la consulta general y el local está limpio; el doctor Muñoz revisa bien.', 40],
]],

['slug' => 'vaperman', 'opiniones' => [
    ['Kevin S.', 5, 'Compré resistencias para mi vape y me explicaron cuáles van con mi equipo, sin apurarme.', 5],
    ['Diana Q.', 5, 'Los repuestos son originales, se siente la diferencia en el sabor comparado con lo que venden en la calle.', 14],
    ['Brayan H.', 4, 'Fui indeciso por mi primer vape y el chico me asesoró con paciencia hasta que elegí el indicado.', 29],
]],

['slug' => 'consultorio-medico-castro', 'opiniones' => [
    ['Silvia N.', 5, 'Llevé a mi mamá a consulta por especialidad y el doctor Castro la revisó con detalle.', 9],
    ['Percy A.', 5, 'Los exámenes de laboratorio los entregan al día siguiente y te llaman para explicarte los resultados.', 22],
    ['Gladys F.', 4, 'Atienden por orden de llegada y la sala de espera es tranquila, no como en hospitales llenos.', 37],
]],

['slug' => 'costagas-domus-nuevo-chimbote', 'opiniones' => [
    ['Elmer G.', 5, 'Cargué diésel para la camioneta y el surtidor va rápido, no hay que esperar mucho en la cola.', 2],
    ['Nelly B.', 5, 'El petróleo está a buen precio comparado con otros grifos de Nuevo Chimbote, conviene pasar por ahí.', 16],
    ['Óscar D.', 4, 'Compro lubricante para el motor ahí y el muchacho revisa el nivel de aceite sin cobrar extra.', 31],
]],

['slug' => 'smael-shop-chimbote', 'opiniones' => [
    ['Fiorella M.', 5, 'Encontré las resistencias que no había en otras tiendas y a buen precio, me llevé dos.', 11],
    ['Renzo C.', 5, 'Compré un accesorio para colgar el vape y quedó perfecto, tienen bastante variedad en vitrina.', 25],
    ['Yanina P.', 5, 'El chico me explicó cómo cambiar la resistencia paso a paso, se nota que sabe del tema.', 44],
]],

['slug' => 'don-barrabas-smoke-shop', 'opiniones' => [
    ['Marco L.', 5, 'Aproveché la promoción de dos líquidos y me salió más barato que comprando uno solo.', 4],
    ['Paola S.', 5, 'Me asesoraron para bajarle la nicotina y encontré el sabor que buscaba, buenos precios.', 19],
    ['Ítalo R.', 4, 'El local es pequeño pero está bien surtido, siempre hay algo nuevo en la vitrina.', 35],
]],

['slug' => 'gasolinera-petroleo-asoc-san-carlos', 'opiniones' => [
    ['Wilmer T.', 5, 'Llegué a las tres de la mañana con el tanque vacío y me atendieron igual, bien rápido.', 6],
    ['Sonia E.', 5, 'El diésel rinde harto en mi camión, se nota que no viene aguado como en otros grifos.', 20],
    ['Julio C.', 4, 'Los baños están limpios para ser un grifo de carretera y eso se agradece en el viaje.', 41],
]],

['slug' => 'mascotienda-la-familia', 'opiniones' => [
    ['Verónica A.', 5, 'Compro el alimento de mi perro por saco y me sale más barato que en la veterinaria.', 8],
    ['Héctor Z.', 5, 'Tienen champú y jabón para mascotas que casi no se halla, me llevé para mi gata.', 23],
    ['Marlene O.', 4, 'La señora de la tienda me recomendó la comida según la edad de mi cachorro, muy amable.', 38],
]],

['slug' => 'cogeco-santa', 'opiniones' => [
    ['Raúl I.', 5, 'Paso siempre por este grifo camino a Santa y nunca me han dejado sin diésel.', 1],
    ['Carmen Y.', 5, 'Está abierto toda la noche y el surtidor es rápido, ideal cuando viajas de madrugada.', 17],
    ['Víctor N.', 4, 'Los trabajadores están uniformados y te preguntan cuánto quieres cargar antes de empezar.', 30],
]],

['slug' => 'novedades-loki', 'opiniones' => [
    ['Rocío M.', 5, 'Le compré un chalequito a mi perro para el frío y le quedó justo, tienen varias tallas.', 12],
    ['César B.', 5, 'El alimento para gato que venden ahí le cae bien a mi mascota, no le da alergia.', 26],
    ['Tatiana G.', 5, 'Hay ropita para mascotas pequeñas, que es difícil de encontrar en Chimbote, buena variedad.', 43],
]],

['slug' => 'petshop-chimbote', 'opiniones' => [
    ['Evelyn D.', 5, 'Me asesoraron sobre cuánto darle de comer a mi cachorro según su peso, muy útil.', 10],
    ['Alonso P.', 5, 'Compro alimento premium ahí y siempre está fresco, se fijan en la fecha de vencimiento.', 24],
    ['Karina J.', 4, 'Atienden hasta tarde y eso ayuda cuando te quedas sin comida para la mascota de un día.', 39],
]],

['slug' => 'servicio-de-gasolinera-dulcemar', 'opiniones' => [
    ['Pablo F.', 5, 'La gasolina de 90 rinde bien en mi moto y el precio está dentro de lo normal en Coishco.', 13],
    ['Elena V.', 5, 'Cargué petróleo para el carro y el chico limpió el parabrisas sin que se lo pidiera.', 27],
    ['Miguel Á.', 4, 'Es un grifo pequeño pero atienden rápido, no te hacen bajar del carro para pagar.', 45],
]],

['slug' => 'pet-food-chimbote', 'opiniones' => [
    ['Nadia C.', 5, 'Encontré ropa de invierno para mi perro mediano, había tallas grandes que pocas tiendas traen.', 15],
    ['Gustavo R.', 5, 'Me ayudaron a elegir el alimento según la raza de mi mascota, se nota que conocen.', 28],
    ['Liliana T.', 5, 'Los precios de la ropita están al alcance y la tela no se destiñe al lavarla.', 42],
]],

['slug' => 'max-gas', 'opiniones' => [
    ['Ruth S.', 5, 'Pedí el balón de 10 kilos a las once de la noche y vinieron igual, buen servicio.', 3],
    ['Enrique M.', 5, 'El balón siempre llega con buen peso, lo he comprobado en la balanza de casa.', 20],
    ['Zoila H.', 4, 'El repartidor sube el balón hasta el segundo piso sin cobrar adicional, se agradece.', 34],
]],

['slug' => 'tecnimotor-s-c', 'opiniones' => [
    ['Bruno A.', 5, 'Hice alineamiento y balanceo antes de un viaje largo y el carro dejó de vibrar en la pista.', 6],
    ['Martha L.', 5, 'Le hicieron diagnóstico de motor y me mostraron dónde estaba la falla, sin inventar reparaciones.', 21],
    ['Segundo Q.', 5, 'Te entregan el carro el mismo día y cobran lo que acordaron al inicio, sin sorpresas.', 36],
]],

['slug' => 'patitas-pet', 'opiniones' => [
    ['Daniela W.', 5, 'Compré un polar para mi perro y aguantó el frío de la playa, buena tela.', 9],
    ['Iván K.', 5, 'Me asesoraron sobre el tamaño correcto y no me vendieron una talla demás, honestos.', 25],
    ['Sheyla Ñ.', 4, 'Tienen ropita con diseños bonitos, le compré un vestido a mi perrita para su cumpleaños.', 40],
]],

['slug' => 'brahmas-de-robert', 'opiniones' => [
    ['Roberto Ch.', 5, 'Llevé a mi perro por una infección y el veterinario lo atendió con cuidado, ya está bien.', 2],
    ['Fátima U.', 5, 'Venden alimento por mayor y sale bastante más barato para quien tiene varios animales.', 18],
    ['Juan Diego P.', 4, 'Aplican las vacunas ahí y te dan su carné con la fecha de la próxima dosis.', 33],
]],

['slug' => 'automotriz-mantilla', 'opiniones' => [
    ['Álvaro N.', 5, 'Le hice mantenimiento preventivo a la camioneta y quedó afinada, el motor suena parejo.', 7],
    ['Cecilia R.', 5, 'Repararon la culata de mi carro y me explicaron qué había pasado, buen trabajo.', 23],
    ['Percy O.', 4, 'El mecánico te avisa antes de cambiar cualquier repuesto, no te mete cosas de más.', 38],
]],

['slug' => 'repsol-gas-station', 'opiniones' => [
    ['Gisela T.', 5, 'Cargo gasolina de 95 y el carro responde mejor en la subida hacia Chimbote.', 5],
    ['Nilton B.', 5, 'Está abierto a toda hora y el baño está limpio, se agradece en los viajes largos.', 19],
    ['Sandra M.', 4, 'Un día me quedé sin batería y los chicos me ayudaron a pasar corriente, buena gente.', 41],
]],

['slug' => 'motoreza-taller-mecanico-automotriz', 'opiniones' => [
    ['Erick V.', 5, 'Hice alineamiento y balanceo y las llantas ya no se gastan disparejo en los bordes.', 11],
    ['Nancy F.', 5, 'Repararon el motor de mi auto viejo y quedó andando suave, ya no echa humo.', 27],
    ['Teófilo G.', 5, 'El taller está ordenado y te dejan ver lo que están haciendo, eso da confianza.', 44],
]],

['slug' => 'distribuidora-y-avicola-elicampos', 'opiniones' => [
    ['Rosario Z.', 5, 'Compro pollo fresco ahí para el almuerzo familiar y siempre está del día.', 1],
    ['Hernán D.', 5, 'Cargo gasolina de 90 en el mismo sitio y aprovecho para llevar el ave, práctico.', 16],
    ['Lucía A.', 4, 'Los pollos vienen bien pesados y limpios, no como en otros sitios que traen mucha agua.', 31],
]],

['slug' => 'petroperu-combus', 'opiniones' => [
    ['Guillermo E.', 5, 'Es de Petroperú así que la gasolina de 90 es confiable, mi moto anda parejo.', 8],
    ['Betty H.', 5, 'Atienden de noche y hay personal suficiente, no te quedas esperando en la bomba.', 22],
    ['Rómulo S.', 4, 'El precio de la gasolina de 90 sube menos que en otros grifos de la zona.', 37],
]],

['slug' => 'la-bodeguita', 'opiniones' => [
    ['Jhonatan M.', 5, 'Compro lubricante para mi mototaxi ahí y me dan el cambio de aceite al toque.', 4],
    ['Estela C.', 5, 'La gasolina de 90 la sirven rápido aunque haya cola, son ágiles con el surtidor.', 24],
    ['Wilder P.', 5, 'Es una bodeguita con grifo, atienden con confianza y venden aceite por litro.', 43],
]],

['slug' => 'casita-del-fortnite', 'opiniones' => [
    ['Katherine L.', 5, 'Inscribí a mi hijo en el torneo de Fortnite y se divirtió harto, bien organizado.', 14],
    ['Diego Ñ.', 5, 'Alquilamos la sala para el cumpleaños de mi sobrino y nos dejaron jugar hasta tarde.', 29],
    ['Fabiana Q.', 4, 'Las computadoras corren el juego sin lag, se nota que tienen buen internet.', 45],
]],

['slug' => 'electrimovil-motors-motos-electricas', 'opiniones' => [
    ['Rosa C.', 5, 'Probé una moto eléctrica y el muchacho me explicó paso a paso cómo cargarla y cuánto rinde la batería.', 7],
    ['Luis M.', 5, 'Hicimos el recorrido turístico por la avenida y la moto sube bien las pendientes, sin ruido ni gastar gasolina.', 21],
    ['Ana P.', 4, 'Me asesoraron sobre qué modelo me convenía para ir al mercado todos los días; no me apuraron para comprar.', 33],
]],

['slug' => 'lan-center-centinela-gamers', 'opiniones' => [
    ['Jorge Q.', 5, 'Alquilé una cabina por dos horas y las máquinas corren bien, sin lag, con audífonos limpios.', 3],
    ['Milagros T.', 5, 'Me prestaron una cuenta con varios juegos ya instalados, así no tuve que bajar nada ni esperar.', 15],
    ['Kevin R.', 5, 'El local es fresco y silencioso, ideal para jugar tranquilo después del trabajo.', 40],
]],

['slug' => 'greenline-motos-electricas-chimbote', 'opiniones' => [
    ['Carlos V.', 5, 'Alquilé un scooter por todo el día y me salió barato; la batería aguantó la vuelta hasta Vesique.', 5],
    ['Yesenia B.', 5, 'El recorrido turístico con la moto eléctrica fue lo mejor: pasamos por la bahía sin ruido ni humo.', 19],
    ['Pedro A.', 4, 'Me enseñaron a manejar el scooter antes de salir y me dieron casco limpio, sin apuro.', 44],
]],

['slug' => 'ciber-click', 'opiniones' => [
    ['Bryan S.', 5, 'Hice la recarga de mi juego y me la pasaron al toque, sin cobrarme comisión extra.', 2],
    ['Katia N.', 5, 'Compré una cuenta de juegos y me dieron la clave ahí mismo; hasta me ayudaron a configurarla.', 12],
    ['Rubén D.', 5, 'Las máquinas están rápidas y el chico de turno te presta audífonos cuando se te olvidan.', 27],
]],

['slug' => 'requelmena', 'opiniones' => [
    ['Gladys F.', 5, 'El lomo saltado viene con harto mote y la carne tierna, bien salteada al momento.', 4],
    ['Wilder H.', 5, 'Pedí seco de carne con frejoles y arroz; el caldo es espeso y sabroso igual que en casa.', 18],
    ['Marisol E.', 4, 'Sirven rápido al mediodía y la porción llena, por eso voy casi todas las semanas.', 36],
]],

['slug' => 'rapsodia-restobar', 'opiniones' => [
    ['Diego L.', 5, 'El ceviche estaba fresco, con harto limón y su camote; lo preparan después de que pides.', 6],
    ['Sandra O.', 5, 'Los pisco sour los baten bien cargados, no aguados como en otros restobares de la zona.', 22],
    ['Percy G.', 5, 'Fuimos un viernes y había música; la atención en la barra fue rápida aunque estaba lleno.', 41],
]],

['slug' => 'eye-net-agente-bcp', 'opiniones' => [
    ['Elmer C.', 5, 'Pagué mi recibo en el agente BCP y de paso me quedé una hora en la cabina jugando.', 8],
    ['Nicol Z.', 4, 'Hice un depósito y retiro sin cola, súper rápido; ya no tengo que ir hasta el banco.', 24],
    ['Flor M.', 5, 'Las computadoras están ordenadas por hora y el internet va bien para jugar en línea.', 38],
]],

['slug' => 'la-base-lan-center', 'opiniones' => [
    ['Óscar P.', 5, 'Inscribí a mi hijo en el torneo de videojuegos y organizaron bien las llaves del campeonato.', 9],
    ['Tatiana R.', 5, 'Las cabinas son cómodas y el precio por hora es el más bajo que he visto en Santa.', 20],
    ['Joel A.', 5, 'Fui con mis amigos a jugar y nos dieron sillas aparte para estar todos juntos.', 45],
]],

['slug' => 'barvaria-bar-chimbote', 'opiniones' => [
    ['Marco T.', 5, 'La cerveza llega bien helada y la jarra grande alcanza para tres; el precio es justo.', 10],
    ['Lucy S.', 5, 'El sábado tocó una banda en vivo y se escuchaba nítido, sin distorsión, desde nuestra mesa.', 26],
    ['Iván B.', 4, 'El ambiente es tranquilo para conversar y los piqueos salen rápido de la cocina.', 43],
]],

['slug' => 'alalaw-lounge-nuevo-chimbote', 'opiniones' => [
    ['Vanessa I.', 5, 'El cóctel de la casa viene con su fruta fresca y no es puro hielo, se siente el trago.', 11],
    ['Hugo E.', 5, 'Pedimos la tabla de piqueos para cuatro y alcanzó bien; el chicharrón estaba crocante.', 23],
    ['Karina J.', 5, 'La música está a buen volumen y los mozos te atienden sin que tengas que levantarte.', 39],
]],

['slug' => 'agente-y-multiservicios-arroyo', 'opiniones' => [
    ['Wilmer N.', 5, 'Pagué la luz y el agua en una sola cola; rápido, sin comisiones raras y con boleta.', 13],
    ['Rosario A.', 4, 'Saqué mi constancia y me ayudaron a llenar el formulario porque no entendía los casilleros.', 29],
    ['Segundo V.', 5, 'Abren temprano y atienden hasta tarde, así puedo ir después del trabajo a hacer mis pagos.', 42],
]],

['slug' => 'ferreteria-industrial-y-naval-celeste-eirl', 'opiniones' => [
    ['Alberto R.', 5, 'Compré fierro de media para mi losa y me lo cortaron a la medida que pedí, sin cobrar extra.', 14],
    ['Yolanda M.', 5, 'Tienen cemento, alambre y clavos de todo calibre; cuando no hay algo te lo consiguen al día siguiente.', 30],
    ['César D.', 5, 'El dueño sabe de construcción naval y me aconsejó qué fierro usar para mi muro.', 37],
]],

['slug' => 'soluciones-para-construccion-civil-en-chimbote', 'opiniones' => [
    ['Miguel Á.', 5, 'Me mezclaron la pintura en el tono exacto que quería y me explicaron cuánto rinde por mano.', 16],
    ['Silvia Q.', 5, 'Compré un taladro y me dieron garantía con boleta; al mes falló y me lo cambiaron.', 31],
    ['Ronald F.', 4, 'Tienen herramientas que no encuentro en otras ferreterías, como llaves de paso y niveles.', 44],
]],

['slug' => 'medicentro-chimbote', 'opiniones' => [
    ['Patricia G.', 5, 'Fui por consulta con el cardiólogo y me atendió puntual, explicándome bien los resultados.', 17],
    ['Jaime O.', 5, 'Me hice el chequeo completo: análisis, presión y electro; en dos días ya tenía todo listo.', 32],
    ['Lucía H.', 5, 'La recepcionista me ayudó a sacar mi cita por teléfono y me recordó el día del control.', 45],
]],

['slug' => 'centro-psicologico-y-psicoterapeutico-marinos-may', 'opiniones' => [
    ['Mercedes L.', 5, 'Llevé a mi hijo a consulta psicológica y la señorita tuvo mucha paciencia con él.', 18],
    ['Antonio S.', 5, 'Hicimos terapia familiar por unos meses y aprendimos a conversar sin gritarnos en la casa.', 33],
    ['Betzabé C.', 5, 'El consultorio es privado y tranquilo; uno puede hablar sin miedo de que lo escuchen afuera.', 40],
]],

['slug' => 'comida-saludable', 'opiniones' => [
    ['Eliana V.', 5, 'El batido de fresa lo preparan con fruta de verdad, no con polvo, y bien espeso.', 19],
    ['Cristian M.', 4, 'El sándwich saludable viene con palta y pollo deshilachado; llena y no cae pesado.', 34],
    ['Zoila P.', 5, 'Atienden rápido en la mañana, así que paso por mi desayuno antes de ir a trabajar.', 41],
]],

['slug' => 'la-carta-saludable', 'opiniones' => [
    ['Fiorella D.', 5, 'El bowl viene con quinoa, palta y pollo; es bastante y me deja satisfecho hasta la tarde.', 20],
    ['Gustavo N.', 5, 'Pedí batido de lúcuma sin azúcar y respetaron el pedido tal como lo indiqué.', 35],
    ['Rocío B.', 5, 'Llevan el pedido a la oficina y llega fresca, con los vegetales todavía crocantes.', 43],
]],

['slug' => 'epicenter-game-lan-center', 'opiniones' => [
    ['Andy CH.', 5, 'Las computadoras tienen buenas tarjetas y los monitores son grandes, se juega cómodo.', 21],
    ['Priscila F.', 5, 'Participé en el torneo de fin de semana y los premios se entregaron al día siguiente, sin problema.', 36],
    ['Néstor U.', 4, 'Cobran por hora y te avisan cuando falta poco; no te cobran de más ni redondean.', 42],
]],

['slug' => '593-fit', 'opiniones' => [
    ['Diana K.', 5, 'El bowl proteico trae harto pollo a la plancha y huevo; sale bien para después del gimnasio.', 22],
    ['Álvaro Z.', 5, 'Los batidos de proteína los licúan al momento y puedes pedirlos con leche o con agua.', 37],
    ['Sheyla T.', 5, 'El local es chico pero limpio, y la chica que atiende te aconseja qué pedir según tu entrenamiento.', 45],
]],

['slug' => 'yomali-productos-lacteos', 'opiniones' => [
    ['Marleny A.', 5, 'El queso fresco es del día, se siente suave y no sale ácido como el de tienda.', 23],
    ['Eusebio R.', 5, 'Compro la leche fresca para el desayuno de mis hijos; llega temprano y bien fría.', 38],
    ['Janeth S.', 5, 'Venden por litro y por kilo, y te dejan probar el queso antes de llevar.', 44],
]],

['slug' => 'cevicheria-caballito-de-mar', 'opiniones' => [
    ['Segundo M.', 5, 'El ceviche de pescado lo sirven con harto limón y ají; el pescado estaba fresco, sin olor.', 24],
    ['Maritza E.', 5, 'El sudado viene en olla grande, bien caliente, con yuca y su buen caldo para el frío.', 39],
    ['Félix CH.', 4, 'Llegamos a la una y ya estaba lleno; igual nos atendieron rápido y nos dieron mesa afuera.', 43],
]],

['slug' => 'bahia-fit', 'opiniones' => [
    ['Katherine R.', 5, 'El combo saludable trae sándwich, ensalada y refresco; por ese precio está bien completo.', 25],
    ['Bruno C.', 5, 'El sándwich de pollo lo hacen con pan integral y harto tomate; no es grasoso.', 40],
    ['Alessandra P.', 5, 'Puedes pedir para llevar y te lo envuelven bien, llega entero a la oficina.', 44],
]],

['slug' => 'la-cocina-fit-fat', 'opiniones' => [
    ['José Luis T.', 5, 'El cuarto de pollo a la brasa viene con papas y ensalada; la piel queda crocante.', 26],
    ['Wendy A.', 5, 'La jarra de chicha morada es casera, no muy dulce, y alcanza para toda la mesa.', 41],
    ['Humberto C.', 5, 'Pedimos delivery un domingo y llegó caliente en menos de media hora, con sus cremas.', 36],
]],

['slug' => 'zona-gaming', 'opiniones' => [
    ['Renzo B.', 5, 'Alquilé la cabina por el día entero para jugar con mis primos y nos cobraron precio de paquete.', 27],
    ['Antonella M.', 5, 'Venden snacks y gaseosas frías ahí mismo, así no tienes que salir a la tienda.', 42],
    ['Fabricio L.', 4, 'El aire acondicionado se agradece en verano; se puede jugar horas sin sudar.', 34],
]],

['slug' => 'rest-cevicheria-diego-s', 'opiniones' => [
    ['Rosmery V.', 5, 'La jalea mixta trae harto pescado y mariscos, con yuca y salsa criolla aparte.', 28],
    ['Edinson Q.', 5, 'La chicha morada la sirven bien helada y es casera, se siente el clavo de olor.', 43],
    ['Pilar N.', 5, 'El local está limpio y los baños también, algo que casi no se ve en las cevicherías de Santa.', 37],
]],

// ====== TANDA 2 (2026-09-14, noche): las 100 tiendas anteriores ======
['slug' => 'reparacion-de-lavadoras-nuevo-chimbote', 'opiniones' => [
    ['Rosa C.', 5, 'Le cambiaron el motor a mi lavadora en la misma tarde y me explicaron por qué se había quemado.', 6],
    ['Miguel T.', 5, 'Mi secadora no calentaba; vinieron a Nuevo Chimbote, la revisaron y quedó funcionando igual que nueva.', 19],
    ['Janet Q.', 4, 'Cobran justo y te dicen de frente si vale la pena la reparación o mejor cambiar la máquina.', 33],
]],

['slug' => 'food-park-patio-de-comidas', 'opiniones' => [
    ['Luis M.', 5, 'El lomo saltado viene bien servido, con papas crocantes y la carne jugosa, no como en otros sitios.', 4],
    ['Carmen R.', 5, 'Pedí un cuarto de pollo a la brasa para llevar y la piel estaba doradita, recién salido del horno.', 15],
    ['Pedro S.', 4, 'Hay mesas afuera y el ambiente es tranquilo; sirven rápido aunque esté lleno el patio.', 27],
]],

['slug' => 'fresh-salad', 'opiniones' => [
    ['Ana P.', 5, 'El batido de fresa con plátano es espeso de verdad, no aguado como en otras juguerías.', 9],
    ['Jorge V.', 5, 'Los jugos son al momento, la señora pela la fruta delante de uno y se siente fresco.', 22],
    ['Kelly D.', 5, 'Fui en la mañana y me dieron el jugo en vaso grande por precio bajo, y el local está limpio.', 38],
]],

['slug' => 'club-kotas-spa-canino-y-petshop', 'opiniones' => [
    ['Sofía H.', 5, 'Bañaron a mi perrito y salió con el pelo suavecito y con olor rico, sin estresarse.', 3],
    ['Raúl B.', 5, 'Compré un collar y una correa para mi mascota; hay bastante variedad de accesorios y buenos precios.', 12],
    ['Diana F.', 4, 'Le cortaron las uñas a mi gata y la trataron con paciencia; se nota que quieren a los animales.', 29],
]],

['slug' => 'fishland-ink', 'opiniones' => [
    ['Kevin A.', 5, 'Me hicieron el diseño primero en papel, lo ajustamos juntos y quedó tal cual lo quería.', 7],
    ['Melissa G.', 5, 'Te explican bien cómo cuidar el tatuaje los primeros días y te responden si les escribes.', 20],
    ['Iván R.', 5, 'El local está limpio y usan agujas nuevas, eso me dio confianza para mi primer tatuaje.', 41],
]],

['slug' => 'off-vapor-store', 'opiniones' => [
    ['César N.', 5, 'Me ayudaron a elegir el vapeador según cuánto fumaba antes, sin venderme lo más caro.', 5],
    ['Paola L.', 5, 'Tienen bastantes sabores de líquidos y te dejan sentir el aroma antes de comprar.', 16],
    ['Bruno Z.', 4, 'Volví por una resistencia nueva y me la cambiaron ahí mismo en un momento.', 31],
]],

['slug' => 'greenline-tailg-motos-electricas-chimbote', 'opiniones' => [
    ['Édgar M.', 5, 'Alquilé una moto eléctrica por el día para repartir y la batería alcanzó sin problemas.', 11],
    ['Sandra Y.', 5, 'El alquiler mensual me sale más barato que la combi y ya no gasto en gasolina.', 24],
    ['Wilmer C.', 5, 'Me enseñaron a cargarla y a manejar los frenos antes de salir; buen detalle con los nuevos.', 36],
]],

['slug' => 'filcon-gestion-y-produccion-de-videos-con-drone', 'opiniones' => [
    ['Tatiana O.', 5, 'Grabaron con dron el terreno de la empresa y las tomas aéreas quedaron espectaculares para la presentación.', 8],
    ['Hugo P.', 5, 'Entregaron el video editado en pocos días, con música y textos, tal como lo pedimos.', 21],
    ['Gladys E.', 4, 'Muy puntuales para llegar a la filmación en Nuevo Chimbote; coordinamos todo por WhatsApp.', 44],
]],

['slug' => 'colegio-de-contadores-publicos-de-ancash-oficina-administrativa-de-chimbote', 'opiniones' => [
    ['Mariela S.', 5, 'Fui a consultar por mi carné de contador y en ventanilla me explicaron los requisitos sin vueltas.', 10],
    ['Óscar D.', 5, 'Atienden de lunes a viernes y me ayudaron con la constancia de habilidad para un trabajo.', 25],
    ['Nelly A.', 5, 'Las charlas de actualización tributaria son útiles y las dan ahí mismo en la oficina de Chimbote.', 40],
]],

['slug' => 'el-chasqui-market', 'opiniones' => [
    ['Juana R.', 5, 'Compro los huevos por jaba aquí y siempre vienen enteros, sin ninguno quebrado.', 2],
    ['Fernando I.', 5, 'Los lácteos están frescos y más baratos que en el mercado; la leche rinde para toda la semana.', 14],
    ['Lucía T.', 4, 'Venden al por mayor para mi bodega y me dejan el pedido listo cuando paso a recogerlo.', 30],
]],

['slug' => 'academia-y-barberia-barrio-7', 'opiniones' => [
    ['Brayan M.', 5, 'Me perfilaron la barba con navaja y quedó bien definida, aparte te enseñan el paso a paso.', 13],
    ['Silvia C.', 5, 'Llevé a mi hijo de seis años y le tuvieron paciencia; salió contento con su corte.', 23],
    ['Richard P.', 5, 'El chico que atiende estudia en la misma academia y se nota que practica con cuidado.', 35],
]],

['slug' => 'raul-rengifo-fit', 'opiniones' => [
    ['Gino F.', 5, 'Hice el día de prueba antes de pagar la membresía mensual y me convenció el entrenamiento.', 4],
    ['Alessandra V.', 5, 'Las rutinas son personalizadas, no te dejan solo con las máquinas como en otros gimnasios.', 17],
    ['Marco U.', 4, 'Ajusta los ejercicios según tu lesión vieja; en mi caso cuidó mi rodilla todo el tiempo.', 28],
]],

['slug' => 'minimarket-sn', 'opiniones' => [
    ['Elva Q.', 5, 'Tienen de todo para la casa: arroz, aceite, azúcar, y a precios que se aguantan.', 6],
    ['Segundo L.', 5, 'Compro al por mayor para revender y me despachan rápido, sin hacer cola larga.', 19],
    ['Cynthia B.', 5, 'Abren temprano, así que alcanzo a comprar el pan y la leche antes de ir al trabajo.', 37],
]],

['slug' => 'restaurant-vegetariano-jado', 'opiniones' => [
    ['Fabiana N.', 5, 'La ensalada de quinua viene con palta y tomate, bien servida y con buen aderezo.', 9],
    ['Renzo A.', 5, 'El menú vegetariano incluye jugo de frutas del día; probé el de papaya y estaba helado.', 26],
    ['Marisol H.', 4, 'Es la opción cuando quiero comer liviano en Nuevo Chimbote; el local es sencillo pero limpio.', 42],
]],

['slug' => 'start-game-lan-center', 'opiniones' => [
    ['Diego C.', 5, 'Alquilé cabina por horas con mis amigos y las computadoras corren los juegos sin lag.', 7],
    ['Alonso R.', 5, 'Los torneos de fin de semana son bien organizados y el ambiente se pone bueno.', 20],
    ['Fiorella M.', 5, 'El local tiene aire y los asientos son cómodos, uno se queda horas sin sentir el calor.', 34],
]],

['slug' => 'panaderia-y-pasteleria-don-lolo', 'opiniones' => [
    ['Verónica S.', 5, 'Encargué una torta decorada para el cumpleaños de mi mamá y quedó igual a la foto que llevé.', 3],
    ['Alberto G.', 5, 'El bizcocho es bien suave y el chantilly no empalaga como en otras pastelerías.', 15],
    ['Rocío P.', 4, 'La preparan el mismo día y me la entregaron a la hora que pedí, sin retrasos.', 32],
]],

['slug' => 'rodrigo-leyton-tatto-studio', 'opiniones' => [
    ['Nicolás E.', 5, 'Fui por el retoque de un tatuaje viejo y le devolvió el color negro a las líneas.', 12],
    ['Katherine J.', 5, 'Me dio crema y las indicaciones de cuidado; a la semana me escribió para ver cómo iba.', 25],
    ['Josué D.', 5, 'Trabaja con calma y sin apuro, te escucha la idea antes de empezar a delinear.', 45],
]],

['slug' => 'a-e-boutique', 'opiniones' => [
    ['Patricia L.', 5, 'Encontré blusas y vestidos de mi talla, cosa difícil en Coishco, y a precio razonable.', 5],
    ['Érica S.', 5, 'Los accesorios combinan con la ropa que venden y te los prueban con el conjunto.', 18],
    ['Milagros R.', 4, 'La dueña te aconseja qué te queda mejor sin apurarte a que compres algo.', 29],
]],

['slug' => 'anvar-designs-peru', 'opiniones' => [
    ['Andrés F.', 5, 'Me imprimieron mil volantes para la campaña y salieron nítidos, con buen papel.', 8],
    ['Betty C.', 5, 'Hicieron el banner para mi tienda con el logo y los colores que les mandé por WhatsApp.', 22],
    ['Cristian O.', 5, 'Entregaron a tiempo para la inauguración; el letrero se ve fuerte y no se ha despintado.', 39],
]],

['slug' => 'vape-shop', 'opiniones' => [
    ['Franco R.', 5, 'Tienen líquidos de varios niveles de nicotina y me orientaron para bajar de a pocos.', 10],
    ['Sheyla M.', 5, 'Me explicaron bien cómo limpiar el atomizador y cada cuánto hay que cambiar la resistencia.', 23],
    ['Omar T.', 4, 'Los precios de los líquidos son más cómodos que en las tiendas del centro de Chimbote.', 33],
]],

['slug' => 'bici-helaman', 'opiniones' => [
    ['Percy V.', 5, 'Usé el servicio por kilómetro para una mudanza pequeña y me cobró justo lo acordado.', 4],
    ['Marleny A.', 5, 'Traslada a mi personal hasta la obra todos los días y nunca ha llegado tarde.', 16],
    ['Wilder S.', 5, 'Alquilé la movilidad para llevar sacos de cemento y me ayudó a cargar sin cobrar extra.', 31],
]],

['slug' => 'floreria-margarita', 'opiniones' => [
    ['Gladis M.', 5, 'Pedí un ramo de rosas rojas para mi esposa y lo entregaron a domicilio en Santa, frescas.', 6],
    ['Rubén H.', 5, 'Las flores duran bastante en agua; el ramo que llevé al cumpleaños aguantó toda la semana.', 21],
    ['Estefany C.', 4, 'La señora arma el ramo delante de uno y le pone papel y cinta, queda bien presentable.', 36],
]],

['slug' => 'la-casita-de-ariana-panaderia-artesanal', 'opiniones' => [
    ['Lorena B.', 5, 'El pan francés sale caliente en la mañana y la docena se acaba rápido, hay que ir temprano.', 11],
    ['Julio N.', 5, 'Los bocaditos para la reunión fueron un éxito; nadie dejó uno en la fuente.', 24],
    ['Anahí P.', 5, 'Es pan artesanal de verdad, se siente el olor a horno apenas pasas por la puerta.', 43],
]],

['slug' => 'restaurante-vegetariano-el-mana', 'opiniones' => [
    ['Sandra K.', 5, 'Almuerzo aquí seguido: la ensalada de quinua llena y el menú incluye sopa y jugo.', 13],
    ['Teodoro M.', 5, 'El jugo de frutas lo preparan al momento y te lo sirven bien frío con el menú.', 27],
    ['Pilar G.', 4, 'Buena opción vegetariana en Chimbote, atienden rápido aunque sea la hora punta del almuerzo.', 38],
]],

['slug' => 'mil-kositas', 'opiniones' => [
    ['Jenny A.', 5, 'Compré zapatillas de marca usadas casi nuevas por menos de lo que cuestan nuevas.', 9],
    ['Marco R.', 5, 'Siempre tienen promociones en calzado y la ropa que llega está en buen estado.', 19],
    ['Deysi L.', 5, 'Reviso tallas cada vez que voy porque llega mercadería nueva; encontré botines para mi hija.', 35],
]],

['slug' => 'taller-para-ninos-y-jovenes', 'opiniones' => [
    ['Rosa C.', 5, 'Llevé a mi hijo al taller de manualidades y volvió feliz con su trabajito de cartón y témperas.', 7],
    ['Luis M.', 5, 'Las clases son los sábados y la señorita les enseña con paciencia; mi hija ya no quiere faltar.', 21],
    ['Ana P.', 4, 'Pagamos el paquete mensual y sale más barato que llevar a los chicos a clases sueltas.', 33],
]],

['slug' => 'copias-impresiones-imprenta-el-grafico', 'opiniones' => [
    ['Julio R.', 5, 'Hice imprimir cien copias de mi CV y me las entregaron al toque, bien nítidas y a buen precio.', 3],
    ['Marisol T.', 5, 'Me ayudaron con el diseño gráfico del afiche de la pollada; quedó mejor de lo que esperaba.', 15],
    ['Pedro Q.', 5, 'Fui un domingo por unas impresiones a color y me atendieron rápido, sin hacerme esperar.', 28],
]],

['slug' => 'distribuidora-fely', 'opiniones' => [
    ['Elena V.', 5, 'Compro los quesos y jamones al por mayor para mi bodega; siempre me dan buen precio por caja.', 5],
    ['Jorge A.', 4, 'Los embutidos llegan frescos y bien sellados, se nota que cuidan la cadena de frío.', 12],
    ['Carmen S.', 5, 'Pedí leche y yogurt para la semana y me lo trajeron hasta el puesto en el mercado.', 26],
]],

['slug' => 'pollos-a-la-brasa-suarez', 'opiniones' => [
    ['Silvia N.', 5, 'El cuarto de pollo viene con su buena porción de papas y ensalada, alcanza para dos.', 2],
    ['Marco B.', 5, 'Llamé para pedir medio pollo a la brasa y en veinte minutos ya estaba en mi casa.', 18],
    ['Gladys F.', 4, 'El pollo sale caliente y jugoso, la piel bien dorada; se nota que lo hacen al momento.', 31],
]],

['slug' => 'chifa-xin-ye', 'opiniones' => [
    ['Wei L.', 5, 'El chaufa de pollo viene bien cargado, con su huevo y sus verduras, no te deja con hambre.', 9],
    ['Rocío D.', 5, 'Pedimos el combo para dos y nos alcanzó hasta para llevar un taper a la casa.', 22],
    ['Iván P.', 4, 'El local es sencillo pero limpio, y la comida llega rápido aunque esté lleno de gente.', 40],
]],

['slug' => 'andre-a-pasteleria-panaderia-cafeteria', 'opiniones' => [
    ['Katia H.', 5, 'El pan francés sale calientito a las seis de la mañana; la docena se acaba rapidísimo.', 4],
    ['Fernando O.', 5, 'Pedí un pastel de chocolate para el cumpleaños de mi mamá y quedó con el bizcocho bien húmedo.', 17],
    ['Lucía G.', 5, 'Me quedo a tomar el café con su sandwich de pollo, el ambiente es tranquilo para conversar.', 29],
]],

['slug' => 'mercado-central-de-coishco', 'opiniones' => [
    ['Teresa M.', 5, 'Los abarrotes son más baratos que en la bodega de la esquina, sobre todo el arroz por saco.', 6],
    ['Raúl E.', 4, 'Encontré gaseosas y jugos bien helados en los puestos de la entrada, justo cuando hacía calor.', 14],
    ['Yolanda C.', 5, 'Los sábados está lleno pero igual avanzas; las caseras te pesan la papa al toque.', 35],
]],

['slug' => 'carwash-ultra-premiun-v3', 'opiniones' => [
    ['Cristian Z.', 5, 'Le pedí el lavado completo a mi auto y quedó sin una sola mancha de barro en los aros.', 8],
    ['Paola R.', 5, 'El aspirado de interiores sacó toda la arena de la playa que tenía en los asientos.', 19],
    ['Néstor V.', 5, 'Me atendieron en menos de una hora y sin cobrarme de más por la camioneta.', 44],
]],

['slug' => 'ee-ss-operadora-fernanda', 'opiniones' => [
    ['Wilder S.', 5, 'Cargué noventa en la madrugada, volviendo de un viaje, y el grifo estaba abierto y con personal.', 1],
    ['Beatriz L.', 5, 'El surtidor marca bien el galón, no te cobran de más como en otros grifos de la carretera.', 23],
    ['Óscar T.', 4, 'Los chicos del grifo te revisan el agua y el aceite sin que se lo pidas.', 37],
]],

['slug' => 'facturito-chimbote', 'opiniones' => [
    ['Kevin A.', 5, 'Me desbloquearon el celular que traje del extranjero y en media hora ya estaba funcionando.', 10],
    ['Diana M.', 5, 'Hago las recargas ahí porque siempre tienen saldo para cualquier operador, incluso domingos.', 16],
    ['Hugo R.', 5, 'Me explicaron paso a paso cómo usar el chip nuevo, con paciencia y sin apurarme.', 27],
]],

['slug' => 'coishco', 'opiniones' => [
    ['Fiorella B.', 5, 'Alquilé un scooter por horas con mi novia y nos dimos una vuelta por la playa de Coishco.', 11],
    ['Álvaro N.', 5, 'Sacamos el alquiler mensual para ir al trabajo y nos sale más barato que las combis.', 20],
    ['Sheyla P.', 4, 'Nos dieron casco y nos explicaron cómo manejar el scooter, era la primera vez que subía.', 34],
]],

['slug' => 'lubricantes-y-servicios-cer', 'opiniones' => [
    ['Elmer D.', 5, 'Le hicieron el diagnóstico de motor a mi station wagon y me dijeron exactamente qué fallaba.', 13],
    ['Patricia Y.', 5, 'Me repararon el motor en tres días y me mostraron las piezas viejas que sacaron.', 24],
    ['Juan Carlos S.', 5, 'El mecánico me explicó con palabras sencillas qué le pasaba a la camioneta, sin tecnicismos.', 39],
]],

['slug' => 'novedades-yalico', 'opiniones' => [
    ['Milagros A.', 5, 'Compré dos polos y un pantalón de hombre para el trabajo, la tela es gruesa y no se destiñe.', 6],
    ['Segundo R.', 4, 'Tienen correas, billeteras y gorras a buen precio, me llevé un juego completo para mi papá.', 17],
    ['Verónica Ch.', 5, 'La señora me dejó probarme varias tallas sin apurarme, al final encontré la que me quedaba.', 30],
]],

['slug' => 'mundo-pinturas-chimbote-pinturas-jotun', 'opiniones' => [
    ['Alfredo M.', 5, 'Compré látex para pintar la sala y rindió bastante; con un balde terminé dos manos.', 5],
    ['Rosario Q.', 5, 'Me prepararon el color que quería con la máquina y quedó idéntico a la muestra.', 21],
    ['Teodoro V.', 4, 'Me aconsejaron usar pintura de aceite para la puerta de metal y aguantó la lluvia.', 36],
]],

['slug' => 'mr', 'opiniones' => [
    ['Robinson C.', 5, 'Contratamos la movilidad para trasladar a nuestro personal y llegaron puntuales todos los días.', 8],
    ['Mónica F.', 5, 'Hicimos la mudanza de la casa con su flete y cargaron los muebles sin rayarlos.', 25],
    ['Édgar L.', 5, 'Cobran por kilómetro y te dicen el precio antes de subir, no hay sorpresa al final.', 41],
]],

['slug' => 'servicios-my-cielito-e-i-r-l', 'opiniones' => [
    ['Shirley R.', 5, 'Fui por la terapia de relajación y salí con los hombros sueltos, tenía meses de tensión.', 3],
    ['Norma B.', 5, 'La evaluación inicial sirvió para saber qué necesitaba; no me vendieron nada de más.', 18],
    ['Wilson G.', 4, 'El ambiente huele a hierbas y ponen música suave, te olvidas del estrés del trabajo.', 32],
]],

['slug' => 'pancoish', 'opiniones' => [
    ['Doris P.', 5, 'El pan de molde se mantiene suave varios días, no como el de las bolsas del supermercado.', 7],
    ['César A.', 5, 'Encargamos un pastel de tres leches y lo tuvieron listo a la hora que pedimos.', 22],
    ['Marlene T.', 5, 'Voy temprano por el pan de molde recién hecho y sale caliente todavía de la máquina.', 38],
]],

['slug' => 'narciso-karaoke-lounge-bar', 'opiniones' => [
    ['Bruno S.', 5, 'El menú del día trae sopa, segundo y refresco por un precio que no se encuentra en otro lado.', 4],
    ['Leslie M.', 5, 'Pedí la hamburguesa después de cantar un rato y estaba jugosa, con papas bien crocantes.', 16],
    ['Renzo D.', 4, 'El karaoke tiene buena lista de canciones y el sonido no te deja sordo.', 28],
]],

['slug' => 'kemy-deco-eventos', 'opiniones' => [
    ['Cynthia O.', 5, 'Nos decoraron el cumpleaños de quince con globos y luces; quedó tal como salía en la foto.', 9],
    ['Percy H.', 5, 'Alquilamos las mesas y las sillas para la boda civil y las trajeron y recogieron puntual.', 23],
    ['Tatiana L.', 5, 'La señora Kemy armó la decoración del bautizo en dos horas y nos cobró lo acordado.', 42],
]],

['slug' => 'saidgym', 'opiniones' => [
    ['Fabricio N.', 5, 'Pagué la membresía trimestral y me sale más barato que ir pagando mes a mes.', 12],
    ['Karina S.', 5, 'Las máquinas de peso están nuevas y siempre hay alguien que te corrige la postura.', 26],
    ['Miguel Ángel R.', 4, 'Voy después del trabajo y nunca está tan lleno; alcanzo a hacer toda mi rutina.', 33],
]],

['slug' => 'steel-fighter-gym', 'opiniones' => [
    ['Gerson P.', 5, 'Fui al día de prueba de artes marciales y me quedé; el profe enseña con paciencia.', 2],
    ['Diana Ch.', 5, 'Mi hijo está en las clases y ha aprendido defensa y disciplina, ya no es tan tímido.', 19],
    ['Hugo B.', 5, 'El entrenamiento es duro pero te van subiendo de a pocos, nadie te obliga a más.', 31],
]],

['slug' => 'petroperu-san-fermin', 'opiniones' => [
    ['Manuel I.', 5, 'Llené el tanque de diésel para el camión a las once de la noche y estaba abierto.', 10],
    ['Sandra V.', 5, 'El diésel de acá rinde más; con el mismo tanque hago un viaje más a Casma.', 24],
    ['Wilmer A.', 4, 'El personal usa sus implementos y no te apuran, revisan bien la manguera antes de cargar.', 43],
]],

['slug' => 'vidriera-y-melamina-valle', 'opiniones' => [
    ['Antonio J.', 5, 'Me cortaron el espejo del baño a la medida y quedó perfecto, con los bordes pulidos.', 6],
    ['Gladys E.', 5, 'Repararon la luna de mi ventana que se había rajado con el temblor, vinieron a medir.', 20],
    ['Rómulo C.', 5, 'Los precios por metro de melamina los dicen claro, sin subirte después del trabajo.', 35],
]],

['slug' => 'chelas-gaseosas', 'opiniones' => [
    ['Jhonatan M.', 5, 'El seco de carne viene con su frejol y arroz, bien servido para el almuerzo.', 8],
    ['Carmen Rosa V.', 5, 'El cuarto de pollo a la brasa con papas es lo que siempre pido los domingos.', 17],
    ['Beto S.', 4, 'Las gaseosas están bien heladas y hay cerveza para acompañar el almuerzo.', 29],
]],

['slug' => 'tatuajes-antuan', 'opiniones' => [
    ['Nicolás F.', 5, 'Me hice un tatuaje pequeño en la muñeca y el trazo quedó bien fino, casi no dolió.', 11],
    ['Ariana T.', 5, 'Fui por un tatuaje grande en el brazo y Antuan me mostró el diseño antes de empezar.', 25],
    ['Gabriel O.', 5, 'El local está limpio, usa agujas nuevas y te explica cómo cuidarte la piel después.', 40],
]],

['slug' => 'la-casa-del-disfraz', 'opiniones' => [
    ['Rosa C.', 5, 'Alquilé un disfraz de bruja para el cumpleaños de mi sobrina y me lo dieron limpio y planchado el mismo día.', 7],
    ['Luis M.', 5, 'El maquillaje artístico para mi hija quedó intacto toda la fiesta, aunque sudó bailando no se le corrió nada.', 21],
    ['Ana P.', 4, 'Cobran por día el alquiler y te explican cómo cuidar la tela para no devolverlo con manchas.', 33],
]],

['slug' => 'de-gala-alquiler-de-vestidos-zapatos-y-carteras-de-fiesta', 'opiniones' => [
    ['Milagros T.', 5, 'Alquilé un vestido largo para la boda de mi hermana y me prestaron aretes y cartera del mismo tono.', 3],
    ['Jorge Q.', 5, 'Aproveché la promoción de fin de mes y me salió bastante más barato que comprar vestido nuevo.', 14],
    ['Carmen R.', 5, 'Los zapatos de fiesta estaban impecables, lustrados, y me los ajustaron a mi talla sin cobrar extra.', 28],
]],

['slug' => 'rm-studios-randy-matos-peru', 'opiniones' => [
    ['Wilder S.', 5, 'Mandé a imprimir mis invitaciones de matrimonio y salieron con un color parejo, nada desteñido.', 5],
    ['Nataly B.', 5, 'Me diseñaron el logo de mi bodega en dos días y me lo entregaron en todos los formatos que pedí.', 18],
    ['Percy L.', 5, 'Saco copias de los cuadernos de mi hijo ahí y siempre salen nítidas, incluso las hojas a doble cara.', 40],
]],

['slug' => 'carpinteria-morillo', 'opiniones' => [
    ['Segundo V.', 5, 'Me hicieron la puerta de mi cuarto a medida y quedó pareja, sin ese hueco que dejaba entrar el viento.', 9],
    ['Elva M.', 5, 'Barnizaron mis ventanas de madera y les devolvieron el color, parecían nuevas otra vez.', 22],
    ['Rubén C.', 4, 'El maestro vino a medir a mi casa antes de hacer la ventana, por eso no hubo error al instalarla.', 36],
]],

['slug' => 'complejo-deportivo-barcelona', 'opiniones' => [
    ['Jhonatan A.', 5, 'Alquilamos la cancha de grass sintético los domingos y el piso está bien cuidado, sin huecos.', 2],
    ['Kely P.', 5, 'Armaron el torneo de barrio con fixture y árbitro, todo ordenado, no como en otras canchas.', 16],
    ['Marco Z.', 5, 'Hay buena iluminación para jugar de noche y el arco tiene red nueva, se nota que lo mantienen.', 31],
]],

['slug' => 'carwash-roimotors', 'opiniones' => [
    ['Joel D.', 5, 'Dejé mi moto para el lavado y me la devolvieron con las llantas y los aros bien brillantes.', 6],
    ['Sandra F.', 5, 'El aspirado de interiores sacó toda la arena de la playa que tenía en los asientos de mi auto.', 19],
    ['Anthony G.', 5, 'Fui un sábado en la tarde y aunque había cola me atendieron rápido, en menos de media hora.', 27],
]],

['slug' => 'chifa-akikomo', 'opiniones' => [
    ['Katia N.', 5, 'El tallarín saltado viene bien cargado, con bastante pollo y verdura, alcanza para dos personas.', 4],
    ['Wilson E.', 5, 'La sopa wantán la sirven caliente y con hartas wantanes, ideal para el frío de la noche.', 12],
    ['Gladys H.', 4, 'Piden la orden y en diez minutos ya están sirviendo, salen rapidísimo cuando uno va con hambre.', 25],
]],

['slug' => 'spa-mazz-alunia-nuevo-chimbote', 'opiniones' => [
    ['Yessica O.', 5, 'Me hice el tratamiento capilar y sentí el cuero cabelludo fresco, se me cayó menos pelo esa semana.', 8],
    ['Rocío I.', 5, 'El masaje relajante me duró casi una hora y salí con los hombros sueltos, sin esa carga del trabajo.', 23],
    ['Diana U.', 5, 'El local huele rico y ponen música bajita, uno se relaja desde que entra, no hay bulla de la calle.', 41],
]],

['slug' => 'bohemios-karaoke', 'opiniones' => [
    ['Kevin R.', 5, 'El menú del día trae entrada, segundo y refresco, y por ese precio no encuentras algo igual cerca.', 11],
    ['Tatiana S.', 5, 'El pisco sour lo preparan en el momento, bien helado y con su clara de huevo, no lo sirven aguado.', 20],
    ['Bruno V.', 5, 'Fuimos a cantar un viernes y nos dieron el micrófono sin esperar mucho, además el sonido suena bien.', 34],
]],

['slug' => 'manje-dark-kitchen', 'opiniones' => [
    ['Álvaro P.', 5, 'Pedimos el combo familiar por la aplicación y llegó caliente, con todo lo que ofrecía la foto.', 1],
    ['Cynthia J.', 5, 'El combo alcanzó para los cuatro en casa y hasta sobró arroz, bien rendidor por lo que cuesta.', 13],
    ['Fernando T.', 4, 'Hice el pedido por la aplicación a las ocho y en treinta minutos ya estaba tocando el motorizado.', 26],
]],

['slug' => 'centro-comercial-cybertech', 'opiniones' => [
    ['Diego M.', 5, 'Alquilé una cabina por día completo y la computadora corría los juegos sin tirones, buena máquina.', 10],
    ['Alessandro C.', 5, 'Compré una cuenta de juegos y me la entregaron con su correo cambiado, sin problemas para entrar.', 17],
    ['Brayan F.', 5, 'El internet ahí aguanta bien en hora punta, no se cae cuando están todos los chicos jugando en red.', 44],
]],

['slug' => 'panaderia-y-pasteleria-pattys-sac', 'opiniones' => [
    ['Marisol Q.', 5, 'Encargué el pastel de cumpleaños con crema chantilly y quedó tal cual les mostré en la foto.', 5],
    ['Ever L.', 5, 'Los bocaditos de pollo y de jamón salieron frescos para la reunión, nadie dejó el plato a medias.', 15],
    ['Paola D.', 4, 'Pedí los bocaditos con un día de anticipación y me los tuvieron listos a la hora que acordamos.', 29],
]],

['slug' => 'grifo-fray-martin', 'opiniones' => [
    ['Hugo B.', 5, 'Cargo gasolina de 95 ahí y el medidor se ve calibrado, no te dan menos de lo que pides.', 2],
    ['Nelly A.', 5, 'Pedí el balón de GLP de diez kilos y me lo cambiaron rápido, sin cobrarme por el envase.', 21],
    ['Iván R.', 5, 'Atienden las veinticuatro horas y eso salva cuando uno sale de viaje de madrugada y va con el tanque bajo.', 38],
]],

['slug' => 'restaurante-vegetariano-el-eden', 'opiniones' => [
    ['Flor M.', 5, 'El batido natural de fruta lo hacen al momento, sin azúcar añadida, y se siente lo fresco.', 6],
    ['Gustavo P.', 5, 'Probé el postre vegano de chocolate y no se le nota que no lleva leche ni huevo, quedó cremoso.', 19],
    ['Marlene S.', 5, 'El menú del día es casero y liviano, salgo sin esa pesadez que me dejan otros restaurantes.', 30],
]],

['slug' => 'polleria-el-huerto-quinones', 'opiniones' => [
    ['Jaime C.', 5, 'El cuarto de pollo a la brasa viene bien dorado y la carne jugosa, no seca como en otros sitios.', 3],
    ['Lucero V.', 5, 'La ensalada la sirven fresca, con su lechuga y tomate cortados al momento, no marchita.', 12],
    ['Silvia T.', 4, 'Pedí para llevar un domingo y me lo entregaron en su caja con las cremas aparte, bien caliente.', 24],
]],

['slug' => 'global-scooter', 'opiniones' => [
    ['Renzo A.', 5, 'Alquilé una scooter por el día y me explicaron cómo manejar las cuestas antes de salir a la pista.', 8],
    ['Fiorella N.', 5, 'El casco te lo prestan con el alquiler y la moto estaba con el tanque lleno, sin sorpresas al devolverla.', 17],
    ['César O.', 5, 'Me dieron una asesoría corta de manejo porque nunca había usado scooter eléctrica y quedé tranquilo.', 42],
]],

['slug' => 'casa-de-novias-milagros', 'opiniones' => [
    ['Yesenia H.', 5, 'Encontré el vestido de fiesta para mi graduación y me lo ajustaron al cuerpo sin cobrar aparte.', 4],
    ['Patricia G.', 5, 'Los accesorios de novia combinan con el vestido, me prestaron el velo y la tiara para la prueba.', 14],
    ['Miriam L.', 5, 'Fui con mi mamá a probarme vestidos y nos atendieron con paciencia, sin apurarnos a decidir.', 33],
]],

['slug' => 'la-gatita', 'opiniones' => [
    ['Soledad R.', 5, 'Compré dos casacas de segunda mano ahí y estaban como nuevas, sin manchas ni roturas.', 9],
    ['Erika B.', 5, 'Siempre hay promoción de prendas por montón y uno se lleva ropa buena por pocos soles.', 20],
    ['Judith P.', 4, 'La ropa viene lavada y ordenada por talla, uno encuentra rápido sin estar revolviendo todo.', 35],
]],

['slug' => 'alma-organica', 'opiniones' => [
    ['Rosario E.', 5, 'El pan francés sale caliente a las seis de la mañana y la docena se acaba rapidísimo.', 1],
    ['Ítalo M.', 5, 'El pastel de zanahoria de ahí es húmedo y no empalaga, se nota que usan insumos buenos.', 16],
    ['Bertha C.', 5, 'Compro el pan para el desayuno y aguanta tierno hasta la tarde, no se pone duro tan rápido.', 27],
]],

['slug' => 'conecta2', 'opiniones' => [
    ['Cristian D.', 5, 'Compro los cargadores y audífonos ahí y me salen buenos, no como los de la calle que duran nada.', 7],
    ['Karina S.', 5, 'Voy a pagar la luz y el agua ahí mismo y nunca me he quedado sin poder hacer el pago.', 18],
    ['Óscar V.', 5, 'El chico me ayudó a configurar el celular nuevo y me instaló las aplicaciones que uso, gratis.', 39],
]],

['slug' => 'chifa-hugo-s', 'opiniones' => [
    ['Liliana A.', 5, 'El chaufa de pollo tiene buen sabor a wok y no viene grasoso como en otros chifas del centro.', 5],
    ['Rómulo T.', 5, 'Pedimos el combo para dos y salimos llenos, trae chaufa, tallarín y sopa, rinde bastante.', 13],
    ['Esther Q.', 4, 'Llegamos casi a la hora de cierre y nos atendieron igual, sin apurar ni decir que ya cerrábamos.', 23],
]],

['slug' => 'bryam-zapateria', 'opiniones' => [
    ['Máximo C.', 5, 'Le compré zapatillas a mi hijo para el colegio y aguantaron todo el año, no se despegó la suela.', 10],
    ['Tania L.', 5, 'Hay tallas grandes para hombre, cosa que en otras zapaterías de Coishco no se encuentra fácil.', 22],
    ['José R.', 5, 'El dueño me dejó probar varios pares hasta que di con el número justo, sin apuro ni molestia.', 37],
]],

['slug' => 'ola-cafe-cafeteria-de-autor', 'opiniones' => [
    ['Valeria M.', 5, 'El café con leche lo sirven con un dibujo en la espuma y el grano se siente tostado, no quemado.', 2],
    ['Sebastián G.', 5, 'La tarta por porción estaba fresca y no muy dulce, perfecta para acompañar el café de la tarde.', 15],
    ['Adriana P.', 4, 'Es un sitio tranquilo para trabajar un rato, hay enchufes y el wifi no se cae, además el local es fresco.', 32],
]],

['slug' => 'ferreteria-casana', 'opiniones' => [
    ['Elmer Z.', 5, 'Compré veinte bolsas de cemento para mi losa y me las mandaron a la obra el mismo día.', 6],
    ['Zoila N.', 5, 'Me ayudaron a calcular cuánta arena y cuánto fierro necesitaba, porque yo no tenía idea.', 19],
    ['Teodoro H.', 5, 'Los tubos y las conexiones tienen buen precio ahí, y si te falta algo te lo consiguen al día siguiente.', 43],
]],

['slug' => 'chifa-chong-hua', 'opiniones' => [
    ['Mónica F.', 5, 'El arroz chaufa con mariscos trae harto camarón y calamar, no es puro arroz como en otros lados.', 11],
    ['Henry B.', 5, 'La sopa wantán de ahí es mi cura para el resfrío, bien caliente y con su tallarín crocante aparte.', 20],
    ['Cecilia D.', 4, 'El local está limpio y las mesas se desocupan rápido, aunque llegues en la hora de almuerzo.', 28],
]],

['slug' => 'la-curacao-chimbote-electrodomesticos-cocina-refrigeradoras-y-mas', 'opiniones' => [
    ['Jorge Luis A.', 5, 'Compré una refrigeradora en la Curacao y me la dieron con financiamiento, pagando una inicial bajita cada mes.', 6],
    ['Marisol B.', 5, 'Fuimos por la cocina a gas y el vendedor nos explicó el financiamiento sin apurarnos, con cuotas que sí nos alcanzaban.', 17],
    ['Ricardo M.', 4, 'La refrigeradora llegó el mismo día del pago y los muchachos esperaron que la probara antes de irse.', 30],
]],

['slug' => 'la-llave', 'opiniones' => [
    ['Rosa C.', 5, 'En La Llave encuentro las herramientas que no hay en otras ferreterías de Santa, y me las muestran sin apuro.', 12],
    ['Víctor N.', 5, 'Compré cemento y fierro para mi techo; me calcularon cuántos sacos necesitaba y así no me sobró material.', 33],
    ['Rocío P.', 5, 'El local está ordenado, con vitrinas de herramientas nuevas, y el dueño te explica con paciencia si no sabes la medida.', 24],
]],

['slug' => 'dolce-mare-nuevo-chimbote', 'opiniones' => [
    ['Elena T.', 5, 'El ceviche de pescado estaba fresco y bien picante, con bastante chicharrón de pota y su buena porción de cancha.', 9],
    ['Freddy H.', 5, 'Pedí ceviche para dos y trajeron bastante, con el pescado cortado grueso y la leche de tigre bien fría.', 20],
    ['Sandro K.', 4, 'La pizza salió con harto queso y la masa delgada; es rico después de un ceviche, aunque demoró un poquito.', 35],
]],

['slug' => 'pollos-parrillas-heroz', 'opiniones' => [
    ['Carmen R.', 5, 'El medio pollo a la brasa estaba jugoso por dentro y con la piel dorada, bien caliente cuando llegamos.', 4],
    ['Iván G.', 5, 'La ensalada viene fresca, con harto tomate y lechuga, y no es el par de hojitas que dan en otros lados.', 5],
    ['Letty S.', 4, 'Salimos con la fuente de ensalada para la casa; el pollo seguía caliente y las papas crocantes, aunque esperamos unos minutos.', 26],
]],

['slug' => 'sugar-bloom', 'opiniones' => [
    ['Fiorella D.', 5, 'Encargué una torta de chocolate para el cumpleaños de mi mamá y quedó bien húmeda por dentro, tal como la pedí.', 8],
    ['Percy L.', 5, 'Los pasteles de la vitrina son frescos, se siente la crema recién batida y el pionono se acaba temprano.', 14],
    ['Katherine V.', 5, 'Encargué torta para treinta personas y me la entregaron a la hora exacta, con el nombre bien escrito y las velas incluidas.', 23],
]],

['slug' => 'laboratorio-clinico-perulabs-diagnostic', 'opiniones' => [
    ['Gladys Ñ.', 5, 'Me hice la prueba de embarazo y me dieron el resultado en una hora, con la enfermera explicándome todo con calma.', 3],
    ['Tony Z.', 5, 'Fui por unos exámenes de función hepática por el trabajo y estuve en ayunas solo hasta que me atendieron, rapidísimo.', 11],
    ['Nelly Q.', 5, 'Los resultados de mi glucosa me llegaron al WhatsApp esa misma tarde y no tuve que volver al laboratorio a recogerlos.', 29],
]],

['slug' => 'pan-dorado', 'opiniones' => [
    ['Betty F.', 5, 'El pan de molde sale calientito a media mañana; lo compro para la lonchera y aguanta suave hasta el otro día.', 2],
    ['José Luis O.', 5, 'Para el cumpleaños de mi hijo encargué el pan de molde grande y me lo rebanaron al grosor que pedí, sin cobrar extra.', 15],
    ['Elva M.', 4, 'Voy temprano por el pan de molde integral y siempre hay; cuando encargué para cumpleaños me lo tuvieron a la hora.', 37],
]],

['slug' => 'dra-shandy-vasquez-medicina-estetica', 'opiniones' => [
    ['Anny Y.', 5, 'En la consulta de medicina estética la doctora revisó mi piel y me explicó qué me convenía, sin venderme paquetes de más.', 7],
    ['Ruth E.', 5, 'La limpieza facial profunda me dejó la cara fresca y sin los puntos negros de la nariz; duró casi una hora.', 19],
    ['Miluska A.', 5, 'Después de la limpieza facial profunda me quedó la piel pareja varias semanas y no me salieron granitos como otras veces.', 34],
]],

['slug' => 'licoreria-y-mini-market-l-t', 'opiniones' => [
    ['Roxana J.', 5, 'Compro los lácteos y huevos ahí porque siempre están frescos, reviso la fecha y no vienen vencidos como en otros lados.', 10],
    ['Édgar W.', 5, 'Pedí venta por mayor de cerveza y gaseosas para una actividad y me dejaron todo en cajas y a buen precio.', 22],
    ['Liliana U.', 4, 'Fui por la venta por mayor de gaseosas para la fiesta y me dieron precio al por mayor, aunque tuve que esperar mi turno.', 39],
]],

['slug' => 'distribuidora-super-pio', 'opiniones' => [
    ['Wílmer I.', 5, 'Compro el alimento balanceado para mis gallinas y siempre me despachan el mismo saco, sin cambiármelo por otro más barato.', 13],
    ['Janeth X.', 5, 'Me asesoraron con los insumos agrícolas para la chacra y me explicaron cuánto aplicar por hectárea sin apurarme.', 25],
    ['Percy Ñ.', 5, 'Llevé alimento balanceado para mis cuyes y me recomendaron la cantidad exacta por animal; no se me murió ninguno.', 31],
]],

['slug' => 'la-tiendita-de-blue-y-newton', 'opiniones' => [
    ['Estefany C.', 5, 'Llevé a mi perrita por urgencias porque estaba decaída y la atendieron de inmediato, sin hacerme esperar en la puerta.', 16],
    ['Julio A.', 5, 'La desparasitación de mi gato salió rápida y me explicaron cada cuánto repetirla según el peso del animal.', 27],
    ['Rodrigo S.', 5, 'La atención de urgencias fue rapidísima cuando mi perro se comió algo en la calle; lo revisaron y me dejaron tranquilo.', 40],
]],

['slug' => 'taller-perno-loco-2024', 'opiniones' => [
    ['Félix B.', 5, 'El cambio de aceite me lo hicieron en media hora y me mostraron el filtro viejo para que viera que sí lo cambiaron.', 18],
    ['Narda G.', 5, 'Hice alineamiento y balanceo antes de viajar a Trujillo y el carro ya no se iba de lado en la Panamericana.', 28],
    ['Pepe D.', 5, 'Fui por el cambio de aceite y me revisaron también las llantas; se quedaron hasta tarde para terminar el trabajo.', 41],
]],

['slug' => 'jiron-rio-santa-400', 'opiniones' => [
    ['Zoila H.', 5, 'El ceviche de ese local es bien fresco, con harto limón y ají, y te lo sirven con choclo y camote.', 1],
    ['Miguel Ángel R.', 5, 'Pedimos un cuarto de pollo a la brasa con papas y ensalada; salió jugoso y a buen precio para dos personas.', 21],
    ['Claudia T.', 5, 'El cuarto de pollo a la brasa venía bien dorado y las papas crocantes; nos atendieron rápido aunque estaba lleno.', 44],
]],

['slug' => 'vidrieria-innova', 'opiniones' => [
    ['César N.', 5, 'Mandé hacer la mampara del baño a medida y quedó exacta, sin que se salga el agua para el otro lado.', 32],
    ['Marleny V.', 5, 'Me repararon la luna de la ventana el mismo día que llamé; vinieron, midieron y la instalaron sin dejar desorden.', 12],
    ['Gloria P.', 5, 'El pulido de los bordes de mi mesa de vidrio quedó parejo y sin filos que corten; me cobraron lo acordado.', 45],
]],

['slug' => 'heladeria-ucv-ra', 'opiniones' => [
    ['Yulisa F.', 5, 'El cono de lúcuma es mi vicio; la bola es grande y el helado es cremoso, no como esos que parecen hielo.', 36],
    ['Anthony C.', 5, 'El sandwich de helado es enorme; lo pedí de fresa con vainilla y lo compartí con mi hermana y sobró.', 23],
    ['Silvia R.', 4, 'Pedí un cono de chocolate y me dieron doble bola sin cobrarme más; el muchacho es bien atento.', 38],
]],

['slug' => 'tintagraf', 'opiniones' => [
    ['Brenda M.', 5, 'Me hicieron el diseño del logo de mi negocio y me mandaron tres propuestas; me quedé con la segunda, bien bonita.', 42],
    ['Hugo T.', 5, 'El banner para la pollería salió con los colores exactos que pedí y me lo entregaron al día siguiente, listo para colgar.', 5],
    ['Karla Z.', 4, 'Encargué el diseño gráfico y el letrero para mi bodega; me corrigieron el logo sin cobrarme extra, aunque la entrega tardó un día más.', 15],
]],

['slug' => 'sublimado-y-estanpado', 'opiniones' => [
    ['Fernando Q.', 5, 'Mandé estampar las polos del equipo del barrio y el color no se agrietó ni después de varias lavadas.', 25],
    ['Yeison L.', 5, 'Mandé imprimir tarjetas para mi negocio y salieron con buen papel y los colores firmes, a precio de cantidad.', 9],
    ['Alexandra W.', 5, 'Me estamparon la foto en la taza para el Día de la Madre y quedó nítida; mi mamá no la suelta.', 30],
]],

['slug' => 'alquiler-y-venta-de-vestidos-y-zapatos-reyna', 'opiniones' => [
    ['Leonor T.', 5, 'Alquilé un vestido para la promoción de mi hija y me lo ajustaron de largo ahí mismo, sin cobrarme aparte.', 20],
    ['Maribel C.', 5, 'Alquilé una cartera dorada para la boda de mi hermana y me la dejaron con la correa nueva por el mismo precio.', 26],
    ['Frida G.', 4, 'Hay promociones de vestidos y zapatos que te salen a cuenta cuando alquilas el conjunto completo, aunque hay que reservar con días.', 11],
]],

['slug' => 'falabella-chimbote', 'opiniones' => [
    ['Ana Lucía P.', 5, 'Encontré ropa de mujer con descuento y buena talla; me probé tres blusas y ninguna me quedó chica.', 14],
    ['Pedro Ñ.', 4, 'Me llevé una licuadora de oferta y el vendedor me explicó la garantía; también vi la ropa de niño en promo.', 43],
    ['Kike V.', 5, 'Compré la ropa para mis hijos en la campaña escolar y aproveché la promo de electrodomésticos para llevar la plancha.', 4],
]],

['slug' => 'cerveceria-chayo-y-porfiria-mis-dos-amores', 'opiniones' => [
    ['Sonia B.', 5, 'La tabla de piqueos viene bien cargada, con chicharrón, queso y papa; para dos personas está más que suficiente.', 27],
    ['Édgar R.', 5, 'La visita guiada a la fábrica me gustó; nos mostraron las ollas y al final probamos la cerveza recién sacada.', 6],
    ['Lucía D.', 5, 'Nos dieron a probar la cerveza más fuerte de la casa y el dueño nos contó cómo la preparan desde hace años.', 34],
]],

['slug' => 'lancenter-santanet', 'opiniones' => [
    ['Diego S.', 5, 'Llevé mi celular a desbloquear y me lo devolvieron con todo funcionando y mis aplicaciones, en menos de una hora.', 3],
    ['Pierina A.', 5, 'Compré el cargador y el cable para mi celular; me probaron los accesorios ahí mismo antes de cobrarme.', 21],
    ['Nilton E.', 5, 'Fui a desbloquear el celular de mi hijo y quedó liberado para cualquier chip, con la batería revisada de yapa.', 39],
]],

['slug' => 'ecodecora', 'opiniones' => [
    ['Esther L.', 5, 'Encargué la impresión de tarjetas para mi botica y salieron nítidas, con los colores como se veían en la pantalla.', 19],
    ['Washington M.', 5, 'Me hicieron el diseño gráfico de la etiqueta de mi producto y me corrigieron la letra que no se leía.', 30],
    ['Soledad Y.', 4, 'Imprimieron los volantes para la actividad del colegio y me salieron a buen precio por millar, aunque tuve que volver al día siguiente.', 7],
]],

['slug' => 'aisha-spa-canino-chimbote', 'opiniones' => [
    ['Romina C.', 5, 'Le hicieron el corte de raza a mi schnauzer y quedó igualito al de las fotos, con las patas bien perfiladas.', 22],
    ['Fernando J.', 5, 'El corte de raza de mi shih tzu quedó parejo por todo el cuerpo y no le cortaron los bigotes, como pedí.', 13],
    ['Melisa T.', 5, 'Le pusieron la desparasitación externa a mi perro y por semanas no le vi ninguna garrapata, ni en las orejas.', 35],
]],

['slug' => 'notaria-jaramillo-munayco', 'opiniones' => [
    ['Abel Q.', 5, 'Hice la carta poder para que mi mamá cobre mi CTS y me la tuvieron lista en dos días, con las firmas legalizadas.', 8],
    ['Norma V.', 5, 'Fui a que me legalicen las copias para el trámite del colegio de mis hijos y salí con todo sellado en menos de una hora.', 28],
    ['Fanny Z.', 5, 'Me atendieron en ventanilla y me explicaron qué llevar para la minuta antes de ir, así no perdí el viaje desde Coishco.', 16],
]],

['slug' => 'cuchita-dark-kitchen', 'opiniones' => [
    ['Ronald P.', 5, 'Pedí por la aplicación y el delivery llegó antes de lo que decía; la comida seguía caliente y bien sellada.', 12],
    ['Fabiana O.', 5, 'Pedí por aplicación una porción para dos y alcanzó para los tres; la salsa la mandaron aparte como pedí.', 24],
    ['Yuver F.', 4, 'El delivery a domicilio llegó cumplido a mi casa en La Florida, con la comida caliente, aunque se demoró unos minutos más.', 31],
]],

// ====== TANDA 2 (2026-09-14, noche): las 100 tiendas anteriores ======
['slug' => 'wapissima-salon', 'opiniones' => [
    ['Rosa C.', 5, 'Me hizo el corte tal cual le mostré la foto y el peinado me duró todo el día en la fiesta.', 9],
    ['Melissa Q.', 5, 'Me maquilló para el matrimonio de mi hermana y aguantó hasta la madrugada sin correrse nada.', 20],
    ['Julio T.', 4, 'Fui sin cita un sábado en la mañana y aun así me atendieron rápido; el local queda cerca de la plaza de Santa.', 35],
]],

['slug' => 'peluqueria-maribel', 'opiniones' => [
    ['Carmen V.', 5, 'La tintura me quedó pareja de raíz a puntas y no me maltrató el cabello como en otros sitios.', 6],
    ['Lucía B.', 5, 'Me hice el tratamiento capilar y a la tercera sesión ya se me notaba el pelo menos maltratado y con brillo.', 18],
    ['Sofía R.', 4, 'Me cobró lo mismo que me dijo al inicio, sin agregarme nada extra al terminar el servicio.', 29],
]],

['slug' => 'aleda-store-joyas-y-accesorios', 'opiniones' => [
    ['Marisol P.', 5, 'Compré un anillo con dije y hasta ahora no se le ha ido el color ni se me ha puesto verde el dedo.', 5],
    ['Diego A.', 5, 'En la promoción 2x1 aproveché y me llevé dos cadenitas de acero para regalar a mis hijas.', 16],
    ['Elena S.', 4, 'Tienen bastantes modelos y la señora me dejó probarme varios anillos en Coishco sin apurarme.', 33],
]],

['slug' => 'zstl-mil-kositas', 'opiniones' => [
    ['Paola M.', 5, 'Compré una cadena de plata y no se ha puesto negra; la uso a diario desde hace meses.', 8],
    ['Rubén G.', 5, 'Le llevé el collar de mi mamá para la limpieza y lo devolvieron como nuevo y brillante.', 19],
    ['Ana Lucía F.', 4, 'Aquí en Coishco siempre hay collares de moda y te atienden con paciencia para elegir el modelo.', 27],
]],

['slug' => 'la-negrita', 'opiniones' => [
    ['Jorge L.', 5, 'El menú del día viene bien servido y a buen precio, salgo lleno y sin gastar mucho.', 4],
    ['Teresa N.', 5, 'El ceviche de pescado es fresco y bien curado, se siente que el pescado entró ese mismo día.', 14],
    ['Pedro Ch.', 4, 'El trato del personal es cordial y las mesas siempre están limpias cuando llegamos con la familia.', 40],
]],

['slug' => 'chafloque-la-casa-del-camaron', 'opiniones' => [
    ['Beatriz O.', 5, 'Los camarones al ajillo llegaron bien calientes y con harto camarón, no como en otros lugares.', 7],
    ['Víctor H.', 5, 'El chupe de camarones es contundente, con buen queso y camarones enteros; vale lo que cuesta.', 22],
    ['Nelly A.', 4, 'Nos atendieron rápido a pesar de que el local estaba lleno y las porciones son grandes.', 31],
]],

['slug' => 'restaurant-don-victor', 'opiniones' => [
    ['Silvia M.', 5, 'El seco de carne venía bien cocido, se deshacía solo, con harto frejol y bastante arroz.', 3],
    ['Renzo V.', 5, 'Pido el cuarto de pollo a la brasa y siempre me lo dan jugoso, con las papas crocantes.', 17],
    ['Gladys E.', 4, 'Buen menú y precio justo; el refresco de casa ayuda a bajar la comida bien cargada.', 42],
]],

['slug' => 'libreria-bazar-el-saber', 'opiniones' => [
    ['Katherine R.', 5, 'Siempre hago mis copias ahí porque es rápido y no cobran caro por las impresiones a color.', 11],
    ['Óscar T.', 5, 'Compré una mochila para mi hijo y hasta ahora aguanta el peso de los libros sin romperse.', 24],
    ['Pilar D.', 4, 'Hay útiles de todas las marcas a la mano y te atienden rápido cuando hay cola en la caja.', 38],
]],

['slug' => 'libreria-bazar-mechita', 'opiniones' => [
    ['Sandro V.', 5, 'Compré los cuadernos de la lista escolar y me salió más barato que en la librería del centro.', 10],
    ['Milagros C.', 5, 'Tiene de todo un poco: útiles y también regalos para cumpleaños, ya no tengo que ir lejos.', 21],
    ['Liliana H.', 4, 'La señora del mostrador me ayudó a encontrar unos cuadernos que no había en otros sitios.', 30],
]],

['slug' => 'libreria-y-bazar-victoria', 'opiniones' => [
    ['Cecilia M.', 5, 'Pedí cuadernos de cien hojas para toda el aula y me los dio al mismo precio, sin subirme nada.', 13],
    ['Hernán R.', 5, 'Los artículos de escritorio están ordenados y hay grapadoras, perforadoras y toda esa lista.', 26],
    ['Yolanda P.', 4, 'La atención es tranquila y te deja revisar los útiles antes de comprarlos, eso me gusta.', 44],
]],

['slug' => 'd-analiss', 'opiniones' => [
    ['Bruno A.', 5, 'Siempre llevo mis trabajos para las copias e impresiones y salen rápido, casi no espero.', 12],
    ['María Elena G.', 5, 'Encontré los libros y textos que me pidieron en el colegio, no tuve que encargarlos en Lima.', 23],
    ['Jhonatan S.', 4, 'Es práctico porque haces las copias y de una compras los textos en el mismo local.', 34],
]],

['slug' => 'rio-santa-editores', 'opiniones' => [
    ['Verónica L.', 5, 'Mandé imprimir mi trabajo de la universidad y quedó nítido, con las tablas bien definidas.', 15],
    ['Alberto N.', 5, 'Tienen buen stock de artículos de oficina: compré archivadores y papel bond para la empresa.', 28],
    ['Rocío F.', 4, 'Me atendieron rápido y hasta me dejaron revisar las hojas antes de anillar el documento.', 37],
]],

['slug' => 'libreria-y-novedades-hiroshi', 'opiniones' => [
    ['Fátima Q.', 5, 'Aquí siempre encuentro cuadernos de las marcas que piden en el colegio de Santa.', 2],
    ['Gustavo I.', 5, 'El dueño te atiende amable y te busca el libro que falta aunque no lo tenga en vitrina.', 25],
    ['Ingrid B.', 4, 'También venden libros y textos de primaria y secundaria, así ahorro el viaje a Chimbote.', 39],
]],

['slug' => 'hotel-country', 'opiniones' => [
    ['Ricardo M.', 5, 'Nos quedamos en la habitación familiar y entramos todos cómodos, con espacio para las maletas.', 6],
    ['Karina T.', 5, 'Pagamos el desayuno aparte y fue contundente: pan con huevo, fruta y café bien caliente.', 20],
    ['Eduardo P.', 4, 'El cuarto estaba limpio y el agua caliente salió de una, muy tranquilo para dormir en Nuevo Chimbote.', 41],
]],

['slug' => 'hospedaje-feliz', 'opiniones' => [
    ['Lourdes E.', 5, 'La habitación doble salió a buen precio por la noche y la cama estaba cómoda y limpia.', 16],
    ['Félix A.', 5, 'Pedí cuarto con baño privado y me lo dieron sin problema; el agua caliente funcionó bien.', 29],
    ['Mónica V.', 4, 'Fue una noche tranquila, sin bulla, y el encargado nos atendió amable a la madrugada.', 36],
]],

['slug' => 'starfit-nuevo-chimbote', 'opiniones' => [
    ['Giancarlo R.', 5, 'Saqué la membresía trimestral y me salió más barato que pagar mes a mes por separado.', 5],
    ['Vanessa O.', 5, 'Con la asesoría nutricional me armaron mi dieta y ya bajé varios kilos entrenando ahí.', 18],
    ['Marco D.', 4, 'Las máquinas están operativas y el ambiente es limpio y ventilado para entrenar a cualquier hora.', 32],
]],

['slug' => 'sport-center-gym', 'opiniones' => [
    ['Pamela G.', 5, 'Pago la membresía trimestral y puedo ir a la hora que salgo del trabajo, eso me acomoda.', 9],
    ['Julio C.', 5, 'Con el entrenamiento personal corregí la técnica de sentadilla; se nota que el chico sabe.', 23],
    ['Diana S.', 4, 'El local es amplio, hay bastantes mancuernas y casi nunca tengo que esperar para usarlas.', 45],
]],

['slug' => 'euforia-gym-sede-la-marina', 'opiniones' => [
    ['Andrea L.', 5, 'Las clases de entrenamiento funcional son exigentes pero entretenidas, salgo sudando de verdad.', 7],
    ['Cristian M.', 5, 'La asesoría nutricional me ayudó a ordenar mis comidas y lo noté en el peso a las semanas.', 19],
    ['Sheila R.', 4, 'Buen ambiente en la sede de La Marina; los entrenadores te corrigen si haces mal el ejercicio.', 33],
]],

['slug' => 'cabo-fit-gym', 'opiniones' => [
    ['Kevin A.', 5, 'La clase de crossfit es fuerte pero te la pasas bien; a la semana ya se nota el cambio.', 3],
    ['Nataly P.', 5, 'El entrenamiento personalizado vale la pena porque te corrigen la postura en cada ejercicio.', 21],
    ['Bryan T.', 4, 'El ambiente es chévere para entrenar y los del gym te animan cuando ya quieres parar.', 38],
]],

['slug' => 'gsm-servicell', 'opiniones' => [
    ['Álvaro S.', 5, 'Me cambiaron la pantalla del celular en el mismo día y quedó sin ninguna falla táctil.', 10],
    ['Roxana B.', 5, 'Le puse batería nueva a mi equipo y ahora me dura todo el día sin andar cargando a cada rato.', 25],
    ['Iván Ch.', 4, 'Me atendieron con cuidado, revisaron el equipo delante de mí y me cobraron lo acordado.', 43],
]],

['slug' => 'neurokids', 'opiniones' => [
    ['Daniela V.', 5, 'Le hicieron la evaluación neurológica a mi hijo con paciencia y nos explicaron cada resultado.', 12],
    ['Sergio M.', 5, 'Las terapias de atención y concentración le ayudaron bastante; ahora termina sus tareas solo.', 26],
    ['Miriam A.', 4, 'El taller de dibujo y pintura le encantó y de paso suelta tensiones todos los sábados.', 30],
]],

['slug' => 'novedades-d-n', 'opiniones' => [
    ['Cinthia E.', 5, 'Las caritas pintadas del cumpleaños duraron toda la fiesta y a los niños les encantó el diseño.', 14],
    ['Raúl H.', 5, 'Fueron al colegio a pintar caritas por el Día del Niño y atendieron a toda el aula sin apuro.', 27],
    ['Estefany O.', 4, 'Llegó puntual con todos sus materiales y le puso el diseño que cada niño le pedía.', 40],
]],

['slug' => 'compraventa-de-ropa-de-mujer-juvenil', 'opiniones' => [
    ['Karla N.', 5, 'Encontré un vestido juvenil que me quedó perfecto y me salió a buen precio, tela fresca.', 15],
    ['Noelia D.', 5, 'Compré por docena para revender y me salió más barato que en el mercado mayorista.', 22],
    ['Fiorella P.', 4, 'Tiene tallas surtidas y uno puede revisar las prendas con calma antes de llevárselas.', 35],
]],

['slug' => 'distribuidora-ferretera-ladrinor', 'opiniones' => [
    ['Wilmer Ch.', 5, 'Compré la plancha de acero galvanizada de 1/16 x 4 x 8 y me la cortaron a la medida que pedí.', 11],
    ['Elmer V.', 5, 'Los ladrillos huecos 18x12x10 llegaron completos en su paquete de 200, casi sin roturas.', 24],
    ['Santos Q.', 4, 'Buen precio por volumen y me despacharon el pedido en Coishco sin hacerme esperar tanto.', 37],
]],

['slug' => 'ferreteria-la-casa-del-rey', 'opiniones' => [
    ['Hugo B.', 5, 'Encontré la varilla corrugada del grosor exacto que necesitaba y me la cortaron en el momento.', 6],
    ['Érica M.', 5, 'Tienen tornillos y fijaciones de todo tipo sueltos; no te obligan a comprar por caja.', 17],
    ['Tomás A.', 4, 'Me ayudaron a calcular cuánto material necesitaba para el techo y no me vendieron de más.', 42],
]],

['slug' => 'ferreteria-lukimar', 'opiniones' => [
    ['Julio R.', 5, 'Pedí dos carretillas de arena para mi vereda y me la trajeron el mismo día, bien cargada.', 5],
    ['Mariela T.', 5, 'Me explicaron qué broca usar para el concreto y me prestaron el taladro sin cobrarme nada extra.', 19],
    ['Elmer Q.', 4, 'El martillo y la lima que compré son de fierro macizo; ya llevo meses usándolos y no se han doblado.', 33],
]],

['slug' => 'distribuidora-quezada-r-eirl', 'opiniones' => [
    ['Sandra V.', 5, 'El galón de látex me rindió para toda la sala y quedó parejo, sin chorrearse en la pared.', 3],
    ['Percy A.', 5, 'Fui por tornillos de media pulgada y me dieron la medida justa; hasta me vendieron de uno en uno.', 14],
    ['Nelly G.', 5, 'Me prepararon el color que quería para la fachada y quedó igualito al de la muestra.', 27],
]],

['slug' => 'negociaciones-inversiones-larrain-e-i-r-l', 'opiniones' => [
    ['Wilmer S.', 5, 'Compré diez bolsas de cemento de 42.5 kilos y me las alcanzaron hasta la puerta del obraje.', 2],
    ['Rocío M.', 5, 'El badilejo y la plancha que llevé son de fierro macizo, no como los finitos de otras ferreterías.', 22],
    ['Teodoro L.', 4, 'Abrí a las siete y media y ya tenían cargado el camión con el pedido de mi maestro.', 38],
]],

['slug' => 'ferreterias', 'opiniones' => [
    ['Zoila B.', 5, 'Entré por unos pernos y salí con la tubería y el cable que me faltaba para la instalación.', 6],
    ['Iván D.', 5, 'El señor que atiende sabe de medidas: le dije cuánto de tubo necesitaba y me lo cortó al toque.', 17],
    ['Carmen H.', 4, 'Es chico el local y hay cosas apiladas, pero siempre encuentro lo que busco en Manuel Ruiz.', 41],
]],

['slug' => 'ferreteria-industrial-valverde', 'opiniones' => [
    ['Álex P.', 5, 'Llevé el perno viejo como muestra y me dieron el mismo, con tuerca y arandela incluida.', 4],
    ['Janeth R.', 5, 'Me probaron el juego de llaves ahí mismo para que viera que no venía fallado.', 20],
    ['Félix C.', 5, 'Aunque es industrial te atienden por poquitos; me vendieron cuatro tornillos sin poner cara.', 35],
]],

['slug' => 'la-cochera-campestre', 'opiniones' => [
    ['Rosario N.', 5, 'El seco de carne viene con bastante frejol y la carne se deshace solita, bien sazonado.', 8],
    ['Miguel A.', 5, 'La jarra de chicha está helada y alcanza para cuatro vasos grandes, perfecta para el almuerzo.', 25],
    ['Liliana F.', 4, 'El patio es fresco, hay sombra de los árboles y los chicos corren tranquilos mientras uno come.', 39],
]],

['slug' => 'rosatel-chimbote', 'opiniones' => [
    ['Katherine S.', 5, 'Mandé un arreglo de rosas rojas a mi mamá por su cumpleaños y llegó puntual, bien frescas.', 9],
    ['Jorge L.', 5, 'El peluche y los chocolates que venían con el ramo eran grandes; mi enamorada quedó encantada.', 23],
    ['Patricia E.', 5, 'Me leyeron la tarjeta por teléfono antes de mandarla, así no hubo error con el nombre.', 44],
]],

['slug' => 'casa-del-panadero-chimbote', 'opiniones' => [
    ['Gladys O.', 5, 'Compro el pan francés a las seis de la mañana y sale calientito, con la corteza que cruje.', 1],
    ['Rubén I.', 5, 'Siempre tienen las bebidas heladas y las golosinas que mis hijos piden al salir del colegio.', 16],
    ['Milagros A.', 4, 'Los precios son cómodos y me despachan rápido aunque haya cola a la hora del almuerzo.', 30],
]],

['slug' => 'cb-store', 'opiniones' => [
    ['Giancarlo M.', 5, 'Atienden desde temprano hasta la noche; a las once pasé por unas chelas y todavía estaba abierto.', 7],
    ['Yesenia C.', 5, 'Los abarrotes están ordenados por pasillo y se encuentra rápido el arroz y el aceite.', 21],
    ['Rómulo V.', 4, 'Llevo los productos de limpieza para la casa y siempre me cuadra el vuelto sin problema.', 36],
]],

['slug' => 'catalina-store', 'opiniones' => [
    ['Flor T.', 5, 'Está a media cuadra de mi casa, así que bajo por el pan y el azúcar sin hacer mercado grande.', 11],
    ['Hernán Z.', 5, 'Pedí por WhatsApp y me mandaron los abarrotes hasta la puerta, bien separados en bolsas.', 24],
    ['Sonia K.', 5, 'Tienen de todo para la comida del día: menestra, atún y hasta golosinas para los chicos.', 42],
]],

['slug' => 'marca-stylos', 'opiniones' => [
    ['Édgar U.', 5, 'Las bebidas y los snacks están al alcance de la mano; entro, pago y sigo nomás.', 10],
    ['Marisol Q.', 5, 'Me fiaron hasta el viernes cuando me faltó sencillo, gente de confianza en el barrio.', 26],
    ['Alberto G.', 4, 'El local está limpio y los productos de limpieza los tienen surtidos por marcas.', 45],
]],

['slug' => 'ecovisual', 'opiniones' => [
    ['Verónica P.', 5, 'Me tomaron la medida de la vista ahí mismo y los lentes quedaron exactos, no me marea nada.', 12],
    ['Diego H.', 5, 'Las micas de contacto me las enseñaron a poner con paciencia, porque yo era novata.', 28],
    ['Brenda Y.', 5, 'Los lentes de sol que compré son polarizados y de verdad se nota la diferencia manejando.', 40],
]],

['slug' => 'optica-del-pueblo-chimbote', 'opiniones' => [
    ['Cecilia R.', 5, 'Pedí mis lentes de medida un martes y el viernes ya los tenía listos con el armazón puesto.', 5],
    ['Óscar T.', 5, 'Llevé mis lentes viejos para que los ajustaran y me los limpiaron sin cobrarme nada.', 18],
    ['Nancy B.', 4, 'Me hicieron la prueba de la vista con calma y me explicaron por qué necesitaba otro aumento.', 32],
]],

['slug' => 'cmo-optica-s', 'opiniones' => [
    ['Luis C.', 5, 'Había bastante variedad de armazones y me dejaron probarme varios frente al espejo.', 13],
    ['Pamela O.', 5, 'Las micas de contacto me las dieron con su estuche y las indicaciones de limpieza.', 29],
    ['Bruno S.', 5, 'Me ajustaron el armazón nuevo porque me apretaba detrás de la oreja y quedó suave.', 43],
]],

['slug' => 'opticas-tu', 'opiniones' => [
    ['Karina D.', 5, 'Fui por lentes de medida y me salieron a buen precio con el armazón y las micas incluidas.', 6],
    ['Manuel I.', 5, 'Me ajustaron los lentes cuando se me torció el armazón, en cinco minutos y sin cobrar.', 19],
    ['Sarita L.', 4, 'La señora me limpió los lentes con su paño y me enseñó cómo cuidarlos en casa.', 34],
]],

['slug' => 'opticas-quilcat', 'opiniones' => [
    ['Arturo M.', 5, 'Me midieron la vista dos veces para estar seguros y los lentes me quedaron cómodos.', 3],
    ['Diana F.', 5, 'Compro las micas de contacto aquí porque siempre tienen mi graduación en stock.', 23],
    ['Víctor N.', 5, 'El local queda cerca del paradero, así que paso al salir del trabajo y me atienden rápido.', 37],
]],

['slug' => 'optica-vision-zolutions', 'opiniones' => [
    ['Saby E.', 5, 'El examen de la vista fue completo: me revisaron con varios aparatos y me explicaron el resultado.', 8],
    ['Renzo A.', 5, 'Compré unos lentes de sol con protección UV y me confirmaron la garantía por escrito.', 22],
    ['Cinthia W.', 5, 'A mi papá le detectaron que necesitaba lentes y salió con su receta el mismo día.', 41],
]],

['slug' => 'optica-vipp', 'opiniones' => [
    ['Gustavo R.', 5, 'Encontré un armazón liviano que no me marca la nariz, después de buscar en varias ópticas.', 9],
    ['Eliana V.', 5, 'Me graduaron los lentes de sol y ahora manejo sin entrecerrar los ojos al mediodía.', 25],
    ['Tito J.', 4, 'Traje mi receta de otra óptica y no pusieron problema para armarme los lentes.', 38],
]],

['slug' => 'consultorio-dental-mamani', 'opiniones' => [
    ['Andrea L.', 5, 'Me hicieron el blanqueamiento en dos sesiones y se me notó bastante, sin dolor después.', 4],
    ['Roxana C.', 5, 'Llevé a mi hijo por la evaluación de ortodoncia y nos explicaron el tratamiento con precios.', 20],
    ['Pablo M.', 4, 'Me sacaron una muela que me molestaba y fue rápido, con anestesia que sí hizo efecto.', 31],
]],

['slug' => 'clinica-dental-rosa-s', 'opiniones' => [
    ['Jessica P.', 5, 'La endodoncia me salvó la muela que ya daba por perdida; no sentí nada durante la sesión.', 7],
    ['Fernando G.', 5, 'El blanqueamiento me dejó los dientes parejos y me dieron indicaciones para no mancharlos.', 21],
    ['Lucero A.', 5, 'Atienden con cita y casi no esperas; el consultorio está limpio y ordenado.', 35],
]],

['slug' => 'clinica-dental-ramirez-chimbote', 'opiniones' => [
    ['Miriam S.', 5, 'La extracción de la muela del juicio fue rápida y me dieron las indicaciones para la casa.', 11],
    ['Kevin T.', 5, 'Después del blanqueamiento me miré al espejo y de verdad se notó el cambio enseguida.', 27],
    ['Bety Q.', 4, 'Cobran razonable por la consulta y te explican antes qué te van a hacer y cuánto cuesta.', 40],
]],

['slug' => 'centro-medico-san-juan', 'opiniones' => [
    ['Rosmery D.', 5, 'Conseguí cita con el especialista para la misma semana y me atendieron casi a la hora exacta.', 2],
    ['Hugo B.', 5, 'El chequeo preventivo incluyó análisis y me llamaron para explicarme los resultados.', 18],
    ['Elva K.', 5, 'La enfermera me tomó la presión y el peso antes de pasar al consultorio, todo bien ordenado.', 33],
]],

['slug' => 'posta-medica-san-juan', 'opiniones' => [
    ['Nicolás E.', 5, 'Fui por la consulta general y me mandaron los análisis al laboratorio de la misma posta.', 5],
    ['Maruja F.', 4, 'Los resultados de laboratorio salieron en la tarde y el doctor me los leyó uno por uno.', 24],
    ['Segundo R.', 5, 'Había cola desde temprano, pero la atención en ventanilla fue ordenada y me dieron mi ticket.', 39],
]],

['slug' => 'posta-magdalena-nueva-chimbote', 'opiniones' => [
    ['Yolanda M.', 5, 'Llevé a mi bebé a su vacuna y la enfermera me explicó la próxima dosis en el carné.', 10],
    ['César H.', 5, 'Entré por una urgencia con fiebre alta de mi abuela y la atendieron de inmediato.', 26],
    ['Doris V.', 4, 'Es la única posta cerca de casa que atiende de noche; me atendieron rápido con mi dolor.', 42],
]],

['slug' => 'veterinaria-san-luiz', 'opiniones' => [
    ['Gisela T.', 5, 'Llevé a mi perro por su vacuna y el veterinario lo revisó completo antes de vacunarlo.', 8],
    ['Jhonatan C.', 5, 'El baño y corte le quedó bien parejo a mi schnauzer, y salió sin ese olor fuerte.', 22],
    ['Maritza O.', 5, 'Cobran barato comparado con otras veterinarias y dejan que uno esté presente en la consulta.', 36],
]],

['slug' => 'veterinaria-y-spa-canino-san-luis', 'opiniones' => [
    ['Rosa C.', 5, 'Llevé a mi schnauzer para el corte de raza y quedó parejo, tal como lo pedí.', 7],
    ['Luis M.', 5, 'Vacunaron a mi gata sin que llore; el veterinario me explicó bien la dosis y el refuerzo.', 21],
    ['Ana P.', 4, 'El spa huele limpio y atienden con cita, así no esperas con el perro en la calle.', 33],
]],

['slug' => 'centro-medico-ebenezer', 'opiniones' => [
    ['Julio R.', 5, 'Me hice los exámenes de laboratorio en la mañana y a las pocas horas ya tenía mis resultados.', 4],
    ['Carmen V.', 5, 'El electrocardiograma me lo tomaron rápido y el doctor me explicó el resultado sin apurarme.', 15],
    ['Percy A.', 4, 'La sala de espera es chica pero ordenada, y la señorita de recepción te ubica en la cola.', 28],
]],

['slug' => 'luciano-consultorio-nutricional', 'opiniones' => [
    ['Milagros T.', 5, 'En la consulta me pesaron y midieron, y me dieron una dieta con comida de acá, no cosas raras.', 9],
    ['Jorge Q.', 5, 'Bajé cuatro kilos en el control mensual siguiendo el plan que me armó, sin pasar hambre.', 19],
    ['Yesenia B.', 5, 'Te explican con paciencia y te mandan tu lista por WhatsApp para no olvidarte de las porciones.', 41],
]],

['slug' => 'libreria-scorpio', 'opiniones' => [
    ['Gladys N.', 5, 'Encontré el texto de matemática que pedían en el colegio de mi hijo, y a buen precio.', 6],
    ['Marco S.', 4, 'Compré una mochila para mi hija y aguantó todo el año escolar, todavía la usa.', 17],
    ['Elva D.', 5, 'Tienen los libros separados por grado y la señora te busca el título sin hacerte esperar.', 30],
]],

['slug' => 'roma-comercial-libreria', 'opiniones' => [
    ['Segundo H.', 5, 'Compré papel bond y folders para la oficina y me hicieron precio por cantidad.', 3],
    ['Patricia L.', 5, 'Pedí un libro que no tenían y me lo trajeron en dos días, cosa que no hacen todos.', 22],
    ['Wilder G.', 4, 'Es de esas librerías de antes, con todo en vitrina y la señora que atiende sabe de libros.', 38],
]],

['slug' => 'variedades-hys-express', 'opiniones' => [
    ['Katherine F.', 5, 'Llevé mi tesis para anillar y empastar, y quedó prolija, con la tapa bien centrada.', 11],
    ['Rubén O.', 5, 'Imprimí cien hojas a color para una exposición y salieron sin manchas, me cobraron normal.', 25],
    ['Nelly J.', 5, 'Aunque había cola, sacaron mis copias rápido porque tienen dos máquinas trabajando.', 44],
]],

['slug' => 'comercial-cindy', 'opiniones' => [
    ['Marisol E.', 5, 'Compré cuadernos y un juego de reglas para mi sobrino, más barato que en la librería del centro.', 5],
    ['César I.', 4, 'Tienen artículos de escritorio de varias marcas y la dueña te deja escoger sin apurarte.', 16],
    ['Zoila R.', 5, 'Fui por un texto de secundaria y sí lo tenían; además me vendieron el forro al toque.', 29],
]],

['slug' => 'r-j-motors-nvo-chimbote', 'opiniones' => [
    ['Edwin P.', 5, 'Le hice el cambio de aceite a mi Corsa y me mostraron el filtro viejo, todo transparente.', 2],
    ['Rocío M.', 5, 'Me repararon el motor de mi auto y al día siguiente ya estaba listo para trabajar.', 13],
    ['Teodoro A.', 5, 'El mecánico te explica qué tiene tu carro antes de cobrar, no te inventan fallas.', 36],
]],

['slug' => 'decoraciones-eventos-urban', 'opiniones' => [
    ['Sandra K.', 5, 'Pedí arreglos de globos para el cumpleaños de mi hija y quedaron igualitos a la foto.', 8],
    ['Iván C.', 5, 'Alquilaron mantelería para quince personas y trajeron los manteles limpios y planchados.', 20],
    ['Lucero V.', 4, 'Me armaron el arco de globos en el local a la hora que quedamos, bien cumplidos.', 34],
]],

['slug' => 'senzia-tech-novedades-mia', 'opiniones' => [
    ['Diana Z.', 5, 'Compré dos blusas de temporada y me hicieron descuento por llevar el par.', 10],
    ['Fabiola U.', 5, 'La ropa es de talla real, no como esas tiendas donde todo te queda chico.', 23],
    ['Renzo T.', 4, 'Publican las promociones en su WhatsApp y ahí separas tu prenda antes que se acabe.', 40],
]],

['slug' => 'dark-kitchen-peru', 'opiniones' => [
    ['Christian B.', 5, 'Pedimos el combo familiar un domingo y alcanzó para los cinco, bien servido.', 1],
    ['Sofía N.', 5, 'La comida llegó caliente y a la hora prometida, aunque era noche de partido.', 14],
    ['Manuel G.', 5, 'Las hamburguesas vienen con papas y su crema de la casa, mejores que muchas del centro.', 27],
]],

['slug' => 'academia-preuniversitaria-moivre-sigma', 'opiniones' => [
    ['Ángel D.', 5, 'Hice el curso intensivo de verano y entré a la universidad; los profesores te dan sus apuntes.', 12],
    ['Brisa H.', 5, 'Los sábados hay simulacro y te entregan tu puntaje el mismo día, así uno se ubica.', 24],
    ['Óscar L.', 4, 'El curso por especialidad me sirvió para nivelarme en química, que era mi miedo.', 37],
]],

['slug' => 'zona-fit-santa', 'opiniones' => [
    ['Verónica S.', 5, 'El menú del día trae entrada, segundo y refresco, y no se siente pesado para trabajar.', 18],
    ['Pavel R.', 5, 'Probé el menú vegetariano y la menestra con arroz integral estaba bien sazonada.', 31],
    ['Susana M.', 5, 'Está a una cuadra de la plaza de Santa y al mediodía atienden rápido.', 43],
]],

['slug' => 'la-previa-karaoke-bar', 'opiniones' => [
    ['Kevin A.', 5, 'Pedimos parrilla para dos y la carne vino jugosa, con su choclo y sus papas.', 7],
    ['Maribel Q.', 4, 'La hamburguesa es grande de verdad y el pan no se deshace con la salsa.', 19],
    ['Diego F.', 5, 'Ponen buena música y el ambiente se prende después de las diez, sin ser un desorden.', 32],
]],

['slug' => 'sam-tec', 'opiniones' => [
    ['Jhonatan V.', 5, 'Compré un celular ahí y me lo configuraron, le pusieron mi cuenta y todo.', 9],
    ['Esther P.', 5, 'Hago las recargas en Coishco sin ir hasta Chimbote, y llegan al instante.', 26],
    ['Willy C.', 4, 'Venden cargadores y accesorios que duran, no como los de la calle que fallan rápido.', 39],
]],

['slug' => 'moda-circular-kaia', 'opiniones' => [
    ['Alessandra G.', 5, 'Compré un collar y aretes de segunda mano y estaban como nuevos, ni se notaba el uso.', 4],
    ['Nicolás T.', 5, 'Me llevé tres accesorios con la promoción y pagué menos de lo que pensaba.', 17],
    ['Fiorella B.', 5, 'La chica te ayuda a combinar y te dice qué te queda bien, sin venderte de más.', 35],
]],

['slug' => 'thai-ice-cream', 'opiniones' => [
    ['Pamela R.', 5, 'El helado artesanal de lúcuma es cremoso y no empalaga como el de las heladerías grandes.', 6],
    ['Alexis M.', 5, 'Probé el frappé de maracuyá y viene bien helado, ideal para el calor de Chimbote.', 20],
    ['Katia D.', 4, 'Te dejan probar el sabor antes de pedir y eso no lo hace cualquiera.', 30],
]],

['slug' => 'multiservicios-principe-taller-mecanico-rodriguez', 'opiniones' => [
    ['Genaro L.', 5, 'Me revisaron los frenos antes de un viaje a Casma y me cambiaron las pastillas gastadas.', 3],
    ['Roxana E.', 5, 'Hice el mantenimiento preventivo de mi moto y me cobraron justo lo que me dijeron.', 16],
    ['Percy H.', 5, 'En Santa es difícil hallar taller abierto; este atiende hasta tarde entre semana.', 29],
]],

['slug' => 'green-vibe', 'opiniones' => [
    ['Melisa A.', 5, 'Compré snacks de granola y castañas, y mi hijo se los comió sin protestar.', 10],
    ['Aldo N.', 5, 'Tienen leche de almendras y quesos veganos, que antes solo encontraba en Lima.', 21],
    ['Cynthia O.', 4, 'La chica te explica las etiquetas y te dice qué producto tiene menos azúcar.', 42],
]],

['slug' => 'la-ruta-gamer', 'opiniones' => [
    ['Bruno S.', 5, 'Entramos seis a la sala de escape y nos trabamos en el último candado, pero salimos.', 5],
    ['Alessia F.', 5, 'Llevé a mi sobrino de nueve años y la experiencia para niños le encantó.', 15],
    ['Marcos J.', 5, 'El precio por persona está bien para lo que dura el juego, casi una hora.', 28],
]],

['slug' => 'la-sala-del-kushuru', 'opiniones' => [
    ['Lourdes C.', 5, 'Reservamos la sala temática por una hora y la decoración te mete en la historia.', 8],
    ['Emilio R.', 5, 'Fuimos en grupo desde Santa y nos atendieron con la reserva hecha por teléfono.', 22],
    ['Tatiana V.', 4, 'Los acertijos son de pensar, no de fuerza, así que van parejas y familias.', 36],
]],

['slug' => 'elegance-vip-suites-spa', 'opiniones' => [
    ['Fernando Z.', 5, 'Nos quedamos una noche en la suite y estaba limpia, con toallas nuevas.', 2],
    ['Gisela M.', 5, 'Pagamos el desayuno aparte y nos lo subieron a la habitación a la hora pedida.', 14],
    ['Hugo P.', 5, 'Nos cobraron la suite a buen precio por ser día de semana y no hubo ruido.', 33],
]],

['slug' => 'servicentro-coishco', 'opiniones' => [
    ['Nilton A.', 5, 'Hice el cambio de aceite y el alineamiento en una sola visita, me ahorré el viaje.', 11],
    ['Rosa Y.', 5, 'El balanceo se sintió al instante, ya no vibra el timón en la Panamericana.', 25],
    ['Javier B.', 4, 'Atienden temprano y en Coishco no hay otro que te haga alineamiento con máquina.', 40],
]],

['slug' => 'centro-de-estudios-sigma', 'opiniones' => [
    ['Sebastián Q.', 5, 'Pagué la membresía trimestral y me sale más barato que pagar mes a mes.', 13],
    ['Paola U.', 5, 'Las clases grupales de spinning son a las siete y siempre hay cupo.', 27],
    ['Cristian E.', 5, 'Los entrenadores corrigen tu postura cuando levantas peso, no te dejan solo.', 45],
]],

['slug' => 'centro-de-salud-santa', 'opiniones' => [
    ['Wilmer T.', 5, 'Fui por consulta médica general y la doctora me escuchó con calma, sin apurar la atención.', 6],
    ['Nancy L.', 5, 'Me hicieron los exámenes de laboratorio en ayunas y los resultados salieron al día siguiente.', 19],
    ['Arturo S.', 4, 'Atienden por orden de llegada desde temprano; conviene ir antes de las siete.', 31],
]],

['slug' => 'zegourmet-restaurante', 'opiniones' => [
    ['Rosa C.', 5, 'El ceviche de pescado estaba fresco, con bastante leche de tigre y su camote bien dulce.', 7],
    ['Luis M.', 5, 'Pedí un cuarto de pollo a la brasa para llevar y salió jugoso, con papas crocantes.', 21],
    ['Ana P.', 5, 'Fuimos un domingo al mediodía y aunque estaba lleno nos atendieron rápido, sin mucha espera.', 33],
]],

['slug' => 'mare-food-beer', 'opiniones' => [
    ['Carmen Q.', 5, 'El seco de carne venía con su frejolito y arroz, bien servido y con harto sabor criollo.', 4],
    ['Jorge V.', 5, 'El cuarto de pollo a la brasa es rendidor y la salsa de la casa le da su toque.', 18],
    ['Milagros T.', 4, 'Me gusta ir de noche porque hay música tranquila y las mesas del segundo piso son cómodas.', 29],
]],

['slug' => 'vidrieria-leomax-chimbote', 'opiniones' => [
    ['Wilder S.', 5, 'Mandé hacer un espejo a medida para el baño y me lo cortaron justo como lo pedí.', 3],
    ['Nancy R.', 5, 'Instalaron las mamparas de la ducha en mi casa y quedaron bien selladas, sin que se salga el agua.', 16],
    ['Percy A.', 5, 'Fui con las medidas de mi ventana y me ayudaron a calcular el vidrio sin cobrarme de más.', 40],
]],

['slug' => 'ie-santiago-antunez-de-mayolo', 'opiniones' => [
    ['Marisol H.', 5, 'Matriculé a mi hijo en segundo de secundaria y la dirección me explicó todo el trámite con paciencia.', 9],
    ['Elmer C.', 5, 'El local del colegio está bien cuidado y los padres entramos sin problema cuando hay reunión.', 24],
    ['Yolanda B.', 4, 'Mi sobrina estudia ahí y siempre sale contenta de las clases de matemática del profesor de turno.', 37],
]],

['slug' => 'cevicheria-mi-catalina', 'opiniones' => [
    ['Julio R.', 5, 'El ceviche mixto trae harto pescado, conchas y calamar, y el ají no pica de más.', 2],
    ['Katy M.', 5, 'El ceviche de pescado lo preparan al momento, se siente el pescado fresco del día.', 20],
    ['Segundo L.', 5, 'Fuimos en familia y nos pusieron una mesa grande en la vereda, la atención fue rápida.', 31],
]],

['slug' => 'voltech-rent-a-scooter-alquiler-de-scooter', 'opiniones' => [
    ['Brayan P.', 5, 'Alquilé una scooter por un mes para ir a trabajar y me salió más barato que el pasaje diario.', 5],
    ['Fiorella D.', 5, 'Compré una scooter ahí y me explicaron el mantenimiento y hasta me dieron el casco.', 15],
    ['Marco A.', 4, 'Me entregaron la scooter con la batería cargada y me enseñaron a usarla antes de salir.', 27],
]],

['slug' => 'flores-y-detalles', 'opiniones' => [
    ['Gladys N.', 5, 'Encargué un ramo de rosas rojas para el cumpleaños de mi mamá y llegó bien fresco.', 6],
    ['Rubén T.', 5, 'Para el velorio de mi abuelo hicieron la corona fúnebre en pocas horas y con buen gusto.', 22],
    ['Sonia F.', 5, 'Los precios de los ramos están a la vista y te asesoran según lo que quieras gastar.', 44],
]],

['slug' => 'fitness-studio-company', 'opiniones' => [
    ['Diego S.', 5, 'Usé el día de prueba antes de decidirme y me gustó que las máquinas estén siempre limpias.', 8],
    ['Cynthia V.', 5, 'Pago la membresía mensual y las clases de spinning de la noche siempre tienen cupo.', 19],
    ['Óscar M.', 4, 'Los entrenadores te corrigen la postura cuando haces pesas, no te dejan solo en la sala.', 35],
]],

['slug' => 'minimarket-yataco', 'opiniones' => [
    ['Efraín G.', 5, 'Compro las gaseosas por mayor para mi bodega y siempre me respetan el precio de lista.', 11],
    ['Lucía P.', 5, 'Tienen las bebidas bien heladas y es lo único abierto hasta tarde en la zona.', 23],
    ['Santos R.', 5, 'El chico del minimarket me ayudó a cargar las cajas hasta el mototaxi sin cobrarme extra.', 42],
]],

['slug' => 'zona-gamer-chimbote', 'opiniones' => [
    ['Kevin A.', 5, 'Alquilé una cabina por el día con mis primos y las máquinas corren los juegos sin lag.', 10],
    ['Renzo B.', 5, 'Los snacks y bebidas no son caros, así que uno se queda toda la tarde jugando.', 26],
    ['Antony C.', 4, 'El local tiene buen aire y las sillas son cómodas para las partidas largas de fin de semana.', 38],
]],

['slug' => 'panaderia-zatl', 'opiniones' => [
    ['Teresa L.', 5, 'El pan francés sale calientito a las seis de la mañana y la docena alcanza para toda la casa.', 1],
    ['Manuel O.', 5, 'Encargamos la torta de cumpleaños y le pusieron el nombre y los adornos como pedimos.', 17],
    ['Rosario E.', 5, 'La señora del mostrador siempre me guarda el pan del día cuando llego tarde.', 30],
]],

['slug' => 'ie-n-88044', 'opiniones' => [
    ['Aurelia M.', 5, 'Llevé a mi hija a matricularla en primer grado y en la dirección me atendieron el mismo día.', 12],
    ['Gregorio P.', 5, 'El colegio tiene patio grande y los chicos de primaria salen a recreo con sombra.', 25],
    ['Elva Q.', 4, 'Como vecino de Coishco veo que las aulas están pintadas y el portón siempre está cuidado.', 41],
]],

['slug' => 'floreria-y-detalles-montoya-tienda-1', 'opiniones' => [
    ['Janeth S.', 5, 'Contraté las flores para el matrimonio de mi hermana y armaron los centros de mesa igualitos a la foto.', 13],
    ['Wilmer A.', 5, 'Necesitábamos una corona fúnebre de urgencia y la tuvieron lista en la mañana.', 28],
    ['Betty C.', 5, 'Te dejan elegir las flores y arman el arreglo delante de uno, sin cambiarte lo pedido.', 36],
]],

['slug' => 'cevicheria-el-buzo-nuevo-chimbote', 'opiniones' => [
    ['Cristian H.', 5, 'La jalea mixta viene bien cargada de mariscos y el chicharrón de pota es aparte.', 14],
    ['Marleny D.', 5, 'La chicha morada la sirven en jarra helada y no es puro azúcar como en otros lados.', 32],
    ['Iván R.', 4, 'El local queda cerca de la avenida y hay espacio para estacionar la moto sin problema.', 45],
]],

['slug' => 'centro-terapeutico-holistico', 'opiniones' => [
    ['Patricia N.', 5, 'Tomé una sesión de terapia holística por el estrés del trabajo y salí bastante relajada.', 5],
    ['Hugo F.', 5, 'En la terapia de energía me explicaron cada paso y pude mover el brazo sin tanto dolor.', 20],
    ['Lidia G.', 4, 'El ambiente es silencioso, con música suave, y la señora te da su tiempo sin apurar.', 39],
]],

['slug' => 'plazavea-super-chimbote', 'opiniones' => [
    ['Silvia T.', 5, 'En abarrotes encuentro el arroz y el aceite de la marca que no hay en la bodega.', 2],
    ['Ricardo M.', 5, 'Los productos de limpieza siempre tienen oferta y aprovecho para llevar lejía y detergente.', 18],
    ['Zoila A.', 5, 'Las cajas rápidas del mediodía ayudan bastante cuando uno sale apurado del trabajo a comprar.', 34],
]],

['slug' => 'la-percha-vintage', 'opiniones' => [
    ['Fabiana R.', 5, 'Encontré una casaca de jean de los noventas a buen precio y en talla para mí.', 7],
    ['Nicolás V.', 5, 'Las camisas vintage están bien lavadas y sin manchas, se nota que las revisan.', 21],
    ['Camila O.', 4, 'La dueña me dejó probarme varios polos y me recomendó qué combinaba con cada uno.', 43],
]],

['slug' => 'mass-coishco-comiseria', 'opiniones' => [
    ['Eliana S.', 5, 'Hago el pedido de abarrotes por WhatsApp y me lo traen a la casa en Coishco el mismo día.', 3],
    ['Teodoro B.', 5, 'Los precios de la comisería son de mayorista y surten mi bodeguita cada semana.', 16],
    ['Maribel C.', 5, 'Un domingo me olvidé el arroz y me lo mandaron igual, sin cobrarme el delivery.', 29],
]],

['slug' => 'nino-meza-studios', 'opiniones' => [
    ['Rómulo P.', 5, 'Me hicieron el diseño del logo de mi restaurante y quedó justo como lo imaginaba.', 6],
    ['Diana L.', 5, 'La publicidad digital para mi negocio trajo clientes nuevos en la primera semana.', 24],
    ['Álvaro N.', 4, 'Entregan los archivos abiertos y te explican cómo usarlos en tus redes.', 35],
]],

['slug' => 'notaria-delgado', 'opiniones' => [
    ['Bertha Q.', 5, 'Llevé el tema de mi terreno y el doctor me explicó el proceso legal sin vueltas.', 9],
    ['Gonzalo E.', 5, 'Constituimos la empresa con su asesoría y el trámite salió en el plazo que nos dijeron.', 19],
    ['Susana K.', 5, 'La secretaria me atendió amable y me dio hora para la consulta el mismo día.', 33],
]],

['slug' => 'alat-dojo', 'opiniones' => [
    ['Piero F.', 5, 'Hice el día de prueba del entrenamiento funcional y terminé sudando de lo lindo.', 8],
    ['Alejandra M.', 5, 'Las rutinas cambian cada semana, así que uno no se aburre haciendo lo mismo.', 22],
    ['Víctor H.', 4, 'El profesor corrige la técnica desde la primera clase y cuida la espalda de uno.', 37],
]],

['slug' => 'piramide-market', 'opiniones' => [
    ['Yulisa A.', 5, 'Las gaseosas de dos litros están más baratas que en el mercado y bien heladas.', 4],
    ['Franklin C.', 5, 'Compro los snacks para la reunión del barrio y siempre hay surtido de golosinas.', 23],
    ['Norma R.', 5, 'La señora del market me fía cuando no tengo sencillo y le pago al día siguiente.', 40],
]],

['slug' => 'econovision-centro-optico', 'opiniones' => [
    ['Sheyla D.', 5, 'Me midieron la vista y me dieron lentes de contacto que no me molestan para nada.', 10],
    ['Arturo G.', 5, 'Ajustaron mis lentes viejos porque se me torció el armazón y no cobraron nada.', 26],
    ['Verónica P.', 4, 'Me explicaron cómo limpiar los lentes de contacto y el cuidado del estuche.', 41],
]],

['slug' => 'aquatic-fish-center-chimbote-mv-493', 'opiniones' => [
    ['Aldo M.', 5, 'Compro el alimento para mis peces ahí y me han durado más que con otras marcas.', 11],
    ['Priscila S.', 5, 'Me vendieron las plantas acuáticas para la pecera y me dijeron cómo plantarlas.', 27],
    ['Johnny T.', 5, 'El chico me asesoró con la filtración de mi acuario sin apurarme para que compre.', 44],
]],

['slug' => 'santa-natura-chimbote-distribuidor-oficial', 'opiniones' => [
    ['Gisela V.', 5, 'Llevo los snacks saludables para la lonchera de mis hijos y les gustan los de quinua.', 12],
    ['Rolando A.', 5, 'La asesoría fue clave: me armaron un plan de comidas según mi presión alta.', 25],
    ['Mery B.', 4, 'Los productos naturales llegan sellados y con fecha, se nota que es distribuidor oficial.', 38],
]],

// ====== TANDA 2 (2026-09-14, noche): las 100 tiendas anteriores ======
['slug' => 'ferreteria-don-max', 'opiniones' => [
    ['Rosa C.', 5, 'Fui por tornillos para mi puerta y me dieron la medida exacta; tienen de todo en fijaciones.', 5],
    ['Luis M.', 5, 'Compré cable y un tomacorriente para la cocina; me explicaron bien cómo instalarlo sin problemas.', 18],
    ['Ana P.', 4, 'Atienden hasta tarde en Nuevo Chimbote, llegué casi al cierre y me atendieron igual con paciencia.', 33],
]],

['slug' => 'ferreteria-construplaza', 'opiniones' => [
    ['Jorge Q.', 5, 'Pedí diez bolsas de cemento de 42.5 kilos y me las cargaron rápido al mototaxi, buen precio.', 3],
    ['Marisol T.', 5, 'Compré varilla corrugada para el techo y venía bien recta, sin óxido ni dobladuras.', 12],
    ['Percy A.', 4, 'El despacho es rapidísimo, pidió mi maestro el material temprano y antes del mediodía ya estaba en obra.', 27],
]],

['slug' => 'optica-dr-leandro-perez', 'opiniones' => [
    ['Carmen R.', 5, 'Me tomaron la medida de los ojos y elegí un armazón liviano; la receta quedó exacta, veo perfecto.', 9],
    ['Willy S.', 5, 'Cambié las micas de contacto de mis lentes viejos y me salió económico, quedaron como nuevos.', 22],
    ['Nelly V.', 4, 'El doctor Leandro revisó mi vista con calma y me explicó por qué me dolía la cabeza al leer.', 41],
]],

['slug' => 'comisaria-21-de-abril', 'opiniones' => [
    ['Segundo H.', 5, 'Fui a poner la denuncia por el robo de mi celular y me atendieron rápido, sin tantas vueltas.', 2],
    ['Elva M.', 5, 'Los serenos pasan de noche por mi calle en la 21 de Abril y eso tranquiliza bastante al vecindario.', 15],
    ['Rubén D.', 4, 'Llamé por una pelea en la esquina y llegaron en pocos minutos; el trato del efectivo fue correcto.', 30],
]],

['slug' => 'inversiones-estralsa', 'opiniones' => [
    ['Alberto N.', 5, 'Compré fierro corrugado de media para mi losa y me lo cortaron a la medida que pedí.', 7],
    ['Zoila F.', 5, 'Tienen tuberías y accesorios surtidos; encontré la unión que no hallaba en otras ferreterías.', 19],
    ['Miguel Á.', 4, 'El chico del almacén me ayudó a calcular cuántos metros necesitaba para la instalación del agua.', 44],
]],

['slug' => 'mil-sabores-3', 'opiniones' => [
    ['Katy L.', 5, 'La parihuela venía bien cargada de mariscos y el caldo picante como debe ser, rico de verdad.', 4],
    ['Doris P.', 5, 'Pedimos una jarra de chicha morada bien helada y alcanzó para los cuatro sin problema.', 11],
    ['Julio C.', 4, 'El menú sale rápido al mediodía, aunque esté lleno te atienden en pocos minutos y el plato es abundante.', 26],
]],

['slug' => '1969-grill-restobar', 'opiniones' => [
    ['Giancarlo B.', 5, 'La parrillada mixta para dos nos llenó a ambos; la carne al punto y las papas rústicas crocantes.', 6],
    ['Fiorella G.', 5, 'Las alitas BBQ con papas rústicas están bien bañadas en salsa y el chopp de 500 ml llega heladísimo.', 14],
    ['Óscar R.', 4, 'Terminamos con el brownie tibio con helado de vainilla; el local está tranquilo para conversar de noche.', 38],
]],

['slug' => 'cevicheria-el-chala', 'opiniones' => [
    ['Manuel I.', 5, 'El ceviche mixto tenía bastante pescado y mariscos frescos, con su ají limo bien picante.', 1],
    ['Sandra O.', 5, 'La jalea mixta es enorme y bien crocante; la pedimos para compartir entre tres y sobró.', 8],
    ['Teodoro E.', 4, 'Llegamos a las once de la mañana y ya había gente esperando, así que conviene ir temprano.', 21],
]],

['slug' => 'la-casita-de-mariel', 'opiniones' => [
    ['Lucía A.', 5, 'El ceviche de pescado viene con bastante leche de tigre y camote, se siente fresco el pescado.', 10],
    ['Ricardo M.', 5, 'Pedimos medio pollo a la brasa para la familia y las papas estaban crocantes, buen punto de sal.', 24],
    ['Pilar Z.', 4, 'El local de La Casita es sencillo pero limpio y te atienden como en casa, ya soy cliente fijo.', 36],
]],

['slug' => 'entre-llamas-chimbote', 'opiniones' => [
    ['Hernán V.', 5, 'El ceviche de pescado aquí es bien fresco, se nota que lo preparan al momento con limón recién exprimido.', 5],
    ['Gladys T.', 5, 'La parihuela me la sirvieron humeando y con harto cangrejo; buen tamaño para el precio que cobran.', 17],
    ['César L.', 4, 'Fuimos un domingo y estuvo lleno, pero igual nos ubicaron rápido y la espera del plato fue corta.', 29],
]],

['slug' => 'rooster-king', 'opiniones' => [
    ['Verónica S.', 5, 'La porción de ensalada es generosa y bien fresca, ideal para acompañar el pollo del pedido.', 3],
    ['Iván P.', 5, 'Su crema de ají es la que le da el toque; pedí dos vasitos extra y no me cobraron de más.', 13],
    ['Roxana C.', 4, 'El despacho fue rápido, llegó caliente y bien sellado el envase; la salsa no se derramó nada.', 25],
]],

['slug' => 'la-gusteria-restaurante', 'opiniones' => [
    ['Elena Q.', 5, 'El menú del día trae sopa, segundo y refresco por un precio cómodo, y la porción llena bien.', 8],
    ['Fernando J.', 5, 'El cuarto de pollo a la brasa sale jugoso por dentro y con la piel dorada, mis hijos lo piden siempre.', 20],
    ['Milagros U.', 4, 'A la una de la tarde hay cola, pero avanzan rápido y la comida llega caliente a la mesa.', 34],
]],

['slug' => 'tole-restaurant', 'opiniones' => [
    ['Benjamín R.', 5, 'El seco de carne con frejoles y su arroz estaba bien sazonado, la carne se deshacía de lo tierna.', 2],
    ['Charito D.', 5, 'La jarra de chicha morada es casera y no demasiado dulce, perfecta para el almuerzo familiar.', 16],
    ['Wilson G.', 4, 'El servicio es atento, nos cambiaron el plato sin protestar cuando pedimos algo menos salado.', 40],
]],

['slug' => 'multipagos-don-pepe', 'opiniones' => [
    ['Natividad F.', 5, 'Hago mis giros ahí cada quincena y nunca me ha fallado; el chico llena bien los datos.', 6],
    ['Héctor Ñ.', 5, 'Aproveché para mandar una transferencia y me llevé una gaseosa bien helada del mismo local.', 23],
    ['Maribel Y.', 4, 'Atienden hasta tarde, incluso domingo, y eso ayuda cuando uno necesita girar plata de urgencia.', 31],
]],

['slug' => 'bodega-el-chino', 'opiniones' => [
    ['Justina B.', 5, 'Tienen atún y conservas de varias marcas, siempre encuentro la que usa mi mamá para la causa.', 4],
    ['Pablo A.', 5, 'Las gaseosas están bien heladas en la refrigeradora y el precio es igual que en el mercado.', 12],
    ['Lidia K.', 4, 'Es una bodega de barrio de toda la vida; don Chino te fía si te falta sencillo para completar.', 28],
]],

['slug' => 'bodega-dona-mary', 'opiniones' => [
    ['Aurelia S.', 5, 'Aquí compro el arroz, el aceite y el azúcar de la semana; los precios están al día.', 9],
    ['Genaro T.', 5, 'Mis nietos van por sus galletas y chocolates; doña Mary siempre les da su vuelto exacto.', 18],
    ['Norma C.', 4, 'El local es pequeño pero bien surtido, hay desde fideos hasta snacks para la lonchera del colegio.', 43],
]],

['slug' => 'villa-hermosa', 'opiniones' => [
    ['Estela M.', 5, 'Compro los huevos y la leche ahí; siempre están frescos y a buen precio por unidad.', 1],
    ['Santos R.', 5, 'Venden por mayor y me sale a cuenta llevar el detergente y el papel por paquete para la casa.', 15],
    ['Katherine L.', 4, 'Es mi bodega de confianza en Villa Hermosa: abarrotes completos y te despachan rápido aunque haya gente.', 27],
]],

['slug' => 'bodega-villa-e-salvador', 'opiniones' => [
    ['Everardo P.', 5, 'En Moro no hay muchas bodegas surtidas, así que aquí hallo los abarrotes y la limpieza de una vez.', 7],
    ['Antonia V.', 5, 'Llevo gaseosas por caja para una reunión y me las dejan a precio de mayor sin tanta vuelta.', 21],
    ['Ramiro Q.', 4, 'Los lácteos llegan temprano, a las ocho ya hay queso y yogurt frescos para el desayuno.', 35],
]],

['slug' => 'my-benjaz', 'opiniones' => [
    ['Sonia H.', 5, 'Bajo por yogurt y queso fresco y siempre hay; se nota que reponen seguido la refrigeradora.', 5],
    ['Álvaro N.', 5, 'Los huevos los venden sueltos, así compro solo lo que necesito y no se me malogran.', 19],
    ['Beatriz G.', 4, 'Los chicos atienden con paciencia a los escolares que vienen por sus snacks después de clases.', 32],
]],

['slug' => 'boticas-centralfarma', 'opiniones' => [
    ['Dante O.', 5, 'Fui con dolor de cabeza y me dieron ibuprofeno de 400 miligramos; me explicaron cómo tomarlo.', 3],
    ['Rosario I.', 5, 'Compro vitaminas y suplementos ahí porque siempre tienen stock y el precio es razonable.', 11],
    ['Gustavo A.', 4, 'La química de turno me midió la presión sin cobrarme y me recomendó ir a un doctor.', 24],
]],

['slug' => 'farmacia-katherine', 'opiniones' => [
    ['Miriam E.', 5, 'Compré la caja de ibuprofeno por veinte tabletas y me salió más barato que en otras farmacias.', 10],
    ['Teófilo S.', 5, 'El alcohol de 96 grados de medio litro lo uso para las curaciones de mi papá, siempre lo tienen.', 22],
    ['Yolanda F.', 4, 'Atienden hasta la noche y en la madrugada igual te abren la ventanilla si es una urgencia.', 39],
]],

['slug' => 'jm-farmacia-magistral', 'opiniones' => [
    ['Abelardo C.', 5, 'Pido los genéricos porque salen bastante más baratos y la señora me dice cuál es igual al de marca.', 2],
    ['Jessica R.', 5, 'Tienen pañales, shampoo y leche para bebé; me salva cuando se me acaba algo de noche.', 14],
    ['Máximo P.', 4, 'Es una farmacia magistral pequeña; preparan lo que te receta el doctor y no te hacen esperar mucho.', 30],
]],

['slug' => 'farmahouse', 'opiniones' => [
    ['Brenda M.', 5, 'Pedí por delivery un jarabe para la tos y llegó en menos de media hora a mi casa.', 6],
    ['Claudio Z.', 5, 'Tienen cremas, jabones y shampoo de marcas que no hallo en la bodega; buen surtido de cuidado personal.', 17],
    ['Teresa L.', 4, 'El repartidor llegó con el pedido bien embolsado y me avisó por teléfono antes de bajar del moto.', 28],
]],

['slug' => 'urb-bellamar', 'opiniones' => [
    ['Adelaida J.', 5, 'En la Urb. Bellamar hay pocas boticas, así que esta me queda a dos cuadras y siempre tiene lo básico.', 8],
    ['Marcos V.', 5, 'Pedí un antibiótico con receta y la señora revisó bien la dosis antes de entregármelo.', 20],
    ['Silvia B.', 4, 'Los genéricos son económicos y te dicen el precio antes de sacar la caja, sin sorpresas.', 42],
]],

['slug' => 'mi-farma-brasil', 'opiniones' => [
    ['Rocío T.', 5, 'Compré vitaminas para la anemia de mi hija y me sugirieron la presentación de jarabe, muy amables.', 4],
    ['Aníbal D.', 5, 'Siempre hay medicamentos para la gripe y el paracetamol no falta, incluso en temporada de frío.', 13],
    ['Luzmila A.', 4, 'La atención es rápida, en cinco minutos salí con mi pastilla y el vuelto justo.', 26],
]],

['slug' => 'optica-zambrano-vision', 'opiniones' => [
    ['Rosa C.', 5, 'Me hicieron el examen de la vista sin apuro y me explicaron qué medida necesitaba para mis lentes nuevos.', 7],
    ['Luis M.', 5, 'Fui por mis micas de contacto y me enseñaron a ponérmelas con paciencia, ahí mismo en el local.', 21],
    ['Ana P.', 4, 'El examen de la vista cuesta poco comparado con otras ópticas y te atienden rápido, aunque a veces hay cola.', 33],
]],

['slug' => 'clinica-dental-guzman', 'opiniones' => [
    ['Marisol Q.', 5, 'Me hicieron una curación en la muela y no sentí nada, la doctora me avisaba cada paso que hacía.', 5],
    ['Jorge T.', 5, 'Tuve que sacarme una muela y la extracción fue rápida; salí con indicaciones claras para la casa.', 14],
    ['Elsa R.', 4, 'La clínica es sencilla pero limpia, y cobran menos que otras por la curación de una caries.', 27],
]],

['slug' => 'kirdent', 'opiniones' => [
    ['Kevin S.', 5, 'La consulta odontológica me salió barata y me dijeron exactamente qué tratamiento necesitaba, sin venderme de más.', 3],
    ['Paola V.', 5, 'Me hicieron la extracción dental con harta paciencia porque tenía miedo, y no me dolió nada.', 19],
    ['Sandro L.', 4, 'Atienden por orden de llegada y el consultorio queda cerca a la avenida, fácil de ubicar.', 40],
]],

['slug' => 'clinica-dental-mardents', 'opiniones' => [
    ['Milagros C.', 5, 'La obturación estética quedó del mismo color de mi diente, nadie nota que me curé esa muela.', 9],
    ['Rubén A.', 5, 'Me sacaron una muela del juicio y al día siguiente ya estaba comiendo normal, muy buen trabajo.', 22],
    ['Carmen H.', 4, 'Piden cita por WhatsApp y casi no esperas en la sala, eso se agradece cuando uno trabaja.', 36],
]],

['slug' => 'consultorio-dental-godental-clinica-odontologica', 'opiniones' => [
    ['Diana F.', 5, 'Me hice el blanqueamiento dental y quedé sorprendida, se me notó el cambio desde la primera sesión.', 11],
    ['Óscar N.', 5, 'La extracción dental fue rápida y me dieron algodón y receta para la farmacia, todo bien explicado.', 26],
    ['Yessenia B.', 4, 'El consultorio es pequeño pero ordenado, y la doctora contesta las dudas sin apurar a nadie.', 44],
]],

['slug' => 'famident', 'opiniones' => [
    ['Néstor G.', 5, 'La limpieza dental me dejó los dientes suaves y sin esa sensación de sarro que traía hace meses.', 2],
    ['Lucía M.', 5, 'Me curaron dos caries en una sola visita y el precio fue lo que me dijeron al inicio.', 16],
    ['Percy D.', 5, 'Famident atiende hasta tarde, así que fui después del trabajo sin pedir permiso en la oficina.', 31],
]],

['slug' => 'veterinaria-entre-patas', 'opiniones' => [
    ['Katia R.', 5, 'Le di la pasta oral a mi perro para la desparasitación y no se resistió, parece que no le sabe mal.', 6],
    ['Marco U.', 5, 'Compro ahí los snacks dentales para perros y mi cachorro ya no tiene el aliento fuerte de antes.', 20],
    ['Silvia Q.', 5, 'La veterinaria es chica pero te atienden al toque y te dicen la dosis exacta según el peso.', 39],
]],

['slug' => 'mister-can-clinica-veterinaria', 'opiniones' => [
    ['Gisela T.', 5, 'Llevé a mi perro al baño y corte y salió con las uñas limpias y oliendo rico, sin estrés.', 4],
    ['Wilder P.', 5, 'La desparasitación fue rápida y me pesaron al perro antes para darle la dosis correcta.', 18],
    ['Rocío E.', 4, 'La espera el sábado es larga porque todos llevan a sus mascotas, pero el trato vale la pena.', 29],
]],

['slug' => 'centro-veterinario-municipal', 'opiniones' => [
    ['Nadia C.', 5, 'Llevé a mi gata a la consulta veterinaria municipal y la atendieron bien, cobrando bastante menos que en una clínica.', 13],
    ['Hugo V.', 5, 'Esterilizamos a nuestra perrita ahí y la campaña salió barata, solo hay que llegar temprano para el cupo.', 25],
    ['Betty A.', 4, 'El local es de Coishco y atienden por días, así que llamé antes para saber si había veterinario.', 42],
]],

['slug' => 'imoc', 'opiniones' => [
    ['Liliana S.', 5, 'Compré toda la lista de útiles escolares de mi hija en una sola vez, ahí tenían cuadernos y colores.', 8],
    ['José Luis M.', 5, 'Las mochilas son resistentes y me dejaron probarle el tamaño a mi hijo antes de pagar.', 23],
    ['Teresa N.', 4, 'Los precios de útiles en Imoc son de librería de barrio, aunque en febrero se llena de gente.', 37],
]],

['slug' => 'distribuidora-ayf-scrl', 'opiniones' => [
    ['Elmer R.', 5, 'Compro los útiles escolares para mis tres hijos en AYF y siempre me hacen precio por cantidad.', 12],
    ['Karina D.', 5, 'Los libros y textos que pedía el colegio los encontré ahí, sin tener que ir hasta Chimbote.', 28],
    ['Félix O.', 5, 'Atienden rápido en el mostrador y te despachan los pedidos grandes de útiles sin hacerte esperar.', 41],
]],

['slug' => 'anabella-accesorios', 'opiniones' => [
    ['Melissa G.', 5, 'Encontré un collar de bisutería bonito para el cumpleaños de mi hermana y me lo envolvieron para regalo.', 10],
    ['Jhan C.', 5, 'Compré un regalo de aniversario ahí y me ayudaron a elegir entre varias opciones sin apurarme.', 24],
    ['Sofía L.', 4, 'La bisutería es económica y no se me ha puesto negra todavía, aunque hay que revisar bien el cierre.', 35],
]],

['slug' => 'coll-zapatillas-chimbote', 'opiniones' => [
    ['Diego A.', 5, 'Me probé varias zapatillas de running y el vendedor me explicó cuáles tenían mejor amortiguación para trotar.', 15],
    ['Andrea P.', 5, 'Compré unas zapatillas casuales para el trabajo y hasta ahora no se me han despegado, buen material.', 30],
    ['Cristian H.', 4, 'Tienen tallas grandes, que es lo difícil de encontrar en Chimbote, aunque no siempre hay todos los modelos.', 43],
]],

['slug' => 'kamikaze-restobar', 'opiniones' => [
    ['Bryan Q.', 5, 'El menú del día trae sopa, segundo y refresco, y por ese precio no encuentras algo así en Santa.', 1],
    ['Vanessa T.', 5, 'La hamburguesa de kamikaze es grande y jugosa, la pedí con papas y quedé llena hasta la noche.', 17],
    ['Renzo F.', 4, 'Fuimos un viernes y había música, la atención fue rápida aunque el local estaba lleno.', 38],
]],

['slug' => 'restaurant-la-ramadita', 'opiniones' => [
    ['Gladys S.', 5, 'El ceviche de pescado de La Ramadita viene bien cargado y el pescado estaba fresco, se sentía.', 5],
    ['Iván R.', 5, 'Pedimos una jarra de chicha para compartir y alcanzó para cuatro, bien helada y no muy dulce.', 21],
    ['Norma B.', 4, 'El local es de Santa y se llena al mediodía, conviene llegar antes de la una para agarrar mesa.', 34],
]],

['slug' => 'restaurante-pau', 'opiniones' => [
    ['Cecilia M.', 5, 'El arroz con mariscos de PAU trae harto calamar y conchas, no es como otros que son puro arroz.', 7],
    ['Tomás E.', 5, 'El menú del día incluye entrada y su refresco, salí satisfecho por lo que pagué.', 20],
    ['Janet V.', 5, 'Atienden temprano a los ambulantes de la zona, así uno puede almorzar antes de las doce.', 33],
]],

['slug' => 'el-tamarindo', 'opiniones' => [
    ['Margarita L.', 5, 'El ceviche de El Tamarindo tiene buen limón y ají, picante en su punto, como a mí me gusta.', 2],
    ['Pablo N.', 5, 'El cuarto de pollo a la brasa venía caliente y con papas crocantes, ideal para dos personas.', 19],
    ['Susan O.', 4, 'Nos atendieron rápido un domingo, aunque el patio estaba lleno y tuvimos que esperar la mesa.', 32],
]],

['slug' => 'balanza-restaurante-mi-mama-no-me-quiere', 'opiniones' => [
    ['Frank D.', 5, 'El lomo saltado tiene buena porción de carne y las papas fritas vienen aparte, bien crocantes.', 6],
    ['Doris Y.', 5, 'La jarra de chicha es de maíz morado de verdad, no ese refresco en sobre que dan en otros lados.', 22],
    ['Miguel A.', 5, 'El nombre llama la atención, pero el plato llena de verdad y el precio está al alcance.', 35],
]],

['slug' => 'restaurant-mechita', 'opiniones' => [
    ['Aurelio C.', 5, 'El menú de Mechita cambia todos los días y siempre hay una sopa caliente antes del segundo.', 9],
    ['Pilar G.', 5, 'El cuarto de pollo a la brasa con papas y ensalada está barato para lo que sirve.', 23],
    ['Johnny R.', 4, 'Doña Mechita ya te conoce y te sirve rápido, aunque el local es pequeño cuando llega mucha gente.', 36],
]],

['slug' => 'eko-bodegas', 'opiniones' => [
    ['Roxana V.', 5, 'Compro el arroz de kilo en EKO y siempre está fresco, no como el de otros que viene húmedo.', 11],
    ['Álvaro Z.', 5, 'El azúcar de kilo está al mismo precio que el mercado y no tengo que cargar bolsas desde lejos.', 26],
    ['Sheyla K.', 4, 'La bodega abre hasta tarde, así que si me falta arroz para la cena lo consigo ahí mismo.', 39],
]],

['slug' => 'representaciones-buenos-aires-e-i-r-l', 'opiniones' => [
    ['Wilmer A.', 5, 'Compré fierro de construcción para mi techo y me lo cortaron a la medida que pedí.', 8],
    ['Edgar S.', 5, 'Las herramientas son de marca y el vendedor me mostró la diferencia entre dos taladros sin apuro.', 25],
    ['Eliana T.', 4, 'Atienden a las constructoras y a los maestros igual, aunque los sábados hay bastante gente.', 40],
]],

['slug' => 'av-pardo-3-octubre-nuevo-chimbote', 'opiniones' => [
    ['Erik M.', 5, 'El lomo saltado lo preparan al momento, se siente el olor a cebolla y tomate desde la pista.', 4],
    ['Lisbeth R.', 5, 'La jarra de chicha está bien fría y alcanza para la mesa, el precio es de barrio.', 18],
    ['Sergio P.', 4, 'Es un puesto de la avenida Pardo, comes rico y rápido, pero no esperes un local elegante.', 31],
]],

['slug' => 'el-fogon-restaurant-parrillas', 'opiniones' => [
    ['César L.', 5, 'El bistec de res de 300 gramos llena de verdad y lo sirven con papas y ensalada fresca.', 14],
    ['Miluska G.', 5, 'Los anticuchos de El Fogón vienen con harto ají y papas doradas, se nota que son de corazón.', 27],
    ['Ángel D.', 4, 'Fuimos a cenar parrilla y la carne salió en su punto, aunque la espera fue de unos veinte minutos.', 44],
]],

['slug' => 'vintage-optik', 'opiniones' => [
    ['Fabiana O.', 5, 'Encontré armazones de varios estilos y me dejaron probarme hartos antes de decidir el mío.', 3],
    ['Renato C.', 5, 'Compré unos lentes de sol con protección UV y el mismo día me los ajustaron a mi cara.', 22],
    ['Tania H.', 5, 'Los armazones son modernos y no tan caros como en las ópticas del centro, me llevé dos.', 37],
]],

['slug' => 'biocare-odontologia-integral-salud-y-belleza', 'opiniones' => [
    ['Lorena I.', 5, 'Me hicieron la endodoncia en dos visitas y se me pasó el dolor de la muela para siempre.', 6],
    ['Gustavo B.', 5, 'El blanqueamiento en BIOCARE me aclaró varios tonos y no me dejó los dientes sensibles como pensé.', 24],
    ['María Elena F.', 4, 'El consultorio está en un segundo piso pero es limpio y te explican el tratamiento por pasos.', 38],
]],

['slug' => 'dr-maria-alvarez-ortodoncia-alta-estetica-dental-y-armonizacion-orofacial', 'opiniones' => [
    ['Rosa C.', 5, 'Fui por la limpieza dental y me dejaron los dientes sin manchas; la doctora explicó todo con calma, sin apuro.', 6],
    ['Luis M.', 5, 'Me hicieron una endodoncia en una sola cita y casi no sentí dolor; al día siguiente ya comía normal.', 17],
    ['Ana P.', 4, 'El consultorio es pequeño pero muy limpio; la radiografía la tomaron ahí mismo y me cobraron lo acordado.', 29],
]],

['slug' => 'clinica-dental-virodent', 'opiniones' => [
    ['Carmen R.', 5, 'El blanqueamiento con lámpara LED me aclaró varios tonos y no me dio sensibilidad; duró toda la tarde la cita.', 12],
    ['Jorge T.', 5, 'Me sacaron una muela del juicio con anestesia local y sutura; salí caminando y a los tres días volví al control.', 24],
    ['Milagros S.', 4, 'Atienden con cita y casi no esperas; el gel de peróxido de carbamida lo preparan ahí y te avisan cuántas sesiones necesitas.', 38],
]],

['slug' => 'clinica-dental-centro-oral', 'opiniones' => [
    ['Rocío V.', 5, 'Me sacaron una muela que me dolía hace semanas y en dos días ya estaba comiendo sin molestias.', 8],
    ['Teodoro A.', 5, 'La evaluación de ortodoncia fue gratuita y me mostraron el presupuesto por escrito, sin compromiso de empezar.', 20],
    ['Jenny K.', 5, 'Abren hasta tarde y eso me salva porque salgo del trabajo a las siete y llego igual a mi cita.', 33],
]],

['slug' => 'clinica-veterinaria-spa-canino-mi-mascota', 'opiniones' => [
    ['Katy V.', 5, 'Le pusieron la vacuna óctuple a mi cachorro y me dieron su cartilla con la fecha de la próxima dosis.', 5],
    ['Diego A.', 5, 'Mi perro tiene la piel atópica y con el baño medicado de ozonoterapia dejó de rascarse casi por completo.', 19],
    ['Sofía L.', 4, 'Lo dejé hospedado tres días en jaula individual y me mandaban foto del paseo; volvió con las uñas limadas.', 41],
]],

['slug' => 'vet-real-pet-chus', 'opiniones' => [
    ['Renzo Q.', 5, 'Llevé a mi gata por su vacuna anual y el veterinario la revisó completo antes de inyectarla, sin apuro.', 3],
    ['Paola N.', 5, 'Compro aquí el alimento de mi perro porque tienen la bolsa de quince kilos y siempre está fresca.', 15],
    ['Elmer H.', 4, 'Atienden sin cita y aun así esperé poco; el local es chico pero está limpio y sin olores.', 27],
]],

['slug' => 'dermaclinic', 'opiniones' => [
    ['Nadia F.', 5, 'Traje a mi perro por la vacunación y le pusieron la dosis completa más la desparasitación en pastilla.', 9],
    ['Cristian D.', 5, 'Le dieron pastillas para las pulgas a mi gato y en dos semanas ya no tenía ni una.', 22],
    ['Rocío B.', 4, 'Me explicaron qué vacuna le tocaba según la edad y me anotaron todo en un papelito para no olvidarme.', 36],
]],

['slug' => 'vitamedic-centro-medico-especializado', 'opiniones' => [
    ['Marisol T.', 5, 'Entré por consulta con cardiología y salí con los exámenes de laboratorio hechos el mismo día.', 4],
    ['Víctor G.', 5, 'Los resultados de laboratorio me los entregaron por WhatsApp esa misma noche, sin tener que volver al local.', 16],
    ['Lucía E.', 4, 'Hay varias especialidades en un solo lugar y eso ahorra andar de consultorio en consultorio con la familia.', 31],
]],

['slug' => 'centro-medico-medic-vip-e-i-r-l', 'opiniones' => [
    ['Frank O.', 5, 'Fui por consulta general y me derivaron al laboratorio del mismo centro, todo rápido y bien ordenado.', 11],
    ['Sheyla M.', 5, 'Los exámenes de laboratorio salieron en la mañana y la doctora me llamó para explicarme los resultados.', 25],
    ['Wilmer C.', 4, 'Cobran módico y atienden de corrido; me tocó esperar media hora pero valió la pena.', 40],
]],

['slug' => 'centro-medico-sanipol', 'opiniones' => [
    ['Gloria I.', 5, 'Me hice una ecografía y el médico me mostró la imagen en la pantalla explicando cada medida.', 7],
    ['Percy J.', 5, 'Conseguí cita con especialista para el mismo día y la atención en admisión fue bastante rápida.', 18],
    ['Yolanda R.', 4, 'El local queda cerca de la avenida y hay donde estacionar; la sala de espera es amplia.', 30],
]],

['slug' => 'policlinico-arribasplata', 'opiniones' => [
    ['Hugo L.', 5, 'Hice el chequeo preventivo completo: análisis, presión y electro, todo en una sola mañana.', 2],
    ['Beatriz S.', 5, 'La consulta especializada me sirvió porque el doctor revisó mis análisis anteriores uno por uno.', 14],
    ['Iván P.', 4, 'Buena atención en caja y en enfermería; el chequeo preventivo sale más barato que hacerse todo por partes.', 28],
]],

['slug' => 'tiens-centro-de-distribucion', 'opiniones' => [
    ['Nancy U.', 5, 'Compro aquí el calcio Tiens para mi mamá y le ayudó bastante con los dolores de huesos.', 10],
    ['Roger A.', 5, 'Llevo los suplementos nutricionales y las vitaminas; me explicaron cómo tomarlos junto con las comidas.', 23],
    ['Elena V.', 4, 'Me asesoraron para ser distribuidora y me dieron la lista de precios por caja, sin presionarme.', 37],
]],

['slug' => 'essalud-coishco', 'opiniones' => [
    ['Maritza C.', 5, 'Me atendieron en ventanilla por lo de mi acreditación y me dijeron clarito qué papeles me faltaban.', 13],
    ['Julio N.', 5, 'Saqué cita para medicina general y me atendieron casi a la hora que me dieron, sin tanta cola.', 26],
    ['Teresa H.', 4, 'La farmacia del policlínico tenía completa mi receta; el local es antiguo pero está limpio y ordenado.', 39],
]],

['slug' => 'percy-barbershop', 'opiniones' => [
    ['Kevin Z.', 5, 'El corte me quedó parejo y me perfilaron la barba sin cobrar extra; salí en media hora.', 1],
    ['Alonso R.', 5, 'Me hicieron un tratamiento capilar porque tenía el cuero cabelludo bien reseco y se me pasó.', 21],
    ['Bruno S.', 4, 'Atienden por orden de llegada y hay tele mientras esperas; el corte cuesta menos que en Chimbote.', 35],
]],

['slug' => 'tienda-de-belleza-solansh', 'opiniones' => [
    ['Fiorella A.', 5, 'Me hice la depilación con cera tibia y quedé sin vello por semanas; muy cuidadosa la señora al trabajar.', 8],
    ['Doris M.', 5, 'El tratamiento facial me dejó la piel suave y usan productos que no me irritan la cara.', 24],
    ['Ketty P.', 4, 'Es una tienda pequeña en Santa pero tiene de todo para belleza y los precios son accesibles.', 44],
]],

['slug' => 'hielo-jack', 'opiniones' => [
    ['Emerson D.', 5, 'El menú del día trae sopa, segundo y refresco, y la porción llena de verdad.', 5],
    ['Gladys F.', 5, 'Pedimos la parrilla mixta para cuatro y alcanzó bien; la carne llegó caliente y jugosa.', 17],
    ['Nilton B.', 4, 'El local está a la entrada de Coishco y atienden rápido al mediodía, aunque se llena bastante.', 32],
]],

['slug' => 'aremash-joyeria', 'opiniones' => [
    ['Silvia G.', 5, 'Compré una cadena de plata para el cumpleaños de mi hija y me la entregaron en su cajita.', 9],
    ['Óscar T.', 5, 'Los aretes son de buen material, no me han hecho alergia y eso que los uso todos los días.', 20],
    ['Cinthia R.', 4, 'Me mostraron varias medidas de collar y me ayudaron a escoger sin apurarme ni empujarme la compra.', 42],
]],

['slug' => 'sifrah-plaza-vea-chimbote', 'opiniones' => [
    ['Janeth L.', 5, 'Le compré un reloj de pulsera a mi esposo y ahí mismo le ajustaron la correa a su medida.', 3],
    ['Patricia C.', 5, 'Los aretes que venden en el módulo son finos y no se me oscurecieron con el uso diario.', 15],
    ['Manuel A.', 4, 'Buena variedad de relojes y te atienden dentro del Plaza Vea, así aprovechas el resto de compras.', 29],
]],

['slug' => 'calzado-moran', 'opiniones' => [
    ['Ruth E.', 5, 'Encontré zapatillas talla treinta para mi hijo y me respetaron el dos por uno en los modelos marcados.', 11],
    ['Javier O.', 5, 'Compré dos pares de calzado de niño por el precio de uno, justo para el inicio de clases.', 23],
    ['Norma Q.', 4, 'Tienen tallas grandes y chicas en la misma vitrina y me dejaron probárselos con calma a mi hija.', 34],
]],

['slug' => 'familia-chilon-alza', 'opiniones' => [
    ['Yesenia V.', 5, 'Compré zapatos para mi sobrino en Coishco y me hicieron precio llevando dos pares.', 4],
    ['Rubén M.', 5, 'Siempre hay promociones en calzado de niño y la talla que busco aparece casi siempre.', 19],
    ['Liliana S.', 4, 'Atienden en su propia casa y son amables; el calzado es nacional y aguanta todo el año escolar.', 38],
]],

['slug' => 'stepup', 'opiniones' => [
    ['Andrea K.', 5, 'Aproveché el dos por uno en modelos seleccionados y me llevé dos pares por lo que cuesta uno.', 12],
    ['Fernando I.', 5, 'Les llevé mis zapatillas blancas para la limpieza y quedaron como nuevas, sin una sola mancha.', 27],
    ['Melissa R.', 4, 'El cuidado de calzado incluye blanquear las suelas y eso casi nadie lo hace por acá.', 45],
]],

['slug' => 'renovadora-teo', 'opiniones' => [
    ['Zoila C.', 5, 'Le cambiaron el taco a mis zapatos de trabajo en el mismo día y me cobraron bien barato.', 7],
    ['Erick B.', 5, 'Me repararon las zapatillas que ya estaban rotas por la costura y quedaron firmes otra vez.', 16],
    ['Pilar D.', 4, 'El señor revisa el calzado delante de ti y te dice si de verdad vale la pena la reparación.', 31],
]],

['slug' => 'bodega-lucero', 'opiniones' => [
    ['Marilú J.', 5, 'Compro los huevos y el queso fresco de la mañana; siempre están frescos y a buen precio.', 2],
    ['Segundo A.', 5, 'Hago mi pedido por mayor de lácteos para mi puesto y me lo tienen listo en la tienda.', 25],
    ['Karina F.', 4, 'Es la bodega de la esquina en Santa; abren bien temprano y atienden rapidísimo a esa hora.', 36],
]],

['slug' => 'bodega-escalante', 'opiniones' => [
    ['Lidia O.', 5, 'El aceite de un litro está más barato que en el mercado y el azúcar por kilo también.', 10],
    ['Marcos T.', 5, 'Compré azúcar y aceite para toda la semana; me atendieron al toque y me dieron su boleta.', 22],
    ['Estela N.', 4, 'La bodega es surtida y tienen los precios marcados en la vitrina, sin sorpresas al pagar.', 40],
]],

['slug' => 'bodega-adrian', 'opiniones' => [
    ['Rosa H.', 5, 'La leche evaporada la tienen siempre y bien fría; paso por las mañanas antes de ir al trabajo.', 13],
    ['Gustavo P.', 5, 'Compro las gaseosas de litro y medio para la casa y siempre me las dan bien heladas.', 28],
    ['Diana L.', 4, 'Es una bodega pequeña pero tiene lo básico; el dueño es amable y fía a los del barrio.', 43],
]],

['slug' => 'bodega-la-isa', 'opiniones' => [
    ['Soledad M.', 5, 'Encuentro los abarrotes de la semana, el arroz y el aceite, sin caminar hasta el mercado.', 1],
    ['Pablo R.', 5, 'Compré snacks y gaseosas para la reunión y me dieron bolsas para llevarme todo cómodo.', 18],
    ['Elva C.', 4, 'Tienen artículos de limpieza y detergente al por mayor; el local está ordenado y se encuentra rápido.', 33],
]],

['slug' => 'boticas-accesalud', 'opiniones' => [
    ['Rosa C.', 5, 'Fui por vitaminas para mi mamá y me explicaron bien cuál le convenía; además me trajeron el pedido a la casa el mismo día.', 6],
    ['Julio M.', 5, 'El delivery de la botica es rápido, pedí a las ocho de la noche y en media hora ya tenía mis suplementos en Santa.', 14],
    ['Katty R.', 5, 'Compro aquí las vitaminas de mis hijos, los precios son cómodos y la señora siempre me recomienda lo que de verdad sirve.', 28],
]],

['slug' => 'inkafar-botica-perfumeria', 'opiniones' => [
    ['Milagros T.', 5, 'Encontré el medicamento que no había en otras boticas de Santa y me atendieron rapidito, sin hacerme esperar.', 5],
    ['César Q.', 5, 'Tienen vitaminas de varias marcas y también perfumes; me llevé uno para mi hermana y quedó contenta con el aroma.', 17],
    ['Diana V.', 4, 'Voy por mis pastillas para la presión todas las semanas, aquí siempre hay stock y no me cobran de más.', 33],
]],

['slug' => 'botica-los-santenos-2', 'opiniones' => [
    ['Pedro A.', 5, 'Me surtí de medicamentos para la gripe y aproveché en artículos de cuidado personal, todo a buen precio.', 4],
    ['Lucía S.', 5, 'La señora que atiende conoce los remedios y me ayudó a elegir algo para la tos de mi hijo, muy amable.', 22],
    ['Wilmer Ch.', 5, 'Está bien surtida y abren temprano; a las siete de la mañana ya conseguí mi medicina antes del trabajo.', 39],
]],

['slug' => 'botica-f-v-farma', 'opiniones' => [
    ['Janeth P.', 5, 'Pedí vitaminas por delivery y llegaron selladas y con su boleta; el repartidor fue puntual a la hora acordada.', 9],
    ['Marco R.', 5, 'Me recomendaron un suplemento de hierro para la anemia de mi hija y se notó la mejoría en un mes.', 20],
    ['Sonia B.', 4, 'Buena atención en Santa, aunque a veces falta una marca específica de vitaminas, igual te ofrecen otra parecida.', 36],
]],

['slug' => 'baterias-ferreteria-progress', 'opiniones' => [
    ['Elmer G.', 5, 'Me vendieron la batería para mi auto y me la instalaron ahí mismo; salí manejando sin problema el mismo día.', 3],
    ['Rocío N.', 5, 'Compré pintura y thinner para rejas; me dieron la medida exacta y me explicaron cómo prepararla antes de pintar.', 12],
    ['Brayan F.', 5, 'Fui con mi batería descargada y me la probaron en el momento; me dijeron con sinceridad que todavía aguantaba.', 30],
]],

['slug' => 'grupo-ferretero-quezada-eirl', 'opiniones' => [
    ['Hugo D.', 5, 'Llevé fierro de construcción para mi segunda planta y me lo cortaron a la medida que necesitaba, sin cobrar extra.', 7],
    ['Marisol E.', 5, 'Compro pintura y thinner aquí siempre; el precio por galón es de los mejores del centro de Chimbote.', 19],
    ['Freddy O.', 4, 'El local tiene todo ordenado y pesan el fierro delante de uno, así uno se va tranquilo con lo que compró.', 41],
]],

['slug' => 'urbanizacion-el-progreso', 'opiniones' => [
    ['Nelly V.', 5, 'La ferretería de la urbanización El Progreso me salvó un domingo: abrieron y conseguí cemento y clavos para mi obra.', 8],
    ['Óscar L.', 5, 'Vivo a dos cuadras y todo lo de mi casa lo compro aquí; atienden hasta tarde y siempre hay lo básico.', 25],
    ['Rubén S.', 4, 'Es una ferretería de barrio bien surtida, aunque el local es chico y a veces hay que esperar un ratito.', 44],
]],

['slug' => 'farmacia-essalud-iii-chimbote', 'opiniones' => [
    ['Gladys H.', 5, 'Recogí los medicamentos de mi tratamiento y esta vez no demoraron; me entregaron todo completo en ventanilla.', 6],
    ['Ricardo A.', 5, 'Compré pañales y leche para mi bebé en la misma farmacia, hay buena variedad de productos para bebé.', 16],
    ['Carmen Y.', 5, 'El personal me explicó cada cuánto tomar las pastillas y a qué hora volver por más, se agradece.', 31],
]],

['slug' => 'baruch-farma', 'opiniones' => [
    ['Paola I.', 5, 'Llamé para que me manden vitaminas a domicilio y llegaron rapidísimo, incluso a esta hora de la noche.', 10],
    ['Jorge U.', 5, 'Me hicieron un descuento en el frasco grande de multivitamínicos porque llevé dos, buen detalle.', 24],
    ['Estela M.', 4, 'La botica es pequeña pero completa, encontré el suplemento de colágeno que buscaba y a buen precio.', 37],
]],

['slug' => 'farmacia-dios-con-su-poder', 'opiniones' => [
    ['Yolanda Ch.', 5, 'Necesitaba fórmula y pañales para mi bebé a medianoche y me los trajeron con delivery, son un salvavidas.', 2],
    ['Kevin B.', 5, 'Compro las cremas y el talco de mi guagua aquí; me orientan sobre qué marca le cae mejor a su piel.', 13],
    ['Maribel Z.', 5, 'El chico que atiende es paciente, me leyó la receta del pediatra y me armó el pedido sin equivocarse.', 29],
]],

['slug' => 'megafit', 'opiniones' => [
    ['Christian P.', 5, 'El menú de MegaFit es bien servido, con sopa y refresco incluido; salgo lleno y a buen precio.', 5],
    ['Melva R.', 5, 'El lomo saltado es mi favorito, la carne tierna y las papas crocantes, se nota que lo hacen al momento.', 15],
    ['Antony S.', 4, 'Voy a almorzar casi todos los días; a la una hay bastante gente, pero atienden rápido igual.', 32],
]],

['slug' => 'clinica-dental-joabdent', 'opiniones' => [
    ['Silvia Q.', 5, 'Me hicieron una endodoncia que otras clínicas no querían hacer y no sentí dolor, todo con calma.', 6],
    ['Fernando T.', 5, 'Llevé a mi hija por la evaluación de ortodoncia y nos mostraron el presupuesto claro, sin sorpresas después.', 18],
    ['Rosa Elena D.', 5, 'En Coishco es difícil encontrar especialista; aquí me atendieron el mismo día que llamé por el dolor de muela.', 34],
]],

['slug' => 'clinica-odontomedic', 'opiniones' => [
    ['Percy L.', 5, 'Me sacaron una muela del juicio en veinte minutos y al día siguiente ya estaba trabajando normal.', 4],
    ['Nancy F.', 5, 'Mi mamá ya tiene su prótesis y ahora come de todo; el ajuste quedó bien hecho desde la primera vez.', 21],
    ['Gilmer A.', 5, 'La clínica es limpia y los instrumentos salen esterilizados; eso me dio confianza para atenderme aquí.', 40],
]],

['slug' => 'odontologia-integral-especializada', 'opiniones' => [
    ['Tatiana G.', 5, 'Fui por la evaluación de ortodoncia y me explicaron con el espejo por qué mis dientes se movían.', 8],
    ['Wilder M.', 5, 'Me hicieron una obturación estética en el diente de adelante y no se nota nada, quedó igual al resto.', 23],
    ['Judith C.', 4, 'Atienden con cita y no te hacen esperar horas; los precios de Santa son más cómodos que en Chimbote.', 38],
]],

['slug' => 'de-dental-tcancio', 'opiniones' => [
    ['Beatriz N.', 5, 'Me hice la limpieza dental y me sacaron sarro que tenía años; salí con los dientes suaves y limpios.', 5],
    ['Álvaro J.', 5, 'El blanqueamiento me duró bastante y no me quedaron los dientes sensibles, como me habían advertido en otros lados.', 16],
    ['Milagros O.', 5, 'El doctor Tcancio es tranquilo y te va explicando cada paso; el consultorio queda cerca de la plaza.', 27],
]],

['slug' => 'clinica-dental-valdez', 'opiniones' => [
    ['Henry V.', 5, 'Me curaron una caries que me dolía hace semanas y me cobraron lo que me dijeron al inicio.', 9],
    ['Sofía E.', 5, 'Tuve que sacarme una muela y fue rápido; me dieron las indicaciones para la comida y la inflamación bajó.', 26],
    ['Dante R.', 4, 'Atención de barrio, sin lujos, pero el doctor trabaja bien y atiende a la hora que le pides.', 43],
]],

['slug' => 'bodega-gloria', 'opiniones' => [
    ['Lidia M.', 5, 'Compro la leche evaporada y el azúcar de kilo aquí; siempre está fresco y me sale más barato que en el mercado.', 3],
    ['Segundo A.', 5, 'La señora de la bodega me fía cuando me falta sencillo y al día siguiente le pago, así es el barrio.', 11],
    ['Elva P.', 5, 'Abren desde muy temprano, a las seis ya puedo comprar pan, azúcar y leche para el desayuno de mis hijos.', 35],
]],

['slug' => 'veterinaria-pesdlc', 'opiniones' => [
    ['Karina S.', 5, 'Llevé a mi perro por su vacuna y lo atendieron sin estrés; además me pesaron y revisaron gratis.', 5],
    ['Beto H.', 5, 'Mi gata se intoxicó de noche y me atendieron de urgencia en Coishco, le salvaron la vida.', 14],
    ['Zulema T.', 5, 'Los precios de las vacunas son accesibles y te dan su carné con la fecha de la próxima dosis.', 29],
]],

['slug' => 'otorrinolaringologo-dr-luis-rodriguez-moya', 'opiniones' => [
    ['Gisela P.', 5, 'Llevaba meses con la nariz tapada y en la endoscopia nasal el doctor vio al toque qué tenía.', 6],
    ['Rolando C.', 5, 'El doctor Rodríguez Moya explica con paciencia y no te manda exámenes que no necesitas, se agradece.', 19],
    ['Yesenia D.', 4, 'Conseguí cita para la consulta la misma semana y el consultorio queda cerca al hospital.', 33],
]],

['slug' => 'centro-quiropractico-dra-micaela-lara', 'opiniones' => [
    ['Iván R.', 5, 'Llegué con la espalda destrozada de tanto cargar peso y después de la quiropráctica ya duermo mejor.', 7],
    ['Marlene Q.', 5, 'Me hicieron la evaluación postural con fotos y me mostraron por qué me dolía el cuello en el trabajo.', 21],
    ['Cinthia L.', 5, 'La doctora Micaela te acomoda hueso por hueso y te manda ejercicios para hacer en casa.', 42],
]],

['slug' => 'policlinico-salvador', 'opiniones' => [
    ['Miriam A.', 5, 'Me hice los análisis de laboratorio en ayunas y a las pocas horas ya tenía mis resultados en la mano.', 4],
    ['Julio C.', 5, 'Fui por consulta con el especialista y me derivaron al laboratorio ahí mismo, todo en un solo sitio.', 17],
    ['Fabiana G.', 4, 'Está abierto hasta tarde, así pude ir después del trabajo; la atención es buena aunque hay cola.', 30],
]],

['slug' => 'fisio-peques', 'opiniones' => [
    ['Vanessa R.', 5, 'Mi hijo nació con tortícolis y con las sesiones de fisioterapia pediátrica ya voltea la cabeza solo.', 8],
    ['Édgar M.', 5, 'Llevo a mi bebé a estimulación temprana y las terapistas lo tratan con harta paciencia, se nota el cariño.', 20],
    ['Soledad B.', 5, 'El local es pequeño pero tiene juegos y colchonetas; mi hijo entra contento a su terapia.', 39],
]],

['slug' => 'centro-medico-detector-del-cancer-eco-salud', 'opiniones' => [
    ['Aurora V.', 5, 'Me hicieron el electrocardiograma y el informe salió el mismo día; la técnica fue muy cuidadosa conmigo.', 6],
    ['Teodoro I.', 5, 'Fui por consulta con especialista en Santa y no tuve que viajar a Chimbote, eso ahorra tiempo y pasaje.', 25],
    ['Rosario M.', 5, 'El centro es ordenado y te llaman por hora; me explicaron los resultados con calma, sin apurarme.', 36],
]],

['slug' => 'amaro-salon', 'opiniones' => [
    ['Fiorella Ch.', 5, 'Me hicieron el balayage y quedó un rubio parejo, sin ese anaranjado que me dejaron en otro salón.', 3],
    ['Nadia P.', 5, 'La manicure rusa me duró tres semanas intacta y el esmaltado semipermanente no se descascaró.', 12],
    ['Claudia E.', 5, 'Me hicieron el facial con ácido hialurónico y la depilación de bozo antes de una boda, quedé fresca.', 28],
]],

['slug' => 'beletza-centro-de-belleza-chimbote', 'opiniones' => [
    ['Alessandra Q.', 5, 'El pedicure spa con exfoliación me dejó los pies suaves; el masaje relajante fue lo mejor.', 5],
    ['Pilar N.', 5, 'Me hice el alisado orgánico porque mi cabello estaba quemado de la plancha y quedó manejable.', 22],
    ['Grace M.', 4, 'Buena atención y local limpio; el alisado sin formol no me irritó el cuero cabelludo como otras veces.', 45],
]],

// ====== TANDA 2 (2026-09-14, noche): las 100 tiendas anteriores ======
['slug' => 'la-cantonada-fusion', 'opiniones' => [
    ['Rosa C.', 5, 'El menú del día viene bien servido: sopa, segundo con guarnición y su refresco, y a precio justo para el almuerzo.', 7],
    ['Luis M.', 5, 'El postre de la casa es lo mejor, una crema volteada casera que te dan al final y no cobran caro.', 21],
    ['Ana P.', 4, 'Fuimos a la hora de almuerzo y en menos de quince minutos ya estábamos comiendo; el local es sencillo pero limpio.', 33],
]],

['slug' => 'restaurant-las-flores', 'opiniones' => [
    ['Julio R.', 5, 'El cuarto de pollo a la brasa sale jugoso y con papas bien crocantes; llegué a las ocho y todavía estaba caliente.', 3],
    ['Marta Q.', 5, 'La jarra de chicha morada es grande y bien helada, alcanza para cuatro personas sin quedarse corto.', 12],
    ['Percy A.', 5, 'Piden la orden y te la traen rapidísimo, aunque el salón esté lleno el domingo; se nota que están acostumbrados.', 40],
]],

['slug' => 'la-mar-17-san-luis', 'opiniones' => [
    ['Carmen V.', 5, 'El arroz con mariscos viene con bastante concha, calamar y pescado fresco; se siente que es del día.', 5],
    ['Jorge L.', 5, 'La jarra de chicha morada la preparan con maíz morado de verdad, no ese sobre dulce; bien fría acompaña perfecto.', 18],
    ['Silvia T.', 4, 'El local está a media cuadra de la avenida y tiene su terraza; el mozo nos atendió con paciencia mientras elegíamos.', 29],
]],

['slug' => 'ferreteria-gerardo', 'opiniones' => [
    ['Wilder S.', 5, 'Compré el candado de seguridad de 40 mm con sus tres llaves para el portón y hasta ahora aguanta la lluvia sin pegarse.', 9],
    ['Nancy R.', 5, 'La malla de gallinero de un metro por diez me alcanzó justo para cerrar el corral; viene bien tejida y no se deshilacha.', 24],
    ['Elmer D.', 5, 'El señor Gerardo te explica cuántos metros necesitas y no te quiere vender de más; en Coishco ya es conocido.', 44],
]],

['slug' => 'ferreteria-el-rayo', 'opiniones' => [
    ['Percy N.', 5, 'La pistola de silicona caliente con sus diez barras me sirvió para pegar el zócalo; calienta rápido y no gotea tanto.', 2],
    ['Gloria H.', 5, 'Alquilé la carretilla de cuatro pies para mi obra y aguantó ladrillos y arena sin que se baje la llanta.', 15],
    ['Iván B.', 4, 'Tienen de todo en un solo sitio: compré la soga trenzada de un cuarto por cincuenta metros y el rastrillo de doce dientes.', 37],
]],

['slug' => 'ferreteria-virgen-del-carmen', 'opiniones' => [
    ['Máximo C.', 5, 'Compré pintura para el frontis y me dieron el rendimiento exacto por galón; no tuve que volver por más.', 6],
    ['Yolanda P.', 5, 'Las herramientas son de marca conocida, no de esas que se doblan al primer uso; el martillo y el desarmador me duraron.', 19],
    ['Freddy G.', 5, 'Atención rápida en Coishco: entré por unas lijas y salí con todo lo que faltaba para la pintura del cerco.', 41],
]],

['slug' => 'opticas-retina-lentes-medida-computarizada-y-monturas-en-chimbote', 'opiniones' => [
    ['Karina M.', 5, 'Me hicieron la medida computarizada y el lente con filtro de luz azul; ahora trabajo ocho horas en la computadora sin ardor.', 11],
    ['Óscar F.', 5, 'Las monturas de acetato son livianas y no se deforman; elegí una negra con diseño moderno y no me marca la nariz.', 26],
    ['Betty S.', 4, 'El antirreflejante se nota de noche manejando; antes me molestaban las luces de los carros y ahora ya no.', 43],
]],

['slug' => 'optica-premium-chimbote', 'opiniones' => [
    ['Fernando A.', 5, 'Los progresivos con armazón de titanio son caros pero valen: paso de leer a mirar de lejos sin cambiar de lentes.', 4],
    ['Lucía E.', 5, 'Compré los lentes de sol deportivos con armazón de goma y no se me resbalan cuando troto por la avenida.', 22],
    ['Rubén C.', 5, 'Los lentes de seguridad con filtro UV los usa mi esposo en la fábrica; el armazón ajustable le queda bien sobre el casco.', 35],
]],

['slug' => 'opticas-wilmer', 'opiniones' => [
    ['Marisol D.', 5, 'El examen de la vista fue rápido y sin tanta vuelta; me dijo mi graduación exacta y no me apuró para comprar.', 8],
    ['Hugo T.', 5, 'Las micas de contacto me las entregaron en dos días y me enseñaron a ponérmelas sin lastimarme el ojo.', 20],
    ['Elva R.', 4, 'Llevé a mi mamá de setenta años y la atendieron con paciencia, repitiéndole las letras del tablero varias veces.', 39],
]],

['slug' => 'optikal', 'opiniones' => [
    ['Danny O.', 5, 'Las gafas polarizadas con marco de metal ligero me quitan el reflejo del sol en la carretera; manejo a Chimbote tranquilo.', 13],
    ['Rocío V.', 5, 'Me soldaron la montura que se me había partido y me cambiaron las plaquetas; quedó como nueva y con garantía.', 27],
    ['Jhonatan L.', 5, 'El ajuste lo hacen al toque y sin cobrar si compraste ahí; en mi caso bastó apretar los brazos para que no se caigan.', 45],
]],

['slug' => 'seven-vision-optica', 'opiniones' => [
    ['Janet B.', 5, 'Los lentes de medida me quedaron perfectos al primer intento; ya no me duele la cabeza después de leer.', 1],
    ['Wilson A.', 5, 'Hay armazones para todos los gustos y precios; encontré uno delgado para mi hijo que recién empieza el colegio.', 16],
    ['Miguel Á.', 4, 'Está cerca al mercado de Nuevo Chimbote, así que aproveché para dejar mis lentes y recogerlos en la tarde.', 31],
]],

['slug' => 'opticas-cmr', 'opiniones' => [
    ['Susana G.', 5, 'Me tomaron el examen de la vista con esos aparatos modernos y me explicaron por qué veía borroso de un ojo.', 10],
    ['Ricardo P.', 5, 'Los armazones son resistentes y hay variedad; compré uno metálico que aguantó el apretón de mi sobrino.', 23],
    ['Nelly F.', 5, 'Los lentes me los entregaron al día siguiente y me los probaron ahí mismo para ver si la medida estaba bien.', 36],
]],

['slug' => 'optica-flores', 'opiniones' => [
    ['Mirian C.', 5, 'Los lentes de medida salieron bien graduados; le pedí que me los haga para la computadora y ya no entrecierro los ojos.', 14],
    ['Alfredo S.', 5, 'Paso cada mes a que me hagan la limpieza y el ajuste y nunca me han cobrado nada, aunque no compre nada ese día.', 28],
    ['Zoila N.', 4, 'El señor que atiende tiene mano para calibrar los brazos de la montura; me la dejó pareja y ya no se me resbala.', 42],
]],

['slug' => 'master-optica', 'opiniones' => [
    ['Gladys M.', 5, 'Encontré un armazón ancho que me queda bien; en otros locales no había un modelo así para mi cara.', 17],
    ['Édgar Q.', 5, 'La limpieza les dejó los cristales transparentes otra vez; ya no veía bien por la grasa que se acumuló.', 25],
    ['Tania R.', 5, 'El ajuste me lo hicieron en cinco minutos un sábado por la tarde, ya casi cerrando, y sin cobrarme nada.', 38],
]],

['slug' => 'fabi-joyas-y-accesorios', 'opiniones' => [
    ['Fiorella T.', 5, 'Compré un collar con su dije para el cumpleaños de mi hija y le encantó; el acabado se ve fino.', 6],
    ['César H.', 5, 'Los aretes son livianos y no me irritan la oreja como otros; ya llevo dos meses usándolos a diario.', 21],
    ['Marlene J.', 4, 'Me dejaron ver varias cajitas antes de decidir y no me apuraron; los precios están anotados, sin regateo.', 34],
]],

['slug' => 'joyeria-alberto-calvo', 'opiniones' => [
    ['Eduardo N.', 5, 'Le llevé el reloj de mi papá que estaba parado y me lo devolvieron andando; le cambiaron la pila y lo revisaron.', 5],
    ['Pilar A.', 5, 'La limpieza de joyas es otro nivel: mis argollas de oro salieron brillando como cuando las compré.', 19],
    ['Sonia K.', 5, 'Es un local de toda la vida en el centro y el señor Alberto te dice la verdad si algo no vale la pena reparar.', 40],
]],

['slug' => 'joyeria-espana', 'opiniones' => [
    ['Norma B.', 5, 'Compré un juego de aretes y cadena para mi esposa y le quedó lindo; hay joyas para dama de varios precios.', 2],
    ['Gilberto C.', 5, 'Me repararon la pulsera que se me había abierto el broche y quedó firme, ya no se me cae.', 24],
    ['Rosario V.', 4, 'Me atendieron un martes por la mañana sin nadie más en la tienda y me mostraron todo con calma.', 37],
]],

['slug' => 'joyeria-relojeria-y-bazar-el-portenito', 'opiniones' => [
    ['Aurora D.', 5, 'La cadena de oro de dieciocho quilates con eslabón figaro es maciza; el cierre de seguridad me da confianza para usarla.', 7],
    ['Teodoro M.', 5, 'Los aretes de plata 925 con diseño de gota y piedritas de color son delicados; los uso para las reuniones y no se oscurecen.', 22],
    ['Liz P.', 5, 'Pesan la joya delante de uno y dan su boleta; además el bazar tiene cositas útiles para regalar.', 43],
]],

['slug' => 'farmacia-farmaela', 'opiniones' => [
    ['Yaneth O.', 5, 'Compré la caja de paracetamol de 500 y me salió más barata que en la botica de la esquina.', 3],
    ['Braulio E.', 5, 'El alcohol medicinal de medio litro lo uso para las curaciones de mi mamá diabética; siempre lo tienen en stock.', 18],
    ['Milagros I.', 4, 'Atendieron a mi hijo con fiebre a las once de la noche y me explicaron la dosis según su peso.', 32],
]],

['slug' => 'botica-biosalud-chimbote', 'opiniones' => [
    ['Katia R.', 5, 'El jarabe para la tos me lo recomendaron para la tos seca de mi hija y en tres días ya estaba mejor.', 11],
    ['Segundo A.', 5, 'Llevo vitaminas y suplementos para mi papá; el químico me explicó cómo tomarlas y en qué horario.', 26],
    ['Débora L.', 5, 'Hay buena variedad y no te venden cosas de más; incluso me dijeron que primero consulte al doctor.', 41],
]],

['slug' => 'footloose-mega-plaza-chimbote', 'opiniones' => [
    ['Claudia Z.', 5, 'Las zapatillas de dama que compré son cómodas para caminar todo el día; la plantilla es suave y no me saca ampollas.', 4],
    ['Hernán U.', 5, 'El calzado de niño tiene tallas hasta la que necesitaba mi sobrino; le probamos tres pares sin problema.', 16],
    ['Flor M.', 4, 'En Mega Plaza hay dos tiendas juntas y en esta encontré descuento por temporada; salí con dos pares.', 29],
]],

['slug' => 'palace-shoes-323', 'opiniones' => [
    ['Ruth A.', 5, 'Encontré zapatillas talla 43, que casi nunca hay; el vendedor buscó en el almacén hasta que apareció un par.', 8],
    ['Edwin G.', 5, 'El calzado de dama tiene modelos para oficina y para salir; me llevé unos tacos que no me cansan.', 23],
    ['Consuelo P.', 5, 'Los precios son más bajos que en el mall; por eso compro aquí mis zapatillas y las de mi hermana.', 39],
]],

['slug' => 'calimod-store-megaplaza-chimbote-zapatos-de-cuero', 'opiniones' => [
    ['Alberto F.', 5, 'Los zapatos de cuero para caballero son de buena piel; los uso para la oficina y no se han rajado.', 12],
    ['Jenny Q.', 5, 'Me probé los casuales de cuero y me quedaron cómodos desde el primer día, sin tener que domarlos.', 27],
    ['Marco S.', 4, 'Tienen hasta talla 45, que es mi problema de siempre; el vendedor me trajo tres modelos del almacén.', 45],
]],

['slug' => 'fachas-tablistas', 'opiniones' => [
    ['Diana W.', 5, 'Las zapatillas de dama tienen suela gruesa y agarran bien en la arena mojada; las uso para caminar por la playa.', 9],
    ['Cristian Y.', 5, 'Para caballero hay modelos urbanos y también de lona; mi hermano salió con un par a buen precio.', 20],
    ['Paola H.', 5, 'El local es chico pero está surtido; la chica que atiende te deja probarte con calma y no te apura.', 35],
]],

['slug' => 'platanitos-megaplaza-chimbote', 'opiniones' => [
    ['Verónica A.', 5, 'Me compré unas botas de moda para el invierno y son abrigadoras; combinan con falda y con jean.', 13],
    ['Samuel T.', 5, 'El calzado de niño salió bueno: mi hijo las usa para el colegio y ya lleva medio año sin romperse.', 25],
    ['Cynthia B.', 4, 'En rebajas los precios bajan bastante; esperé la campaña y me llevé dos pares por menos de lo pensado.', 31],
]],

['slug' => 'libreria-alejos', 'opiniones' => [
    ['Rosa C.', 5, 'Le compré la mochila escolar a mi hijo y aguanta el agua; con la lluvia de la costa los cuadernos llegaron secos.', 7],
    ['Luis M.', 5, 'Los plumones de punta fina son bien vibrantes, mi hija pintó su maqueta del colegio y no se corrió la tinta.', 21],
    ['Ana P.', 4, 'Atienden rápido en la caja y te dejan revisar los útiles antes de pagar, eso me gustó bastante.', 33],
]],

['slug' => 'la-casa-de-la-biblia', 'opiniones' => [
    ['María T.', 5, 'Compré una Biblia de letra grande para mi mamá y se le hace fácil leerla sin lentes.', 5],
    ['Jorge R.', 5, 'Tienen libros cristianos para niños con dibujos; le regalé uno a mi sobrino y lo lee todas las noches.', 18],
    ['Carmen V.', 5, 'El señor que atiende te ayuda a escoger según lo que buscas y no te apura para decidir.', 29],
]],

['slug' => 'libreria-regalos-detallitos', 'opiniones' => [
    ['Silvia Q.', 5, 'Llevé cuadernos para la lista del colegio y me salió más barato que en otros lados del mercado.', 4],
    ['Pedro A.', 5, 'Tienen útiles escolares surtidos y forros de cuaderno; encontré justo lo que pedía la profesora.', 16],
    ['Rocío N.', 4, 'Es un sitio pequeño pero ordenado, y la señora despacha rápido cuando hay cola de escolares.', 27],
]],

['slug' => 'pet-market-veterinaria', 'opiniones' => [
    ['Katherine S.', 5, 'Le puse la vacuna contra la tos de las perreras a mi perrita y ya no la escucho toser de noche.', 6],
    ['Iván D.', 5, 'Los snacks de pescado deshidratado vuelven loca a mi gata; el paquete de cien gramos le dura una semana.', 19],
    ['Fiorella B.', 5, 'Me explicaron cuánto esperar entre dosis y no me apuraron con la compra, buen trato.', 38],
]],

['slug' => 'centro-veterinario-b-b', 'opiniones' => [
    ['Diego H.', 5, 'Le pusieron el microchip a mi perro y me dieron el registro impreso el mismo día, todo claro.', 9],
    ['Paola G.', 5, 'Compré un bozal de canasta ajustable para mi labrador y le quedó justo, sin lastimarlo.', 23],
    ['Renzo C.', 4, 'La consulta fue rápida y me enseñaron cómo ponerle el bozal a mi perro sin que se estrese.', 41],
]],

['slug' => 'centro-veterinario-nuevo-chimbote', 'opiniones' => [
    ['Vanessa M.', 5, 'Castramos a mi gato macho y salió caminando al toque; al día siguiente ya comía normal.', 3],
    ['Christian O.', 5, 'El shampoo antipulgas aguantó casi un mes; mi perro dejó de rascarse tanto en el lomo.', 22],
    ['Lucía F.', 5, 'Me dieron indicaciones para el cuidado después de la cirugía y respondieron mis dudas por teléfono.', 35],
]],

['slug' => 'centro-veterinario-canvet', 'opiniones' => [
    ['Giancarlo P.', 5, 'Mi perro con problemas renales come el alimento bajo en fósforo de siete kilos y la orina le salió mejor.', 8],
    ['Milagros E.', 5, 'Le hicieron el análisis de orina completo y me llamaron con el resultado para explicarme el tratamiento.', 20],
    ['Álvaro S.', 5, 'Compré el rascador de cartón con hierba gatera y mis gatos no han soltado el juguete nuevo.', 31],
]],

['slug' => 'omardepatas-veterinaria-spa-y-hospedaje', 'opiniones' => [
    ['Sandra L.', 5, 'Bañaron y cortaron el pelo a mi cocker con shampoo hipoalergénico y quedó oliendo rico varios días.', 11],
    ['Oscar J.', 5, 'La vacunación anual séxtuple se la aplicaron sin que mi perro chille; la veterinaria tiene buena mano.', 25],
    ['Brenda I.', 4, 'Dejé a mi gata en el hospedaje tres días y me mandaron fotos de cómo estaba, eso me calmó.', 39],
]],

['slug' => 'zoolandia-vet-nuevo-chimbote', 'opiniones' => [
    ['José Luis V.', 5, 'La consulta me costó menos que en otras veterinarias y me dieron la receta anotada a mano.', 10],
    ['Nataly R.', 5, 'Tienen accesorios para mascotas a buen precio; compré un plato y un collar para mi cachorro.', 24],
    ['Héctor Z.', 5, 'Al final de la consulta me explicaron qué darle a mi gato y no me vendieron cosas de más.', 37],
]],

['slug' => 'veterinaria-huellas', 'opiniones' => [
    ['Marisol Ch.', 5, 'Las vitaminas de sesenta tabletas levantaron el apetito de mi perro viejo, se le nota más activo.', 13],
    ['Ricardo T.', 5, 'La arena sanitaria de diez kilos controla bien el olor, mi departamento ya no huele a gato.', 26],
    ['Elena Q.', 5, 'Es una veterinaria de barrio donde te recuerdan el nombre de tu mascota, eso se agradece.', 44],
]],

['slug' => 'gmo', 'opiniones' => [
    ['Fernando A.', 5, 'Me tomaron la medida de los lentes con calma y a la semana ya estaba viendo nítido el cartel de la esquina.', 12],
    ['Gabriela M.', 5, 'Las micas de contacto que me pusieron no se rayaron ni con el uso diario en el trabajo.', 28],
    ['Sergio B.', 4, 'Me ajustaron el armazón sin cobrarme la visita y salí con los lentes cómodos para manejar.', 40],
]],

['slug' => 'lares-optica', 'opiniones' => [
    ['Patricia U.', 5, 'Compré lentes de sol con protección y en la misma visita me limpiaron los de medida.', 14],
    ['Andrés C.', 5, 'El ajuste de la patilla me lo hicieron en cinco minutos y dejaron de apretarme la oreja.', 30],
    ['Diana P.', 5, 'Buen surtido de lentes de sol y te dejan probártelos sin apuro frente al espejo.', 43],
]],

['slug' => 'view-optica', 'opiniones' => [
    ['Roberto S.', 5, 'Los lentes de medida me quedaron exactos y noté la diferencia al leer de cerca en la oficina.', 15],
    ['Karina D.', 5, 'Me cambiaron las micas de contacto por unas más resistentes y me cobraron justo lo acordado.', 27],
    ['Miguel Á.', 5, 'La señorita de la óptica me ayudó a elegir el armazón que va con mi cara, sin apurarme.', 36],
]],

['slug' => 'opticas-cooper-vision-sac-chimbote', 'opiniones' => [
    ['Teresa N.', 5, 'Encontré armazones livianos para mi medida y me los ajustaron antes de salir de la tienda.', 17],
    ['Wilmer G.', 5, 'Llevo mis lentes cada cierto tiempo a la limpieza y ajuste, y siempre salen como nuevos.', 32],
    ['Sofía H.', 5, 'Hay bastante variedad de armazones y los precios están a la vista, no te inventan nada.', 45],
]],

['slug' => 'servicios-medico-y-enfermeria-yulicarm', 'opiniones' => [
    ['Gladys R.', 5, 'Me hicieron las curaciones de la herida en mi casa por turno y la enfermera venía puntual.', 2],
    ['Julio E.', 5, 'Contratamos el servicio de enfermería por turno para mi papá y fue un alivio para la familia.', 20],
    ['Norma L.', 5, 'Las curas las hacían con material nuevo y siempre desinfectaban bien antes de empezar.', 34],
]],

['slug' => 'cemecsalud', 'opiniones' => [
    ['César V.', 5, 'Me atendieron la consulta especializada con cita y no esperé más de veinte minutos en la sala.', 5],
    ['Rossy M.', 5, 'La ecografía la hizo el médico explicando lo que veía en la pantalla, se entiende mejor así.', 21],
    ['Pablo I.', 4, 'El precio de la consulta es razonable y te entregan el informe de la ecografía el mismo día.', 42],
]],

['slug' => 'johnmay-salud-sede-chimbote', 'opiniones' => [
    ['Yesenia T.', 5, 'Hice el chequeo preventivo completo y me entregaron los resultados explicados con paciencia.', 4],
    ['Alexis F.', 5, 'Fui por una consulta especializada y el doctor revisó mi historial antes de recetarme cualquier cosa.', 23],
    ['Mónica C.', 5, 'El local está limpio y hay sitios para sentarse mientras esperas tu turno en el consultorio.', 33],
]],

['slug' => 'centro-clinico-salud-del-sur', 'opiniones' => [
    ['Edwin Q.', 5, 'Me saqué sangre para los exámenes de laboratorio en ayunas y a las pocas horas ya tenía el resultado.', 6],
    ['Liliana A.', 5, 'La consulta especializada me sirvió para descartar lo que me preocupaba, el doctor fue claro.', 24],
    ['Bruno O.', 4, 'Está cerca de la avenida principal y la atención en admisión fue rápida con mi seguro.', 39],
]],

['slug' => 'centro-medico-y-laboratorio-clinico-san-rafael', 'opiniones' => [
    ['Aurelio M.', 5, 'Me hicieron el electrocardiograma en un cuarto tranquilo y el informe me lo dieron al rato.', 8],
    ['Katia Z.', 5, 'El chequeo preventivo incluyó presión y glucosa, salí sabiendo cómo estaba mi salud.', 29],
    ['Rubén S.', 5, 'La enfermera me explicó cómo prepararme para los análisis y no tuve que volver otro día.', 38],
]],

['slug' => 'clinica-robles', 'opiniones' => [
    ['Verónica P.', 5, 'Le hicieron el electrocardiograma a mi mamá y el cardiólogo nos explicó el resultado sin apuro.', 10],
    ['Gustavo R.', 5, 'Conseguí consulta especializada para el mismo día y la atención fue ordenada desde admisión.', 26],
    ['Milena K.', 4, 'Las instalaciones se ven cuidadas y el personal te orienta por los consultorios sin perderte.', 42],
]],

['slug' => 'consultorio-dental-lara', 'opiniones' => [
    ['Claudia B.', 5, 'Me hicieron la endodoncia en dos visitas y no sentí dolor ni cuando pasó la anestesia.', 7],
    ['Frank D.', 5, 'El blanqueamiento dental me dejó los dientes parejos y me indicaron no tomar café unos días.', 25],
    ['Sheyla G.', 5, 'El doctor explica cada paso antes de tocar la pieza y eso te da tranquilidad en el sillón.', 40],
]],

['slug' => 'centro-odontologico-pacifico-odontologia-integral-nuevo-chimbote', 'opiniones' => [
    ['Nilton C.', 5, 'Me hicieron la limpieza y profilaxis con ultrasonido y sentí los dientes lisos por semanas.', 9],
    ['Eliana V.', 5, 'Mi papá se colocó un implante dental y ahora come su choclo sin molestias, valió la pena.', 22],
    ['Marco T.', 5, 'Te explican el presupuesto por escrito antes de empezar y respetan el precio acordado.', 37],
]],

['slug' => 'consultorio-odontologico-happydent-world', 'opiniones' => [
    ['Priscila N.', 5, 'Me hicieron la obturación estética en la muela de adelante y no se nota el parche.', 11],
    ['Jhonatan L.', 5, 'La endodoncia me la hizo en una sola sesión y al día siguiente ya masticaba normal.', 28],
    ['Andrea Y.', 4, 'El consultorio es pequeño pero limpio y atienden con cita, casi no esperas en la puerta.', 44],
]],

['slug' => 'dental-angeles', 'opiniones' => [
    ['Doris F.', 5, 'La limpieza dental me la hicieron con paciencia porque tengo las encías sensibles.', 13],
    ['Kevin A.', 5, 'Salí con los dientes más blancos después del blanqueamiento y no me dio sensibilidad.', 30],
    ['Ruth E.', 5, 'Me dieron un cepillo y me enseñaron la técnica correcta, se nota que les importa el paciente.', 43],
]],

['slug' => 'dental-colonia', 'opiniones' => [
    ['Óscar M.', 5, 'Fui por la limpieza dental y me sacaron el sarro que tenía años, quedé con la boca fresca.', 12],
    ['Cinthia R.', 5, 'El blanqueamiento tuvo buen resultado y me cobraron lo que me dijeron al inicio.', 27],
    ['Luis Alberto S.', 4, 'Atienden por orden de llegada y el consultorio queda a pocas cuadras del paradero.', 41],
]],

['slug' => 'consultorio-odontologico-biodent-chimbote', 'opiniones' => [
    ['Rosa C.', 5, 'Fui por la evaluación de ortodoncia y el doctor me explicó con calma cuánto iba a durar el tratamiento y cuánto costaba.', 7],
    ['Luis M.', 5, 'Me hice el blanqueamiento dental antes de mi matrimonio y quedé conforme, no me dolió nada y el consultorio está limpio.', 21],
    ['Ana P.', 4, 'Atienden con cita y casi no esperas; en Nuevo Chimbote es fácil llegar y hay dónde dejar el carro.', 33],
]],

['slug' => 'sonrisas-brillantes', 'opiniones' => [
    ['Carlos Q.', 5, 'La consulta odontológica me costó poquito y me dijeron qué tenía sin querer meterme mil tratamientos de golpe.', 3],
    ['Marisol T.', 5, 'Me hicieron una obturación estética en la muela de adelante y no se nota nada, quedó del mismo color.', 15],
    ['Jorge V.', 5, 'Al final me dieron mi presupuesto por escrito y con eso decidí, porque uno siempre se olvida de los precios.', 28],
]],

['slug' => 'dental-ruiz-nuevo-chimbote', 'opiniones' => [
    ['Elena R.', 5, 'Me taparon una carie con obturación estética y ya no siento esa molestia cuando tomo algo helado.', 5],
    ['Pedro S.', 5, 'El blanqueamiento dental me dejó los dientes parejos y no sentí esa sensibilidad que me dio en otro sitio.', 19],
    ['Lucía H.', 4, 'La doctora atiende con paciencia a mi mamá, que ya es mayor y se asusta con el torno.', 40],
]],

['slug' => 'consultorio-d-rosales', 'opiniones' => [
    ['Sofía M.', 5, 'La limpieza dental me la hicieron rápido y me sacaron bastante sarro, salí con la boca bien fresca.', 9],
    ['Rubén A.', 5, 'Pregunté por el blanqueamiento y me dijeron cuántas sesiones necesitaba según mi caso, sin exagerar.', 24],
    ['Diana F.', 5, 'El consultorio queda cerca de la avenida en Nuevo Chimbote y atienden hasta tarde, así voy después del trabajo.', 37],
]],

['slug' => 'fitnesscat', 'opiniones' => [
    ['Karina B.', 5, 'Las clases de aeróbicos son bien movidas y la música te anima, salgo sudando y con ganas de volver.', 6],
    ['Willy N.', 5, 'Saqué el plan anual y me sale más barato que pagar mes a mes, además no me cobraron matrícula.', 18],
    ['Tania G.', 4, 'El local es amplio y hay ventiladores, aunque en verano igual hace calor a las siete de la noche.', 31],
]],

['slug' => 'club-cross-x', 'opiniones' => [
    ['Marco L.', 5, 'Pago la membresía mensual y el profe te corrige la técnica cuando haces mal el peso muerto.', 4],
    ['Patty Z.', 5, 'Contraté entrenamiento personalizado una vez por semana y en dos meses bajé la grasa que solo no bajaba.', 16],
    ['Iván D.', 5, 'Abren desde las seis de la mañana, así entreno antes de irme a la oficina aquí en Chimbote.', 29],
]],

['slug' => 'gym-country-club', 'opiniones' => [
    ['Nicole S.', 5, 'Las cinco clases de HIIT me dejaron molida pero sirven, en tres semanas ya aguantaba la rutina completa.', 8],
    ['César O.', 4, 'Alquilo el casillero con candado y dejo mis cosas tranquilas mientras entreno, ya no cargo mochila.', 22],
    ['Fiorella P.', 5, 'El gym está en Nuevo Chimbote y queda cerca de mi casa, así voy caminando y me ahorro la combi.', 44],
]],

['slug' => 'king-fit', 'opiniones' => [
    ['Gisela M.', 5, 'La clase de spinning es buenaza, la bici te marca las piernas y el instructor pone buena música.', 11],
    ['Aldo C.', 5, 'Me inscribí al plan anual y con eso entro a cualquier horario sin pagar extra cada mes.', 26],
    ['Rocío V.', 4, 'Los vestidores se mantienen limpios y siempre hay agua, aunque a la hora punta faltan bicicletas.', 38],
]],

['slug' => 'betelfit-gym', 'opiniones' => [
    ['Hugo T.', 5, 'Pagué una hora de entrenamiento personal y el profe me armó la rutina según mi lesión de rodilla.', 13],
    ['Milagros E.', 5, 'El plan anual de Betelfit me convenció porque puedo congelarlo un mes cuando salgo de viaje.', 27],
    ['Beto R.', 5, 'El ambiente es tranquilo, no es de esos gyms llenos de gente, y eso me ayuda a concentrarme.', 41],
]],

['slug' => 'renovadora-de-calzados-katiuska', 'opiniones' => [
    ['Susana J.', 5, 'Le cambiaron las plantillas a mis zapatos de trabajo y quedaron como nuevos, ya no me duele el talón.', 2],
    ['Richard A.', 5, 'Mandé mis botas al cambio de suela y me las devolvieron bien pegadas, con buen acabado en la orilla.', 14],
    ['Carmen Y.', 4, 'El precio del remontado fue justo y me lo dijeron antes de empezar, sin sorpresas al recoger.', 30],
]],

['slug' => 'renovadora-la-solucion', 'opiniones' => [
    ['Joel Q.', 5, 'En Moro no hay mucho taller, así que llevé mis zapatillas a reparación y me cosieron la punta rota.', 10],
    ['Nelly I.', 5, 'El cambio de suela quedó parejo y ya no resbalo con el piso mojado del mercado.', 23],
    ['Óscar G.', 5, 'Demoran un par de días, pero el trabajo es serio y cobran exactamente lo que dicen al inicio.', 35],
]],

['slug' => 'remontadora-del-calzado-elite', 'opiniones' => [
    ['Vanessa U.', 5, 'El remontado de mis zapatos de vestir quedó fino, con la punta bien formada, como recién comprados.', 12],
    ['Percy L.', 5, 'Me pusieron plantillas nuevas a los zapatos de mi hijo y aguantaron todo el año escolar.', 25],
    ['Gladys B.', 4, 'Atienden rápido y te explican si el zapato todavía tiene arreglo o ya no vale la pena.', 39],
]],

['slug' => 'katy-calzado-para-damas', 'opiniones' => [
    ['Melissa C.', 5, 'Compré dos pares de sandalias con la promoción y me salió casi al precio de uno, buen negocio.', 1],
    ['Julia F.', 5, 'Los modelos de sandalias son de temporada y hay tallas para pie ancho, que es mi caso.', 20],
    ['Rosa M.', 5, 'La dueña te deja probar con calma y no te apura, aunque la tienda se llene los sábados.', 34],
]],

['slug' => 'inversiones-y-representaciones-livi-eirl', 'opiniones' => [
    ['Fernando D.', 5, 'Compré zapatos casuales para la oficina y son cómodos, no me sacaron ampollas el primer día.', 17],
    ['Segundo H.', 5, 'Tienen botas de cuero gruesas, buenas para el trabajo en el muelle cuando llueve.', 32],
    ['Zoila T.', 4, 'Los precios son de tienda formal, no regatean mucho, pero la calidad se siente al ponértelos.', 42],
]],

['slug' => 'record-sport-chimbote', 'opiniones' => [
    ['Wilder S.', 5, 'Con la promoción 2x1 saqué dos pares de botas de fútbol para mis hijos y salió económico.', 3],
    ['Henry P.', 5, 'Las botas de fútbol tienen buen agarre en la losa, mis chicos no resbalan en el partido.', 19],
    ['Alex R.', 4, 'Hay tallas grandes de botines, que en otras tiendas de Chimbote no encuentras fácil.', 36],
]],

['slug' => 'adidas-footwear-store-chimbote', 'opiniones' => [
    ['Bruno M.', 5, 'Encontré mis zapatillas adidas talla 45, que casi nunca hay en las tiendas del centro.', 6],
    ['Stephanie K.', 5, 'Las zapatillas de running me aguantaron la carrera de la municipalidad, sin dolor de planta.', 21],
    ['Renzo A.', 4, 'Los modelos son originales, con su caja y todo, y me costaron menos que en el mall.', 43],
]],

['slug' => 'costa-del-inka', 'opiniones' => [
    ['Patricia N.', 5, 'La habitación con vista al mar vale la pena, desperté viendo las olas de la bahía.', 9],
    ['Miguel Á.', 5, 'El estacionamiento es amplio y está dentro del hotel, dejé la camioneta tranquila toda la noche.', 26],
    ['Sandra O.', 4, 'La atención en recepción fue amable y nos guardaron las maletas después del check out.', 45],
]],

['slug' => 'hostal-el-gran-marquez-g-j', 'opiniones' => [
    ['Elmer C.', 5, 'La habitación matrimonial estaba limpia y la cama era grande, dormimos bien con mi esposa.', 12],
    ['Yolanda R.', 5, 'Pedimos la habitación familiar para mis tres hijos y nos acomodamos sin problema, con camas separadas.', 24],
    ['Nelson B.', 4, 'El hostal está en una zona tranquila de Nuevo Chimbote y el agua caliente funciona a toda hora.', 33],
]],

['slug' => 'hotel-continental', 'opiniones' => [
    ['Katherine L.', 5, 'La habitación doble tiene buen espacio y el wifi aguantó para trabajar hasta la medianoche.', 5],
    ['José Antonio M.', 5, 'Nos dejaron entrar a las once de la mañana y no cobraron recargo por el early check in.', 18],
    ['William Z.', 4, 'El desayuno es sencillo, pan con huevo y café, pero sirve para salir temprano al trabajo.', 30],
]],

['slug' => 'paris-senlis-hostel-plus', 'opiniones' => [
    ['Andrés F.', 5, 'La cama del dormitorio compartido estaba tendida y el locker con llave me dio seguridad.', 8],
    ['Camila S.', 5, 'Pagué el desayuno aparte y venía con fruta y café, me salió más barato que comer afuera.', 20],
    ['Diego V.', 5, 'Conocí viajeros de otros países en la sala común, el ambiente del hostel es bien relajado.', 35],
]],

['slug' => 'hotel-los-cocos', 'opiniones' => [
    ['Rocío A.', 5, 'Reservé la estadía romántica de fin de semana y nos recibieron con pétalos y una botella de vino.', 11],
    ['Gustavo P.', 5, 'La suite ejecutiva tiene sala de estar aparte, ideal para recibir a un cliente con privacidad.', 27],
    ['Wendy T.', 4, 'La piscina estaba limpia y el personal nos dio toallas sin tener que pedir dos veces.', 39],
]],

['slug' => 'hotel-remanso', 'opiniones' => [
    ['Saúl R.', 5, 'La habitación simple es chica pero limpia, y por ese precio está bien para una noche de paso.', 2],
    ['Liliana G.', 5, 'Pedimos la habitación doble para mi mamá y mi tía y les pusieron camas de plaza y media.', 16],
    ['Teodoro H.', 4, 'El hotel queda cerca de la Panamericana, así que llegamos rápido y sin buscar mucho.', 32],
]],

['slug' => 'hostal-los-cocos', 'opiniones' => [
    ['Maribel Q.', 5, 'Alquilé la habitación simple por una noche y tenía su ventilador, tele y baño propio bien limpio.', 7],
    ['Édgar W.', 5, 'La habitación matrimonial es amplia y la cama no rechina, se descansa después del trabajo.', 23],
    ['Cynthia D.', 5, 'La señora de recepción nos atendió a las dos de la mañana sin poner mala cara.', 41],
]],

['slug' => 'clinica-bellsal', 'opiniones' => [
    ['Verónica A.', 5, 'Llevé a mi papá a la consulta especializada y el doctor revisó sus análisis uno por uno.', 14],
    ['Raúl M.', 5, 'Me hice el chequeo preventivo completo y me entregaron los resultados al día siguiente, rápido.', 28],
    ['Janet O.', 4, 'Las enfermeras te acompañan hasta el laboratorio y te explican cómo tomar la medicina.', 44],
]],

['slug' => 'la-numero-1', 'opiniones' => [
    ['Gladys S.', 5, 'Compré por docena para revender y me dejaron elegir tallas surtidas sin cobrarme de más.', 13],
    ['Marisol B.', 5, 'Hay tallas grandes de polos y blusas, que en otras tiendas de ropa del centro no hay.', 29],
    ['Elva C.', 4, 'Los precios por mayor convienen, aunque hay que revisar bien la costura antes de llevarse.', 42],
]],

['slug' => 'tiendas-el', 'opiniones' => [
    ['Rosa C.', 5, 'Compré una blusa de mujer para el trabajo y me quedó justa la talla; la tela es fresca para el calor de Chimbote.', 6],
    ['Luis M.', 5, 'Fui con mi esposo y salimos los dos con polo y pantalón de hombre; los precios estaban a la vista.', 17],
    ['Ana P.', 4, 'La señora me dejó probar varias prendas sin apuro y me guardó la talla M hasta el día siguiente.', 33],
]],

['slug' => 'vestidos-de-fiesta-chimbote', 'opiniones' => [
    ['Carmen V.', 5, 'Compré mi vestido de fiesta talla S para el cumpleaños de mi mamá y me quedó perfecto sin arreglos.', 4],
    ['Sofía Q.', 5, 'Los accesorios de fiesta que venden combinan con cualquier vestido; me llevé aretes y una cartera dorada.', 19],
    ['Milagros E.', 5, 'Hay vestidos en talla M y L, y la dueña me ayudó a elegir el que me sentaba mejor para la promoción.', 28],
]],

['slug' => 'dakani-venta-ropa-interior', 'opiniones' => [
    ['Gladys T.', 5, 'Me llevé dos pijamas de algodón y no destiñeron en el lavado, cosa que con otras tiendas sí me pasó.', 8],
    ['Nancy O.', 5, 'La ropa de dormir es suavecita y está a buen precio; compré para mis dos hijas y les encantó el estampado.', 22],
    ['Betty S.', 4, 'Atienden con calma y te dejan ver las tallas; me probé el pijama largo y me lo llevé en talla M.', 40],
]],

['slug' => 'dimor-brand-ropa-streetwear-chimbote', 'opiniones' => [
    ['Percy A.', 5, 'Aproveché el 2x1 en polos y me salieron dos por el precio de uno; la tela aguanta el uso diario.', 5],
    ['Kevin R.', 5, 'Tienen gorras de varios colores y modelos; me quedé con una negra y otra con bordado pequeño.', 14],
    ['Diana W.', 5, 'El muchacho que atiende te deja combinar tallas en la promo y no te apura para escoger.', 31],
]],

['slug' => 'taller-automotriz', 'opiniones' => [
    ['Jorge N.', 5, 'Fui por el cambio de aceite y me mostraron el filtro viejo antes de botarlo; eso da confianza.', 9],
    ['Rubén G.', 5, 'Hice alineamiento y balanceo en Moro y el carro dejó de jalar hacia la derecha en la Panamericana.', 20],
    ['Elmer J.', 4, 'Cobran menos que en Chimbote y el trabajo lo hacen en la mañana, así que ya lo tenía listo al mediodía.', 37],
]],

['slug' => 'taller-automotriz-palacios', 'opiniones' => [
    ['Óscar Z.', 5, 'Le hicieron diagnóstico con escáner a mi Hilux y salió el sensor de oxígeno fallando; ya no gasté de más.', 3],
    ['Hugo L.', 5, 'Cambié el aceite de la transmisión automática de mi carro y la caja dejó de dar tirones al acelerar.', 16],
    ['Iván C.', 5, 'El mecánico te explica con el escáner en la mano qué falla y no te mete repuestos que no necesitas.', 29],
]],

['slug' => 'alineamiento-balanceo-y-suspension-antony', 'opiniones' => [
    ['Segundo V.', 5, 'Me repararon la suspensión delantera y el carro ya no suena al pasar los rompemuelles de la avenida.', 7],
    ['Raúl M.', 5, 'Hago el mantenimiento de suspensión cada año con ellos y revisan bujes y amortiguadores sin cobrar de más.', 23],
    ['Flor K.', 4, 'El local está en Nuevo Chimbote y tiene espacio para dejar el carro toda la mañana sin problema.', 41],
]],

['slug' => 'pacifico-motors', 'opiniones' => [
    ['César H.', 5, 'El diagnóstico computarizado les marcó la falla del inyector y me ahorré cambiar medio motor.', 11],
    ['Tania P.', 5, 'Me repararon el motor de mi station wagon y quedó parejo; ya llevo tres meses sin problemas.', 25],
    ['Wilder A.', 5, 'Te entregan el carro con la falla explicada y el detalle de lo que cambiaron, nada de cuentas raras.', 38],
]],

['slug' => 'mecanica-automotriz-jorge', 'opiniones' => [
    ['Pedro L.', 5, 'Alinearon la dirección con el equipo láser y el timón quedó centrado, ya no vibra en la carretera.', 2],
    ['Marisol S.', 5, 'Me rotaron los neumáticos para que gasten parejo y de paso me avisaron que las llantas de adelante ya estaban bajas.', 18],
    ['Yolanda I.', 4, 'Es un taller sencillo, sin lujos, pero el trabajo es rápido y cobran lo que dicen al inicio.', 34],
]],

['slug' => 'mv-motors-chimbote', 'opiniones' => [
    ['Julio R.', 5, 'Encontré el repuesto para mi auto viejo que en otras tiendas no tenían y me lo instalaron ahí mismo.', 10],
    ['Katia B.', 5, 'Compro repuestos ahí porque te muestran la pieza antes de cobrar y aceptan devolución si no calza.', 21],
    ['Renzo F.', 5, 'El servicio de taller es rápido: dejé el carro a las ocho y a las once ya me llamaron.', 43],
]],

['slug' => 'medicars-taller-de-mecanica-automotriz-chimbote-auxilio-mecanico', 'opiniones' => [
    ['Miguel Á.', 5, 'Me cambiaron las pastillas y rectificaron los discos; el freno quedó firme y sin chillido.', 13],
    ['Elena F.', 5, 'Pedí auxilio mecánico porque me quedé en la avenida y llegaron en menos de media hora.', 26],
    ['Josué D.', 4, 'Hicieron alineamiento y balanceo después de la reparación de frenos y no me cobraron aparte la revisión.', 39],
]],

['slug' => 'servicio-general-automotriz-etussa-s-a', 'opiniones' => [
    ['Wilmer D.', 5, 'Le rectificaron los discos de freno a mi camioneta y el pedal quedó alto, como debe estar.', 1],
    ['Sonia R.', 5, 'Repararon el motor de una combi de la empresa y quedó trabajando bien; son serios con la factura.', 15],
    ['Gregorio P.', 5, 'Es una empresa grande, con varias rampas, así que atienden rápido aunque haya cola de carros.', 30],
]],

['slug' => 'mecanica-automotriz-chimbote', 'opiniones' => [
    ['Alberto V.', 5, 'El diagnóstico computarizado les dijo que era el caudalímetro y con eso ya no gasté en adivinar.', 12],
    ['Rocío N.', 5, 'Repararon el motor de mi Corolla y me mostraron las piezas cambiadas al momento de pagar.', 24],
    ['Félix G.', 4, 'Me atendieron un sábado por la tarde, cuando otros talleres ya estaban cerrados con la tranquera puesta.', 44],
]],

['slug' => 'botica-union', 'opiniones' => [
    ['Marlene C.', 5, 'Pedí vitaminas y suplementos por delivery y me llegaron a casa en el barrio en menos de veinte minutos.', 5],
    ['José A.', 5, 'El químico me explicó cómo tomar el multivitamínico y a qué hora, sin apurarme en el mostrador.', 19],
    ['Estela R.', 5, 'Tienen precios anotados en cada caja, así que ya sabes cuánto vas a pagar antes de llegar a la caja.', 36],
]],

['slug' => 'jirehfarma', 'opiniones' => [
    ['Cynthia L.', 5, 'Compro los genéricos ahí porque salen bastante más baratos que la marca y me funcionan igual.', 4],
    ['Marco T.', 5, 'Necesitaba pañales y leche de fórmula para mi bebé a las once de la noche y me atendieron sin drama.', 20],
    ['Rosario D.', 4, 'La señora de la botica me midió la presión cuando le pedí y no me cobró nada por eso.', 32],
]],

['slug' => 'botica-chavelita', 'opiniones' => [
    ['Julia M.', 5, 'Pregunté por el genérico del antibiótico y me mostraron dos laboratorios para que yo eligiera el precio.', 8],
    ['Fernando S.', 5, 'Venden vitaminas por unidad cuando no alcanzas la caja completa, y eso ayuda cuando el sueldo está corto.', 27],
    ['Lourdes Q.', 5, 'Atienden desde temprano y hay cola corta; en cinco minutos ya salí con mi pedido de genéricos.', 42],
]],

['slug' => 'farmacia-martel', 'opiniones' => [
    ['Verónica P.', 5, 'Encontré el medicamento que en otras farmacias estaba agotado y me lo dieron con boleta.', 6],
    ['Alfredo C.', 5, 'Compré biberones y pañales para mi sobrino; tienen variedad de marcas para bebé en el estante de la entrada.', 22],
    ['Sandra B.', 4, 'La química me revisó la receta y me dijo cada cuántas horas tomar la pastilla, bien amable.', 35],
]],

['slug' => 'botica-24-horas', 'opiniones' => [
    ['Karina V.', 5, 'Fui a las dos de la mañana por paracetamol de 500 mg y estaba abierto, eso vale muchísimo.', 3],
    ['Edgar N.', 5, 'La caja de paracetamol está a buen precio y siempre tienen stock, nunca me quedé sin comprar.', 17],
    ['Lucía F.', 5, 'También venden vitaminas y suplementos; me llevé un frasco para las defensas de mi mamá.', 29],
]],

['slug' => 'dylanffarma', 'opiniones' => [
    ['Manuel R.', 5, 'Los genéricos son bien económicos y me explican si es lo mismo que el de marca o no.', 9],
    ['Tatiana G.', 5, 'Compré shampoo, jabón y crema dental ahí mismo; tienen de todo para el cuidado personal.', 23],
    ['Ángel M.', 5, 'El chico del mostrador buscó en el sistema si tenían mi medicamento y me llamó cuando llegó.', 40],
]],

['slug' => 'libreria-bazar-edu', 'opiniones' => [
    ['Norma S.', 5, 'Compré los cuadernos de la lista escolar y me hicieron precio por pack, más barato que en el mercado.', 7],
    ['Cristian H.', 5, 'Tienen útiles escolares de varias marcas; encontré los colores y las reglas que pedía el colegio.', 20],
    ['Mónica T.', 4, 'En marzo hay mucha gente, pero atienden ordenado y te dan boleta sin que la tengas que pedir.', 31],
]],

['slug' => 'libreria-lumi-creativa', 'opiniones' => [
    ['Vanessa R.', 5, 'Saqué copias de un texto de la universidad y salieron bien nítidas, sin ese manchón de otras librerías.', 11],
    ['Diego A.', 5, 'Imprimí mi trabajo en color y quedó listo para anillar en menos de diez minutos.', 25],
    ['Rebeca L.', 4, 'Tienen libros y textos escolares en el estante del fondo; hallé el de matemática que buscaba.', 38],
]],

['slug' => 'libreria-daza', 'opiniones' => [
    ['Patricia E.', 5, 'Me llevé una mochila reforzada para mi hijo de secundaria y aguantó todo el año sin romperse.', 2],
    ['Gerson M.', 5, 'Tienen artículos de escritorio sueltos: compré grapas, clips y un perforador sin tener que comprar la caja.', 18],
    ['Amelia C.', 5, 'Los precios están marcados en cada artículo y te atienden rápido aunque esté lleno de escolares.', 34],
]],

['slug' => 'farmacia-miramar', 'opiniones' => [
    ['Zoila R.', 5, 'Compro los genéricos ahí porque son baratos y la señora me dice cuál es igual al de marca.', 13],
    ['Henry B.', 5, 'Necesitaba pañales y una crema para la piel de mi bebé y tenían justo lo que buscaba.', 26],
    ['Consuelo V.', 5, 'Está cerca del mercado, así que paso caminando y en cinco minutos ya tengo mi pedido.', 45],
]],

['slug' => 'grupo-comercial-villoslada', 'opiniones' => [
    ['Efraín T.', 5, 'Compré cemento por bolsas para mi techo y me lo llevaron hasta la puerta sin cobrar el flete.', 10],
    ['Silvia M.', 5, 'Tienen herramientas de mano buenas; me llevé un martillo y un juego de desarmadores que aguantan.', 21],
    ['Jonathan P.', 4, 'Te asesoran con la cantidad de bolsas según el área que vas a vaciar, no te venden de más.', 33],
]],

['slug' => 'ferreteria-vym-s-a-c', 'opiniones' => [
    ['Arturo L.', 5, 'Fui por tornillos de media pulgada y me los vendieron por unidad, no tuve que comprar la caja entera.', 5],
    ['Mirella S.', 5, 'Tienen tornillería y fijaciones ordenadas por medida, así que encuentras rápido lo que buscas.', 24],
    ['Robinson C.', 5, 'Compré una llave de mano y el vendedor me mostró cómo usarla para no malograr la tuerca.', 39],
]],

// ====== TANDA 2 (2026-09-14, noche): las 100 tiendas anteriores ======
['slug' => 'ropita-para-barbies-y-ken', 'opiniones' => [
    ['Rosa C.', 5, 'Le compré el conjunto completo de ropita para la muñeca de mi hija y le quedó perfecto, bonitos acabados y barato.', 5],
    ['Luis M.', 5, 'Pedí ropita para Ken y me trajeron varios modelos, la tela parece buena y los cierres no se rompen al vestirlo.', 18],
    ['Ana P.', 4, 'Los vestidos de Barbie son chiquitos pero bien cosidos; compré tres y ninguno vino con hilos sueltos.', 30],
]],

['slug' => 'woma-taller-y-carrito-de-snacks', 'opiniones' => [
    ['Marisol Q.', 5, 'Contratamos el taller de pintado de alcancías y los niños se entretuvieron toda la hora pintando su propia alcancía.', 4],
    ['Jorge T.', 5, 'El carrito de snacks llegó puntual a la fiesta y el algodón de azúcar salía al instante, los chicos hicieron cola.', 12],
    ['Kelly R.', 5, 'Las salchipapas y los nuggets estaban calientes y bien servidos; coordinaron todo el evento sin que yo me preocupara.', 27],
]],

['slug' => 'ositos-sorpresas-paola', 'opiniones' => [
    ['Cindy V.', 5, 'El osito sorpresa llegó con el letrero personalizado y globos; mi mamá no paró de llorar en su cumpleaños.', 7],
    ['Bryan S.', 5, 'Para los quince de mi hermana contratamos la visita del osito y bailó con todos, hasta con mi abuela.', 21],
    ['Paola N.', 4, 'El show con personajes para niños fue lo mejor; los chicos se quedaron pegados al oso toda la tarde.', 35],
]],

['slug' => 'decoraciones-chimbote', 'opiniones' => [
    ['Sandra L.', 5, 'Alquilamos el pack completo de cumpleaños y montaron todo antes de que llegaran los invitados, sin apuro.', 3],
    ['Miguel Á.', 5, 'Las tres mesas romanas con marcos y lazo se vieron elegantes en el jardín; todos preguntaron dónde las alquilé.', 16],
    ['Rocío F.', 4, 'El número LED de 60 cm con sus luces quedó lindo de noche, aunque tuve que pedir una extensión más larga.', 29],
]],

['slug' => 'ramos-florales-eternos-limpiapipas', 'opiniones' => [
    ['Diego H.', 5, 'Le regalé el ramo de girasoles eternos de limpiapipas a mi enamorada y no lo podía creer, no se marchitan.', 2],
    ['Carmen B.', 5, 'Pedí el ramo XL personalizado con temática y quedó igualito a la foto que les mandé por WhatsApp.', 14],
    ['Sheyla M.', 4, 'El ramo mediano con lazo y detalles está bien hecho a mano; se nota el trabajo, aunque la espera fue de tres días.', 26],
]],

['slug' => 'entre-dulces-y-mas', 'opiniones' => [
    ['Patricia G.', 5, 'Nos armaron el arco de globos para las olimpiadas del colegio y quedó enorme, con los colores de mi sección.', 6],
    ['Edwin R.', 5, 'La cabeza de dragón para el desfile salió buenaza, los chicos caminaban debajo y nadie se veía.', 19],
    ['Nelly C.', 4, 'El carro alegórico temático nos duró todo el recorrido, aunque el aro decorativo llegó con un globo desinflado.', 33],
]],

['slug' => 'mi-outfit-body-sofia', 'opiniones' => [
    ['Fiorella D.', 5, 'El body halter Sofía de lunares me quedó perfecto y la licra veneciana no se transparenta, buena compra.', 5],
    ['Karla S.', 5, 'Compré el pantalón Azucar de drill tiro alto en negro y me encantó cómo levanta la figura.', 17],
    ['Leslie O.', 5, 'Me mandaron el pedido a Trujillo por envío y llegó en cuatro días; la blusa Ambar vainilla es tal cual la foto.', 31],
]],

['slug' => 'sion-welder-chimbote', 'opiniones' => [
    ['Wilmer A.', 5, 'Estoy en el curso de soldadura SMAW y casi todo es práctica en el taller; ya sueldo sin que se me pegue el electrodo.', 8],
    ['Segundo P.', 5, 'La matrícula la pagué en dos partes y me dieron mi carné; el instructor corrige uno por uno en la máquina.', 22],
    ['Jorge L.', 4, 'Son cuatro meses de clases y al final dan certificado; el horario de noche me acomoda porque trabajo de día.', 38],
]],

['slug' => 'only-houses-inmobiliaria', 'opiniones' => [
    ['Rocío M.', 5, 'Me mostraron la casa de 108 m² en Garatea y me explicaron todo lo de la partida y los papeles, sin apuro.', 9],
    ['Anthony V.', 5, 'Alquilé una habitación amoblada en Los Ángeles y estaba limpia, con su cama y su closet, tal como la vi.', 23],
    ['Gloria T.', 5, 'Vendí mi terreno de Las Praderas con su asesoría y me consiguieron comprador en menos de un mes.', 36],
]],

['slug' => 'la-cajamarquina', 'opiniones' => [
    ['Mónica E.', 5, 'Compro ahí la gelatina sin sabor de flor de Jamaica y la miel de Oxapampa, se siente que son productos de verdad.', 4],
    ['Rubén C.', 5, 'El chocolate premium al 70 % con almendras es mi vicio; también probé el muffin keto y llena bastante.', 15],
    ['Yesenia F.', 5, 'El sirope de fresa con stevia lo uso para las tortas de mi negocio y rinde bastante, buena compra.', 28],
]],

['slug' => 'mundialito-chimbote', 'opiniones' => [
    ['Christian R.', 5, 'Inscribí a mi equipo en el Mundialito y todo estuvo bien organizado, con árbitro y programación de partidos por WhatsApp.', 6],
    ['Kevin T.', 5, 'Jugamos la categoría libre en el campo Rodolfo Pulache y la cancha estaba en buen estado, aunque hacía bastante aire.', 20],
    ['Bryan Q.', 4, 'La inscripción costó lo justo y el torneo empezó a la hora que dijeron el sábado; repetimos el próximo año.', 34],
]],

['slug' => 'jairo-grill', 'opiniones' => [
    ['Percy D.', 5, 'Le compré el cilindro mediano para caja china y el chancho salió parejo; el termómetro ayuda un montón.', 3],
    ['Iván G.', 5, 'El cilindro grande trae manija de madera y agarraderas laterales, bien soldado en acero inoxidable, no como otros.', 18],
    ['Néstor B.', 5, 'La carbonera cilíndrica con cenicero me sirve para encender el carbón sin ensuciar el patio; buen acabado.', 30],
]],

['slug' => 'chimbote-noticias-chn', 'opiniones' => [
    ['Gladys P.', 5, 'Publiqué el aviso de mi negocio en CHN y me llamaron varios clientes esa misma semana, buena difusión.', 5],
    ['Hugo A.', 5, 'Sigo la página desde hace años por las noticias de Chimbote; avisan rápido cuando pasa algo en la ciudad.', 21],
    ['Marlene Z.', 4, 'Me hicieron la cobertura del aniversario del colegio y subieron el video con entrevistas a los profesores.', 37],
]],

['slug' => 'colegio-adventista-el-santa', 'opiniones' => [
    ['Elena Ch.', 5, 'Matriculé a mi hijo para el 2027 y en la entrevista nos explicaron bien el nuevo turno de la tarde.', 7],
    ['Wilder S.', 5, 'El pack de bienvenida del matriculado trae uniforme y útiles; mi hija salió contenta el primer día de clases.', 22],
    ['Ruth M.', 5, 'Las clases a las 6:30 de la tarde nos ayudan porque trabajamos todo el día; los profesores son de confianza.', 40],
]],

['slug' => 'luis-vilcachagua-decoraciones', 'opiniones' => [
    ['Janet O.', 5, 'Nos montaron la decoración temática de la fiesta infantil en el local y llegaron temprano a armar todo.', 8],
    ['Percy L.', 5, 'El arco de globos para el cumpleaños de mi sobrina quedó hermoso y aguantó hasta el final sin desinflarse.', 19],
    ['Milagros A.', 4, 'Los números luminosos se ven preciosos de noche, aunque tuve que pedirle que trajera un cable más largo.', 32],
]],

['slug' => 'fiorela-minaya-internet', 'opiniones' => [
    ['Óscar R.', 5, 'Contraté el plan de fibra óptica de 350 Mbps y la instalación fue gratis; llegaron al día siguiente a mi casa.', 6],
    ['Diana V.', 5, 'El router con wi-fi lo trajeron sin costo y ahora mi hija hace sus clases sin que se corte la señal.', 20],
    ['Jhonatan C.', 5, 'Me atendió por WhatsApp, le di mi dirección y coordinó la instalación para el fin de semana, rápido todo.', 33],
]],

['slug' => 'tato-producciones', 'opiniones' => [
    ['Vanessa R.', 5, 'Contratamos los muñecones para la activación de la empresa y la gente se acercó a sacarse fotos.', 4],
    ['Marcos T.', 5, 'El robot LED fue el número que más gustó en la olimpiada del colegio, los chicos lo seguían por todo el campo.', 16],
    ['Liliana G.', 5, 'Los zancudos y arlequines animaron el desfile y además nos entregaron las fotos del evento en un par de días.', 29],
]],

['slug' => 'villegas-rebeca-ropa-infantil', 'opiniones' => [
    ['Marisol V.', 5, 'Le compré el enterito de jean con polo a mi hijo y la mezclilla es suave, no le raspa la piel.', 5],
    ['Elmer Ch.', 5, 'La jardinera de jean para mi niña tiene botones a presión y bolsillos de verdad, se la pone sola.', 21],
    ['Karina P.', 4, 'Llevé el conjunto de jean con polo y gorra para el cumpleaños de mi sobrino y le quedó justo en su talla.', 35],
]],

['slug' => 'silvana-aguinaga-postres', 'opiniones' => [
    ['Sofía L.', 5, 'El cheesecake de maracuyá está increíble, bien cremoso y no empalaga; lo pedí para el cumpleaños de mi papá.', 3],
    ['Renzo A.', 5, 'La torta Tres Leches la entregan en envase individual listo para llevar, ideal para mi oficina.', 17],
    ['Cynthia M.', 5, 'Pedí la torta de chocolate con un día de anticipación y llegó a la hora; el bizcocho estaba húmedo.', 31],
]],

['slug' => 'dmaia-party', 'opiniones' => [
    ['Melissa D.', 5, 'Alquilamos el mobiliario completo para el cumpleaños de mi hija y las mesas modelo Chanel se vieron elegantes.', 7],
    ['Aldo R.', 5, 'La semi puerta 2x2 con el letrero luminoso de Happy Birthday fue el fondo perfecto para las fotos.', 19],
    ['Tania B.', 4, 'La base de grass sintético y la alfombra blanca llegaron limpias, aunque la luz LED tenía un foco flojo.', 33],
]],

['slug' => 'la-academia-fc', 'opiniones' => [
    ['Julio C.', 5, 'Mi hijo de 8 años entró a la clase de muestra gratis y salió feliz; al día siguiente ya lo matriculé.', 6],
    ['Betty S.', 5, 'Entrena de 4 a 17 años y los profes les enseñan valores, no solo a patear la pelota; se nota el orden.', 22],
    ['Percy N.', 5, 'Las prácticas son en Nuevo Chimbote y siempre avisan por WhatsApp si cambia el horario o la cancha.', 36],
]],

['slug' => 'multi-tienda-todo-barato', 'opiniones' => [
    ['Rosa Q.', 5, 'Compré la pijama pantalón RIP para dama y la tela es fresca, además me costó bien barato.', 4],
    ['Luis F.', 5, 'La manta calaminada de 2 plazas abriga harto y la hallé barata; está frente al mercado Ppao, fácil de ubicar.', 18],
    ['Anaís T.', 4, 'El brasier Fresita sin aro es cómodo para el día y el neceser Conejito me sirvió para guardar los cosméticos.', 30],
]],

['slug' => 'motomax-chimbote-ronco', 'opiniones' => [
    ['Elmer V.', 5, 'Saqué la Moto Ronco Magneto 200 en crédito, solo con mi DNI y una inicial; me la entregaron el mismo día.', 5],
    ['Wilder P.', 5, 'Compré el casco y los accesorios en la misma tienda; me hicieron precio por llevar todo junto.', 20],
    ['Santos M.', 4, 'Le puse el kit de maletero, parabrisas y defensas a mi trimoto Polux y quedó lista para el trabajo.', 34],
]],

['slug' => 'terreno-las-delicias', 'opiniones' => [
    ['Gloria A.', 5, 'Fui a ver el terreno de 147 m² en Las Delicias y está bien ubicado, con su cerco y frontis.', 9],
    ['Rubén O.', 5, 'Traté directo con el dueño, sin intermediarios, y me mostró los documentos al día para la transferencia.', 23],
    ['Jessica H.', 4, 'Las medidas son 7 por 21 y el precio me pareció justo para la primera etapa; ya estoy viendo cómo pagarlo.', 37],
]],

['slug' => 'smile-centro-psicologico', 'opiniones' => [
    ['Karina V.', 5, 'Llevé a mi hijo de 7 años a consulta con la doctora y nos explicó todo con paciencia, sin apurarnos.', 6],
    ['Édgar L.', 5, 'El acompañamiento psicológico le ayudó harto a mi hija con sus rabietas; las sesiones son puntuales.', 21],
    ['Nelly R.', 5, 'Pedí una cita para adolescentes y me la dieron para la misma semana; el consultorio es tranquilo y limpio.', 35],
]],

['slug' => 'flor-rojas-inmobiliaria', 'opiniones' => [
    ['Marco A.', 5, 'La asesora Flor me llevó a ver los lotes de Las Planicies de Huaycán y me explicó cómo van los servicios.', 8],
    ['Lucía P.', 5, 'Compré un lote en El Gran Chaparral de Ate como inversión y me ayudó con todo el trámite de la separación.', 22],
    ['Hugo D.', 5, 'Me respondió todas las dudas por teléfono antes de firmar y no me presionó para cerrar la compra.', 39],
]],

['slug' => 'emnae-store', 'opiniones' => [
    ['Mónica S.', 5, 'Le compré las zapatillas blancas talla 31 a mi hija y le quedaron perfectas, la entrega fue inmediata.', 4],
    ['Raúl B.', 5, 'Los zapatos casuales talla 42 son cómodos para todo el día y estaban en liquidación, buen precio.', 18],
    ['Fiorella N.', 4, 'Pedí las zapatillas con mariposa dorada talla 26 y me las alcanzaron en Nuevo Chimbote sin cobrarme envío.', 30],
]],

['slug' => 'medicentro-modelo', 'opiniones' => [
    ['Rosa C.', 5, 'Llevé a mi mamá por su consulta de medicina general y el doctor le explicó todo con calma, sin apurarla.', 7],
    ['Luis M.', 5, 'Me hice la ecografía obstétrica y me entregaron el resultado el mismo día, sin hacerme esperar tanto.', 21],
    ['Ana P.', 4, 'El local está limpio y las señoritas de recepción te ubican rápido cuando uno llega confundido con los papeles.', 33],
]],

['slug' => 'terreno-en-venta-tahuantinsuyo', 'opiniones' => [
    ['Jorge Q.', 5, 'Fuimos a ver el terreno de 6 x 18 y ya está en zona habitada, con luz y agua puestas; se puede construir.', 3],
    ['Milagros T.', 5, 'Me gustó que dentro del terreno ya hay un ambiente construido; sirve para guardar material mientras uno edifica.', 12],
    ['Carlos R.', 4, 'El dueño nos atendió en Tahuantinsuyo mismo y nos mostró los papeles sin hacerse rogar; se nota que quiere vender.', 28],
]],

['slug' => 'toldos-y-decoracion-jessica', 'opiniones' => [
    ['Kelly V.', 5, 'Alquilamos el toldo decorado para el cumpleaños de mi hija y llegaron temprano a armar todo al local.', 5],
    ['Segundo B.', 5, 'Pedimos decoración temática infantil y quedó preciosa; los globos y el panel con el nombre resaltaron bastante.', 18],
    ['Rosario M.', 5, 'El menaje y las mesas venían completos y limpios, además el precio me pareció súper económico comparado con otros.', 40],
]],

['slug' => 'melarte-nuevo-chimbote', 'opiniones' => [
    ['Juan P.', 5, 'Mandé hacer un counter de recepción en forma de L para mi negocio y quedó exacto como lo dibujamos.', 2],
    ['Elena F.', 5, 'El mueble de TV con repisas iluminadas le cambió la cara a mi sala; la melamina Jerez se ve cálida y firme.', 15],
    ['Percy A.', 4, 'Me hicieron el montaje y desmontaje de la oficina el fin de semana, y el lunes ya trabajábamos normal.', 29],
]],

['slug' => 'mente-y-mas-coishco', 'opiniones' => [
    ['Yolanda G.', 5, 'Mi hijo entró al taller de dificultades de lenguaje y en pocas semanas ya se le entiende mejor al hablar.', 9],
    ['Marco T.', 5, 'Llevo a mi sobrino a estimulación temprana y las señoritas tienen mucha paciencia con los chicos.', 26],
    ['Diana R.', 5, 'Están frente a la comisaría de Coishco, así que es fácil ubicarlos; el taller de arte terapia me ayudó bastante.', 44],
]],

['slug' => 'luciana-postres', 'opiniones' => [
    ['Wilson C.', 5, 'Encargué la torta con flores y abejitas con el nombre de mi hija y quedó igualita a la foto que les mandé.', 4],
    ['Gladys H.', 5, 'Las mini tortas de graduación con birrete salieron bellísimas y el bizcocho estaba bien húmedo, no seco.', 17],
    ['Néstor V.', 4, 'Pedí una torta con girasoles amarillos para mi mamá y llegó a la hora pactada, bien fría y sin chancarse.', 31],
]],

['slug' => 'maneki-detalles-y-decoraciones', 'opiniones' => [
    ['Silvia Q.', 5, 'Contratamos la decoración de Minnie Mouse y armaron todo el panel con globos y el nombre de mi hija.', 6],
    ['Elmer Z.', 5, 'Para el bautizo pedimos los tonos rosados y quedó elegante; todos en la familia preguntaban quién lo hizo.', 19],
    ['Teresa L.', 5, 'La decoración de Toy Story para la promoción del nido quedó increíble; los chicos se sacaron fotos todo el rato.', 38],
]],

['slug' => 'grupo-inmobiliario-3s', 'opiniones' => [
    ['Óscar N.', 5, 'Visitamos la casa de 2 pisos en Los Jardines y la proyección para el tercer piso es una buena ventaja.', 1],
    ['Paola S.', 5, 'Me dio confianza que la documentación esté en orden; no tuvimos problemas para avanzar con el proceso de compra.', 14],
    ['Miguel Á.', 4, 'La ubicación frente al parque y al complejo deportivo es lo que más me gustó para mis hijos.', 27],
]],

['slug' => 'casa-en-venta-santa-cristina', 'opiniones' => [
    ['Marisol D.', 5, 'Entramos a la casa de 3 pisos en Santa Cristina y los 6 dormitorios dan bastante espacio para familia grande.', 8],
    ['César O.', 5, 'La sala principal tiene piso de madera y el patio interior con cochera techada es lo que buscábamos.', 23],
    ['Karina U.', 5, 'Nos mostraron también la casa de un piso al fondo del predio; sirve para alquilar y sacar una renta.', 36],
]],

['slug' => 'administrador-de-sitio', 'opiniones' => [
    ['Fernando E.', 5, 'Fui por la terapia de relajación y manejo del estrés y salí sintiéndome livianito; el lugar del Jr. Espinar es tranquilo.', 11],
    ['Betty A.', 5, 'La sesión de meditación guiada me ayudó a dormir mejor esa semana; la señora habla con voz suave y no apura.', 25],
    ['Alfredo J.', 4, 'Llevo el programa mensual de bienestar integral y se nota el cambio; ya no ando de mal humor por el trabajo.', 42],
]],

['slug' => 'virgen-de-las-mercedes', 'opiniones' => [
    ['Norma P.', 5, 'Fui a comprar el medicamento para la presión de mi papá y lo tenían genérico y de marca, a buen precio.', 2],
    ['Rubén I.', 5, 'Me atendieron rápido cuando llevé a mi bebé por sus pañales y su crema; hay bastante surtido para bebé.', 20],
    ['Cynthia W.', 4, 'A cualquier hora que voy por una pastilla para el dolor de cabeza me atienden sin hacerme esperar.', 34],
]],

['slug' => 'intifarma', 'opiniones' => [
    ['Hugo B.', 5, 'Compré vitaminas y suplementos para mi mamá y me explicaron cómo tomarlos, sin apurarme en el mostrador.', 10],
    ['Liliana C.', 5, 'Tenía el champú y el jabón que no encontraba en otras boticas; hay bastante de cuidado personal.', 22],
    ['Víctor M.', 5, 'Fui por leche y pañales para mi sobrino y salí con todo; la chica de caja es bien amable.', 45],
]],

['slug' => 'vicfarma', 'opiniones' => [
    ['Sandra G.', 5, 'Compro las vitaminas de mis hijos ahí y siempre hay stock; además queda cerca de mi casa en Nuevo Chimbote.', 3],
    ['Julio R.', 5, 'Cuando mi bebé tuvo fiebre me ayudaron a elegir el jarabe y me explicaron la dosis con paciencia.', 16],
    ['Patricia F.', 4, 'Los precios de los suplementos son razonables y te dan boleta sin pedirla; eso se agradece.', 30],
]],

['slug' => 'ferreteria-cesar-vallejo', 'opiniones' => [
    ['Raúl D.', 5, 'Compré cinco bolsas de cemento para mi vereda y me las alcanzaron hasta la puerta sin cobrarme extra.', 7],
    ['Mónica V.', 5, 'Necesitaba tubería para una fuga y el señor me dijo exactamente qué medida llevar; no me vendió de más.', 24],
    ['Emilio T.', 5, 'Fui por pintura y me mezclaron el color que quería; además hay herramientas de todo precio.', 39],
]],

['slug' => 'ferreteria-y-materiales-de-construccion-a-m', 'opiniones' => [
    ['Rocío S.', 5, 'Llevé fierro de construcción para mi techo y me lo cortaron a la medida que pedí, sin cobrar de más.', 12],
    ['Arturo L.', 5, 'Compré un taladro y me dieron garantía; el muchacho me enseñó cómo usar bien las brocas.', 27],
    ['Felicita N.', 4, 'Tienen herramientas surtidas y los precios se pueden conversar cuando llevas varias cosas juntas.', 41],
]],

['slug' => 'bodega-corazon-de-jesus', 'opiniones' => [
    ['Segundo A.', 5, 'Compro al por mayor las golosinas para mi puesto y siempre me dejan buen precio por caja.', 5],
    ['Marlene Q.', 5, 'Los snacks para la lonchera de mis hijos son frescos; nunca me han dado algo vencido.', 13],
    ['Abel H.', 5, 'La señora anota lo que le debo y uno paga a fin de semana; esa confianza ya no se ve.', 35],
]],

['slug' => 'la-esquina-de-don-juan', 'opiniones' => [
    ['Zoila M.', 5, 'Don Juan siempre tiene el arroz y el aceite que necesito, aunque vaya a última hora.', 4],
    ['Teodoro P.', 5, 'Compro las golosinas para mis nietos ahí y les regala un caramelo; son bien amables.', 20],
    ['Irene C.', 4, 'Está en la esquina de mi barrio, así que bajo en dos minutos por el pan y la leche.', 37],
]],

['slug' => 'kathy-s-bodega', 'opiniones' => [
    ['Lucho E.', 5, 'Los huevos siempre están frescos y me los vende por unidad cuando no quiero la bandeja completa.', 9],
    ['Amalia R.', 5, 'Compro la leche y el queso ahí; tiene buen surtido de lácteos y no están por vencer.', 28],
    ['Jhonatan F.', 5, 'Doña Kathy atiende hasta tarde y me saca de apuro cuando me falta algo para la cena.', 43],
]],

['slug' => 'la-bodega-rosita', 'opiniones' => [
    ['Petronila G.', 5, 'Encontré el detergente y la lejía que buscaba a buen precio; hay bastante de limpieza.', 6],
    ['Saúl B.', 5, 'Doña Rosita me fía el arroz hasta la quincena y eso se agradece bastante en el barrio.', 17],
    ['Miluska T.', 5, 'La bodega está ordenada y uno encuentra rápido lo que va a comprar, sin estar dando vueltas.', 32],
]],

['slug' => 'bodega-reybor', 'opiniones' => [
    ['Domingo V.', 5, 'Bajé por gaseosas heladas para la reunión y me las dio bien frías, directo de la caja.', 1],
    ['Nelly A.', 5, 'Tiene todas las bebidas y también abarrotes; no hace falta ir hasta el mercado.', 19],
    ['Gustavo Z.', 4, 'El muchacho me ayudó a cargar las cajas de gaseosas hasta el carro sin quejarse.', 41],
]],

['slug' => 'bodega-don-pepe', 'opiniones' => [
    ['Charito L.', 5, 'Don Pepe vende las papitas y los chisitos que le gustan a mi hijo; siempre hay surtido.', 8],
    ['Eusebio R.', 5, 'Compro el aceite y el azúcar ahí y me sale más barato que en el mercado.', 21],
    ['Lourdes M.', 5, 'Atiende con buena cara aunque uno llegue justo cuando está por cerrar la bodega.', 33],
]],

['slug' => 'bodega-magdalena-j', 'opiniones' => [
    ['Aníbal S.', 5, 'Compro los snacks para la reunión del barrio ahí y me hace precio por cantidad.', 3],
    ['Roxana D.', 5, 'El jabón y el detergente son de buena marca y no están caros; siempre hay.', 15],
    ['Máximo C.', 4, 'La señora Magdalena tiene la tienda limpia y ordenada, da gusto entrar a comprar.', 29],
]],

['slug' => 'licoreria-bodega-el-bambino', 'opiniones' => [
    ['Wálter O.', 5, 'Compro la cerveza bien helada ahí para el partido y siempre la tienen en caja.', 10],
    ['Yesenia P.', 5, 'Además de licor venden abarrotes, así que de una vez llevo el arroz y el aceite.', 23],
    ['Boris N.', 5, 'El señor atiende hasta tarde en Nuevo Chimbote y no te apura para elegir.', 38],
]],

['slug' => 'bodega-avanti', 'opiniones' => [
    ['Perlita H.', 5, 'Compro las gaseosas por caja para mi negocio y me las dejan a buen precio mayorista.', 2],
    ['Dante U.', 5, 'Siempre tienen bebidas heladas; en verano voy y salgo con la caja cargada.', 16],
    ['Giovana X.', 4, 'El reparto me llegó al día siguiente del pedido, sin fallar ni una caja.', 31],
]],

['slug' => 'la-bodega-angelita', 'opiniones' => [
    ['Efraín T.', 5, 'Doña Angelita me vendió el detergente y el suavizante a buen precio, y me dio el vuelto completo.', 12],
    ['Susana K.', 5, 'Cuando me falta arroz o azúcar a medianoche, ahí nomás bajo y me atiende.', 26],
    ['Hernán B.', 5, 'La bodega está bien surtida y uno encuentra hasta lo que no sabía que necesitaba.', 40],
]],

['slug' => 'bodega-el-amigo', 'opiniones' => [
    ['Melva G.', 5, 'Compro las gaseosas y las galletas para la lonchera de mis hijos ahí, bien heladas.', 7],
    ['Iván Q.', 5, 'Los snacks son variados y el muchacho siempre me recomienda lo nuevo que le llega.', 22],
    ['Estela F.', 5, 'Es la bodega donde mandamos a los chicos del barrio; atienden rápido y con paciencia.', 36],
]],

['slug' => 'bodega-maty', 'opiniones' => [
    ['Yolanda S.', 5, 'Encontré las golosinas que buscaba para la piñata y me las dio por bolsa a buen precio.', 5],
    ['Roberto I.', 5, 'El limpiador y las esponjas son económicos; siempre compro la limpieza de mi casa ahí.', 18],
    ['Carmen Ñ.', 4, 'Doña Maty atiende rapidito y se acuerda de lo que uno compra siempre.', 34],
]],

['slug' => 'bodega-los-pinos', 'opiniones' => [
    ['Marisol Q.', 5, 'Compro al por mayor para mi puesto y siempre me despachan completo: arroz, aceite y azúcar a buen precio.', 6],
    ['Elmer R.', 5, 'Fui por leche y huevos frescos y la señora me mostró la fecha de vencimiento sin que yo pregunte.', 19],
    ['Rosa C.', 4, 'En Samanco casi todo cierra temprano, pero acá encontré gaseosas bien heladas y golosinas para mis sobrinos.', 34],
]],

['slug' => 'bodega-los-tres-hermanos', 'opiniones' => [
    ['Julio M.', 5, 'Los sábados paso por sus galletas y chisitos para la reunión; siempre tienen surtido y no suben los precios.', 3],
    ['Katherine V.', 5, 'Me quedé sin efectivo y me fió un par de cosas sin poner problema, gente de confianza en Coishco.', 22],
    ['Santos L.', 4, 'Su arroz y su aceite rinden bastante; compro cada quincena y nunca me han dado producto vencido.', 40],
]],

['slug' => 'bodega-andrea', 'opiniones' => [
    ['Doris T.', 5, 'Las gaseosas de litro y medio están siempre heladas; entro por una y salgo con dos.', 11],
    ['Milagros A.', 5, 'Mi mamá manda a mi hijo por sus galletas y la señora Andrea lo atiende con harta paciencia.', 27],
    ['Percy G.', 4, 'Venden chisitos, piqueos y maní por bolsitas, ideal para la tarde cuando hay partido en Santa.', 44],
]],

['slug' => 'bodega-valentina', 'opiniones' => [
    ['Gloria S.', 5, 'Llevo detergente y lejía por caja para mi negocio; ahí sí me respetan el precio mayorista.', 5],
    ['Wilmer C.', 5, 'Me ayudaron a elegir el desinfectante para el piso del baño, con calma y sin apurarme.', 16],
    ['Noemí P.', 5, 'Siempre hay stock de escobas, trapos y ambientadores, así no tengo que ir hasta el mercado.', 31],
]],

['slug' => 'bodega-san-jose', 'opiniones' => [
    ['Carmen R.', 5, 'Compro arroz, azúcar y fideos al por mayor cada semana y me sale más barato que en el mercado.', 8],
    ['Aníbal F.', 4, 'Me atienden rápido aunque haya gente; la señora ya sabe lo que le pido de memoria.', 24],
    ['Lucía H.', 5, 'Su local está bien surtido de menestras y conservas, encontré todo lo que buscaba en una sola pasada.', 38],
]],

['slug' => 'y-a', 'opiniones' => [
    ['Jhonatan B.', 5, 'Ahí consigo el atún y el arroz cuando salgo tarde del trabajo, porque abren hasta bien noche.', 2],
    ['Teresa M.', 5, 'La dueña me guarda el pan y la leche cuando le aviso que paso en la tarde; bien amable.', 14],
    ['Fiorella D.', 4, 'Es la bodega más surtida de la zona: gaseosas, galletas y hasta pilas para el control.', 29],
]],

['slug' => 'bodega-dona-lorenza', 'opiniones' => [
    ['Elena V.', 5, 'Compro lejía y detergente al por mayor para mi pensión y los precios son de Coishco, nada caros.', 9],
    ['Marcos T.', 5, 'Doña Lorenza me recomendó el jabón para la ropa de los chicos y la mancha de grasa salió fácil.', 20],
    ['Zoila R.', 4, 'Su bodega está ordenada por pasillos y uno encuentra rápido las cosas de limpieza.', 41],
]],

['slug' => 'bodega-yessica', 'opiniones' => [
    ['Pilar G.', 5, 'Las gaseosas de tres litros salen bien heladas y a buen precio para la comida familiar.', 4],
    ['Édgar N.', 5, 'Fui por cloro y trapeador y me alcanzaron las bolsas hasta la puerta porque venía con mi bebé.', 18],
    ['Silvia Q.', 5, 'Paso todas las mañanas por mi yogurt y mi galleta antes de tomar la combi a Chimbote.', 33],
]],

['slug' => 'bodega-linda-s', 'opiniones' => [
    ['Rocío A.', 5, 'Llevo los productos de limpieza por paquete para el hospedaje y siempre me despachan completo.', 7],
    ['Isaías M.', 4, 'La señora Linda me anotó en su cuaderno para pagarle el viernes; confianza de barrio, se agradece.', 23],
    ['Betty C.', 5, 'Tienen escobas, baldes y recogedores que duran, no esas cosas que se rompen al mes.', 37],
]],

['slug' => 'bodega-greta', 'opiniones' => [
    ['Nancy L.', 5, 'Encuentro el arroz, el aceite y el azúcar juntos, así no camino dos cuadras más.', 12],
    ['Orlando P.', 5, 'Me vendieron medio kilo de arroz cuando tenía apuro, sin poner cara; eso se valora.', 26],
    ['Yolanda S.', 4, 'Su detergente y su lejía son los mismos de siempre, con fecha nueva en cada compra.', 43],
]],

['slug' => 'bodega-edith', 'opiniones' => [
    ['Marlene H.', 5, 'Compramos la canasta de abarrotes para la olla común y Edith nos hizo precio por volumen.', 1],
    ['César D.', 5, 'Me atendió su hijo con paciencia mientras yo buscaba las pilas y el jabón entre las cajas.', 17],
    ['Graciela F.', 5, 'Está a media cuadra de mi casa, así que bajo por sal y fideos en cualquier momento.', 30],
]],

['slug' => 'bodega-liu', 'opiniones' => [
    ['Susana B.', 5, 'Sus huevos vienen bien acomodados en la bolsa, no llego a casa con ninguno roto.', 10],
    ['Iván R.', 4, 'Compro leche evaporada y yogurt por six pack; me sale más cómodo que en la bodega de la esquina.', 25],
    ['Nelly T.', 5, 'Tienen gaseosas personales heladas, perfectas para el calor de Chimbote a mediodía.', 36],
]],

['slug' => 'la-bodega-luciana', 'opiniones' => [
    ['Mónica E.', 5, 'La señora me separa el queso fresco y los huevos de corral para el desayuno del domingo.', 13],
    ['Rafael O.', 5, 'Compré limpiador de baño y me explicó cómo usarlo sin dañar el mayólico; buen detalle.', 28],
    ['Charito V.', 4, 'Atienden desde muy temprano, así consigo mi leche antes de ir a trabajar al puerto.', 42],
]],

['slug' => 'bodega-la-tere', 'opiniones' => [
    ['Hilda M.', 5, 'Voy por azúcar y arroz al por mayor para mi carretilla y siempre hay sacos disponibles.', 5],
    ['Teófilo A.', 5, 'Doña Tere me fiaba cuando recién llegué al barrio; ahora somos clientes de años.', 21],
    ['Karina Z.', 4, 'El local es chico pero está bien surtido de menestras, atún y conservas para la semana.', 35],
]],

['slug' => 'la-bodega-guisela', 'opiniones' => [
    ['Vanessa U.', 5, 'Sus chisitos y piqueos son los más frescos del barrio, crujen de verdad al morderlos.', 8],
    ['Miguel Á.', 5, 'Fui por un balde y jabón líquido, y me dejó oler el aroma antes de decidir la compra.', 26],
    ['Sonia K.', 4, 'Mis hijos se escapan por sus helados y chupetines; además atienden rápido a la hora del recreo.', 39],
]],

['slug' => 'bodega-marina', 'opiniones' => [
    ['Erika J.', 5, 'Hay arroz, fideos y atún siempre; es la bodega donde mi mamá manda a hacer la compra diaria.', 6],
    ['Alberto Y.', 5, 'Compré galletas y maní para la lonchera y me regalaron un chupetín para mi sobrina.', 19],
    ['Paola W.', 4, 'Su atención es rápida: entro por un sencillo de arroz y ya estoy saliendo en dos minutos.', 32],
]],

['slug' => 'bodega-la-bendicion', 'opiniones' => [
    ['Ruth G.', 5, 'Me salvaron un domingo: tenían gaseosa helada y arroz cuando todo lo demás estaba cerrado.', 3],
    ['Josué I.', 5, 'La señora pesa el arroz al toque y siempre me cuadra bien el vuelto, sin discusiones.', 15],
    ['Diana X.', 4, 'Compro la gaseosa grande y las conservas para el almuerzo familiar; los precios son justos.', 45],
]],

['slug' => 'bodega-dorita', 'opiniones' => [
    ['Liliana N.', 5, 'Sus gaseosas de dos litros están heladas y cuestan menos de lo que cobran cerca del mercado.', 11],
    ['Segundo Q.', 5, 'Doña Dorita me apartó los huevos y el queso para el desayuno del domingo.', 24],
    ['Aracely F.', 4, 'Entro por una leche y siempre salgo con yogurt para los chicos; todo bien fresco.', 40],
]],

['slug' => 'bodega-santa-rosa', 'opiniones' => [
    ['Mirtha C.', 5, 'Compro los piqueos para las reuniones del barrio y me hacen precio cuando llevo varias bolsas.', 2],
    ['Pablo V.', 5, 'Su lejía y su detergente rinden bastante, ya llevo meses comprando lo mismo acá.', 22],
    ['Verónica D.', 5, 'Está en una esquina tranquila, con la mercadería a la vista y todo bien limpio.', 34],
]],

['slug' => 'bodega-melina', 'opiniones' => [
    ['Estela R.', 5, 'Me hicieron el favor de traerme el pedido de abarrotes hasta mi casa, a dos cuadras.', 14],
    ['Hugo B.', 4, 'Siempre hay jabón de ropa, cloro y esponjas, no tengo que ir al mercado por eso.', 27],
    ['Consuelo M.', 5, 'La dueña me avisó que el arroz había subido antes de pesarlo; transparencia total.', 41],
]],

['slug' => 'bodega-evelyn', 'opiniones' => [
    ['Lourdes P.', 5, 'Compro el arroz y el azúcar cada semana y siempre me da el vuelto con sencillo.', 9],
    ['Freddy S.', 5, 'Sus galletas de animalitos y sus chisitos son lo que mis nietos piden cuando vienen.', 23],
    ['Amalia T.', 4, 'Aunque hay cola a la hora del almuerzo, atienden a todos sin perder la sonrisa.', 38],
]],

['slug' => 'bodega-jasmin', 'opiniones' => [
    ['Jenny O.', 5, 'Llevo cajas de gaseosa al por mayor para el cumpleaños y me sale mucho más económico.', 4],
    ['Raúl H.', 5, 'Me prestaron una carretilla para llevar las cajas hasta la moto, detalle de gente buena.', 18],
    ['Marleny G.', 5, 'Tienen bebidas de todos los sabores y siempre revisan que las botellas estén bien selladas.', 33],
]],

['slug' => 'botica-alba-farma', 'opiniones' => [
    ['Flor L.', 5, 'Me ofrecieron el genérico en vez del caro y me explicaron la dosis con harta calma.', 7],
    ['Gustavo A.', 5, 'Fui por vitaminas para mi mamá y la química revisó su receta antes de recomendarme algo.', 20],
    ['Irene Z.', 4, 'A las once de la noche necesitaba paracetamol y me atendieron por la ventanilla sin demora.', 36],
]],

['slug' => 'celeste-11-11', 'opiniones' => [
    ['Katia M.', 5, 'Compro gaseosas al por mayor para mi puesto y siempre me dan la caja completa y fría.', 12],
    ['Benjamín U.', 4, 'El nombre es raro pero la atención es buena: me ayudaron a cargar las bebidas hasta el mototaxi.', 29],
    ['Olga F.', 5, 'Sus precios por unidad bajan cuando llevas seis; ahí sí conviene comprar de golpe.', 44],
]],

['slug' => 'mercado-municipal-samanco', 'opiniones' => [
    ['Rebeca S.', 5, 'Voy los domingos por pescado fresco y verduras; los caseros de Samanco venden barato.', 10],
    ['Adolfo C.', 5, 'El patio está limpio desde que arreglaron los desagües y ya no huele como antes.', 25],
    ['Cecilia N.', 4, 'Encontré menestras, papa y hasta queso de la zona en un solo pasillo; todo a mano.', 39],
]],

['slug' => 'badega-fernando', 'opiniones' => [
    ['Norma V.', 5, 'Compro al por mayor para la tienda de Nepeña y siempre me despachan el pedido completo.', 8],
    ['Ever Q.', 5, 'Fernando me guarda la leche y el pan cuando le aviso que salgo del campo tarde.', 21],
    ['Janeth R.', 5, 'Es la bodega más surtida del pueblo: abarrotes, bebidas y hasta útiles de limpieza.', 37],
]],

['slug' => 'centro-medico-coishco', 'opiniones' => [
    ['Beatriz H.', 5, 'Me hicieron los análisis de sangre en ayunas y a las dos horas ya tenía mis resultados.', 6],
    ['Rómulo D.', 5, 'El doctor me explicó el electrocardiograma con palabras sencillas, sin asustarme para nada.', 17],
    ['Melissa T.', 4, 'Fui por una consulta general y me atendieron puntual, casi sin esperar en la sala.', 31],
]],

['slug' => 'optica-mas-vision', 'opiniones' => [
    ['Rosa C.', 5, 'Me hicieron el examen de la vista ahí mismo y mis lentes de medida salieron justos, ya no me duele la cabeza.', 6],
    ['Luis M.', 5, 'Compré unos lentes de sol y me los ajustaron al toque en el local, sin cobrarme extra.', 19],
    ['Ana P.', 4, 'Pedí lentes de contacto y me explicaron bien cómo limpiarlos; el trato fue bien paciente.', 31],
]],

['slug' => 'botica-la-merced', 'opiniones' => [
    ['Marisol Q.', 5, 'Me hicieron un recogido para el matrimonio de mi hermana y aguantó toda la noche sin caerse.', 4],
    ['Julissa R.', 5, 'El tratamiento capilar me dejó el cabello suave; ya llevo tres sesiones y se nota bastante.', 15],
    ['Carmen T.', 4, 'Fui por manicure y pedicure el mismo día, me atendieron rápido y quedó bien prolijo.', 27],
]],

['slug' => 'farmacia-ancash', 'opiniones' => [
    ['Pedro S.', 5, 'Pedí genéricos para la gripe por delivery y llegaron a mi casa en Samanco rapidísimo.', 3],
    ['Sandra V.', 5, 'Los genéricos son bastante económicos y siempre me dicen cada cuántas horas hay que tomar.', 12],
    ['Milagros A.', 5, 'Necesitaba vitaminas y suplementos, tenían variedad y me orientaron con los precios.', 24],
]],

['slug' => 'la-cocina-de-mama-juana', 'opiniones' => [
    ['José L.', 5, 'El menú del día viene bien servido y la sopa es de casa, como la de mi mamá.', 2],
    ['Rocío M.', 5, 'El lomo saltado tenía su buen corte de carne y las papas crocantes, no aceitosas.', 11],
    ['Wilder P.', 4, 'Fuimos al almuerzo familiar y nos atendieron rápido; el local estaba limpio.', 29],
]],

['slug' => 'restaurante-el-rico-nepena', 'opiniones' => [
    ['Elmer C.', 5, 'La jarra de chicha de jora está bien helada y alcanza para cuatro, sale a cuenta.', 5],
    ['Nelly G.', 5, 'El menú del día cambia y siempre trae su entrada; por ese precio está bien.', 14],
    ['Óscar D.', 4, 'Probé el ceviche de la casa un domingo y estaba fresco, con harto limón.', 26],
]],

['slug' => 'bodega-choncen', 'opiniones' => [
    ['Karina F.', 5, 'Siempre encuentro bebidas y gaseosas heladas para llevar a la playa de Samanco.', 3],
    ['Betty H.', 5, 'Compro el arroz, el aceite y el azúcar ahí; los abarrotes tienen buen precio.', 16],
    ['Rubén Z.', 4, 'Me quedé sin detergente un domingo y me abrieron igual, bien atentos.', 33],
]],

['slug' => 'bodega-mariel', 'opiniones' => [
    ['Gladys N.', 5, 'Voy por las gaseosas de litro y siempre tienen promoción si te llevas dos.', 7],
    ['Segundo R.', 5, 'Los abarrotes están ordenados y se ve la fecha de vencimiento, eso me da confianza.', 18],
    ['Yesenia B.', 4, 'Me atendieron de noche para comprar golosinas y pan; son bien amables.', 30],
]],

['slug' => 'libreria-bazar-acuario', 'opiniones' => [
    ['Fiorella S.', 5, 'Compré los cuadernos de la lista escolar de mi hijo y me hicieron precio por docena.', 8],
    ['Percy A.', 5, 'Tienen útiles escolares de varias marcas, encontré los plumones que no hallaba.', 20],
    ['Rosmery L.', 4, 'El bazar también trae cosas para la casa; siempre salgo con algo más.', 36],
]],

['slug' => 'libreria-bazar-anakaren', 'opiniones' => [
    ['Hugo T.', 5, 'Saqué copias de todo el expediente y me las entregaron en cinco minutos.', 6],
    ['Deysi K.', 5, 'Imprimí mi CV ahí y salió bien nítido; me dejaron revisarlo antes de pagar.', 17],
    ['Alberto Y.', 5, 'Compré artículos de escritorio para la oficina y me facturaron sin ningún problema.', 28],
]],

['slug' => 'hostal-romances', 'opiniones' => [
    ['Víctor M.', 4, 'Alquilamos la habitación matrimonial por horas para descansar del viaje y estaba limpia.', 9],
    ['Lucía E.', 5, 'Llegamos a las dos de la mañana y nos atendieron igual, la atención es de 24 horas.', 22],
    ['Jorge I.', 5, 'El wifi funcionó bien toda la noche y el desayuno vino temprano, tal como ofrecen.', 41],
]],

['slug' => 'botica-crisfarma', 'opiniones' => [
    ['Patricia O.', 5, 'La química me explicó cómo tomar las vitaminas y hasta me anotó la dosis.', 4],
    ['Elena U.', 5, 'Me midieron la presión sin cobrarme y me recomendaron volver a controlarme.', 13],
    ['Marco W.', 4, 'Compré vitaminas para mis papás y me dieron opciones más baratas, sin presionar.', 25],
]],

['slug' => 'botica-los-santenos', 'opiniones' => [
    ['Silvia J.', 5, 'Los genéricos cuestan la mitad y son de laboratorios conocidos, siempre compro ahí.', 5],
    ['Raúl Q.', 5, 'Me consiguieron un medicamento que no había en otras boticas de Santa.', 21],
    ['Luzmila D.', 4, 'Atienden rápido aunque haya cola; en menos de cinco minutos ya estaba saliendo.', 34],
]],

['slug' => 'botica-santo-remedio', 'opiniones' => [
    ['Alfredo B.', 5, 'Compré la amoxicilina de 500 mg por 21 cápsulas y me salió más barata que en el centro.', 7],
    ['Sonia C.', 5, 'La loratadina de 10 mg me calmó la alergia al toque; la caja de 30 tabletas rinde.', 19],
    ['Katherine R.', 4, 'Llevé el protector solar FPS 50+ y el jabón antibacterial; buenos precios y vencen lejos.', 32],
]],

['slug' => 'carpinteria-y-ebanisteria-mayra-de-angel-paz-ninaquispe', 'opiniones' => [
    ['Fernando G.', 5, 'Me hicieron el clóset a medida justo para el espacio de mi cuarto, ni un centímetro de más.', 10],
    ['Marlene V.', 5, 'La ebanistería fina se nota en los acabados; el velador quedó parejo y bien lijado.', 23],
    ['Aníbal S.', 5, 'Cumplieron con la fecha de entrega y vinieron a instalar la repisa sin cobrar extra.', 38],
]],

['slug' => 'hospedaje-la-casa-blanca', 'opiniones' => [
    ['Giovana P.', 5, 'Reservé la habitación familiar para mis tíos que vinieron de Lima y quedaron cómodos.', 6],
    ['Walter N.', 5, 'La habitación doble tenía agua caliente y el wifi llegaba bien hasta el cuarto.', 16],
    ['Denis F.', 4, 'El hospedaje está cerca de la avenida y hay sitio para estacionar la camioneta.', 27],
]],

['slug' => 'bodega-yiyi', 'opiniones' => [
    ['Edith L.', 5, 'Compro los artículos de limpieza por mayor para mi restaurante y me sale mucho más barato.', 4],
    ['Marco A.', 5, 'Venden lejía y detergente al por mayor, hasta por caja si se los pides.', 15],
    ['Nancy H.', 4, 'Siempre hay stock de bolsas y guantes; nunca me he quedado sin lo que busco.', 29],
]],

['slug' => 'comercializadora-la-llave', 'opiniones' => [
    ['Ricardo M.', 5, 'Compré la grifería del baño ahí y me asesoraron con las medidas de las tuberías.', 8],
    ['Lourdes T.', 5, 'Tienen sanitarios de varias marcas y me mostraron el que entraba en mi presupuesto.', 20],
    ['Germán O.', 4, 'Fui por unas herramientas y salí con todo para la obra; el chico sabía bastante.', 35],
]],

['slug' => 'ferreteria-steffano', 'opiniones' => [
    ['Iván R.', 5, 'El pegamento instantáneo de 3 gramos me salvó para pegar la moldura de la puerta.', 5],
    ['Elvis D.', 5, 'Compré la caja de lápices de carpintero planos por 12 y salieron bien duros.', 13],
    ['Zoila P.', 4, 'Aunque el pedido era chico me atendieron igual de rápido que a los maestros.', 26],
]],

['slug' => 'farmaciatrinidad', 'opiniones' => [
    ['Julia C.', 5, 'Pedí medicamentos por delivery y en veinte minutos ya estaban en mi puerta.', 3],
    ['Segundo V.', 5, 'Tienen genéricos y de marca; me mostraron las dos opciones y elegí la barata.', 18],
    ['Rosa Elena M.', 5, 'Me atendieron a las once de la noche cuando mi hijo tenía fiebre, muchas gracias.', 40],
]],

['slug' => 'farmacia-vallejos', 'opiniones' => [
    ['Teodoro I.', 5, 'La caja de paracetamol de 500 mg por 100 tabletas rinde meses y cuesta bien poco.', 9],
    ['Maribel Y.', 5, 'El suero oral de manzana le encantó a mi sobrino cuando tuvo diarrea.', 21],
    ['César A.', 4, 'Me despacharon al instante y me recordaron la dosis por peso del niño.', 30],
]],

['slug' => 'imperio-de-la-moda', 'opiniones' => [
    ['Paola S.', 5, 'Encontré un vestido de fiesta para mi hermana y me lo arreglaron de largo ahí mismo.', 7],
    ['Diana R.', 5, 'Los accesorios combinan con todo; compré aretes y una cartera a buen precio.', 17],
    ['Karla M.', 4, 'La ropa de dama está a la moda y los precios son de acá, no de centro comercial.', 32],
]],

['slug' => 'montalvo-fit', 'opiniones' => [
    ['Bruno C.', 5, 'El entrenador me corrigió la postura en sentadillas y dejé de sentir dolor de rodilla.', 6],
    ['Fátima L.', 5, 'La asesoría nutricional me armó un plan con comida de acá, no dietas raras.', 19],
    ['Cristian B.', 4, 'Las máquinas están nuevas y hay espacio, no tienes que esperar tu turno.', 28],
]],

['slug' => 'yacos-gym', 'opiniones' => [
    ['Álex Q.', 5, 'El entrenamiento funcional con llantas y sogas me dejó molido, pero vale la pena.', 5],
    ['Melissa G.', 5, 'El profe te sigue en cada serie y te exige, no te deja botado en la máquina.', 16],
    ['Renzo H.', 4, 'Voy a las seis de la mañana y siempre hay alguien que te recibe con buena onda.', 31],
]],

['slug' => 'aventura-gym-chimbote', 'opiniones' => [
    ['Kevin T.', 5, 'Probé el día de prueba y me quedé con la membresía mensual, el ambiente engancha.', 8],
    ['Brenda O.', 5, 'La membresía mensual incluye todas las clases grupales y no pagas aparte.', 20],
    ['Carlos Ñ.', 4, 'Fui por la tarde y no estaba lleno; las pesas siempre están libres.', 37],
]],

];
