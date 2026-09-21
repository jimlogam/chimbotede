<?php
/**
 * includes/pdf_simple.php — 🖨️ GENERADOR DE PDF SIN DEPENDENCIAS
 * ==============================================================
 * Escribe un PDF 1.4 válido a mano, con las fuentes estándar del formato (Helvetica, Helvetica-Bold,
 * Helvetica-Oblique), sin librerías externas, sin Composer y sin binarios: es lo único que puede
 * correr en este hosting compartido. Lo usan los documentos oficiales del sitio (Políticas de
 * Privacidad, Preguntas Frecuentes y Manual de Uso) a través de `documento_pdf.php`.
 *
 * Qué sabe hacer (lo que necesitan los documentos):
 *   · Página A4 con márgenes, encabezado y pie con numeración («Página X de Y»).
 *   · Portada institucional con banda de color.
 *   · Índice con los capítulos y su número de página (se calcula en dos pasadas).
 *   · Títulos de capítulo con filete, apartados numerados, párrafos **justificados**, listas con
 *     viñeta y listas numeradas, y recuadros de observación con filete lateral.
 *   · Marcado mínimo en línea (`<b>` y `<i>`) interpretado como negrita y cursiva de verdad.
 *
 * Detalles técnicos que conviene no olvidar si algún día se toca este archivo:
 *   · El texto se convierte a **Windows-1252** (la codificación que declaran las fuentes con
 *     `/WinAnsiEncoding`): así las tildes, la eñe y los signos de apertura salen correctos.
 *   · El ancho de cada línea se calcula con la **tabla de anchos de Helvetica** (unidades de
 *     1/1000 em): sin eso, el texto se saldría del margen o quedaría desprolijo.
 *   · La justificación se logra con el operador **Tw** (espaciado entre palabras), que es la forma
 *     correcta de estirar una línea sin deformar las letras.
 *   · No se comprime nada: el archivo pesa lo que pesa el texto y se abre en cualquier lector.
 */

if (!class_exists('PdfSimple')) {

class PdfSimple {

    // ---- Medidas (puntos tipográficos: 1 pt = 1/72 pulgada) ----
    public $W = 595.28;          // A4 de ancho
    public $H = 841.89;          // A4 de alto
    public $ml = 64;             // margen izquierdo
    public $mr = 64;             // margen derecho
    public $mt = 62;             // margen superior (debajo del encabezado)
    public $mb = 58;             // margen inferior (encima del pie)

    // ---- Estado ----
    private $paginas = [];       // cada entrada: ['ops' => string, 'portada' => bool]
    private $ops = '';
    private $y = 0;
    private $titulo_doc = '';
    private $ancho_tabla = [];
    private $mapa_alta = [];
    private $en_portada = false;

    /** Colores de la casa (los mismos del sitio, en notación PDF 0..1). */
    const C_GRANATE = '0.427 0.027 0.102';
    const C_TEXTO   = '0.153 0.153 0.161';
    const C_GRIS    = '0.42 0.42 0.45';
    const C_FILE    = '0.878 0.85 0.855';

    public function __construct($titulo = '') {
        $this->titulo_doc = (string)$titulo;
        $this->tablas();
        $this->y = $this->H - $this->mt;
    }

    // =================================================================================
    // TABLAS DE ANCHOS (Helvetica y Helvetica-Bold, ASCII 32..126)
    // =================================================================================
    private function tablas() {
        $helv = '278 278 355 556 556 889 667 191 333 333 389 584 278 333 278 278 556 556 556 556 556 556 556 556 556 556 278 278 584 584 584 556 1015 667 667 722 722 667 611 778 722 278 500 667 556 833 722 778 667 778 722 667 611 722 667 944 667 667 611 278 278 278 469 556 333 556 556 500 556 556 278 556 556 222 222 500 222 833 556 556 556 556 333 500 278 556 500 722 500 500 500 334 260 334 584';
        $bold = '278 333 474 556 556 889 722 238 333 333 389 584 278 333 278 278 556 556 556 556 556 556 556 556 556 556 333 333 584 584 584 611 975 722 722 722 722 667 611 778 722 278 556 722 611 833 722 778 667 778 722 667 611 722 667 944 667 667 611 333 278 333 584 556 333 556 611 556 611 556 333 611 611 278 278 556 278 889 611 611 611 611 389 556 333 611 556 778 556 556 500 389 280 389 584';
        $this->ancho_tabla = [
            'normal' => array_map('intval', explode(' ', $helv)),
            'bold'   => array_map('intval', explode(' ', $bold)),
        ];

        // Las letras acentuadas de Windows-1252 miden como su letra base (diferencia despreciable).
        $pares = [
            0xC0=>0x41, 0xC1=>0x41, 0xC2=>0x41, 0xC3=>0x41, 0xC4=>0x41, 0xC5=>0x41, 0xC7=>0x43,
            0xC8=>0x45, 0xC9=>0x45, 0xCA=>0x45, 0xCB=>0x45, 0xCC=>0x49, 0xCD=>0x49, 0xCE=>0x49,
            0xCF=>0x49, 0xD1=>0x4E, 0xD2=>0x4F, 0xD3=>0x4F, 0xD4=>0x4F, 0xD5=>0x4F, 0xD6=>0x4F,
            0xD8=>0x4F, 0xD9=>0x55, 0xDA=>0x55, 0xDB=>0x55, 0xDC=>0x55, 0xDD=>0x59,
            0xE0=>0x61, 0xE1=>0x61, 0xE2=>0x61, 0xE3=>0x61, 0xE4=>0x61, 0xE5=>0x61, 0xE7=>0x63,
            0xE8=>0x65, 0xE9=>0x65, 0xEA=>0x65, 0xEB=>0x65, 0xEC=>0x69, 0xED=>0x69, 0xEE=>0x69,
            0xEF=>0x69, 0xF1=>0x6E, 0xF2=>0x6F, 0xF3=>0x6F, 0xF4=>0x6F, 0xF5=>0x6F, 0xF6=>0x6F,
            0xF8=>0x6F, 0xF9=>0x75, 0xFA=>0x75, 0xFB=>0x75, 0xFC=>0x75, 0xFD=>0x79, 0xFF=>0x79,
            0xA0=>0x20,                     // espacio duro
            0xBF=>0x3F, 0xA1=>0x21,         // ¿ ¡
            0xAA=>0x61, 0xBA=>0x6F,         // ª º
            0xB0=>0x30,                     // °
            0xAB=>0x22, 0xBB=>0x22,         // « »
            0x91=>0x27, 0x92=>0x27,         // ‘ ’
            0x93=>0x22, 0x94=>0x22,         // “ ”
            0x96=>0x2D, 0x97=>0x2D,         // – —
            0x85=>0x2E,                     // …
            0x80=>0x30,                     // €
        ];
        $this->mapa_alta = $pares;
    }

    /** Ancho de un texto, en puntos, para un tamaño y un estilo dados. */
    public function ancho($txt, $tam, $estilo = 'normal') {
        $bytes = $this->bytes($txt);
        $tabla = $this->ancho_tabla[($estilo === 'bold') ? 'bold' : 'normal'];
        $total = 0;
        for ($i = 0, $n = strlen($bytes); $i < $n; $i++) {
            $b = ord($bytes[$i]);
            if ($b >= 32 && $b <= 126) {
                $total += $tabla[$b - 32];
            } elseif (isset($this->mapa_alta[$b])) {
                $base = $this->mapa_alta[$b];
                $total += ($base >= 32 && $base <= 126) ? $tabla[$base - 32] : 500;
            } else {
                $total += 556;
            }
        }
        return $total * $tam / 1000;
    }

    /** Convierte a Windows-1252 (la codificación de las fuentes estándar del PDF). */
    private function bytes($txt) {
        $txt = (string)$txt;
        $txt = str_replace(["\r\n", "\r", "\t", "\xE2\x80\xA6"], ["\n", "\n", ' ', '...'], $txt);
        if (function_exists('iconv')) {
            $conv = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $txt);
            if ($conv !== false) return $conv;
        }
        return $txt;
    }

    // =================================================================================
    // PÁGINAS Y DIBUJO
    // =================================================================================
    /** Cierra la página en curso y abre una nueva. */
    public function cerrar_pagina() {
        $this->paginas[] = ['ops' => $this->ops, 'portada' => $this->en_portada];
        $this->ops = '';
        $this->en_portada = false;
        $this->y = $this->H - $this->mt;
        return $this;
    }

    public function alto_util() { return $this->H - $this->mt - $this->mb; }
    public function y() { return $this->y; }
    public function mover($dy) { $this->y -= $dy; return $this; }
    /** Cuántas páginas lleva cerradas (sin contar la que está en curso). */
    public function paginas_cerradas() { return count($this->paginas); }

    /** La página en la que se está escribiendo ahora mismo (1 = la primera). */
    public function pagina_actual() { return count($this->paginas) + 1; }

    /** Salta de página si no quedan al menos $alto puntos útiles. */
    public function cabe($alto) {
        if ($this->y - $alto < $this->mb) { $this->cerrar_pagina(); }
        return $this;
    }

    private function esc($txt) {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $this->bytes($txt));
    }

    /** Escribe una línea (sin partir) en la posición indicada. */
    public function linea_en($txt, $x, $y, $tam = 10.5, $estilo = 'normal', $color = self::C_TEXTO, $tw = 0) {
        if ($txt === '') return;
        $f = $this->fuente($estilo);
        $this->ops .= sprintf("BT /%s %.2F Tf %s rg %.2F Tw %.2F %.2F Td (%s) Tj ET\n",
            $f, $tam, $color, (float)$tw, $x, $y, $this->esc($txt));
    }

    private function fuente($estilo) {
        if ($estilo === 'bold')   return 'F2';
        if ($estilo === 'italic') return 'F3';
        return 'F1';
    }

    /** Rectángulo relleno. */
    public function rect($x, $y, $w, $h, $color) {
        $this->ops .= sprintf("%s rg %.2F %.2F %.2F %.2F re f\n", $color, $x, $y, $w, $h);
    }
    /** Línea. */
    public function raya($x1, $y1, $x2, $y2, $color = self::C_FILE, $grosor = 0.7) {
        $this->ops .= sprintf("%s RG %.2F w %.2F %.2F m %.2F %.2F l S\n", $color, $grosor, $x1, $y1, $x2, $y2);
    }

    // =================================================================================
    // TEXTO CON MARCADO EN LÍNEA Y JUSTIFICACIÓN
    // =================================================================================
    /**
     * Convierte un texto con <b> y <i> en una lista de tramos: [ ['t'=>texto, 'e'=>estilo], … ].
     */
    public function tramos($txt) {
        $txt = (string)$txt;
        $txt = preg_replace('/<(b|strong)>/i', '<b>', $txt);
        $txt = preg_replace('/<\/(b|strong)>/i', '</b>', $txt);
        $txt = preg_replace('/<(i|em)>/i', '<i>', $txt);
        $txt = preg_replace('/<\/(i|em)>/i', '</i>', $txt);
        $partes = preg_split('/(<b>|<\/b>|<i>|<\/i>)/', $txt, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = [];
        $neg = false; $cur = false;
        foreach ($partes as $p) {
            if ($p === '<b>')  { $neg = true;  continue; }
            if ($p === '</b>') { $neg = false; continue; }
            if ($p === '<i>')  { $cur = true;  continue; }
            if ($p === '</i>') { $cur = false; continue; }
            if ($p === '') continue;
            $estilo = $neg ? 'bold' : ($cur ? 'italic' : 'normal');
            $out[] = ['t' => $p, 'e' => $estilo];
        }
        return $out;
    }

    /**
     * Parte un texto en líneas que quepan en $ancho.
     * Devuelve [ ['tramos' => [...], 'ancho' => float, 'espacios' => int], … ]
     */
    public function partir($txt, $tam, $ancho) {
        $tramos = $this->tramos($txt);
        $palabras = [];
        $primera = true;
        foreach ($tramos as $tr) {
            $trozos = preg_split('/\s+/u', trim($tr['t']));
            foreach ($trozos as $p) {
                if ($p === '') continue;
                // ⚠️ Un signo de puntuación al empezar un tramo va PEGADO a la palabra anterior: si se
                //    le metiera el espacio de separación, saldría «reclamo . En consecuencia».
                $pega = (bool)preg_match('/^[.,;:)\]}»?!…%]/u', $p);
                $palabras[] = ['t' => $p, 'e' => $tr['e'], 'espacio' => (!$primera && !$pega)];
                $primera = false;
            }
        }
        if (!$palabras) return [];

        $lineas = [];
        $actual = []; $ancho_actual = 0.0; $espacios = 0;
        foreach ($palabras as $pal) {
            $ancho_pal = $this->ancho($pal['t'], $tam, $pal['e']);
            $ancho_sep = ($pal['espacio'] && $ancho_actual > 0) ? $this->ancho(' ', $tam, $pal['e']) : 0;
            if ($ancho_actual > 0 && $ancho_actual + $ancho_sep + $ancho_pal > $ancho) {
                $lineas[] = ['tramos' => $actual, 'ancho' => $ancho_actual, 'espacios' => $espacios];
                $actual = []; $ancho_actual = 0.0; $espacios = 0; $ancho_sep = 0;
            }
            if ($ancho_sep > 0) { $espacios++; $ancho_actual += $ancho_sep; }
            $texto_pal = ($ancho_sep > 0 ? ' ' : '') . $pal['t'];
            if (!empty($actual) && end($actual)['e'] === $pal['e']) {
                $ult = array_pop($actual);
                $actual[] = ['t' => $ult['t'] . $texto_pal, 'e' => $pal['e']];
            } else {
                $actual[] = ['t' => $texto_pal, 'e' => $pal['e']];
            }
            $ancho_actual += $ancho_pal;
        }
        if ($actual) $lineas[] = ['tramos' => $actual, 'ancho' => $ancho_actual, 'espacios' => $espacios];
        return $lineas;
    }

    /**
     * Párrafo justificado (la última línea no se estira, como manda la tipografía).
     * @return float  El espacio vertical consumido.
     */
    public function parrafo($txt, $tam = 10.5, $interlinea = 1.48, $x_extra = 0, $ancho_extra = 0, $color = self::C_TEXTO) {
        $x = $this->ml + $x_extra;
        $ancho = $this->W - $this->ml - $this->mr - $ancho_extra - $x_extra;
        $lineas = $this->partir($txt, $tam, $ancho);
        if (!$lineas) return 0;
        $alto = 0;
        $n = count($lineas);
        foreach ($lineas as $i => $ln) {
            $salto = $tam * $interlinea;
            if ($this->y - $salto < $this->mb) { $this->cerrar_pagina(); }
            $this->y -= $salto;
            $alto += $salto;
            $justificar = ($i < $n - 1) && ($ln['espacios'] > 0);
            $sobra = $justificar ? max(0.0, ($ancho - $ln['ancho']) / $ln['espacios']) : 0.0;
            $cx = $x;
            foreach ($ln['tramos'] as $tr) {
                $this->linea_en($tr['t'], $cx, $this->y, $tam, $tr['e'], $color, $sobra);
                $cx += $this->ancho($tr['t'], $tam, $tr['e']) + ($sobra * substr_count($tr['t'], ' '));
            }
        }
        return $alto;
    }

    /** Añade espacio vertical en blanco. */
    public function blanco($dy) { $this->y -= $dy; return $this; }

    // =================================================================================
    // PIEZAS DEL DOCUMENTO
    // =================================================================================
    /** Portada institucional (siempre la primera página: se dibuja sobre ella). */
    public function portada($titulo, $subtitulo, $meta = [], $coleccion = []) {
        $this->ops = '';
        $this->en_portada = true;

        // Banda superior granate a sangre
        $this->rect(0, $this->H - 210, $this->W, 210, self::C_GRANATE);
        $this->linea_en('DECHIMBOTE.COM', $this->ml, $this->H - 76, 11, 'bold', '1 1 1');
        $this->linea_en('DOCUMENTACIÓN OFICIAL', $this->ml, $this->H - 92, 8.4, 'normal', '0.92 0.88 0.89');

        // Título (se parte solo si hace falta)
        $lineas = $this->partir($titulo, 23, $this->W - 2 * $this->ml);
        $yy = $this->H - 136;
        foreach ($lineas as $ln) {
            $cx = $this->ml;
            foreach ($ln['tramos'] as $tr) {
                $this->linea_en($tr['t'], $cx, $yy, 23, 'bold', '1 1 1');
                $cx += $this->ancho($tr['t'], 23, $tr['e']);
            }
            $yy -= 28;
        }

        $this->y = $this->H - 250;
        $this->parrafo($subtitulo, 11.5, 1.5, 0, 0, self::C_GRIS);
        $this->blanco(18);
        foreach ($meta as $m) {
            $this->parrafo($m, 10.5, 1.4);
            $this->blanco(2);
        }

        // Pie de portada: la colección documental
        $y0 = 190;
        $this->raya($this->ml, $y0 + 26, $this->W - $this->mr, $y0 + 26, self::C_FILE, 0.8);
        $this->linea_en('Documentación disponible', $this->ml, $y0 + 8, 9.5, 'bold', self::C_GRANATE);
        $yy = $y0 - 8;
        foreach ($coleccion as $c) {
            $this->linea_en($c, $this->ml, $yy, 9.5, 'normal', self::C_TEXTO);
            $yy -= 13;
        }
        $this->linea_en('dechimbote.com', $this->ml, $this->mb + 10, 9, 'normal', self::C_GRIS);
        return $this;
    }

    /** Página de índice con los capítulos y su página. */
    public function indice($titulo, $entradas) {
        $this->cerrar_pagina();
        $this->linea_en('ÍNDICE', $this->ml, $this->y, 9, 'bold', self::C_GRANATE);
        $this->y -= 12;
        $this->linea_en($titulo, $this->ml, $this->y, 15, 'bold', self::C_TEXTO);
        $this->y -= 16;
        $this->raya($this->ml, $this->y, $this->W - $this->mr, $this->y, self::C_GRANATE, 1.1);
        $this->y -= 22;

        foreach ($entradas as $e) {
            if ($this->y - 16 < $this->mb) { $this->cerrar_pagina(); }
            $num = (string)$e['pagina'];
            $ancho_num = $this->ancho($num, 10.5, 'normal');
            $disponible = $this->W - $this->ml - $this->mr - $ancho_num - 14;
            $lineas = $this->partir($e['titulo'], 10.5, $disponible);
            $primera = true;
            foreach ($lineas as $ln) {
                $this->y -= 15.5;
                $cx = $this->ml + 16;
                foreach ($ln['tramos'] as $tr) {
                    $this->linea_en($tr['t'], $cx, $this->y, 10.5, $tr['e'], self::C_TEXTO);
                    $cx += $this->ancho($tr['t'], 10.5, $tr['e']);
                }
                if ($primera) {
                    $this->linea_en($num, $this->W - $this->mr - $ancho_num, $this->y, 10.5, 'bold', self::C_GRANATE);
                    $primera = false;
                }
            }
            $this->y -= 3;
        }
        return $this;
    }

    /** Título de capítulo (puede empezar página nueva). */
    public function capitulo($titulo, $nueva_pagina = false) {
        if ($nueva_pagina) {
            $this->cerrar_pagina();
        } else {
            $this->cabe(70);
        }
        $this->y -= 12;
        $lineas = $this->partir($titulo, 13.5, $this->W - $this->ml - $this->mr);
        foreach ($lineas as $ln) {
            $this->y -= 19;
            $cx = $this->ml;
            foreach ($ln['tramos'] as $tr) {
                $this->linea_en($tr['t'], $cx, $this->y, 13.5, 'bold', self::C_GRANATE);
                $cx += $this->ancho($tr['t'], 13.5, $tr['e']);
            }
        }
        $this->y -= 5;
        $this->raya($this->ml, $this->y, $this->W - $this->mr, $this->y, self::C_GRANATE, 0.9);
        $this->y -= 16;
        return $this;
    }

    /** Apartado: número y título en negrita, y luego su contenido. */
    public function apartado($n, $titulo, $cuerpo) {
        $this->cabe(56);
        $encabezado = ($n === '' ? '' : $n . '.  ') . $titulo;
        $lineas = $this->partir($encabezado, 10.8, $this->W - $this->ml - $this->mr);
        foreach ($lineas as $ln) {
            $this->y -= 15.5;
            $cx = $this->ml;
            foreach ($ln['tramos'] as $tr) {
                $this->linea_en($tr['t'], $cx, $this->y, 10.8, 'bold', self::C_TEXTO);
                $cx += $this->ancho($tr['t'], 10.8, $tr['e']);
            }
        }
        $this->y -= 4;
        return $this->cuerpo($cuerpo);
    }

    /** El contenido de un apartado: párrafos, listas y observación. */
    public function cuerpo($cuerpo) {
        foreach (($cuerpo['p'] ?? []) as $par) {
            $this->cabe(30);
            $this->parrafo($par, 10.3, 1.5, 0, 0);
            $this->blanco(6);
        }
        if (!empty($cuerpo['ul'])) {
            foreach ($cuerpo['ul'] as $item) {
                $this->vineta($item);
            }
            $this->blanco(5);
        }
        if (!empty($cuerpo['ol'])) {
            $i = 1;
            foreach ($cuerpo['ol'] as $item) {
                $this->vineta($item, $i . '.');
                $i++;
            }
            $this->blanco(5);
        }
        if (!empty($cuerpo['av'])) {
            $this->observacion($cuerpo['av']);
        }
        foreach (($cuerpo['p2'] ?? []) as $par) {
            $this->cabe(30);
            $this->parrafo($par, 10.3, 1.5, 0, 0);
            $this->blanco(6);
        }
        return $this;
    }

    /** Elemento de lista (viñeta o número). */
    public function vineta($txt, $marca = '') {
        $sangria = 18;
        $this->cabe(28);
        $y_inicio = $this->y;
        $this->parrafo($txt, 10.3, 1.48, $sangria, 0);
        if ($marca === '') {
            // Cuadrado granate de viñeta, alineado con la primera línea del texto.
            $this->ops .= sprintf("%s rg %.2F %.2F 2.8 2.8 re f\n",
                self::C_GRANATE, $this->ml + 5, $y_inicio - 10.6);
        } else {
            $this->linea_en($marca, $this->ml + 1, $y_inicio - 11, 10.3, 'bold', self::C_GRANATE);
        }
        $this->blanco(3);
        return $this;
    }

    /** Recuadro de observación con filete lateral. */
    public function observacion($txt) {
        $this->cabe(40);
        $y0 = $this->y;
        $this->y -= 8;
        $this->parrafo('<b>Observación.</b> ' . $txt, 10, 1.45, 14, 4, '0.35 0.27 0.15');
        $this->y -= 8;
        $this->raya($this->ml + 4, $y0 - 2, $this->ml + 4, $this->y + 2, '0.71 0.51 0.20', 1.6);
        $this->blanco(6);
        return $this;
    }

    // =================================================================================
    // SALIDA
    // =================================================================================
    /** Cierra la última página y arma el archivo. */
    public function salir() {
        if ($this->ops !== '' || empty($this->paginas)) { $this->cerrar_pagina(); }
        $n = count($this->paginas);
        if ($n === 0) { $this->paginas[] = ['ops' => '', 'portada' => false]; $n = 1; }

        // Encabezado y pie de las páginas que NO son portada
        foreach ($this->paginas as $i => &$p) {
            if (!empty($p['portada'])) continue;
            $extra = '';
            // Encabezado
            $extra .= sprintf("BT /F1 7.8 Tf %s rg %.2F %.2F Td (%s) Tj ET\n",
                self::C_GRIS, $this->ml, $this->H - 40, $this->esc($this->titulo_doc));
            $extra .= sprintf("BT /F1 7.8 Tf %s rg %.2F %.2F Td (dechimbote.com) Tj ET\n",
                self::C_GRIS, $this->W - $this->mr - $this->ancho('dechimbote.com', 7.8), $this->H - 40);
            $extra .= sprintf("%s RG 0.6 w %.2F %.2F m %.2F %.2F l S\n",
                self::C_FILE, $this->ml, $this->H - 46, $this->W - $this->mr, $this->H - 46);
            // Pie con numeración
            $pie = 'Página ' . ($i + 1) . ' de ' . $n;
            $extra .= sprintf("%s RG 0.6 w %.2F %.2F m %.2F %.2F l S\n",
                self::C_FILE, $this->ml, $this->mb - 14, $this->W - $this->mr, $this->mb - 14);
            $extra .= sprintf("BT /F1 7.8 Tf %s rg %.2F %.2F Td (%s) Tj ET\n",
                self::C_GRIS, $this->ml, $this->mb - 25, $this->esc($this->titulo_doc));
            $extra .= sprintf("BT /F1 7.8 Tf %s rg %.2F %.2F Td (%s) Tj ET\n",
                self::C_GRIS, $this->W - $this->mr - $this->ancho($pie, 7.8), $this->mb - 25, $this->esc($pie));
            $p['ops'] = $extra . $p['ops'];
        }
        unset($p);

        // Armado del archivo
        $objs = [];
        $objs[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objs[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $objs[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";
        $objs[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique /Encoding /WinAnsiEncoding >>";
        $objs[6] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-BoldOblique /Encoding /WinAnsiEncoding >>";
        $info = '<< /Title (' . $this->esc($this->titulo_doc) . ') /Author (DeChimbote.com) '
              . '/Subject (Documentacion oficial de DeChimbote.com) /Creator (DeChimbote.com) '
              . '/Producer (DeChimbote.com) >>';
        $objs[7] = $info;

        $kids = [];
        $base = 8;
        // ⚠️ /Font va DENTRO de /Resources: si se pone suelto en el diccionario de la página, los
        //    visores estándar (y `extract_text` de pypdf) no encuentran la fuente y NO PINTA EL TEXTO.
        $recursos = '/Resources << /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R /F4 6 0 R >> >>';
        foreach ($this->paginas as $i => $p) {
            $op = $base + $i * 2;          // objeto de la página
            $oc = $op + 1;                 // objeto del contenido
            $objs[$op] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 "
                       . sprintf('%.2F %.2F', $this->W, $this->H) . "] $recursos /Contents $oc 0 R >>";
            $stream = $p['ops'];
            $objs[$oc] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
            $kids[] = "$op 0 R";
        }
        $objs[2] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . count($this->paginas) . " >>";

        ksort($objs);
        $out = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];
        foreach ($objs as $num => $cuerpo) {
            $offsets[$num] = strlen($out);
            $out .= "$num 0 obj\n$cuerpo\nendobj\n";
        }
        $xref = strlen($out);
        $max = max(array_keys($objs));
        $out .= "xref\n0 " . ($max + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= $max; $i++) {
            $out .= isset($offsets[$i]) ? sprintf("%010d 00000 n \n", $offsets[$i])
                                        : "0000000000 65535 f \n";
        }
        $out .= "trailer\n<< /Size " . ($max + 1) . " /Root 1 0 R /Info 7 0 R >>\n";
        $out .= "startxref\n$xref\n%%EOF\n";
        return $out;
    }
}

}
