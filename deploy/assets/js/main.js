/* ============================================================
   CHIMBOTE AL DÍA — JS principal
   ============================================================ */

(function() {
    'use strict';

    // ====== SLIDER PLANTILLA B (manual, sin auto-rotación) ======
    function initSliderB() {
        const slides = document.querySelectorAll('.ficha-B__hero-slide');
        const dots = document.querySelectorAll('.ficha-B__hero-dot');
        const btnAnt = document.querySelector('.ficha-B__nav--anterior');
        const btnSig = document.querySelector('.ficha-B__nav--siguiente');
        const hero = document.querySelector('.ficha-B__hero');
        if (slides.length === 0) return;

        let idx = 0;
        function mostrar(nuevo) {
            if (nuevo < 0) nuevo = slides.length - 1;
            if (nuevo >= slides.length) nuevo = 0;
            slides[idx].classList.remove('activo');
            if (dots[idx]) dots[idx].classList.remove('activo');
            idx = nuevo;
            slides[idx].classList.add('activo');
            if (dots[idx]) dots[idx].classList.add('activo');
        }
        if (btnSig) btnSig.addEventListener('click', e => { e.preventDefault(); mostrar(idx + 1); });
        if (btnAnt) btnAnt.addEventListener('click', e => { e.preventDefault(); mostrar(idx - 1); });
        dots.forEach((d, i) => d.addEventListener('click', () => mostrar(i)));

        // Swipe táctil
        let touchX = 0;
        if (hero) {
            hero.addEventListener('touchstart', e => touchX = e.changedTouches[0].screenX, { passive: true });
            hero.addEventListener('touchend', e => {
                const diff = e.changedTouches[0].screenX - touchX;
                if (Math.abs(diff) > 50) mostrar(idx + (diff < 0 ? 1 : -1));
            }, { passive: true });
        }
    }

    // ====== TOGGLE DESCRIPCIÓN (Plantilla B) ======
    function initToggleDesc() {
        const t = document.querySelector('.ficha-B__descripcion-toggle');
        if (!t) return;
        t.addEventListener('click', () => {
            t.closest('.ficha-B__descripcion').classList.toggle('expandida');
        });
    }

    // ====== TABS PLANTILLA C ======
    function initTabsC() {
        const tabs = document.querySelectorAll('.ficha-C__tab');
        const paneles = document.querySelectorAll('.ficha-C__panel');
        if (tabs.length === 0) return;
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.tab;
                tabs.forEach(t => t.classList.remove('ficha-C__tab--activo'));
                tab.classList.add('ficha-C__tab--activo');
                paneles.forEach(p => {
                    p.classList.toggle('ficha-C__panel--activo', p.dataset.panel === target);
                });
            });
        });
    }

    // ====== AJAX: REGISTRAR VISTA (al abrir ficha) ======
    function initRegistrarVista() {
        const el = document.querySelector('[data-negocio-id]');
        if (!el) return;
        const id = el.dataset.negocioId;
        if (!id) return;
        // Llamada AJAX al endpoint (no bloqueante)
        fetch(SITE_URL + '/api/registrar-vista', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'negocio_id=' + encodeURIComponent(id) + '&csrf=' + (window.CSRF_TOKEN || ''),
            credentials: 'same-origin'
        }).catch(() => {});
    }

    // ====== FLASH AUTO-DISMISS ======
    function initFlashDismiss() {
        const flashs = document.querySelectorAll('.flash');
        flashs.forEach(f => {
            setTimeout(() => {
                f.style.transition = 'opacity 0.3s';
                f.style.opacity = '0';
                setTimeout(() => f.remove(), 300);
            }, 5000);
        });
    }

    // ====== BUSCADOR PREDICTIVO ======
    // Cualquier input con data-predictivo ofrece sugerencias al escribir (>=3 letras)
    function initPredictivo() {
        const inputs = document.querySelectorAll('[data-predictivo]');
        if (inputs.length === 0) return;
        inputs.forEach(input => {
            // envolver siempre en .pred-wrap para posicionar el dropdown
            let wrap = input.closest('.pred-wrap');
            if (!wrap) {
                wrap = document.createElement('div');
                wrap.className = 'pred-wrap';
                input.parentNode.insertBefore(wrap, input);
                wrap.appendChild(input);
            }
            let sug = null;
            let timer = null;
            let abierto = false;

            function cerrar() { if (sug) { sug.remove(); sug = null; abierto = false; } }
            function resaltar(txt){
                const q = input.value.trim();
                if(!q) return txt;
                const rx = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')','ig');
                return String(txt).replace(rx, '<em>$1</em>');
            }
            async function buscar(){
                const q = input.value.trim();
                if (q.length < 3) { cerrar(); return; }
                try{
                    const r = await fetch(SITE_URL + '/api/sugerir.php?q=' + encodeURIComponent(q), {credentials:'same-origin'});
                    const d = await r.json();
                    if (input.value.trim().length < 3) return;
                    cerrar();
                    const n = (d.negocios||[]), p = (d.productos||[]);
                    if (n.length===0 && p.length===0){
                        sug = document.createElement('div');
                        sug.className = 'pred-sug';
                        sug.innerHTML = '<div class="pred-vacio">Sin coincidencias para "'+q+'". Pulsa Buscar para ver más.</div>';
                        (wrap||input.parentNode).appendChild(sug);
                        abierto = true; return;
                    }
                    sug = document.createElement('div');
                    sug.className = 'pred-sug';
                    let html = '';
                    if (n.length){
                        html += '<div class="pred-sug__grupo">🏪 Negocios</div>';
                        n.forEach(it=>{
                            html += '<a class="pred-item" href="'+SITE_URL+'/neg/'+it.slug+'">'+
                              '<span class="pre">'+resaltar(it.nombre)+'</span>'+
                              '<span class="meta">'+(it.categoria_nombre||'')+(it.distrito_nombre?' · 📍'+it.distrito_nombre:'')+'</span></a>';
                        });
                    }
                    if (p.length){
                        html += '<div class="pred-sug__grupo">📦 Productos</div>';
                        p.forEach(it=>{
                            html += '<a class="pred-item" href="'+SITE_URL+'/neg/'+it.negocio_slug+'">'+
                              '<span class="pre">'+resaltar(it.titulo)+'</span>'+
                              '<span class="precio">S/ '+Number(it.precio).toFixed(2)+'</span>'+
                              '<span class="meta">'+(it.negocio_nombre||'')+(it.unidad?' · '+it.unidad:'')+'</span></a>';
                        });
                    }
                    sug.innerHTML = html;
                    (wrap||input.parentNode).appendChild(sug);
                    abierto = true;
                }catch(e){ cerrar(); }
            }
            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(buscar, 220);
            });
            input.addEventListener('focus', () => { if (input.value.trim().length>=3) buscar(); });
            document.addEventListener('click', (e)=>{ if(!e.target.closest('.pred-wrap')) cerrar(); });
            input.addEventListener('keydown', (e)=>{ if(e.key==='Escape') cerrar(); });
        });
    }

    // ====== INIT ======
    function init() {
        initSliderB();
        initToggleDesc();
        initTabsC();
        initRegistrarVista();
        initFlashDismiss();
        initPredictivo();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
