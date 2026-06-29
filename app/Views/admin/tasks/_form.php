<?php
$isEdit  = $task !== null;
$curUser = $isEdit ? (int) $task['user_id'] : (int) ($prefillUser ?? 0);
$curPrio = $isEdit ? (string) $task['priority'] : 'normal';
$curDue  = $isEdit && $task['due_date'] ? esc($task['due_date']) : '';
$prios   = ['low' => 'Düşük', 'normal' => 'Normal', 'high' => 'Yüksek'];
$stats   = ['pending' => 'Bekliyor', 'in_progress' => 'Yapılıyor', 'done' => 'Tamamlandı', 'cancelled' => 'İptal'];
?>
<form method="post" action="<?= $isEdit ? site_url('admin/tasks/' . $task['id']) : site_url('admin/tasks') ?>">
    <?= csrf_field() ?>
    <div class="field"><label>Başlık *</label><input type="text" name="title" value="<?= esc($isEdit ? $task['title'] : old('title')) ?>" maxlength="150" required autofocus placeholder="Kısa ve net bir görev başlığı"></div>
    <div class="field"><label>Açıklama</label><textarea name="description" rows="4" placeholder="Görevle ilgili detay veya talimat (isteğe bağlı)"><?= esc($isEdit ? (string) ($task['description'] ?? '') : old('description')) ?></textarea></div>

    <div class="field">
        <label>Personel *</label>
        <?php if ($isEdit): ?>
            <select name="user_id" required>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= (int) $emp['id'] ?>" <?= $curUser === (int) $emp['id'] ? 'selected' : '' ?>><?= esc($emp['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <div class="pick">
                <div class="pick-head">
                    <span class="pick-search"><input type="search" placeholder="Personel ara…" autocomplete="off"></span>
                    <label class="pick-all"><input type="checkbox"> Tümü</label>
                    <span class="pick-count"><b>0</b> seçili</span>
                </div>
                <div class="pick-list">
                    <?php foreach ($employees as $emp): $sel = $curUser === (int) $emp['id']; ?>
                        <label class="pick-item <?= $sel ? 'is-checked' : '' ?>" data-name="<?= esc($emp['full_name'], 'attr') ?>">
                            <input type="checkbox" name="user_id[]" value="<?= (int) $emp['id'] ?>" <?= $sel ? 'checked' : '' ?>>
                            <span class="pick-avatar"><?= esc(initials($emp['full_name'])) ?></span>
                            <span><?= esc($emp['full_name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                    <?php if (empty($employees)): ?><div class="pick-empty">Personel bulunmuyor. Önce personel ekle.</div><?php endif; ?>
                </div>
            </div>
            <span class="muted-sm" style="display:block;margin-top:6px">Bir veya daha fazla personel seç — her birine ayrı görev oluşturulur.</span>
        <?php endif; ?>
    </div>

    <div class="grid2">
        <div class="field"><label>Öncelik</label><select name="priority"><?php foreach ($prios as $k => $v): ?><option value="<?= $k ?>" <?= $curPrio === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Son tarih</label><input type="date" name="due_date" value="<?= $curDue ?>"></div>
    </div>
    <?php if ($isEdit): ?>
    <div class="field"><label>Durum</label><select name="status"><?php foreach ($stats as $k => $v): ?><option value="<?= $k ?>" <?= $task['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
    <?php endif; ?>
    <div class="form-actions"><button class="btn btn-primary">Kaydet</button><a class="btn btn-link" href="<?= site_url('admin/tasks') ?>">İptal</a></div>
</form>
<?php if (! $isEdit): ?>
<script>
(function () {
    document.querySelectorAll('.pick:not([data-ready])').forEach(function (pick) {
        pick.setAttribute('data-ready', '1');
        var items   = Array.prototype.slice.call(pick.querySelectorAll('.pick-item'));
        var search  = pick.querySelector('.pick-search input');
        var allBox  = pick.querySelector('.pick-all input');
        var countEl = pick.querySelector('.pick-count b');
        function visible(it) { return it.style.display !== 'none'; }
        function update() {
            var checked = 0, vis = 0, visChecked = 0;
            items.forEach(function (it) {
                var cb = it.querySelector('input[type=checkbox]');
                it.classList.toggle('is-checked', cb.checked);
                if (cb.checked) { checked++; }
                if (visible(it)) { vis++; if (cb.checked) { visChecked++; } }
            });
            if (countEl) { countEl.textContent = checked; }
            if (allBox) { allBox.checked = vis > 0 && visChecked === vis; allBox.indeterminate = visChecked > 0 && visChecked < vis; }
        }
        pick.addEventListener('change', function (e) { if (e.target && e.target.matches('.pick-item input')) { update(); } });
        if (allBox) {
            allBox.addEventListener('change', function () {
                items.forEach(function (it) { if (visible(it)) { it.querySelector('input[type=checkbox]').checked = allBox.checked; } });
                update();
            });
        }
        if (search) {
            search.addEventListener('input', function () {
                var q = search.value.toLocaleLowerCase('tr');
                items.forEach(function (it) {
                    var name = (it.getAttribute('data-name') || '').toLocaleLowerCase('tr');
                    it.style.display = name.indexOf(q) > -1 ? '' : 'none';
                });
                update();
            });
        }
        update();
    });
})();
</script>
<?php endif; ?>
