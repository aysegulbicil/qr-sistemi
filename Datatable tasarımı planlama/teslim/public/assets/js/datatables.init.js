/* ============================================================
   Kurumsal Klasik — DataTables otomatik başlatıcı
   Devam Takip & Yönetim Sistemi
   ------------------------------------------------------------
   Sayfadaki tüm "table.data" tablolarını (".no-dt" hariç) klasik
   DataTable'a çevirir:  Göster 10/25/50/100 · arama · sıralama ·
   "X kayıttan Y–Z arası" + numaralı sayfalama · mobil responsive.
   Yükleme sırası:  jQuery → DataTables → (Responsive) → bu dosya.
   ============================================================ */
(function () {
    'use strict';
    if (typeof window.jQuery === 'undefined' || !jQuery.fn || !jQuery.fn.DataTable) {
        if (window.console) { console.warn('[DataTable] jQuery/DataTables yüklenemedi — liste standardı atlandı.'); }
        return;
    }
    var $ = window.jQuery;

    /* ---- Türkçe dil ---- */
    var LANG = {
        emptyTable:     'Tabloda gösterilecek veri yok',
        info:           '_TOTAL_ kayıttan _START_–_END_ arası gösteriliyor',
        infoEmpty:      'Kayıt yok',
        infoFiltered:   '(_MAX_ kayıt içinden filtrelendi)',
        lengthMenu:     'Göster _MENU_ kayıt',
        loadingRecords: 'Yükleniyor…',
        processing:     'İşleniyor…',
        search:         '',
        searchPlaceholder: 'Ara…',
        zeroRecords:    'Eşleşen kayıt bulunamadı',
        paginate:       { first: '«', last: '»', next: 'Sonraki ›', previous: '‹ Önceki' },
        aria:           { sortAscending: ': artan sırala', sortDescending: ': azalan sırala' }
    };

    /* ---- Türkçe tarih (gg.aa.yyyy [ss:dd]) sıralaması ---- */
    function parseTrDate(raw) {
        var s = $('<div>').html(raw == null ? '' : raw).text().trim();
        var m = s.match(/^(\d{2})\.(\d{2})\.(\d{4})(?:[ T]+(\d{1,2}):(\d{2}))?/);
        if (!m) { return null; }
        return new Date(+m[3], +m[2] - 1, +m[1], m[4] ? +m[4] : 0, m[5] ? +m[5] : 0).getTime();
    }
    if (!$.fn.dataTable.ext.type.order['trdate-pre']) {
        $.fn.dataTable.ext.type.order['trdate-pre'] = function (d) {
            var t = parseTrDate(d);
            return t === null ? -Infinity : t;
        };
    }

    /* ---- sıralanmayacak sütunlar (seçim kutusu / işlem / boş başlık) ---- */
    function nonSortableTargets(table) {
        var t = [], ths = table.querySelectorAll('thead th');
        Array.prototype.forEach.call(ths, function (th, i) {
            var txt = (th.textContent || '').trim().toLowerCase();
            if (!txt ||
                th.classList.contains('no-sort') ||
                th.querySelector('input[type=checkbox]') ||
                txt === 'işlem' || txt === 'işlemler' || txt === 'actions') {
                t.push(i);
            }
        });
        return t;
    }

    /* ---- tarih sütunlarını ilk satırdan tespit et ---- */
    function dateTargets(table) {
        var cols = [];
        var body = table.tBodies && table.tBodies[0];
        var row  = body && body.rows[0];
        if (!row) { return cols; }
        Array.prototype.forEach.call(row.cells, function (td, i) {
            if (/^\s*\d{2}\.\d{2}\.\d{4}/.test(td.textContent || '')) { cols.push(i); }
        });
        return cols;
    }

    /* ---- bir tablo için ortak DataTables ayarları ---- */
    function baseOptions(table) {
        var noSort    = nonSortableTargets(table);
        var dateCols  = dateTargets(table).filter(function (i) { return noSort.indexOf(i) === -1; });
        var defs = [{ orderable: false, searchable: false, targets: noSort }];
        if (dateCols.length) { defs.push({ type: 'trdate', targets: dateCols }); }
        return {
            language:   LANG,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Tümü']],
            pageLength: 25,
            pagingType: 'simple_numbers',
            responsive: !!$.fn.dataTable.Responsive,
            autoWidth:  false,
            order:      [],
            columnDefs: defs
        };
    }

    /* görünümlerin kendi başlatması için dışa aç (örn. Personeller toplu seçim) */
    window.DTBrand = { lang: LANG, baseOptions: baseOptions };

    function initAll() {
        document.querySelectorAll('table.data:not(.no-dt)').forEach(function (table) {
            if (!table.tBodies.length || $.fn.dataTable.isDataTable(table)) { return; }
            try { $(table).DataTable(baseOptions(table)); }
            catch (e) { if (window.console) { console.warn('[DataTable] başlatılamadı:', e); } }
        });

        /* harici açılır filtreler:
           <select data-dt-target="#tabloId" data-dt-col="2"> ... </select>
           seçilen değer ilgili sütunda tam eşleşme ile filtrelenir. */
        document.querySelectorAll('select[data-dt-col][data-dt-target]').forEach(function (sel) {
            var tgt = document.querySelector(sel.getAttribute('data-dt-target'));
            if (!tgt || !$.fn.dataTable.isDataTable(tgt)) { return; }
            var dt  = $(tgt).DataTable();
            var col = parseInt(sel.getAttribute('data-dt-col'), 10);
            sel.addEventListener('change', function () {
                var v = sel.value ? '^' + $.fn.dataTable.util.escapeRegex(sel.value) + '$' : '';
                dt.column(col).search(v, true, false).draw();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
