/* ============================================================
   Modal — ekleme/düzenleme formlarını diyalogda açar (AJAX)
   ------------------------------------------------------------
   Tetikleyici:  herhangi bir <a>/<button> üzerinde
       data-modal               açılışı tetikler
       data-modal-url           hedef URL (yoksa href kullanılır)
       data-modal-title         modal başlığı
       data-modal-size="lg"     geniş diyalog (büyük formlar)
   Akış:
       GET (X-Requested-With) -> form parçası (fragment) -> modal gövdesine enjekte
       Form gönderimi POST (X-Requested-With) -> sunucu JSON döndürür:
         {ok:true,  redirect}                       -> sayfaya git (flash toast sunucuda set edilir)
         {ok:false, error, csrf:{name,hash}}        -> hata bandı + token yenile, modal AÇIK kalır
   Yükleme sırası: bu dosya, layout'taki "progress" inline script'inden ÖNCE gelir;
   böylece modal işlemlerinde stopImmediatePropagation ile ilerleme çubuğu tetiklenmez.
   ============================================================ */
(function () {
    'use strict';

    var overlay = document.getElementById('appModal');
    if (!overlay) { return; }
    var dialog  = overlay.querySelector('.modal-dialog');
    var titleEl = overlay.querySelector('.modal-title');
    var bodyEl  = overlay.querySelector('.modal-body');
    var lastFocus = null;

    function openOverlay() {
        overlay.hidden = false;
        document.body.classList.add('modal-open');
    }
    function closeModal() {
        overlay.hidden = true;
        document.body.classList.remove('modal-open');
        bodyEl.innerHTML = '';
        dialog.classList.remove('modal-lg');
        if (lastFocus && lastFocus.focus) { try { lastFocus.focus(); } catch (e) {} }
        lastFocus = null;
    }
    function setLoading() {
        bodyEl.innerHTML = '<div class="modal-loading">Yükleniyor…</div>';
    }

    /* innerHTML, eklenen <script>'leri çalıştırmaz; fragment script'lerini yeniden çalıştır. */
    function runScripts(container) {
        container.querySelectorAll('script').forEach(function (old) {
            var s = document.createElement('script');
            if (old.src) { s.src = old.src; } else { s.textContent = old.textContent; }
            document.body.appendChild(s);
            if (s.parentNode) { s.parentNode.removeChild(s); }
        });
    }
    function focusFirst() {
        var el = bodyEl.querySelector('input:not([type=hidden]):not([disabled]), select, textarea');
        if (el) { try { el.focus(); } catch (e) {} }
    }

    function openModal(url, title, size) {
        if (titleEl) { titleEl.textContent = title || ''; }
        dialog.classList.toggle('modal-lg', size === 'lg');
        setLoading();
        openOverlay();
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) {
                if (r.redirected || !r.ok) { window.location.href = url; throw new Error('fallback'); }
                return r.text();
            })
            .then(function (html) {
                bodyEl.innerHTML = html;
                runScripts(bodyEl);
                focusFirst();
            })
            .catch(function () { /* fallback zaten yönlendirildi */ });
    }

    function showError(form, msg) {
        var box = form.querySelector('.modal-error');
        if (!box) {
            box = document.createElement('div');
            box.className = 'modal-error';
            form.insertBefore(box, form.firstChild);
        }
        box.textContent = msg || 'İşlem tamamlanamadı.';
        box.hidden = false;
        box.scrollIntoView({ block: 'nearest' });
    }

    /* --- tıklama: aç / kapat (event delegation) --- */
    document.addEventListener('click', function (e) {
        var trg = e.target.closest('[data-modal]');
        if (trg) {
            var url = trg.getAttribute('data-modal-url') || trg.getAttribute('href');
            if (!url || url === '#') { return; }
            e.preventDefault();
            e.stopImmediatePropagation();          /* ilerleme çubuğunu tetikleme */
            lastFocus = trg;
            openModal(url, trg.getAttribute('data-modal-title'), trg.getAttribute('data-modal-size'));
            return;
        }
        if (e.target.closest('[data-modal-close]')) { e.preventDefault(); closeModal(); return; }
        if (e.target === overlay) { closeModal(); return; }
        /* modal içindeki "İptal" (.btn-link) bağlantısı -> kapat */
        if (!overlay.hidden && e.target.closest('.modal-body .btn-link')) { e.preventDefault(); closeModal(); return; }
    });

    /* --- Esc ile kapat --- */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !overlay.hidden) { closeModal(); }
    });

    /* --- modal içi form gönderimi -> AJAX --- */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (overlay.hidden || !bodyEl.contains(form)) { return; }
        e.preventDefault();
        e.stopImmediatePropagation();              /* layout'un progress submit handler'ını atla */

        var btn = form.querySelector('button[type=submit], button:not([type])');
        if (btn) { btn.classList.add('is-loading'); btn.disabled = true; }

        fetch(form.getAttribute('action') || window.location.href, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json().catch(function () { return null; }); })
            .then(function (data) {
                if (data && data.ok) {
                    window.location.href = data.redirect || window.location.href;
                    return;
                }
                if (btn) { btn.classList.remove('is-loading'); btn.disabled = false; }
                if (data && data.csrf && data.csrf.name) {
                    /* token rotasyonu sonrası sayfadaki TÜM csrf alanlarını tazele
                       (satır-içi Sil, toplu işlem formları vb. geçerli kalsın) */
                    document.querySelectorAll('input[name="' + data.csrf.name + '"]').forEach(function (tok) {
                        tok.value = data.csrf.hash;
                    });
                }
                showError(form, data ? data.error : 'Sunucu hatası. Lütfen tekrar deneyin.');
            })
            .catch(function () {
                if (btn) { btn.classList.remove('is-loading'); btn.disabled = false; }
                showError(form, 'Bağlantı hatası. Lütfen tekrar deneyin.');
            });
    });
})();
