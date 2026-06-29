<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php
$statusOpts = ['' => 'Tüm durumlar', 'Aktif' => 'Aktif', 'Pasif' => 'Pasif', 'Ayrıldı' => 'Ayrıldı'];
?>
<div class="card-head">
    <h1>Personeller <span class="count-pill"><?= count($rows) ?></span></h1>
    <a class="btn btn-primary" href="<?= site_url('admin/employees/new') ?>" data-modal data-modal-title="Yeni personel" data-modal-size="lg">+ Yeni personel</a>
</div>

<form method="post" action="<?= site_url('admin/employees/bulk') ?>" id="empBulk">
    <?= csrf_field() ?>
    <div class="card">
        <?php if (empty($rows)): ?>
            <?= view('partials/empty', [
                'title'       => 'Henüz personel yok',
                'message'     => 'İlk personelini ekleyerek başla.',
                'actionUrl'   => site_url('admin/employees/new'),
                'actionLabel' => '+ Yeni personel', 'actionModal' => true, 'actionTitle' => 'Yeni personel', 'actionSize' => 'lg',
            ]) ?>
        <?php else: ?>
        <!-- Sütun bazlı filtreler (DataTable'a bağlanır) -->
        <div class="filters" style="margin-bottom:14px">
            <select id="empDept" data-col="2">
                <option value="">Tüm departmanlar</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= esc($d['name'], 'attr') ?>"><?= esc($d['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="empStatus" data-col="4">
                <?php foreach ($statusOpts as $val => $lbl): ?>
                    <option value="<?= esc((string) $val, 'attr') ?>"><?= esc($lbl) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <table class="data no-dt" id="empTable" style="width:100%">
            <thead><tr>
                <th class="no-sort"><input type="checkbox" id="selAll" aria-label="Tümünü seç"></th>
                <th>Personel</th>
                <th>Departman</th>
                <th>Pozisyon</th>
                <th>Durum</th>
                <th class="no-sort">İşlem</th>
            </tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <?php [$slbl, $scls] = emp_status_badge($r['employment_status'] ?? 'active'); ?>
                <tr>
                    <td><input type="checkbox" class="rowCheck" data-id="<?= (int) $r['id'] ?>" aria-label="Seç"></td>
                    <td>
                        <a class="cell-user" href="<?= site_url('admin/employees/' . $r['id']) ?>">
                            <span class="mini-avatar"><?= esc(initials($r['full_name'])) ?></span>
                            <span class="stack"><strong><?= esc($r['full_name']) ?></strong><span class="muted-sm"><?= esc($r['employee_code'] ?: $r['username']) ?></span></span>
                        </a>
                    </td>
                    <td><?= $r['department_name'] ? esc($r['department_name']) : '<span class="muted">—</span>' ?></td>
                    <td><?= $r['position_name'] ? esc($r['position_name']) : '<span class="muted">—</span>' ?></td>
                    <td><span class="badge <?= $scls ?>"><?= $slbl ?></span></td>
                    <td class="row-actions">
                        <a href="<?= site_url('admin/employees/' . $r['id']) ?>">Profil</a>
                        <a class="btn btn-warning-soft btn-sm" href="<?= site_url('admin/employees/' . $r['id'] . '/edit') ?>" data-modal data-modal-title="Personeli düzenle" data-modal-size="lg">Düzenle</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <?php if (! empty($rows)): ?>
        <?= view('partials/bulk_bar', [
            'actions' => [
                'active'     => 'Durum: Aktif yap',
                'passive'    => 'Durum: Pasif yap',
                'terminated' => 'Durum: Ayrıldı işaretle',
                'export'     => 'Seçilenleri CSV indir',
            ],
        ]) ?>
    <?php endif; ?>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tableEl = document.getElementById('empTable');
    if (! tableEl || ! window.jQuery || ! window.DTBrand) { return; }
    var $ = window.jQuery;

    // DataTable'ı Kurumsal Klasik ayarlarıyla başlat (seçim/işlem sütunu korunur)
    var opts = window.DTBrand.baseOptions(tableEl);
    opts.columnDefs = (opts.columnDefs || []).concat([
        { responsivePriority: 1, targets: 0 },
        { responsivePriority: 1, targets: 1 },
        { responsivePriority: 2, targets: 5 }
    ]);
    var dt = $(tableEl).DataTable(opts);

    // Departman / Durum sütun filtreleri
    ['empDept', 'empStatus'].forEach(function (id) {
        var sel = document.getElementById(id);
        if (! sel) { return; }
        var col = parseInt(sel.getAttribute('data-col'), 10);
        sel.addEventListener('change', function () {
            var v = sel.value ? '^' + $.fn.dataTable.util.escapeRegex(sel.value) + '$' : '';
            dt.column(col).search(v, true, false).draw();
        });
    });

    // Toplu seçim — sayfalar arası çalışır (DataTables API ile)
    var form     = document.getElementById('empBulk');
    var selAll   = document.getElementById('selAll');
    var bar      = document.getElementById('bulkBar');
    var count    = document.getElementById('selCount');
    var clearBtn = document.getElementById('bulkClear');

    function allChecks() { return $(dt.rows().nodes()).find('.rowCheck').toArray(); }
    function refresh() {
        var all = allChecks();
        var n   = all.filter(function (b) { return b.checked; }).length;
        if (count) { count.textContent = n; }
        if (bar)   { bar.hidden = n === 0; }
        if (selAll) {
            selAll.checked       = n > 0 && n === all.length;
            selAll.indeterminate = n > 0 && n < all.length;
        }
    }
    if (selAll) {
        selAll.addEventListener('change', function () {
            allChecks().forEach(function (b) { b.checked = selAll.checked; });
            refresh();
        });
    }
    $(tableEl).on('change', '.rowCheck', refresh);
    dt.on('draw', refresh);
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            allChecks().forEach(function (b) { b.checked = false; });
            refresh();
        });
    }
    if (form) {
        form.addEventListener('submit', function (e) {
            var sel     = form.querySelector('select[name=bulk_action]');
            var checked = allChecks().filter(function (b) { return b.checked; });
            if (! sel || ! sel.value) { e.preventDefault(); alert('Lütfen bir toplu işlem seçin.'); return; }
            if (checked.length === 0) { e.preventDefault(); alert('Lütfen en az bir personel seçin.'); return; }
            if (sel.value !== 'export') {
                var label = sel.options[sel.selectedIndex].text;
                if (! confirm(checked.length + ' personele "' + label + '" uygulansın mı?')) { e.preventDefault(); return; }
            }
            // Seçili id'leri (tüm sayfalardan) gizli input olarak forma ekle
            form.querySelectorAll('input.bulk-id-hidden').forEach(function (x) { x.remove(); });
            checked.forEach(function (b) {
                var h = document.createElement('input');
                h.type = 'hidden'; h.name = 'ids[]'; h.value = b.getAttribute('data-id');
                h.className = 'bulk-id-hidden';
                form.appendChild(h);
            });
        });
    }
    refresh();
});
</script>
<?= $this->endSection() ?>
