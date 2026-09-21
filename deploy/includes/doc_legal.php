<?php
/**
 * includes/doc_legal.php — 📚 LA DOCUMENTACIÓN OFICIAL DEL SITIO (texto y estructura)
 * ==================================================================================
 * Aquí vive, UNA SOLA VEZ, el texto íntegro de los tres documentos oficiales de
 * DeChimbote.com. De esta misma fuente se sirven:
 *
 *   · la versión web   → `includes/doc_vista.php` (las páginas `/privacidad`,
 *                        `/preguntas-frecuentes` y `/manual-de-uso`);
 *   · la versión PDF   → `includes/pdf_simple.php` + `documento_pdf.php`
 *                        (`/documentos/<documento>.pdf`, descarga directa).
 *
 * 🔴 REGISTRO Y TONO (orden del jefe, 2026-09-21): redacción **formal, impersonal y estructurada**,
 *    como la de un despacho jurídico de primer nivel. Reglas de estilo que se respetan en TODO el
 *    texto de este archivo:
 *      · **No se usa la primera persona** («nosotros», «te», «escríbenos»): se emplean los sujetos
 *        definidos —**el Prestador**, **el Usuario**, **el Anunciante**— y la voz impersonal
 *        («se informa», «podrá», «deberá», «se entenderá»).
 *      · **Sin emojis, sin signos de admiración y sin coloquialismos.**
 *      · Cada materia va en su **capítulo**, cada regla en su **cláusula numerada** y cada cláusula
 *        con su **título propio** (así el índice de la versión web funciona como submenú).
 *      · **Precisión léxica:** se elige el verbo exacto de la acción (publicar, difundir, retirar,
 *        suprimir, extraer, descargar, adecuar, consignar, acreditar) y no un sinónimo aproximado.
 *
 * ⚠️ AL MODIFICAR EL TEXTO: se sube la `version` y la `vigencia` del documento (van escritas a mano,
 *    nunca con `date()`: una fecha que siempre dice «hoy» no informa nada) y se vuelven a subir los
 *    archivos que correspondan. Los tres documentos se numeran de forma independiente.
 *
 * Estructura de cada documento:
 *   [
 *     'id'       => identificador y nombre del PDF,
 *     'tipo'     => 'politicas' | 'manual' | 'faq'  (lo usa el render para el rótulo de los ítems),
 *     'titulo'   => título oficial,
 *     'subtitulo'=> línea de naturaleza del documento,
 *     'version' / 'vigencia' / 'actualizado',
 *     'preambulo'=> párrafo de apertura,
 *     'resumen'  => ['…'] (resumen ejecutivo, en viñetas),
 *     'capitulos'=> [ ['id'=>ancla, 'titulo'=>'Capítulo I. …', 'clausulas'=>[ … ]] ],
 *     'anexos'   => [ ['titulo'=>'…', 'p'=>['…'], 'ul'=>['…']] ],
 *   ]
 * Cada cláusula (o pregunta, o apartado):
 *   ['n' => '1.1', 'ti' => 'Título de la cláusula', 'p' => ['párrafo', 'párrafo'], 'ul' => ['…'],
 *    'av' => 'texto de aviso destacado', 'ol' => ['paso 1', 'paso 2']]
 */

if (!function_exists('doc_legal_textos')) {

    /** Los tres documentos oficiales, indexados por su identificador. */
    function doc_legal_textos() {
        static $docs = null;
        if ($docs !== null) return $docs;

        $docs = [];

        // =================================================================================
        // I. POLÍTICAS DE PRIVACIDAD Y CONDICIONES GENERALES DE USO
        // =================================================================================
        $docs['privacidad'] = [
            'id'         => 'privacidad',
            'tipo'       => 'politicas',
            'titulo'     => 'Políticas de Privacidad y Condiciones Generales de Uso',
            'subtitulo'  => 'Documento contractual e informativo aplicable al acceso y a la utilización '
                          . 'de la plataforma DeChimbote.com',
            'version'    => 'Versión 2.0',
            'vigencia'   => 'Vigente desde el 21 de septiembre de 2026',
            'actualizado'=> '21 de septiembre de 2026',
            'preambulo'  =>
                'El presente documento establece las condiciones generales que rigen el acceso y la '
                . 'utilización de la plataforma digital DeChimbote.com, el régimen de titularidad de los '
                . 'contenidos incorporados a ella, las causales de baja de los establecimientos '
                . 'publicados, los criterios de adecuación editorial y el tratamiento de los datos '
                . 'personales recabados. Su aceptación, expresa o tácita, constituye requisito '
                . 'indispensable para el uso de los servicios ofrecidos. Se recomienda su lectura '
                . 'íntegra antes de publicar cualquier contenido.',
            'resumen'    => [
                'La titularidad exclusiva de los contenidos incorporados a la Plataforma corresponde al Prestador, en los términos del Capítulo III.',
                'La baja del establecimiento publicado bajo el Plan Gratuito extingue toda pretensión de reclamo, resarcimiento o indemnización, conforme al Capítulo VI.',
                'El Anunciante de un Plan de Pago podrá obtener la extracción y descarga de su base de datos, según el procedimiento del Capítulo VI.',
                'El Prestador se reserva la facultad de adecuar los establecimientos publicados conforme a los criterios del Capítulo IV.',
                'Se prohíbe la difusión de los contenidos enumerados en el Capítulo V, cuya infracción determina el retiro inmediato y la baja de la cuenta.',
            ],
            'capitulos'  => [

                // ------------------------------------------------------------------
                [
                    'id'     => 'cap-i',
                    'titulo' => 'Capítulo I. Disposiciones generales',
                    'clausulas' => [
                        [
                            'n'  => '1.1',
                            'ti' => 'Identificación del Prestador',
                            'p'  => [
                                'La Plataforma es administrada y explotada por el titular del nombre comercial DeChimbote.com (en adelante, «el Prestador»), con domicilio de operación en la ciudad de Chimbote, provincia del Santa, departamento de Áncash, República del Perú.',
                                'Toda comunicación relativa al presente documento deberá dirigirse al Canal Oficial de atención consignado en el pie de la Plataforma y en el Anexo II.',
                            ],
                        ],
                        [
                            'n'  => '1.2',
                            'ti' => 'Ámbito de aplicación',
                            'p'  => [
                                'El presente documento resulta aplicable a toda persona que acceda, navegue o utilice la Plataforma, en cualquiera de sus soportes y secciones, así como a todo aquel que publique, administre o reclame un establecimiento a través de ella.',
                                'Su ámbito comprende la versión web, la versión móvil, los formularios de contacto, los servicios de atención automatizada y cualquier servicio accesorio que el Prestador incorpore en el futuro.',
                            ],
                        ],
                        [
                            'n'  => '1.3',
                            'ti' => 'Aceptación',
                            'p'  => [
                                'El acceso a la Plataforma o la utilización de cualquiera de sus funcionalidades implica la aceptación plena y sin reservas del presente documento. Quien no comparta sus términos deberá abstenerse de utilizarla.',
                                'La publicación de un establecimiento requiere, además, la aceptación expresa e informada de los Capítulos III, IV, V y VI.',
                            ],
                        ],
                        [
                            'n'  => '1.4',
                            'ti' => 'Definiciones',
                            'p'  => [
                                'A los efectos del presente documento, y con independencia de que los términos se empleen en singular o en plural, se entiende por:',
                            ],
                            'ul' => [
                                '<b>Plataforma:</b> el sitio web DeChimbote.com y todos sus servicios, secciones, formularios y aplicaciones asociadas.',
                                '<b>Prestador:</b> el titular de la Plataforma, encargado de su administración, explotación y mantenimiento.',
                                '<b>Usuario:</b> toda persona que accede a la Plataforma en calidad de visitante.',
                                '<b>Anunciante:</b> el Usuario que publica, reclama o administra un establecimiento, sea persona natural o jurídica.',
                                '<b>Establecimiento:</b> la ficha, el perfil comercial o el anuncio publicado en la Plataforma.',
                                '<b>Contenido:</b> toda información incorporada a la Plataforma por el Anunciante o por sus colaboradores, comprendiendo textos, denominaciones, descripciones, imágenes, material audiovisual, precios, catálogos, datos de contacto y ubicación.',
                                '<b>Plan Gratuito y Planes de Pago:</b> las modalidades de publicación descritas en el Capítulo VIII, cuyas prestaciones y contraprestaciones vigentes se publican en la sección de precios de la Plataforma.',
                                '<b>Documentación:</b> el presente documento y el Manual de Uso de la Plataforma, que se complementan entre sí.',
                                '<b>Canal Oficial:</b> el medio de atención al administrador consignado en el pie de la Plataforma; ningún otro canal tiene carácter vinculante.',
                            ],
                        ],
                        [
                            'n'  => '1.5',
                            'ti' => 'Capacidad legal',
                            'p'  => [
                                'La utilización de la Plataforma en calidad de Anunciante exige capacidad legal para contratar conforme a la legislación peruana. Quien actúe en representación de una persona jurídica declara contar con facultades suficientes para obligarla.',
                            ],
                        ],
                        [
                            'n'  => '1.6',
                            'ti' => 'Documentos descargables y prelación de versiones',
                            'p'  => [
                                'El Prestador pone a disposición versiones descargables en formato PDF de este documento y del Manual de Uso, a efectos de su conservación y lectura fuera de línea o por parte de terceros.',
                                'Las versiones descargables reproducen fielmente el texto publicado. En caso de discrepancia derivada de una actualización, prevalece el texto vigente publicado en la Plataforma, con indicación de su fecha de entrada en vigor.',
                            ],
                        ],
                        [
                            'n'  => '1.7',
                            'ti' => 'Criterios de interpretación',
                            'p'  => [
                                'Los títulos de los capítulos y de las cláusulas se consignan exclusivamente para facilitar su localización y no alteran, amplían ni restringen el sentido de su contenido.',
                                'Los términos definidos en la cláusula 1.4 conservan el mismo significado en todo el documento y en el Manual de Uso.',
                            ],
                        ],
                    ],
                ],

                // ------------------------------------------------------------------
                [
                    'id'     => 'cap-ii',
                    'titulo' => 'Capítulo II. Registro, cuenta y administración del establecimiento',
                    'clausulas' => [
                        [
                            'n'  => '2.1',
                            'ti' => 'Constitución de la cuenta',
                            'p'  => [
                                'La publicación de un establecimiento requiere la constitución de una cuenta mediante el procedimiento habilitado en la Plataforma, en el que se consignan los datos de identificación y el número de teléfono del Anunciante.',
                                'La clave de acceso se entrega al momento de la publicación y es de uso personal e intransferible.',
                            ],
                        ],
                        [
                            'n'  => '2.2',
                            'ti' => 'Veracidad y actualización de los datos',
                            'p'  => [
                                'El Anunciante garantiza que los datos consignados son veraces, exactos y se encuentran actualizados, y se obliga a mantenerlos en ese estado durante la vigencia de la publicación.',
                                'La consignación de datos falsos, inexactos o correspondientes a un tercero faculta al Prestador a suspender o retirar el establecimiento, sin perjuicio de las acciones que correspondan.',
                            ],
                        ],
                        [
                            'n'  => '2.3',
                            'ti' => 'Confidencialidad de las credenciales',
                            'p'  => [
                                'El Anunciante es responsable de la custodia de sus credenciales y de toda actividad realizada con ellas. El personal del Prestador no solicita claves ni datos de instrumentos de pago a través de la atención automatizada o de mensajería.',
                                'Ante la pérdida de la clave de acceso, el Anunciante podrá solicitar su restablecimiento por el procedimiento habilitado y a través del Canal Oficial.',
                            ],
                        ],
                        [
                            'n'  => '2.4',
                            'ti' => 'Reclamación de establecimientos preexistentes',
                            'p'  => [
                                'El Anunciante que acredite la titularidad de un establecimiento publicado con anterioridad por el Prestador podrá solicitar su reclamación y asumir su administración, sin contraprestación alguna, mediante la acreditación de los extremos que le sean requeridos.',
                            ],
                        ],
                        [
                            'n'  => '2.5',
                            'ti' => 'Suspensión de cuentas',
                            'p'  => [
                                'El Prestador podrá suspender o dar de baja una cuenta, de forma temporal o definitiva, ante el incumplimiento del presente documento, la existencia de reclamos fundados de terceros, la suplantación de identidad comercial o cualquier uso contrario a la ley, la moral o el orden público.',
                            ],
                        ],
                    ],
                ],

                // ------------------------------------------------------------------
                [
                    'id'     => 'cap-iii',
                    'titulo' => 'Capítulo III. Titularidad de los contenidos incorporados',
                    'clausulas' => [
                        [
                            'n'  => '3.1',
                            'ti' => 'Titularidad exclusiva del Prestador',
                            'p'  => [
                                '<b>El Prestador es propietario exclusivo de todo aquello que se comparte en la Plataforma.</b> En consecuencia, la titularidad de los derechos patrimoniales sobre el Contenido incorporado corresponde íntegramente al Prestador desde el momento de su publicación.',
                                'La titularidad señalada comprende, de manera enunciativa y no limitativa: la denominación comercial consignada, las descripciones y textos de cualquier naturaleza, las fotografías e imágenes fijas, el material audiovisual, los catálogos, las listas de precios, los datos de contacto y de ubicación, y las compilaciones, ordenaciones y adaptaciones que de ellos se realicen.',
                            ],
                        ],
                        [
                            'n'  => '3.2',
                            'ti' => 'Alcance de la titularidad',
                            'p'  => [
                                'La titularidad exclusiva del Prestador habilita a este, sin necesidad de autorización adicional ni de comunicación previa, a ejercer respecto del Contenido los derechos de reproducción, comunicación pública, distribución, transformación, traducción, adaptación, extracción y reutilización, en cualquier medio, soporte y territorio, por todo el plazo legalmente previsto.',
                                'Comprende asimismo la difusión del Contenido en la propia Plataforma, en sus secciones destacadas, en las páginas aliadas del Prestador y en sus canales de comunicación institucional, con la finalidad de promover la visibilidad del Establecimiento.',
                            ],
                            'av' => 'La titularidad exclusiva recae sobre el Contenido publicado, no sobre la actividad comercial del Anunciante ni sobre los resultados económicos que esta genere, que permanecen íntegramente en su esfera.',
                        ],
                        [
                            'n'  => '3.3',
                            'ti' => 'Facultad de uso reservada al Anunciante',
                            'p'  => [
                                'Sin perjuicio de lo dispuesto en las cláusulas precedentes, el Anunciante conserva la facultad de emplear el Contenido en la gestión ordinaria de su propio establecimiento, siempre que dicho uso no contravenga el presente documento ni el ordenamiento jurídico.',
                            ],
                        ],
                        [
                            'n'  => '3.4',
                            'ti' => 'Declaraciones y garantías del Anunciante',
                            'p'  => [
                                'El Anunciante declara y garantiza que el Contenido que incorpora es de su titularidad o que cuenta con las autorizaciones necesarias para su difusión, y que no vulnera derechos de propiedad intelectual, industrial, de imagen, de honor, de intimidad ni de ningún otro tercero.',
                                'El Anunciante responde por las consecuencias derivadas de la incorporación de Contenido que infrinja derechos de terceros, conforme al régimen de indemnidad del Capítulo IX.',
                            ],
                        ],
                        [
                            'n'  => '3.5',
                            'ti' => 'Signos distintivos de terceros',
                            'p'  => [
                                'No se admite la incorporación de marcas, logotipos, denominaciones o signos distintivos de terceros sin autorización acreditada de su titular, ni la consignación de referencias que induzcan a confusión sobre la identidad o el origen comercial del Establecimiento.',
                            ],
                        ],
                        [
                            'n'  => '3.6',
                            'ti' => 'Solicitud de retiro del Contenido',
                            'p'  => [
                                'El Anunciante podrá solicitar el retiro del Contenido mediante comunicación cursada por el Canal Oficial, en la que se identifiquen con precisión los elementos cuya supresión se requiere.',
                                'El Prestador atenderá la solicitud en un plazo de quince (15) días hábiles, sin que ello genere derecho a resarcimiento alguno, y sin perjuicio de las facultades de conservación que correspondan por razones legales, contables o de seguridad.',
                            ],
                        ],
                    ],
                ],

                // ------------------------------------------------------------------
                [
                    'id'     => 'cap-iv',
                    'titulo' => 'Capítulo IV. Criterios editoriales y adecuación de los establecimientos',
                    'clausulas' => [
                        [
                            'n'  => '4.1',
                            'ti' => 'Facultad de adecuación',
                            'p'  => [
                                '<b>El Establecimiento publicado podrá ser modificado de acuerdo con el criterio del Prestador.</b> Dicha facultad comprende, entre otras, la modificación, sustitución o reordenación del material gráfico; la corrección de textos, títulos, denominaciones y signos ortográficos; la reasignación de rubros y etiquetas; el ajuste de la plantilla, la paleta cromática y la disposición de los bloques informativos; y el retiro de aquello que no se ajuste a los criterios del presente documento.',
                                'Las adecuaciones se adoptan con la finalidad de preservar la claridad, la uniformidad, la legibilidad, la interoperabilidad y la seguridad de la Plataforma, así como la correcta presentación de la oferta comercial.',
                            ],
                        ],
                        [
                            'n'  => '4.2',
                            'ti' => 'Oportunidad de las adecuaciones',
                            'p'  => [
                                'Las adecuaciones podrán efectuarse en cualquier momento y no requieren comunicación previa al Anunciante, sin que su adopción genere derecho a reclamo, indemnización o compensación de ninguna naturaleza.',
                                'Las adecuaciones no alteran los precios consignados por el Anunciante ni los datos de contacto por él registrados.',
                            ],
                        ],
                        [
                            'n'  => '4.3',
                            'ti' => 'Solicitud de revisión por el Anunciante',
                            'p'  => [
                                'El Anunciante que considere que una adecuación afecta de manera sustancial la información de su Establecimiento podrá solicitar su revisión por el Canal Oficial, exponiendo los fundamentos de su pedido.',
                                'El Prestador evaluará la solicitud y comunicará su decisión, que será adoptada atendiendo a los criterios de la cláusula 4.1.',
                            ],
                        ],
                        [
                            'n'  => '4.4',
                            'ti' => 'Difusión y posicionamiento',
                            'p'  => [
                                'El Prestador no garantiza una posición determinada en los resultados de búsqueda internos, en los servicios de geolocalización ni en los buscadores externos. La posición resulta de criterios objetivos de relevancia, proximidad, actualización y actividad del Establecimiento.',
                                'Los Planes de Pago podrán comprender prestaciones adicionales de visibilidad, según lo publicado en la sección de precios vigente.',
                            ],
                        ],
                    ],
                ],

                // ------------------------------------------------------------------
                [
                    'id'     => 'cap-v',
                    'titulo' => 'Capítulo V. Contenidos y actividades prohibidas',
                    'clausulas' => [
                        [
                            'n'  => '5.1',
                            'ti' => 'Enumeración taxativa',
                            'p'  => [
                                'Queda terminantemente prohibida la publicación, difusión, ofrecimiento, promoción o referencia, en cualquier sección de la Plataforma —incluidos el Establecimiento, el catálogo, el material gráfico y las opiniones—, de los siguientes contenidos y actividades:',
                            ],
                            'ul' => [
                                '<b>Instrumentos de maltrato animal:</b> artefactos, dispositivos, sustancias o métodos destinados a infligir sufrimiento a los animales, así como todo contenido que promueva, exhiba, celebre o banalice el maltrato animal.',
                                '<b>Instrumentos peligrosos:</b> pólvora, explosivos y artificios pirotécnicos; armas de fuego, sus componentes, accesorios, municiones y réplicas; y, en general, todo elemento cuyo comercio se encuentre reservado, restringido o prohibido por la legislación vigente.',
                                '<b>Pornografía infantil:</b> todo contenido de naturaleza sexual en el que intervengan o se represente a personas menores de edad, cualquiera sea su formato. Hechos de esta naturaleza serán puestos en conocimiento de las autoridades competentes.',
                                '<b>Incitación al odio, racismo, maltrato social y discriminación:</b> todo contenido que promueva, justifique o fomente el odio, la hostilidad o el menosprecio por razón de origen, nacionalidad, color de piel, sexo, género, idioma, religión, convicción, discapacidad, orientación sexual, edad o condición económica o social.',
                                '<b>Demás contenidos ilícitos:</b> toda actividad contraria a la ley, al orden público o a las buenas costumbres, comprendiendo el comercio de bienes de procedencia ilícita, la suplantación de identidad y la oferta de servicios para cuya prestación se exija habilitación no acreditada.',
                            ],
                        ],
                        [
                            'n'  => '5.2',
                            'ti' => 'Medidas ante el incumplimiento',
                            'p'  => [
                                'Verificada la existencia de un contenido prohibido, el Prestador procederá al retiro inmediato del contenido y a la baja del Establecimiento y de la cuenta del Anunciante, sin que proceda devolución, reembolso ni compensación alguna por los importes que se hubieran abonado.',
                                'Cuando los hechos pudieran constituir delito, el Prestador pondrá el caso en conocimiento de las autoridades competentes y conservará la información necesaria a disposición de estas.',
                            ],
                        ],
                        [
                            'n'  => '5.3',
                            'ti' => 'Reporte por terceros',
                            'p'  => [
                                'Cualquier Usuario podrá reportar contenidos presuntamente prohibidos mediante los mecanismos habilitados en la Plataforma o por el Canal Oficial. El reporte será evaluado y atendido con la reserva que corresponda.',
                            ],
                        ],
                    ],
                ],

                // ------------------------------------------------------------------
                [
                    'id'     => 'cap-vi',
                    'titulo' => 'Capítulo VI. Vigencia, baja del establecimiento y reclamaciones',
                    'clausulas' => [
                        [
                            'n'  => '6.1',
                            'ti' => 'Vigencia de la publicación',
                            'p'  => [
                                'El Establecimiento permanece publicado mientras subsista la cuenta del Anunciante, se mantenga el cumplimiento del presente documento y, en el caso de los Planes de Pago, se encuentre vigente la contraprestación correspondiente.',
                            ],
                        ],
                        [
                            'n'  => '6.2',
                            'ti' => 'Baja a solicitud del Anunciante',
                            'p'  => [
                                'El Anunciante podrá solicitar la baja de su Establecimiento en cualquier momento, mediante comunicación cursada por el Canal Oficial. La baja se hará efectiva en el plazo que el Prestador comunique al efecto.',
                            ],
                        ],
                        [
                            'n'  => '6.3',
                            'ti' => 'Baja por decisión del Prestador',
                            'p'  => [
                                'El Prestador podrá dar de baja el Establecimiento por incumplimiento del presente documento, por la comisión de las conductas descritas en el Capítulo V, por reclamos fundados de terceros, por desuso prolongado o por la discontinuidad, total o parcial, del servicio.',
                            ],
                        ],
                        [
                            'n'  => '6.4',
                            'ti' => 'Efectos de la baja bajo el Plan Gratuito',
                            'p'  => [
                                'Producida la baja del Establecimiento publicado bajo el Plan Gratuito, <b>el Anunciante pierde todo derecho a reclamo</b>. En consecuencia, el Anunciante reconoce y acepta que no procederá pretensión alguna —de naturaleza indemnizatoria, compensatoria, resarcitoria o de cualquier otra índole— respecto del Establecimiento, su Contenido, su material gráfico, sus estadísticas, sus registros de visitas y contactos, sus clientes potenciales y sus resultados comerciales, presentes o futuros.',
                                'La extinción señalada opera de pleno derecho desde la fecha en que la baja se hace efectiva, sin necesidad de requerimiento previo, y subsiste aun cuando el Anunciante vuelva a publicar el mismo Establecimiento con posterioridad.',
                            ],
                            'av' => 'La gratuidad del Plan Gratuito es la contrapartida de esta cláusula: el servicio se presta sin contraprestación económica y, por consiguiente, sin obligación de conservación, restitución ni compensación una vez producida la baja.',
                        ],
                        [
                            'n'  => '6.5',
                            'ti' => 'Extracción de la base de datos en los Planes de Pago',
                            'p'  => [
                                'Tratándose de un Establecimiento publicado bajo cualquiera de los Planes de Pago, y sin perjuicio de lo dispuesto en la cláusula 6.4 respecto de la titularidad del Contenido difundido, <b>se permite al Anunciante descargar su base de datos</b> y la información que hubiera recopilado a través de la Plataforma, comprendiendo los registros de pedidos, consultas y comunicaciones recibidas, así como los datos de contacto de los Usuarios que se hubieran puesto en contacto con el Establecimiento.',
                            ],
                            'ol' => [
                                'La solicitud se cursa por el Canal Oficial, indicando la denominación del Establecimiento y el número de teléfono registrado por el Anunciante.',
                                'El Prestador verifica la titularidad de la cuenta y comunica la conformidad de la solicitud.',
                                'La información se entrega en formato de hoja de cálculo (CSV o equivalente), dentro de los quince (15) días hábiles siguientes a la conformidad.',
                            ],
                            'p2' => [
                                'No forman parte de la entrega los respaldos internos, las copias de seguridad, los registros técnicos de funcionamiento, la información sujeta a deber legal de conservación ni los datos personales de terceros cuya comunicación no resulte lícita conforme a la normativa aplicable.',
                            ],
                        ],
                        [
                            'n'  => '6.6',
                            'ti' => 'Cese de responsabilidad',
                            'p'  => [
                                'Efectuada la baja, cesa toda obligación de conservación, custodia, publicación o difusión a cargo del Prestador respecto del Establecimiento, sin perjuicio de las obligaciones legales que subsistan.',
                            ],
                        ],
                        [
                            'n'  => '6.7',
                            'ti' => 'Reclamaciones',
                            'p'  => [
                                'Las reclamaciones relativas a la publicación, a las adecuaciones o a la baja se presentan por el Canal Oficial y son atendidas en un plazo máximo de quince (15) días hábiles, mediante respuesta fundada.',
                                'La interposición de una reclamación no suspende los efectos de las medidas adoptadas.',
                            ],
                        ],
                    ],
                ],

                // ------------------------------------------------------------------
                [
                    'id'     => 'cap-vii',
                    'titulo' => 'Capítulo VII. Datos personales y privacidad',
                    'clausulas' => [
                        [
                            'n'  => '7.1',
                            'ti' => 'Responsable del tratamiento',
                            'p'  => [
                                'El Prestador es responsable de los bancos de datos personales que administra a través de la Plataforma y realiza el tratamiento conforme a la Ley N.º 29733, Ley de Protección de Datos Personales, su reglamento y las disposiciones complementarias vigentes en la República del Perú.',
                            ],
                        ],
                        [
                            'n'  => '7.2',
                            'ti' => 'Categorías de información tratada',
                            'p'  => ['La Plataforma trata las siguientes categorías de información:'],
                            'ul' => [
                                '<b>Información del Anunciante:</b> denominación del Establecimiento, nombre del titular, número de teléfono o aplicación de mensajería, dirección, horarios, formas de pago, servicios de entrega, redes sociales y, cuando se consigne, el registro único de contribuyentes.',
                                '<b>Información publicada:</b> el catálogo, los precios, el material gráfico y las descripciones incorporadas por el Anunciante.',
                                '<b>Información de navegación:</b> búsquedas realizadas, fichas consultadas, reacciones y preferencias, y datos técnicos de conexión.',
                                '<b>Información de contacto comercial:</b> las consultas y pedidos dirigidos a los Establecimientos, con los datos que el Usuario consigne voluntariamente al efecto.',
                                '<b>Opiniones:</b> las manifestaciones vertidas en la sección de opiniones, que se publican sin el nombre completo de su autor.',
                            ],
                        ],
                        [
                            'n'  => '7.3',
                            'ti' => 'Finalidades del tratamiento',
                            'p'  => ['La información se trata con las siguientes finalidades:'],
                            'ul' => [
                                'Permitir la publicación, administración y difusión del Establecimiento.',
                                'Facilitar el contacto entre el Usuario y el Anunciante, y la atención de consultas y pedidos.',
                                'Elaborar estadísticas de uso, medición de audiencia y elaboración de informes, con datos agregados o disociados.',
                                'Verificar el cumplimiento del presente documento, prevenir el fraude y atender requerimientos de autoridad competente.',
                                'Comunicar novedades, mejoras y promociones del servicio, cuando el titular haya prestado su consentimiento o exista relación contractual vigente.',
                            ],
                        ],
                        [
                            'n'  => '7.4',
                            'ti' => 'Cookies y tecnologías similares',
                            'p'  => [
                                'La Plataforma emplea cookies propias de carácter técnico y estadístico, entre ellas una cookie identificadora anónima destinada al recuento de visitas, que no contiene el nombre, el documento de identidad ni dato alguno que permita identificar directamente a la persona.',
                                'Se emplean, además, servicios de analítica web de terceros para la medición de audiencia. El Usuario puede configurar su navegador para rechazar o eliminar las cookies, sin que ello impida el acceso a los contenidos publicados.',
                            ],
                        ],
                        [
                            'n'  => '7.5',
                            'ti' => 'Conservación y medidas de seguridad',
                            'p'  => [
                                'La información se conserva mientras subsista la finalidad que motivó su tratamiento y, luego, por los plazos de prescripción legalmente previstos. El Prestador adopta medidas técnicas y organizativas razonables para preservar su confidencialidad, integridad y disponibilidad.',
                            ],
                        ],
                        [
                            'n'  => '7.6',
                            'ti' => 'Comunicación a terceros',
                            'p'  => [
                                'La información no es objeto de venta, arrendamiento ni cesión onerosa. Podrá ser comunicada a proveedores de infraestructura tecnológica y de analítica que prestan servicios al Prestador, así como a autoridades competentes cuando medie requerimiento legal.',
                            ],
                        ],
                        [
                            'n'  => '7.7',
                            'ti' => 'Derechos del titular de los datos',
                            'p'  => [
                                'El titular de los datos personales podrá ejercer los derechos de información, acceso, rectificación, supresión, oposición y los demás reconocidos por la legislación aplicable, mediante solicitud cursada por el Canal Oficial, la que será atendida en los plazos legales.',
                            ],
                        ],
                        [
                            'n'  => '7.8',
                            'ti' => 'Personas menores de edad',
                            'p'  => [
                                'No se admite la publicación de contenido alguno que involucre a personas menores de edad en contextos de naturaleza sexual, ni la consignación de sus datos personales por parte de terceros no autorizados. La infracción se sujeta al régimen del Capítulo V.',
                            ],
                        ],
                        [
                            'n'  => '7.9',
                            'ti' => 'Advertencia sobre solicitudes fraudulentas',
                            'p'  => [
                                'El Prestador no solicita, por ningún medio, claves de acceso, códigos de verificación ni datos de instrumentos de pago. Toda comunicación de esa naturaleza debe considerarse fraudulenta y ser puesta en conocimiento del Prestador por el Canal Oficial.',
                            ],
                        ],
                    ],
                ],

                // ------------------------------------------------------------------
                [
                    'id'     => 'cap-viii',
                    'titulo' => 'Capítulo VIII. Planes, contraprestación y promociones',
                    'clausulas' => [
                        [
                            'n'  => '8.1',
                            'ti' => 'Modalidades de publicación',
                            'p'  => [
                                'La Plataforma ofrece un Plan Gratuito y Planes de Pago. Las prestaciones comprendidas en cada modalidad, así como su contraprestación mensual vigente, se publican en la sección de precios de la Plataforma, que forma parte integrante del presente documento.',
                            ],
                        ],
                        [
                            'n'  => '8.2',
                            'ti' => 'Modalidad de pago y ausencia de permanencia',
                            'p'  => [
                                'Los Planes de Pago se contratan por períodos mensuales y no imponen permanencia mínima. La falta de pago determina el retorno al Plan Gratuito, sin que ello importe la pérdida del Establecimiento, de su Contenido ni de sus registros, y sin perjuicio de lo dispuesto en la cláusula 6.4.',
                            ],
                        ],
                        [
                            'n'  => '8.3',
                            'ti' => 'Ausencia de comisiones sobre las ventas',
                            'p'  => [
                                'El Prestador no percibe comisión, porcentaje ni participación alguna sobre las ventas que el Anunciante concrete. La contraprestación se limita al importe del plan contratado.',
                            ],
                        ],
                        [
                            'n'  => '8.4',
                            'ti' => 'Promociones y condiciones de resultado',
                            'p'  => [
                                'Las promociones que el Prestador difunda —entre ellas las que ofrezcan un período inicial sin costo o un resultado mínimo garantizado— se sujetan a las condiciones que se publiquen al efecto, comprendiendo el producto o los productos materia del acuerdo, el plazo de cómputo y la modalidad de verificación.',
                                'El cumplimiento de la promoción se evalúa sobre las ventas atribuibles a la Plataforma conforme al mecanismo de medición que se informe, y su resultado no depende de factores ajenos al servicio.',
                            ],
                        ],
                        [
                            'n'  => '8.5',
                            'ti' => 'Variación de precios',
                            'p'  => [
                                'El Prestador podrá actualizar las prestaciones y la contraprestación de los Planes de Pago, comunicando la variación con una anticipación no menor de quince (15) días calendario a su entrada en vigor. El Anunciante que no preste conformidad podrá solicitar el retorno al Plan Gratuito.',
                            ],
                        ],
                    ],
                ],

                // ------------------------------------------------------------------
                [
                    'id'     => 'cap-ix',
                    'titulo' => 'Capítulo IX. Responsabilidad',
                    'clausulas' => [
                        [
                            'n'  => '9.1',
                            'ti' => 'Naturaleza del servicio',
                            'p'  => [
                                'La Plataforma constituye un directorio comercial de carácter digital que facilita la puesta en contacto entre Usuarios y Anunciantes. El Prestador no interviene en la negociación, la celebración, la ejecución ni el pago de las operaciones que aquellos celebren, ni forma parte de la relación comercial entablada entre ellos.',
                            ],
                        ],
                        [
                            'n'  => '9.2',
                            'ti' => 'Obligaciones del Anunciante',
                            'p'  => [
                                'El Anunciante responde por la veracidad de la información publicada, por la legalidad de los bienes y servicios ofrecidos, por la obtención de las autorizaciones administrativas exigibles para su actividad y por la atención que dispense a los Usuarios.',
                            ],
                        ],
                        [
                            'n'  => '9.3',
                            'ti' => 'Exclusiones de responsabilidad',
                            'p'  => [
                                'El Prestador no responde por la disponibilidad ininterrumpida del servicio, por las interrupciones derivadas de caso fortuito o fuerza mayor, por las fallas de los servicios de telecomunicaciones o de terceros proveedores, ni por el contenido de los sitios externos a los que la Plataforma remita mediante enlaces.',
                            ],
                        ],
                        [
                            'n'  => '9.4',
                            'ti' => 'Limitación de responsabilidad',
                            'p'  => [
                                'En la medida en que la legislación aplicable lo permita, la responsabilidad del Prestador por daños directos se limita al importe efectivamente abonado por el Anunciante en los tres (3) meses anteriores al hecho que la origine. Queda excluida la responsabilidad por lucro cesante, pérdida de oportunidad comercial o daño indirecto.',
                            ],
                        ],
                        [
                            'n'  => '9.5',
                            'ti' => 'Indemnidad',
                            'p'  => [
                                'El Anunciante mantendrá indemne al Prestador frente a toda reclamación, demanda, sanción o gasto —incluidos los honorarios profesionales razonables— que se origine en el Contenido por él incorporado, en el incumplimiento del presente documento o en la vulneración de derechos de terceros.',
                            ],
                        ],
                    ],
                ],

                // ------------------------------------------------------------------
                [
                    'id'     => 'cap-x',
                    'titulo' => 'Capítulo X. Disposiciones finales',
                    'clausulas' => [
                        [
                            'n'  => '10.1',
                            'ti' => 'Modificación del documento',
                            'p'  => [
                                'El Prestador podrá modificar el presente documento para adecuarlo a cambios normativos, técnicos o de servicio. Las modificaciones entran en vigor desde su publicación en la Plataforma, con indicación de su fecha, y el uso continuado del servicio implica su aceptación.',
                            ],
                        ],
                        [
                            'n'  => '10.2',
                            'ti' => 'Independencia de las cláusulas',
                            'p'  => [
                                'Si alguna cláusula fuera declarada nula, inválida o ineficaz, las restantes conservan plena vigencia, y la cláusula afectada se tendrá por sustituida por aquella que, siendo válida, responda con mayor fidelidad a su finalidad.',
                            ],
                        ],
                        [
                            'n'  => '10.3',
                            'ti' => 'Cesión de posición contractual',
                            'p'  => [
                                'El Prestador podrá ceder su posición contractual, total o parcialmente, a una sociedad vinculada o a quien lo suceda en la explotación de la Plataforma, comunicándolo por el Canal Oficial.',
                            ],
                        ],
                        [
                            'n'  => '10.4',
                            'ti' => 'Notificaciones',
                            'p'  => [
                                'Las comunicaciones entre las partes se cursan por el Canal Oficial y se entienden recibidas el día hábil siguiente a su remisión, salvo constancia en contrario.',
                            ],
                        ],
                        [
                            'n'  => '10.5',
                            'ti' => 'Ley aplicable y jurisdicción',
                            'p'  => [
                                'El presente documento se rige por la legislación de la República del Perú. Toda controversia derivada de su interpretación, ejecución o cumplimiento se somete a los tribunales competentes del distrito judicial correspondiente a la ciudad de Chimbote, sin perjuicio de los derechos que la ley reconozca al consumidor.',
                            ],
                        ],
                        [
                            'n'  => '10.6',
                            'ti' => 'Vigencia y versión',
                            'p'  => [
                                'La presente versión sustituye a las anteriores y rige desde la fecha consignada en el encabezado del documento. Las versiones descargables en formato PDF corresponden a esta misma versión.',
                            ],
                        ],
                    ],
                ],
            ],
            'anexos' => [
                [
                    'titulo' => 'Anexo I. Síntesis de contenidos prohibidos',
                    'p'      => ['A los efectos de facilitar su consulta, se reitera la enumeración del Capítulo V:'],
                    'ul'     => [
                        'Instrumentos de maltrato animal y todo contenido que lo promueva o banalice.',
                        'Pólvora, explosivos, artificios pirotécnicos, armas de fuego, municiones, accesorios y réplicas.',
                        'Pornografía infantil y todo contenido sexual en el que intervengan personas menores de edad.',
                        'Incitación al odio, racismo, maltrato social y discriminación de cualquier tipo.',
                        'Cualquier otra actividad o contenido contrario a la ley, la moral o el orden público.',
                    ],
                ],
                [
                    'titulo' => 'Anexo II. Canales de atención y documentación complementaria',
                    'p'      => [
                        'Toda solicitud, consulta o reclamación relativa al presente documento se cursa por el Canal Oficial de atención al administrador de la Plataforma, consignado en el pie de página. La documentación complementaria comprende:',
                    ],
                    'ul'     => [
                        'Manual de Uso de la Plataforma (explicación funcional de cada característica).',
                        'Preguntas Frecuentes (respuestas a las consultas de mayor recurrencia).',
                        'Sección de precios (prestaciones y contraprestación de cada plan vigente).',
                    ],
                ],
            ],
        ];

        // =================================================================================
        // II. PREGUNTAS FRECUENTES
        // =================================================================================
        $docs['faq'] = [
            'id'         => 'faq',
            'tipo'       => 'faq',
            'titulo'     => 'Preguntas Frecuentes',
            'subtitulo'  => 'Respuestas oficiales a las consultas de mayor recurrencia sobre el uso de la '
                          . 'Plataforma, la titularidad de los contenidos, los planes contratados y el '
                          . 'tratamiento de los datos personales',
            'version'    => 'Versión 2.0',
            'vigencia'   => 'Vigente desde el 21 de septiembre de 2026',
            'actualizado'=> '21 de septiembre de 2026',
            'preambulo'  =>
                'El presente documento reúne las consultas formuladas con mayor frecuencia por los '
                . 'Usuarios y por los Anunciantes, con las respuestas que el Prestador brinda de manera '
                . 'uniforme. Su contenido es complementario de las Políticas de Privacidad y Condiciones '
                . 'Generales de Uso y del Manual de Uso de la Plataforma, y no los modifica ni los '
                . 'sustituye: ante cualquier discrepancia, prevalece el texto de dichos documentos.',
            'resumen'    => [
                'La publicación de un establecimiento en el Plan Gratuito no irroga costo alguno y no genera comisión sobre las ventas.',
                'La titularidad exclusiva del contenido publicado corresponde al Prestador, conforme al Capítulo III de las Políticas.',
                'La baja del establecimiento bajo el Plan Gratuito extingue toda pretensión de reclamo.',
                'Los Anunciantes de Planes de Pago pueden solicitar la extracción de su base de datos.',
                'La difusión de los contenidos prohibidos determina el retiro inmediato y la baja de la cuenta.',
            ],
            'capitulos'  => [
                [
                    'id'     => 'faq-uso',
                    'titulo' => 'A. Sobre el uso de la Plataforma',
                    'clausulas' => [
                        ['n'=>'A.1','ti'=>'¿Qué es DeChimbote.com y qué servicio presta?',
                         'p'=>['DeChimbote.com es un directorio comercial digital que reúne establecimientos de Chimbote, Nuevo Chimbote y los distritos de la provincia del Santa, y que facilita su localización por parte de los Usuarios mediante búsquedas por nombre, rubro, producto o proximidad geográfica. El Prestador no interviene en las operaciones que se conciertan entre Usuarios y Anunciantes.']],
                        ['n'=>'A.2','ti'=>'¿El uso de la Plataforma tiene algún costo para quien busca o compra?',
                         'p'=>['No. La consulta del directorio, la búsqueda por texto o por voz, la localización de establecimientos próximos, la consulta de catálogos y la remisión de pedidos o consultas son enteramente gratuitas para el Usuario.']],
                        ['n'=>'A.3','ti'=>'¿Cómo se localiza un establecimiento o un producto determinado?',
                         'p'=>['El Usuario puede consignar el término de búsqueda en el campo dispuesto en la cabecera —con tolerancia a errores de escritura—, dictarlo mediante el mecanismo de reconocimiento de voz, recorrer los rubros publicados o emplear el botón de proximidad, que ordena los resultados por distancia respecto de la ubicación que el Usuario autorice compartir. El procedimiento se detalla en el Manual de Uso, Capítulo IX.']],
                        ['n'=>'A.4','ti'=>'¿Cómo se cursa un pedido o una consulta a un establecimiento?',
                         'p'=>['El Usuario puede marcar los productos de su interés mediante el mecanismo denominado «Me interesa», con lo cual se conforma un pedido único que se remite al Anunciante por la aplicación de mensajería habilitada en la ficha. También puede emplear los botones de contacto directo publicados en el Establecimiento. El detalle consta en el Manual de Uso, Capítulo X.']],
                        ['n'=>'A.5','ti'=>'¿Las opiniones publicadas son verificadas por el Prestador?',
                         'p'=>['Las opiniones expresan la manifestación de quien las suscribe y no la posición del Prestador. Se publican sin el nombre completo de su autor y pueden ser reportadas por cualquier Usuario; verificada su falsedad, su carácter injurioso o su apartamiento de estas reglas, se procede a su retiro.']],
                        ['n'=>'A.6','ti'=>'¿La Plataforma dispone de otras secciones de contenido?',
                         'p'=>['Además del directorio, la Plataforma publica un resumen diario de noticias locales y un tablón de ofertas de empleo de la zona. Ambas secciones son de acceso gratuito y se rigen por las presentes reglas en cuanto a contenidos prohibidos.']],
                    ],
                ],
                [
                    'id'     => 'faq-publicacion',
                    'titulo' => 'B. Sobre la publicación y la administración del establecimiento',
                    'clausulas' => [
                        ['n'=>'B.1','ti'=>'¿Qué se requiere para publicar un establecimiento?',
                         'p'=>['Se requiere constituir una cuenta, consignar el número de teléfono del titular y aportar material gráfico del establecimiento. El sistema de publicación asistida por inteligencia artificial genera, a partir de dicho material, la descripción comercial y el catálogo inicial. El procedimiento se detalla en el Manual de Uso, Capítulo III.']],
                        ['n'=>'B.2','ti'=>'¿Cuántas fotografías deben aportarse?',
                         'p'=>['El procedimiento principal admite hasta ocho (8) fotografías del establecimiento y de sus productos. El procedimiento de publicación abreviada, concebido para el registro presencial, requiere un mínimo de tres (3) y admite hasta ocho (8). Los criterios técnicos constan en el Manual de Uso, Capítulo V.']],
                        ['n'=>'B.3','ti'=>'¿Puede modificarse la información una vez publicada?',
                         'p'=>['Sí. El Anunciante accede a su panel de administración y puede modificar la información del establecimiento, su material gráfico, sus precios y su catálogo cuantas veces lo requiera y sin intervención de terceros. Las adecuaciones que el Prestador introduzca de oficio se rigen por el Capítulo IV de las Políticas.']],
                        ['n'=>'B.4','ti'=>'El establecimiento ya figura publicado y su titular desea administrarlo. ¿Qué corresponde hacer?',
                         'p'=>['Corresponde presentar la solicitud de reclamación por el procedimiento habilitado al efecto, acreditando la titularidad del establecimiento. La reclamación no irroga costo alguno y habilita de inmediato la administración de la ficha.']],
                        ['n'=>'B.5','ti'=>'¿Qué ocurre si se extravía la clave de acceso?',
                         'p'=>['Se solicita su restablecimiento por el mecanismo dispuesto en la Plataforma o por el Canal Oficial. El personal del Prestador no solicita la clave anterior ni datos de instrumentos de pago.']],
                    ],
                ],
                [
                    'id'     => 'faq-titularidad',
                    'titulo' => 'C. Sobre la titularidad de los contenidos',
                    'clausulas' => [
                        ['n'=>'C.1','ti'=>'¿A quién corresponde la titularidad de lo que se publica en la Plataforma?',
                         'p'=>['<b>La titularidad exclusiva corresponde al Prestador.</b> Todo aquello que se comparte en la Plataforma —denominación, descripciones, material gráfico, catálogos, precios y datos de contacto y ubicación— queda bajo su propiedad exclusiva desde el momento de su publicación, conforme al Capítulo III de las Políticas de Privacidad y Condiciones Generales de Uso.']],
                        ['n'=>'C.2','ti'=>'¿El Anunciante conserva la facultad de emplear su propio material?',
                         'p'=>['Sí. Conserva la facultad de emplear dicho material en la gestión ordinaria de su establecimiento, sin perjuicio de la titularidad exclusiva del Prestador sobre el contenido difundido en la Plataforma.']],
                        ['n'=>'C.3','ti'=>'¿En qué ámbitos puede el Prestador difundir el contenido publicado?',
                         'p'=>['En la propia Plataforma y en sus secciones destacadas, en las páginas aliadas del Prestador y en sus canales de comunicación institucional, con la finalidad de promover la visibilidad del establecimiento.']],
                        ['n'=>'C.4','ti'=>'¿Puede solicitarse el retiro de un contenido determinado?',
                         'p'=>['Sí, mediante comunicación cursada por el Canal Oficial en la que se identifiquen con precisión los elementos cuya supresión se requiere. La solicitud se atiende en un plazo de quince (15) días hábiles, sin que ello genere derecho a resarcimiento, y sin perjuicio de las obligaciones legales de conservación que correspondan.']],
                        ['n'=>'C.5','ti'=>'El material aportado corresponde a un tercero. ¿Qué responsabilidad asume el Anunciante?',
                         'p'=>['El Anunciante declara y garantiza que el contenido que incorpora es de su titularidad o que cuenta con las autorizaciones necesarias, y mantiene indemne al Prestador frente a las reclamaciones que se originen en la vulneración de derechos de terceros.']],
                    ],
                ],
                [
                    'id'     => 'faq-baja',
                    'titulo' => 'D. Sobre la baja del establecimiento y la extracción de información',
                    'clausulas' => [
                        ['n'=>'D.1','ti'=>'¿Qué consecuencias produce la baja del establecimiento bajo el Plan Gratuito?',
                         'p'=>['<b>La baja del establecimiento publicado bajo el Plan Gratuito determina la pérdida de todo derecho a reclamo.</b> El Anunciante reconoce y acepta que no procederá pretensión indemnizatoria, compensatoria ni resarcitoria alguna respecto del establecimiento, su contenido, sus estadísticas, sus registros y sus resultados comerciales. La materia se rige por la cláusula 6.4 de las Políticas.']],
                        ['n'=>'D.2','ti'=>'¿Puede recuperarse la información recopilada si el establecimiento se da de baja?',
                         'p'=>['Tratándose de un Plan de Pago, el Anunciante tiene derecho a la extracción y descarga de su base de datos y de la información recopilada a través de la Plataforma. Bajo el Plan Gratuito no se contempla dicha entrega, conforme a lo dispuesto en la cláusula 6.4.']],
                        ['n'=>'D.3','ti'=>'¿Cómo se solicita la base de datos y en qué plazo se entrega?',
                         'p'=>['La solicitud se cursa por el Canal Oficial, indicando la denominación del establecimiento y el número de teléfono registrado. Verificada la titularidad, la información se entrega en formato de hoja de cálculo dentro de los quince (15) días hábiles siguientes a la conformidad. El procedimiento íntegro consta en la cláusula 6.5.']],
                        ['n'=>'D.4','ti'=>'¿Qué comprende y qué excluye dicha entrega?',
                         'p'=>['Comprende los registros de pedidos, consultas y comunicaciones recibidas, y los datos de contacto de los Usuarios que se dirigieron al establecimiento. Excluye los respaldos internos, las copias de seguridad, los registros técnicos de funcionamiento, la información sujeta a deber legal de conservación y los datos personales de terceros cuya comunicación no resulte lícita.']],
                        ['n'=>'D.5','ti'=>'¿Puede el Prestador dar de baja el establecimiento por decisión propia?',
                         'p'=>['Sí, conforme al Capítulo VI de las Políticas, por incumplimiento del documento, por la comisión de las conductas descritas en el Capítulo V, por reclamos fundados de terceros, por desuso prolongado o por discontinuidad del servicio.']],
                    ],
                ],
                [
                    'id'     => 'faq-planes',
                    'titulo' => 'E. Sobre los planes y la contraprestación',
                    'clausulas' => [
                        ['n'=>'E.1','ti'=>'¿Cuáles son las modalidades de publicación y su costo?',
                         'p'=>['La Plataforma ofrece un Plan Gratuito y Planes de Pago, y publica las prestaciones y la contraprestación vigente de cada uno en su sección de precios, que forma parte integrante de las Políticas. La consulta de dicha sección es permanente y gratuita.']],
                        ['n'=>'E.2','ti'=>'¿Existe comisión sobre las ventas?',
                         'p'=>['No. El Prestador no percibe comisión, porcentaje ni participación alguna sobre las ventas que el Anunciante concrete. La contraprestación se limita al importe del plan contratado, y en el Plan Gratuito no existe contraprestación.']],
                        ['n'=>'E.3','ti'=>'¿Existe permanencia mínima?',
                         'p'=>['No. Los Planes de Pago se contratan por períodos mensuales. El Anunciante puede solicitar el retorno al Plan Gratuito o el cambio de modalidad en cualquier momento.']],
                        ['n'=>'E.4','ti'=>'¿Qué ocurre si no se abona el plan contratado?',
                         'p'=>['Se produce el retorno al Plan Gratuito, sin que ello importe la pérdida del establecimiento, de su contenido ni de sus registros, y sin perjuicio de las consecuencias previstas en la cláusula 6.4 ante una eventual baja.']],
                        ['n'=>'E.5','ti'=>'¿Cómo se solicita la contratación o el cambio de plan?',
                         'p'=>['Por el Canal Oficial de atención al administrador. La habilitación de la modalidad contratada se comunica al Anunciante, y las prestaciones adicionales se activan en el mismo acto.']],
                        ['n'=>'E.6','ti'=>'¿Pueden variar las prestaciones o la contraprestación de un plan?',
                         'p'=>['Sí. El Prestador puede actualizarlas comunicando la variación con una anticipación no menor de quince (15) días calendario. El Anunciante que no preste conformidad puede solicitar el retorno al Plan Gratuito.']],
                    ],
                ],
                [
                    'id'     => 'faq-datos',
                    'titulo' => 'F. Sobre los datos personales y la privacidad',
                    'clausulas' => [
                        ['n'=>'F.1','ti'=>'¿Qué información se recaba del Anunciante?',
                         'p'=>['La denominación del establecimiento, el nombre de su titular, el número de teléfono o de aplicación de mensajería, la dirección, los horarios, las formas de pago, los servicios de entrega, las redes sociales y, cuando se consigne, el registro único de contribuyentes. El detalle consta en la cláusula 7.2.']],
                        ['n'=>'F.2','ti'=>'¿Qué información se recaba de quien navega por la Plataforma?',
                         'p'=>['Las búsquedas realizadas, las fichas consultadas, las reacciones y preferencias, y datos técnicos de conexión, con fines estadísticos. Se emplea una cookie propia de carácter anónimo, que no permite identificar directamente a la persona.']],
                        ['n'=>'F.3','ti'=>'¿Se emplean servicios de medición de audiencia de terceros?',
                         'p'=>['Sí. Se emplean servicios de analítica web para la medición de audiencia del sitio. El Usuario puede configurar su navegador para rechazar o eliminar las cookies, sin que ello impida el acceso a los contenidos publicados.']],
                        ['n'=>'F.4','ti'=>'¿Se vende o se cede la información a terceros?',
                         'p'=>['No. La información no es objeto de venta, arrendamiento ni cesión onerosa. Puede comunicarse a proveedores de infraestructura tecnológica y de analítica que prestan servicios al Prestador, y a autoridades competentes cuando medie requerimiento legal.']],
                        ['n'=>'F.5','ti'=>'¿Cómo se ejercen los derechos sobre los datos personales?',
                         'p'=>['Mediante solicitud cursada por el Canal Oficial, en la que se acredite la titularidad de los datos. La solicitud se atiende en los plazos previstos por la Ley N.º 29733, Ley de Protección de Datos Personales, y su reglamento.']],
                        ['n'=>'F.6','ti'=>'¿El Prestador solicita claves o datos de instrumentos de pago?',
                         'p'=>['No, por ningún medio. Toda comunicación que los requiera debe considerarse fraudulenta y ser puesta en conocimiento del Prestador por el Canal Oficial.']],
                    ],
                ],
                [
                    'id'     => 'faq-prohibido',
                    'titulo' => 'G. Sobre contenidos prohibidos y reportes',
                    'clausulas' => [
                        ['n'=>'G.1','ti'=>'¿Qué contenidos se encuentran prohibidos?',
                         'p'=>['Los instrumentos de maltrato animal; los instrumentos peligrosos, comprendiendo la pólvora, los explosivos y las armas de fuego; la pornografía infantil; y todo contenido de incitación al odio, racismo, maltrato social o discriminación de cualquier tipo. La enumeración taxativa consta en la cláusula 5.1.']],
                        ['n'=>'G.2','ti'=>'¿Qué consecuencias irroga la difusión de un contenido prohibido?',
                         'p'=>['El retiro inmediato del contenido y la baja del establecimiento y de la cuenta, sin que proceda devolución, reembolso ni compensación alguna por los importes abonados. Si los hechos pudieran constituir delito, se ponen en conocimiento de las autoridades competentes.']],
                        ['n'=>'G.3','ti'=>'¿Cómo se reporta un contenido presuntamente prohibido?',
                         'p'=>['Mediante los mecanismos de reporte habilitados en la propia Plataforma o por el Canal Oficial. El reporte es evaluado y atendido con la reserva que corresponda.']],
                        ['n'=>'G.4','ti'=>'¿Puede publicarse contenido de terceros sin autorización?',
                         'p'=>['No. Se prohíbe la incorporación de marcas, logotipos, denominaciones o signos distintivos de terceros sin autorización acreditada de su titular, así como toda referencia que induzca a confusión sobre la identidad o el origen comercial del establecimiento.']],
                    ],
                ],
            ],
            'anexos' => [],
        ];

        // =================================================================================
        // III. MANUAL DE USO
        // =================================================================================
        $docs['manual'] = [
            'id'         => 'manual',
            'tipo'       => 'manual',
            'titulo'     => 'Manual de Uso de la Plataforma',
            'subtitulo'  => 'Guía funcional destinada a Anunciantes y Usuarios: descripción y operación '
                          . 'de cada característica del servicio',
            'version'    => 'Versión 2.0',
            'vigencia'   => 'Vigente desde el 21 de septiembre de 2026',
            'actualizado'=> '21 de septiembre de 2026',
            'preambulo'  =>
                'El presente manual describe, de manera ordenada y secuencial, la operación de cada una de '
                . 'las características de la plataforma DeChimbote.com. Se encuentra destinado a los '
                . 'Anunciantes que publican y administran establecimientos y a los Usuarios que consultan '
                . 'el directorio. Cada capítulo expone el procedimiento aplicable, los requisitos que deben '
                . 'satisfacerse y las observaciones que resulten pertinentes para su correcta ejecución. '
                . 'Su contenido es complementario de las Políticas de Privacidad y Condiciones Generales de '
                . 'Uso, cuyo texto prevalece en caso de discrepancia.',
            'resumen'    => [
                'La publicación asistida por inteligencia artificial genera la descripción comercial y el catálogo inicial a partir del material gráfico aportado.',
                'La administración del establecimiento es permanente y no requiere intervención de terceros.',
                'Las estadísticas de visitas, contactos y pedidos se encuentran disponibles en el panel de administración.',
                'La geolocalización ordena los resultados por proximidad respecto de la ubicación autorizada por el Usuario.',
                'La atención automatizada responde consultas sobre el establecimiento con la información en él publicada.',
            ],
            'capitulos'  => [
                [
                    'id'     => 'man-i',
                    'titulo' => 'Capítulo I. Alcance, destinatarios y requisitos',
                    'clausulas' => [
                        ['n'=>'1.1','ti'=>'Alcance del manual',
                         'p'=>['El manual comprende la totalidad de las funciones disponibles en la Plataforma a la fecha de su emisión: publicación y administración de establecimientos, gestión de catálogos, material gráfico, atención automatizada, estadísticas, búsqueda, geolocalización, pedidos y opiniones.']],
                        ['n'=>'1.2','ti'=>'Destinatarios',
                         'p'=>['Se encuentra dirigido a los Anunciantes —personas naturales o jurídicas que publican un establecimiento— y a los Usuarios que consultan el directorio. Los capítulos III a VIII y XI se dirigen principalmente a los primeros; los capítulos IX y X, a ambos.']],
                        ['n'=>'1.3','ti'=>'Requisitos técnicos',
                         'ul'=>['Dispositivo con navegador web actualizado, de escritorio o móvil.',
                                'Conexión a internet estable.',
                                'Número de teléfono operativo con aplicación de mensajería, para la recepción de las comunicaciones del servicio.',
                                'Autorización de acceso a la ubicación, únicamente para el uso de las funciones de proximidad.',
                                'Autorización de acceso a la cámara o a la galería, únicamente para el aporte de material gráfico.'],
                         'av'=>'No se requiere la instalación de programa alguno. La totalidad de las funciones se opera desde el navegador.'],
                        ['n'=>'1.4','ti'=>'Convenciones empleadas',
                         'p'=>['Las instrucciones se exponen en forma secuencial y numerada. Las observaciones consignadas en recuadro contienen advertencias o precisiones que deben tenerse presentes durante la ejecución del procedimiento.']],
                    ],
                ],
                [
                    'id'     => 'man-ii',
                    'titulo' => 'Capítulo II. Acceso, registro de cuenta y credenciales',
                    'clausulas' => [
                        ['n'=>'2.1','ti'=>'Acceso a la Plataforma',
                         'p'=>['El acceso a los contenidos publicados no exige registro. La constitución de una cuenta resulta necesaria para publicar un establecimiento, administrarlo, remitir pedidos y guardar preferencias.']],
                        ['n'=>'2.2','ti'=>'Constitución de la cuenta',
                         'ol'=>['Seleccione la opción de creación de cuenta dispuesta en el menú principal.',
                                'Consigne su nombre, un correo electrónico válido y su número de teléfono.',
                                'Confirme la operación y custodie la clave de acceso que se le proporcione.'],
                         'av'=>'La clave de acceso se entrega por única vez y es de uso personal. Su comunicación a terceros habilita la administración del establecimiento por quien la reciba.'],
                        ['n'=>'2.3','ti'=>'Acceso con credenciales',
                         'ol'=>['Seleccione la opción de ingreso.',
                                'Consigne su número de teléfono o su correo electrónico y su clave.',
                                'Confirme la operación.'],
                         'p2'=>['Si el sistema no reconoce las credenciales, verifique la exactitud de los datos consignados y, de persistir el inconveniente, proceda conforme al apartado 2.4.']],
                        ['n'=>'2.4','ti'=>'Restablecimiento de la clave de acceso',
                         'ol'=>['Seleccione la opción de recuperación de clave.',
                                'Consigne el número de teléfono registrado.',
                                'Solicite la emisión de una nueva clave y aguarde su remisión por el Canal Oficial.'],
                         'av'=>'El personal del Prestador no solicita la clave anterior ni datos de instrumentos de pago. Toda comunicación que los requiera debe considerarse fraudulenta.'],
                        ['n'=>'2.5','ti'=>'Reclamación de un establecimiento ya publicado',
                         'ol'=>['Localice el establecimiento cuya titularidad le corresponde.',
                                'Seleccione la opción de reclamación dispuesta al efecto.',
                                'Acredite la titularidad mediante los extremos que le sean requeridos.',
                                'Recibida la conformidad, asuma la administración de la ficha.'],
                         'p2'=>['La reclamación no irroga costo alguno.']],
                    ],
                ],
                [
                    'id'     => 'man-iii',
                    'titulo' => 'Capítulo III. Publicación del establecimiento',
                    'clausulas' => [
                        ['n'=>'3.1','ti'=>'Publicación asistida por inteligencia artificial',
                         'p'=>['Procedimiento principal, recomendado para el Anunciante que opera por cuenta propia. A partir del material gráfico aportado, el sistema genera la descripción comercial, la clasificación por rubro y el catálogo inicial.'],
                         'ol'=>['Seleccione la opción de creación de tienda en el menú principal.',
                                'Aporte hasta ocho (8) fotografías del establecimiento y de sus productos, tomadas con buena iluminación.',
                                'Confirme la dirección y el distrito del establecimiento.',
                                'Aguarde la generación automática de la descripción y del catálogo inicial.',
                                'Revise el resultado y corríjalo desde el panel de administración, conforme al Capítulo IV.'],
                         'av'=>'La inteligencia artificial propone; la decisión final corresponde al Anunciante. Se recomienda revisar precios, denominaciones y descripciones antes de difundir el enlace del establecimiento.'],
                        ['n'=>'3.2','ti'=>'Publicación abreviada con dispositivo móvil',
                         'p'=>['Procedimiento concebido para el registro presencial del establecimiento, con un mínimo de tres (3) fotografías y un máximo de ocho (8).'],
                         'ol'=>['Acceda a la modalidad de publicación abreviada.',
                                'Aporte las fotografías mediante la cámara del dispositivo.',
                                'Confirme la información propuesta por el sistema.']],
                        ['n'=>'3.3','ti'=>'Verificación del resultado de la publicación',
                         'ol'=>['Confirme la recepción del enlace público del establecimiento, de su usuario y de su clave.',
                                'Abra el enlace y verifique la exactitud de la denominación, el rubro, la dirección, el horario y los datos de contacto.',
                                'Verifique la correspondencia entre cada producto de su catálogo y su imagen.'],
                         'av'=>'El enlace público puede compartirse por aplicaciones de mensajería y redes sociales, y es el medio idóneo para difundir el establecimiento.'],
                    ],
                ],
                [
                    'id'     => 'man-iv',
                    'titulo' => 'Capítulo IV. Administración del establecimiento',
                    'clausulas' => [
                        ['n'=>'4.1','ti'=>'Acceso al panel de administración',
                         'p'=>['El panel de administración concentra la totalidad de las funciones de gestión del establecimiento y se encuentra disponible desde cualquier navegador, previo ingreso con las credenciales del Anunciante.']],
                        ['n'=>'4.2','ti'=>'Información del establecimiento',
                         'ol'=>['Acceda al panel de administración.',
                                'Seleccione el establecimiento que desea modificar.',
                                'Modifique la denominación comercial, la descripción, el horario de atención, la dirección y su referencia de ubicación, las formas de pago admitidas, la modalidad de entrega y las redes sociales.',
                                'Confirme los cambios.'],
                         'av'=>'Las modificaciones son inmediatas y no requieren aprobación previa. Se recomienda mantener actualizado el horario de atención, por ser el dato de mayor consulta.'],
                        ['n'=>'4.3','ti'=>'Sustitución del material gráfico',
                         'p'=>['La portada, la galería y las imágenes de los productos pueden sustituirse cuantas veces se requiera, conforme a los criterios técnicos del Capítulo V.']],
                        ['n'=>'4.4','ti'=>'Adecuaciones introducidas por el Prestador',
                         'p'=>['El Prestador puede adecuar el establecimiento conforme al Capítulo IV de las Políticas. El Anunciante que considere afectada de manera sustancial la información de su ficha puede solicitar la revisión por el Canal Oficial, exponiendo los fundamentos de su pedido.']],
                    ],
                ],
                [
                    'id'     => 'man-v',
                    'titulo' => 'Capítulo V. Material gráfico: criterios y sustitución',
                    'clausulas' => [
                        ['n'=>'5.1','ti'=>'Criterios técnicos recomendados',
                         'ul'=>['Formato: imagen en formato comprimido de uso web (WebP o JPEG).',
                                'Dimensión: lado mayor no inferior a ochocientos (800) píxeles.',
                                'Peso: preferentemente inferior a doscientos (200) kilobytes por imagen, a fin de favorecer la velocidad de carga.',
                                'Orientación: horizontal para la portada; libre para los productos, según su naturaleza.',
                                'Iluminación: uniforme, sin contraluz ni sombras pronunciadas.'],
                         'av'=>'El sistema comprime automáticamente las imágenes aportadas desde un dispositivo móvil, con el objeto de optimizar la velocidad de carga y el consumo de datos.'],
                        ['n'=>'5.2','ti'=>'Criterios de contenido',
                         'ul'=>['La portada debe representar el establecimiento o su producto principal, y no contener precios, números de teléfono, avisos de empleo ni leyendas ajenas a la actividad.',
                                'Las imágenes de productos deben corresponder efectivamente al producto ofrecido.',
                                'No deben incorporarse imágenes de terceros sin autorización, ni marcas o logotipos ajenos.',
                                'No deben incorporarse imágenes que infrinjan el Capítulo V de las Políticas.'],
                         'p2'=>['El Prestador puede sustituir o retirar el material gráfico que no satisfaga estos criterios.']],
                        ['n'=>'5.3','ti'=>'Sustitución de una imagen',
                         'ol'=>['Acceda al panel de administración y seleccione el establecimiento.',
                                'Ubique la imagen que desea sustituir.',
                                'Seleccione la opción de reemplazo.',
                                'Aporte la nueva imagen desde la cámara o desde la galería del dispositivo.',
                                'Confirme la operación y verifique la actualización de la ficha.']],
                        ['n'=>'5.4','ti'=>'Imágenes compartidas entre establecimientos',
                         'p'=>['Cuando un producto carezca de imagen propia, el sistema puede asignarle una imagen representativa de su categoría, procedente del banco de imágenes de la Plataforma. Dicha asignación es provisional y se sustituye automáticamente al aportarse una imagen propia del producto.']],
                    ],
                ],
                [
                    'id'     => 'man-vi',
                    'titulo' => 'Capítulo VI. Catálogo de productos y servicios',
                    'clausulas' => [
                        ['n'=>'6.1','ti'=>'Incorporación de un producto',
                         'ol'=>['Acceda al panel de administración y seleccione el establecimiento.',
                                'Seleccione la opción de incorporación de producto.',
                                'Consigne la denominación, la descripción, el precio y la unidad de medida.',
                                'Aporte la imagen del producto, conforme al Capítulo V.',
                                'Confirme la operación.']],
                        ['n'=>'6.2','ti'=>'Edición y supresión',
                         'p'=>['Todo producto incorporado puede ser editado o suprimido en cualquier momento desde el panel de administración, con efecto inmediato en el catálogo publicado.']],
                        ['n'=>'6.3','ti'=>'Precios y unidades',
                         'ul'=>['Los precios se consignan en soles, con el importe efectivamente aplicable al público.',
                                'Cuando el precio dependa de la cantidad, la presentación o la modalidad de entrega, dicha circunstancia debe consignarse en la descripción del producto.',
                                'Los servicios cuya prestación sea gratuita deben indicarlo expresamente.',
                                'La actualización de precios es responsabilidad del Anunciante y conviene realizarla con la periodicidad que su actividad requiera.'],
                         'av'=>'Los precios publicados son de exclusiva responsabilidad del Anunciante. El Prestador no los modifica.'],
                        ['n'=>'6.4','ti'=>'Carga de catálogos extensos',
                         'p'=>['Para catálogos de gran extensión, el Anunciante puede solicitar por el Canal Oficial la carga asistida de su inventario, comprendiendo denominaciones, descripciones, precios y unidades, así como su actualización periódica. Dicha prestación se encuentra comprendida en la modalidad de mayor alcance descrita en el Capítulo XI.']],
                    ],
                ],
                [
                    'id'     => 'man-vii',
                    'titulo' => 'Capítulo VII. Atención automatizada mediante inteligencia artificial',
                    'clausulas' => [
                        ['n'=>'7.1','ti'=>'Asistente general de la Plataforma',
                         'p'=>['La Plataforma dispone de un asistente automatizado que atiende consultas de carácter general: publicación de establecimientos, gestión de catálogos, localización de negocios próximos, ofertas de empleo, noticias locales y funcionamiento de los planes. Se encuentra disponible en todas las páginas del sitio.']],
                        ['n'=>'7.2','ti'=>'Asistente del establecimiento',
                         'p'=>['Dentro de la ficha de cada establecimiento opera un asistente que responde consultas con la información publicada en ella: productos, precios, horario de atención, dirección y referencia de ubicación, formas de pago y modalidad de entrega.'],
                         'av'=>'El asistente no inventa información ausente: cuando un dato no consta en la ficha, lo señala e invita a formular la consulta por el canal de contacto del establecimiento.'],
                        ['n'=>'7.3','ti'=>'Asistente especializado del establecimiento',
                         'p'=>['La modalidad de mayor alcance comprende la configuración de un asistente especializado, entrenado exclusivamente con el catálogo y la información del establecimiento contratante, cuya operación se ajusta a los mismos principios del apartado precedente.']],
                        ['n'=>'7.4','ti'=>'Consulta mediante imagen',
                         'p'=>['Los asistentes admiten la remisión de una imagen para su interpretación, con el objeto de orientar la consulta del Usuario. Las imágenes remitidas no se publican ni se incorporan al establecimiento.']],
                        ['n'=>'7.5','ti'=>'Límites del servicio',
                         'ul'=>['La atención automatizada no sustituye la atención humana del establecimiento.',
                                'Las consultas que excedan la información publicada se derivan al canal de contacto del Anunciante.',
                                'El servicio no recaba claves, códigos de verificación ni datos de instrumentos de pago.',
                                'La disponibilidad del servicio puede verse afectada por labores de mantenimiento.']],
                    ],
                ],
                [
                    'id'     => 'man-viii',
                    'titulo' => 'Capítulo VIII. Estadísticas, informes y métricas',
                    'clausulas' => [
                        ['n'=>'8.1','ti'=>'Información disponible',
                         'ul'=>['Visitas a la ficha del establecimiento y a cada uno de sus productos.',
                                'Consultas de contacto: comunicaciones iniciadas por mensajería y llamadas telefónicas.',
                                'Pedidos conformados mediante el mecanismo «Me interesa».',
                                'Términos de búsqueda que condujeron al establecimiento.',
                                'Distribución territorial de las visitas.'],
                         'p2'=>['La información se presenta en forma agregada y se actualiza de manera continua.']],
                        ['n'=>'8.2','ti'=>'Modalidades y profundidad de la información',
                         'p'=>['La profundidad de la información estadística depende de la modalidad contratada, conforme a lo publicado en la sección de precios. La modalidad de mayor alcance comprende información en tiempo real y análisis comparativos del rubro.']],
                        ['n'=>'8.3','ti'=>'Interpretación de los indicadores',
                         'ul'=>['El número de visitas mide el interés suscitado por la ficha, no la concreción de ventas.',
                                'Las consultas de contacto constituyen el indicador de mayor proximidad a una operación comercial.',
                                'La comparación entre períodos permite evaluar el efecto de las actualizaciones de catálogo y de material gráfico.',
                                'Los indicadores se elaboran con datos propios de la Plataforma y con servicios de analítica de terceros.'],
                         'av'=>'Se recomienda contrastar la información estadística con los registros propios del establecimiento antes de adoptar decisiones comerciales de importancia.'],
                    ],
                ],
                [
                    'id'     => 'man-ix',
                    'titulo' => 'Capítulo IX. Visibilidad, búsqueda y geolocalización',
                    'clausulas' => [
                        ['n'=>'9.1','ti'=>'Búsqueda por texto',
                         'p'=>['El campo de búsqueda de la cabecera admite la consignación de denominaciones, rubros, productos y términos afines, con tolerancia a errores de escritura y a la omisión de signos diacríticos.']],
                        ['n'=>'9.2','ti'=>'Búsqueda por voz',
                         'ol'=>['Seleccione el ícono de micrófono dispuesto junto al campo de búsqueda.',
                                'Autorice el acceso al micrófono del dispositivo.',
                                'Dicte el término buscado y aguarde su transcripción.',
                                'Confirme la búsqueda.']],
                        ['n'=>'9.3','ti'=>'Búsqueda por proximidad geográfica',
                         'ol'=>['Seleccione el botón de proximidad dispuesto en la portada, en el menú principal o en la ficha de un establecimiento.',
                                'Autorice el acceso a su ubicación.',
                                'El sistema ordena los resultados por distancia y amplía progresivamente el radio de búsqueda hasta reunir un número suficiente de resultados.'],
                         'av'=>'La ubicación se emplea exclusivamente para ordenar los resultados de la búsqueda en curso y no se conserva asociada a la identidad del Usuario.'],
                        ['n'=>'9.4','ti'=>'Condiciones para la correcta aparición en los resultados',
                         'ul'=>['Consignar la dirección exacta y su referencia de ubicación.',
                                'Mantener el catálogo actualizado y con material gráfico propio.',
                                'Mantener activa la ficha mediante la atención de las consultas recibidas.']],
                    ],
                ],
                [
                    'id'     => 'man-x',
                    'titulo' => 'Capítulo X. Pedidos, consultas y opiniones',
                    'clausulas' => [
                        ['n'=>'10.1','ti'=>'Conformación de un pedido',
                         'ol'=>['Seleccione los productos de su interés mediante el mecanismo «Me interesa».',
                                'Revise el resumen del pedido conformado, con precios y cantidades.',
                                'Remita el pedido al establecimiento por la aplicación de mensajería habilitada.',
                                'Aguarde la confirmación del Anunciante respecto de la disponibilidad y las condiciones de entrega.'],
                         'av'=>'La Plataforma no procesa pagos ni interviene en la operación. La confirmación del pedido, su pago y su entrega se acuerdan directamente con el establecimiento.'],
                        ['n'=>'10.2','ti'=>'Consultas directas',
                         'p'=>['Cada establecimiento publica sus canales de contacto —mensajería y llamada telefónica— con el mensaje de apertura ya redactado, a efectos de facilitar la consulta.']],
                        ['n'=>'10.3','ti'=>'Publicación de una opinión',
                         'ol'=>['Acceda a la sección de opiniones de la ficha.',
                                'Seleccione la opción de redacción de opinión.',
                                'Consigne su manifestación y la calificación correspondiente.',
                                'Confirme su publicación.'],
                         'p2'=>['La opinión se publica sin el nombre completo de su autor. Las opiniones que infrinjan el Capítulo V de las Políticas, o que resulten falsas o injuriosas, pueden ser reportadas y son retiradas.']],
                        ['n'=>'10.4','ti'=>'Reporte de una opinión',
                         'p'=>['Toda opinión admite su reporte mediante el mecanismo dispuesto al efecto. El reporte es evaluado por el Prestador, que resuelve su retiro o su conservación.']],
                    ],
                ],
                [
                    'id'     => 'man-xi',
                    'titulo' => 'Capítulo XI. Planes: contratación, modificación y efectos',
                    'clausulas' => [
                        ['n'=>'11.1','ti'=>'Consulta de las modalidades disponibles',
                         'p'=>['Las prestaciones y la contraprestación vigente de cada modalidad se publican en la sección de precios de la Plataforma, de consulta permanente y gratuita.']],
                        ['n'=>'11.2','ti'=>'Contratación y activación',
                         'ol'=>['Solicite la contratación por el Canal Oficial, indicando la modalidad deseada y la denominación del establecimiento.',
                                'Aguarde la comunicación de habilitación.',
                                'Verifique la activación de las prestaciones adicionales en su panel de administración.']],
                        ['n'=>'11.3','ti'=>'Cambio de modalidad y retorno al Plan Gratuito',
                         'p'=>['El Anunciante puede solicitar el cambio de modalidad o el retorno al Plan Gratuito en cualquier momento y sin permanencia mínima. La falta de pago produce el retorno automático al Plan Gratuito, sin pérdida del establecimiento ni de sus registros.']],
                        ['n'=>'11.4','ti'=>'Efectos de la baja',
                         'p'=>['Las consecuencias de la baja del establecimiento, y en particular la extracción de la base de datos en los Planes de Pago, se rigen por el Capítulo VI de las Políticas de Privacidad y Condiciones Generales de Uso.']],
                    ],
                ],
                [
                    'id'     => 'man-xii',
                    'titulo' => 'Capítulo XII. Soporte, incidencias y buenas prácticas',
                    'clausulas' => [
                        ['n'=>'12.1','ti'=>'Canales de soporte',
                         'p'=>['La atención se brinda por el Canal Oficial consignado en el pie de la Plataforma y por el asistente automatizado disponible en todas las páginas del sitio.']],
                        ['n'=>'12.2','ti'=>'Comunicación de una incidencia',
                         'ol'=>['Describa la incidencia con la mayor precisión posible.',
                                'Indique la dirección de la página en la que se presentó y el dispositivo empleado.',
                                'Aporte, cuando resulte posible, una captura de pantalla.',
                                'Aguarde la respuesta del equipo de soporte.']],
                        ['n'=>'12.3','ti'=>'Buenas prácticas recomendadas',
                         'ul'=>['Mantener actualizados el horario de atención, los precios y la disponibilidad.',
                                'Aportar material gráfico propio, con buena iluminación y sin leyendas ajenas.',
                                'Atender con prontitud las consultas recibidas por los canales de contacto publicados.',
                                'Revisar periódicamente la información estadística del establecimiento.',
                                'Custodiar las credenciales de acceso y no comunicarlas a terceros.']],
                        ['n'=>'12.4','ti'=>'Actualización del manual',
                         'p'=>['El manual se actualiza conforme a la evolución de las funciones de la Plataforma. La versión vigente es la publicada, con indicación de su fecha de entrada en vigor, y su versión descargable en formato PDF corresponde a esa misma versión.']],
                    ],
                ],
            ],
            'anexos' => [
                [
                    'titulo' => 'Anexo. Índice de características descritas',
                    'ul'     => [
                        'Publicación asistida por inteligencia artificial — Capítulo III.',
                        'Publicación abreviada con dispositivo móvil — Capítulo III.',
                        'Administración del establecimiento — Capítulo IV.',
                        'Material gráfico y su sustitución — Capítulo V.',
                        'Catálogo de productos y servicios — Capítulo VI.',
                        'Atención automatizada — Capítulo VII.',
                        'Estadísticas e informes — Capítulo VIII.',
                        'Búsqueda por texto, por voz y por proximidad — Capítulo IX.',
                        'Pedidos, consultas y opiniones — Capítulo X.',
                        'Planes y modalidades — Capítulo XI.',
                        'Soporte e incidencias — Capítulo XII.',
                    ],
                ],
            ],
        ];

        return $docs;
    }

    /**
     * Devuelve un documento por su identificador.
     * @param string $id  'privacidad' | 'manual' | 'faq'
     */
    function doc_legal($id) {
        $docs = doc_legal_textos();
        if (!isset($docs[$id])) return null;
        $d = $docs[$id];

        // Los tres documentos se publican y se sirven con las mismas claves, para que los render no
        // tengan que preguntar de cuál se trata.
        $d += ['resumen' => [], 'capitulos' => [], 'anexos' => []];
        return $d;
    }

    /** El rótulo del ítem numerado, según el tipo de documento (cláusula · pregunta · apartado). */
    function doc_legal_rotulo($tipo) {
        if ($tipo === 'faq')    return 'Pregunta';
        if ($tipo === 'manual') return 'Apartado';
        return 'Cláusula';
    }

    // =================================================================================
    // 🚪 LAS DIRECCIONES DE LA DOCUMENTACIÓN (una página por capítulo: SIN ANCLAS)
    // ---------------------------------------------------------------------------------
    // Orden del jefe (2026-09-21): «no poner enlaces de anclas dentro de la misma página». Por eso cada
    // capítulo de cada documento tiene su **propia página** y el índice del documento es un **menú
    // interno** que lleva de una página a otra.
    // =================================================================================

    /** La parte de URL que le toca a cada documento. */
    function doc_legal_ruta($id) {
        $rutas = ['privacidad' => 'privacidad', 'faq' => 'preguntas-frecuentes', 'manual' => 'manual-de-uso'];
        return $rutas[$id] ?? (string)$id;
    }

    /** La URL de un documento, o la de uno de sus capítulos (cada uno es una página). */
    function doc_legal_url($id, $cap_slug = '') {
        $u = doc_legal_ruta($id);
        if ((string)$cap_slug !== '') $u .= '/' . $cap_slug;
        return url($u);
    }

    /**
     * Convierte el título de un capítulo en una dirección legible:
     * «Capítulo III. Titularidad de los contenidos incorporados» → «titularidad-de-los-contenidos-incorporados».
     */
    function doc_cap_slug($titulo) {
        $t = (string)$titulo;
        // Fuera el rótulo («Capítulo III.», «Sección A.», «A.»): la dirección la dice el asunto.
        $t = preg_replace('/^(Capítulo|Sección|Apartado)\s+[IVXLC0-9]+\s*[.\-–—]\s*/u', '', $t);
        $t = preg_replace('/^[A-Z]\.\s*/u', '', $t);
        $t = strtr($t, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        ]);
        $t = mb_strtolower($t, 'UTF-8');
        $t = preg_replace('/[^a-z0-9]+/u', '-', $t);
        $t = trim((string)$t, '-');
        if ($t === '') $t = 'capitulo';
        return substr($t, 0, 60);
    }

    /** El mapa del documento: dirección del capítulo → índice (garantiza direcciones únicas). */
    function doc_legal_mapa($doc) {
        static $cache = [];
        $id = (string)($doc['id'] ?? '');
        if (isset($cache[$id])) return $cache[$id];

        $mapa = [];
        foreach ($doc['capitulos'] as $i => $cap) {
            $base = doc_cap_slug((string)$cap['titulo']);
            $slug = $base; $n = 2;
            while (isset($mapa[$slug])) { $slug = $base . '-' . $n; $n++; }
            $mapa[$slug] = $i;
        }
        // Los anexos, si el documento los tiene, son una página más (siempre al final).
        if (!empty($doc['anexos'])) {
            $base = 'anexos'; $slug = $base; $n = 2;
            while (isset($mapa[$slug])) { $slug = $base . '-' . $n; $n++; }
            $mapa[$slug] = 'anexos';
        }
        $cache[$id] = $mapa;
        return $mapa;
    }

    /** El título que corresponde a una dirección del documento. */
    function doc_cap_titulo($doc, $slug) {
        $mapa = doc_legal_mapa($doc);
        if (!isset($mapa[$slug])) return '';
        $i = $mapa[$slug];
        return ($i === 'anexos') ? 'Anexos' : (string)$doc['capitulos'][$i]['titulo'];
    }

    /**
     * El capítulo que corresponde a una dirección, con sus vecinos (para los botones anterior/siguiente).
     * Devuelve null si la dirección no existe: quien lo llame debe responder 404.
     */
    function doc_legal_capitulo($doc, $slug) {
        $mapa = doc_legal_mapa($doc);
        if (!isset($mapa[$slug])) return null;

        $claves = array_keys($mapa);
        $pos    = array_search($slug, $claves, true);
        $i      = $mapa[$slug];

        $vecino = function ($p) use ($claves, $doc) {
            if ($p < 0 || $p >= count($claves)) return null;
            $s = $claves[$p];
            return ['slug' => $s, 'titulo' => doc_cap_titulo($doc, $s)];
        };

        if ($i === 'anexos') {
            return [
                'slug' => $slug, 'indice' => null, 'anexos' => true,
                'titulo' => 'Anexos',
                'cap' => ['id' => 'anexos', 'titulo' => 'Anexos', 'clausulas' => []],
                'anterior' => $vecino($pos - 1), 'siguiente' => null,
            ];
        }

        return [
            'slug' => $slug, 'indice' => (int)$i, 'anexos' => false,
            'titulo' => (string)$doc['capitulos'][$i]['titulo'],
            'cap' => $doc['capitulos'][$i],
            'anterior' => $vecino($pos - 1), 'siguiente' => $vecino($pos + 1),
        ];
    }
}
