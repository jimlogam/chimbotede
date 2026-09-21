/* ============================================================
 * buscador_limpieza.js — 🧹 LA LIMPIEZA DE LA BÚSQUEDA (el mismo motor que usa el servidor)
 * Proyecto: DeChimbote.com
 * ------------------------------------------------------------
 * Pedido del jefe (2026-09-14): «la palabra "comprar" debería ser filtrada de los resultados de
 * búsqueda… el buscador debe saber filtrar palabras que simplemente acompañan una búsqueda pero no
 * son parte de la búsqueda… "dónde hay cerveza" debe dar resultados de cerveza y no de "dónde hay"».
 *
 * Esto es PROGRAMACIÓN, no IA: un diccionario corto (mandos, conectores y muletillas) y una rutina
 * que los quita. Gratis, instantáneo (0 ms, sin red) y predecible.
 *
 * ⚠️ EL DICCIONARIO NO VIVE AQUÍ: lo manda PHP (`includes/busqueda_limpieza.php` →
 *    `busqueda_diccionario_js()`) pintado en `includes/footer.php` como `window.CHIMBOTE_LIMPIEZA`,
 *    y este archivo tiene que ir DESPUÉS de ese `<script>`. Así los dos lados (PHP y navegador)
 *    limpian con la MISMA lista.
 *    El diccionario de RESPALDO que hay abajo solo se usa si el de PHP no llegó (por ejemplo una
 *    página cacheada vieja): la prueba `node D:\RELAX\__limpieza_prueba.js` comprueba que sea idéntico
 *    al de PHP — si alguien añade una palabra en un lado y no en el otro, la prueba avisa.
 *
 * Lo usan: `buscador_fuzzy.js` (el desplegable y lo que se envía del formulario) y `buscador_voz.js`
 * (el dictado, que antes tenía su propia lista). Guía del módulo: `GUIA_BUSCADOR_FUZZY.md` §4.8.
 * ============================================================ */
(function () {
    'use strict';

    // ---------------------------------------------------------------- respaldo
    // Copia exacta de includes/busqueda_limpieza.php (comprobada por __limpieza_prueba.js).
    var RESPALDO = {
        mandos: [
            'sabes que', 'sabes donde', 'sabes si', 'sabes de', 'sabes',
            'no sabes donde', 'tu sabes donde', 'tu sabes',
            'quiero comprar', 'quiero ver', 'quiero buscar', 'quiero saber', 'quiero encontrar',
            'quisiera comprar', 'quisiera ver', 'quisiera saber', 'quisiera',
            'quiero', 'queremos', 'queria',
            'vamos a comprar', 'vamos a ver', 'vamos a buscar', 'vamos a', 'vamos',
            'necesito comprar', 'necesito ver', 'necesito encontrar', 'necesito saber',
            'necesito', 'necesitaba', 'estoy necesitando',
            'estoy buscando', 'estamos buscando', 'ando buscando',
            'me puedes recomendar', 'me puede recomendar', 'me recomiendas', 'recomiendame',
            'recomiendan', 'recomienda',
            'me interesa', 'me gustaria', 'me ayudas a encontrar', 'ayudame a encontrar',
            'ayudame a', 'conoces algun', 'conoces alguna', 'conoces',
            'en que lugar venden', 'en que tienda venden',
            'buscar', 'busca', 'busco', 'buscame', 'buscando', 'busquenme', 'buscar por',
            'donde puedo comprar', 'donde comprar', 'donde compro', 'comprar', 'compro', 'comprame',
            'donde puedo encontrar', 'donde hay', 'donde encuentro', 'donde venden', 'donde queda',
            'donde estan', 'donde esta', 'donde',
            'quienes venden', 'quienes tienen', 'quien vende', 'quien tiene', 'quien hace',
            'quien', 'quienes',
            'alguien que venda', 'alguien que vende', 'alguien que tenga', 'alguien que haga',
            'alguien que', 'alguien',
            'venda', 'vendan', 'vendas', 'tenga', 'tengan', 'haga', 'hagan', 'alquile', 'alquilen',
            'que venden', 'que vende', 'que tienen', 'que tiene', 'que hay',
            'muestrame', 'muestra', 'ensename', 'dime', 'dame', 'ver',
            'tienes', 'tienen', 'venden', 'vende',
            'precios de', 'precio de', 'cuanto cuesta', 'a cuanto esta', 'cuanto',
            'informacion sobre', 'informacion de', 'informacion',
            'como llegar a', 'como llego a', 'opciones para', 'opciones de', 'opciones',
            'hay algunos', 'hay algunas', 'hay algun', 'hay alguna', 'hay',
            'buenos dias', 'buenas tardes', 'buenas noches', 'buenas', 'hola', 'oye', 'oiga',
            'disculpa', 'disculpe', 'amigo', 'amiga'
        ],
        conectores: [
            'en', 'de', 'del', 'la', 'el', 'los', 'las', 'lo', 'un', 'una', 'unos', 'unas',
            'al', 'a', 'y', 'e', 'o', 'u', 'con', 'sin', 'para', 'por', 'mi', 'mis',
            'tu', 'tus', 'su', 'sus', 'que', 'es', 'son', 'esta', 'este', 'estos', 'estas',
            'me', 'te', 'se', 'le', 'les', 'nos',
            'hay', 'cerca', 'cerquita', 'favor', 'gracias', 'porfa', 'porfavor', 'pues',
            'oe', 'oee', 'ahi', 'alli', 'aca', 'aqui',
            'algo', 'algun', 'alguna', 'algunos', 'algunas'
        ],
        muletillas: ['favor', 'porfavor', 'porfa', 'gracias', 'pues', 'ya', 'oe', 'oee', 'papa', 'pe'],
        jerga: {
            'puchitos': 'cigarrillos', 'puchito': 'cigarrillos',
            'puchos': 'cigarrillos', 'pucho': 'cigarrillos',
            'chelas': 'cerveza', 'chela': 'cerveza',
            'celu': 'celular', 'celus': 'celular',
            'compu': 'computadora', 'compus': 'computadora',
            'boticas': 'farmacia', 'botica': 'farmacia',
            'refri': 'refrigeradora'
        }
    };

    /** El diccionario que manda el servidor (o el de respaldo si no llegó). */
    function diccionario() {
        var d = (typeof window !== 'undefined') ? window.CHIMBOTE_LIMPIEZA : null;
        if (d && d.mandos && d.mandos.length && d.conectores && d.conectores.length) return d;
        return RESPALDO;
    }

    /** Minúsculas y sin tildes: para COMPARAR (nunca para mostrar). Igual que busqueda_norm() en PHP. */
    function norm(txt) {
        return String(txt == null ? '' : txt).toLowerCase()
            .replace(/[áàäâã]/g, 'a').replace(/[éèëê]/g, 'e').replace(/[íìïî]/g, 'i')
            .replace(/[óòöô]/g, 'o').replace(/[úùüû]/g, 'u').replace(/ñ/g, 'n')
            .replace(/ç/g, 'c');
    }

    /** 🧺 El saco de las palabras vacías: conectores + muletillas + TODAS las palabras de los mandos
     *  (la frase entera y cada palabra suelta). Igual que busqueda_limpieza_vacias() en PHP. */
    var _saco = null;
    function saco() {
        if (_saco) return _saco;
        var d = diccionario(), s = {};
        (d.conectores || []).concat(d.muletillas || []).forEach(function (p) { s[norm(p)] = true; });
        (d.mandos || []).forEach(function (m) {
            s[norm(m)] = true;
            norm(m).split(/\s+/).filter(Boolean).forEach(function (w) { s[w] = true; });
        });
        return (_saco = s);
    }

    /**
     * 🧹 Limpia lo que escribió (o dictó) el visitante y cuenta lo que hizo.
     * «comprar clavos» → «clavos» · «dónde hay cerveza» → «cerveza» ·
     * «sabes que quiero comprar cerveza» → «cerveza» · «vamos a comprar unos puchitos» → «puchitos».
     *
     * Es UNA sola pasada a propósito: se tira la puntuación y después toda palabra del saco de
     * palabras vacías. Sin reglas de posición no hay forma de que una frase rara («…bodega que venda
     * puchitos») se cuele.
     *
     * ⚠️ RED DE SEGURIDAD: si al limpiar no queda nada útil, se devuelve el texto tal cual (mejor una
     *    búsqueda rara que una búsqueda vacía). La limpieza NUNCA puede dejar al visitante en blanco.
     *
     * @returns {{crudo:string, base:string, limpio:string, quitaron:string[], cambio:boolean, vacio:boolean}}
     */
    function limpiarInfo(txt) {
        var crudo = String(txt == null ? '' : txt).replace(/\s+/g, ' ').trim();
        var base  = crudo.replace(/[^0-9A-Za-zÀ-ÿ]+/g, ' ').replace(/\s+/g, ' ').trim();

        var salida = { crudo: crudo, base: base, limpio: base, quitaron: [], cambio: false, vacio: false };
        if (!base) return salida;

        var vacias = saco();
        var toks = base.split(' ').filter(function (t) { return !vacias[norm(t)]; });
        var limpio = toks.join(' ');

        // ⚠️ RED DE SEGURIDAD: sin nada útil, se busca lo que escribió (quien busca SOLO «comprar» o
        //    «dónde hay» no puede quedarse sin nada que buscar). `vacio` avisa de ese caso.
        if (limpio.replace(/[^0-9A-Za-zÀ-ÿ]/g, '').length < 2) {
            limpio = base;
            salida.vacio = true;
        }

        salida.limpio = limpio;
        salida.cambio = (limpio !== base);
        if (salida.cambio) {
            var quedan = limpio.split(/\s+/).filter(Boolean).map(norm);
            base.split(/\s+/).filter(Boolean).forEach(function (t) {
                if (quedan.indexOf(norm(t)) === -1) salida.quitaron.push(t);
            });
        }
        return salida;
    }

    /** El término de verdad de una búsqueda: «comprar clavos» → «clavos». */
    function limpiar(txt) {
        return limpiarInfo(txt).limpio;
    }

    /** Las palabras útiles de la búsqueda, ya normalizadas (las usa el índice Fuse.js). */
    function tokens(txt) {
        return norm(limpiar(txt)).split(/[^0-9a-z]+/).filter(function (t) { return t.length >= 2; });
    }

    /**
     * 🗣️ La jerga local traducida al idioma de las fichas: «puchitos» → «cigarrillos».
     * Devuelve null si no había nada que traducir. Es un SEGUNDO INTENTO: primero se busca lo que
     * escribió el visitante y solo si eso no encontró nada se reintenta con esta traducción.
     */
    function jerga(txt) {
        var j = diccionario().jerga || {};
        var toks = String(txt == null ? '' : txt).trim().split(/\s+/).filter(Boolean);
        if (!toks.length) return null;

        var de = [], a = [];
        toks = toks.map(function (t) {
            var n = norm(t);
            if (!j[n]) return t;
            de.push(t); a.push(j[n]);
            return j[n];
        });
        if (!de.length) return null;

        return { texto: toks.join(' '), de: unicos(de), a: unicos(a) };
    }

    function unicos(arr) {
        var vistos = {}, out = [];
        arr.forEach(function (x) { if (!vistos[x]) { vistos[x] = 1; out.push(x); } });
        return out;
    }

    var API = {
        limpiar: limpiar,
        limpiarInfo: limpiarInfo,
        tokens: tokens,
        jerga: jerga,
        norm: norm,
        diccionario: diccionario,
        RESPALDO: RESPALDO
    };

    if (typeof window !== 'undefined') window.ChimboteLimpieza = API;

    // Para las pruebas sin navegador (node D:\RELAX\__limpieza_prueba.js).
    if (typeof module !== 'undefined' && module.exports) module.exports = API;
})();
