<?php
$isEdit = $location !== null;
$v      = static fn (string $k) => esc($isEdit ? ($location[$k] ?? '') : old($k));
$mode   = qr_effective_mode($isEdit ? ($location['qr_mode'] ?? 'fixed') : (old('qr_mode') ?: 'fixed'));
$regen  = $regen ?? ['limit' => 0, 'used' => 0];
?>
<form method="post" action="<?= $isEdit ? site_url('admin/locations/' . $location['id']) : site_url('admin/locations') ?>">
    <?= csrf_field() ?>
    <div class="grid2">
        <div class="field"><label>Kod * <span class="hint">(QR adresinde; otomatik sadeleştirilir)</span></label><input type="text" name="code" value="<?= $v('code') ?>" placeholder="main-gate" required></div>
        <div class="field"><label>Ad *</label><input type="text" name="name" value="<?= $v('name') ?>" placeholder="Ana Giriş" required></div>
    </div>
    <div class="field"><label>QR modu</label>
        <?php if (qr_dynamic_enabled()): ?>
        <select name="qr_mode">
            <option value="fixed" <?= $mode === 'fixed' ? 'selected' : '' ?>>Sabit (tek basılı kod)</option>
            <option value="dynamic" <?= $mode === 'dynamic' ? 'selected' : '' ?>>Dinamik (30 sn'de yenilenen, ekran gerekir)</option>
        </select>
        <?php else: ?>
        <input type="hidden" name="qr_mode" value="fixed">
        <input type="text" value="Sabit (tek basılı kod)" disabled>
        <p class="hint" style="margin-top:6px">Bu kurulum yalnızca Sabit QR içerir.</p>
        <?php endif; ?>
    </div>

    <?php if ($isEdit && $mode === 'fixed' && $regen['limit'] > 0): ?>
    <p class="hint" style="margin:-6px 0 16px">Sabit QR yenileme hakkı: <strong><?= max(0, $regen['limit'] - $regen['used']) ?></strong> / <?= $regen['limit'] ?> kaldı. Kodu değiştirmek bir yenileme sayılır.</p>
    <?php endif; ?>
    <div class="form-section"><div class="sec-title">Konum doğrulama (GPS)</div></div>
    <div class="grid2">
        <div class="field"><label>Enlem (lat)</label><input type="text" id="geo_lat" name="geo_lat" value="<?= $v('geo_lat') ?>" placeholder="41.0082000"></div>
        <div class="field"><label>Boylam (lng)</label><input type="text" id="geo_lng" name="geo_lng" value="<?= $v('geo_lng') ?>" placeholder="28.9784000"></div>
        <div class="field"><label>İzin verilen yarıçap (m)</label><input type="number" min="10" name="geo_radius_m" value="<?= esc($isEdit ? ($location['geo_radius_m'] ?? '') : old('geo_radius_m')) ?>" placeholder="150"></div>
    </div>
    <button type="button" class="btn btn-outline btn-sm" id="getloc">Bu cihazın konumunu al</button>
    <div class="field" style="margin-top:14px"><label class="check"><input type="checkbox" name="enforce_geo" value="1" <?= ($isEdit && $location['enforce_geo']) ? 'checked' : '' ?>> Konum <strong>zorunlu</strong> olsun (alan dışı giriş/çıkışı engelle)</label></div>

    <?php if ($isEdit): ?>
        <div class="field"><label class="check"><input type="checkbox" name="is_active" value="1" <?= $location['is_active'] ? 'checked' : '' ?>> Aktif</label></div>
    <?php endif; ?>

    <div class="form-actions"><button class="btn btn-primary">Kaydet</button><a class="btn btn-link" href="<?= site_url('admin/locations') ?>">İptal</a></div>
</form>
<script>
(function () {
    var btn = document.getElementById('getloc');
    if (! btn) { return; }
    btn.addEventListener('click', function () {
        if (! navigator.geolocation) { btn.textContent = 'Tarayıcı konumu desteklemiyor'; return; }
        btn.textContent = 'Konum alınıyor...';
        navigator.geolocation.getCurrentPosition(function (p) {
            document.getElementById('geo_lat').value = p.coords.latitude.toFixed(7);
            document.getElementById('geo_lng').value = p.coords.longitude.toFixed(7);
            btn.textContent = 'Konum alındı';
        }, function () { btn.textContent = 'Konum alınamadı (izin gerekli)'; });
    });
})();
</script>
