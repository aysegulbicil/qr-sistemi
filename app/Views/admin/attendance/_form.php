<?php
$isEdit  = $log !== null;
$curUser = $isEdit ? (int) $log['user_id'] : (int) ($prefillUser ?? 0);
$curType = $isEdit ? (string) $log['type'] : (string) ($prefillType ?? 'in');
$curWhen = $isEdit ? date('Y-m-d\TH:i', strtotime($log['event_at'])) : date('Y-m-d\TH:i');
$curLoc  = $isEdit ? (int) ($log['location_id'] ?? 0) : 0;
$curNote = $isEdit ? (string) ($log['note'] ?? '') : '';
?>
<form method="post" action="<?= $isEdit ? site_url('admin/attendance/' . $log['id']) : site_url('admin/attendance') ?>">
    <?= csrf_field() ?>
    <div class="field"><label>Personel *</label>
        <select name="user_id" required>
            <option value="">— Seç —</option>
            <?php foreach ($employees as $emp): ?>
                <option value="<?= (int) $emp['id'] ?>" <?= $curUser === (int) $emp['id'] ? 'selected' : '' ?>><?= esc($emp['full_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="grid2">
        <div class="field"><label>Yön *</label>
            <select name="type" required>
                <option value="in" <?= $curType === 'in' ? 'selected' : '' ?>>Giriş</option>
                <option value="out" <?= $curType === 'out' ? 'selected' : '' ?>>Çıkış</option>
            </select>
        </div>
        <div class="field"><label>Tarih / Saat *</label><input type="datetime-local" name="event_at" value="<?= esc($curWhen) ?>" required></div>
    </div>
    <div class="field"><label>Lokasyon</label>
        <select name="location_id">
            <option value="">— (isteğe bağlı) —</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?= (int) $loc['id'] ?>" <?= $curLoc === (int) $loc['id'] ? 'selected' : '' ?>><?= esc($loc['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field"><label>Not</label><input type="text" name="note" value="<?= esc($curNote) ?>" placeholder="örn. unutulan çıkış elle eklendi" maxlength="255"></div>
    <div class="form-actions"><button class="btn btn-primary">Kaydet</button><a class="btn btn-link" href="<?= site_url('admin/attendance') ?>">İptal</a></div>
</form>
