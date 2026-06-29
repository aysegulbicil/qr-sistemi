<?php
$isEdit      = $shift !== null;
$checkedDays = $isEdit ? array_map('strval', array_filter(explode(',', (string) ($shift['workdays'] ?? '')))) : ['1', '2', '3', '4', '5'];
$dayLabels   = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
?>
<form method="post" action="<?= $isEdit ? site_url('admin/shifts/' . $shift['id']) : site_url('admin/shifts') ?>">
    <?= csrf_field() ?>
    <div class="field"><label>Vardiya adı *</label><input type="text" name="name" value="<?= esc($isEdit ? $shift['name'] : old('name')) ?>" required autofocus></div>
    <div class="grid2">
        <div class="field"><label>Başlangıç *</label><input type="time" name="start_time" value="<?= esc($isEdit ? substr($shift['start_time'], 0, 5) : old('start_time')) ?>" required></div>
        <div class="field"><label>Bitiş *</label><input type="time" name="end_time" value="<?= esc($isEdit ? substr($shift['end_time'], 0, 5) : old('end_time')) ?>" required></div>
        <div class="field"><label>Giriş toleransı (dk)</label><input type="number" min="0" name="grace_in_minutes" value="<?= esc($isEdit ? $shift['grace_in_minutes'] : (old('grace_in_minutes') ?? 5)) ?>"></div>
        <div class="field"><label>Çıkış toleransı (dk)</label><input type="number" min="0" name="grace_out_minutes" value="<?= esc($isEdit ? $shift['grace_out_minutes'] : (old('grace_out_minutes') ?? 0)) ?>"></div>
    </div>
    <div class="field"><label>Çalışma günleri</label>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px">
            <?php foreach ($dayLabels as $num => $lbl): ?>
                <label class="check" style="background:var(--surface-2);padding:7px 11px;border-radius:9px;font-weight:600"><input type="checkbox" name="workdays[]" value="<?= $num ?>" <?= in_array((string) $num, $checkedDays, true) ? 'checked' : '' ?>> <?= $lbl ?></label>
            <?php endforeach; ?>
        </div>
    </div>
    <p class="muted" style="font-size:.84rem">Bitiş saati başlangıçtan küçükse vardiya otomatik olarak <strong>gece vardiyası</strong> sayılır.</p>
    <div class="form-actions"><button class="btn btn-primary">Kaydet</button><a class="btn btn-link" href="<?= site_url('admin/shifts') ?>">İptal</a></div>
</form>
