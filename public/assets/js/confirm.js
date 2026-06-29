/* ============================================================
   SweetAlert2 tabanli onay/bilgi — native confirm()/alert() yerine.
   - data-confirm="mesaj" tasiyan formlar otomatik yakalanir.
   - data-confirm-safe  -> mavi onay (yikici degil), yoksa kirmizi.
   - data-confirm-title / data-confirm-ok ile baslik/buton metni.
   - window.swalConfirm(opts) / window.swalInfo(msg, icon) programatik.
   Capture fazinda calisir; modal.js ve progress submit handler'larindan once.
   ============================================================ */
(function () {
    'use strict';

    function fire(opts) {
        if (typeof window.Swal === 'undefined') { return Promise.resolve({ isConfirmed: true }); }
        return window.Swal.fire(opts);
    }

    window.swalConfirm = function (o) {
        o = o || {};
        var danger = o.danger !== false;
        return fire({
            title: o.title || 'Emin misin?',
            text: o.text || '',
            icon: o.icon || 'warning',
            showCancelButton: true,
            confirmButtonText: o.confirmText || 'Evet',
            cancelButtonText: o.cancelText || 'Vazgeç',
            reverseButtons: true,
            focusCancel: true,
            confirmButtonColor: danger ? '#be123c' : '#2563eb',
            cancelButtonColor: '#64748b'
        });
    };

    window.swalInfo = function (msg, icon) {
        return fire({ text: msg, icon: icon || 'info', confirmButtonText: 'Tamam', confirmButtonColor: '#2563eb' });
    };

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (! form || form.nodeName !== 'FORM' || ! form.hasAttribute('data-confirm')) { return; }
        if (form.dataset.confirmed === '1') { return; }
        e.preventDefault();
        e.stopImmediatePropagation();
        window.swalConfirm({
            title: form.getAttribute('data-confirm-title') || 'Emin misin?',
            text: form.getAttribute('data-confirm'),
            confirmText: form.getAttribute('data-confirm-ok') || 'Evet',
            danger: ! form.hasAttribute('data-confirm-safe')
        }).then(function (r) {
            if (r.isConfirmed) {
                form.dataset.confirmed = '1';
                if (form.requestSubmit) { form.requestSubmit(); } else { form.submit(); }
            }
        });
    }, true);
})();
