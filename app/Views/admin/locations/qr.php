<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $mode = qr_effective_mode($location['qr_mode']); ?>
<h1><?= esc($location['name']) ?>
    <span class="badge <?= $mode === 'dynamic' ? 'badge-blue' : 'badge-grey' ?>" style="vertical-align:middle"><?= $mode === 'dynamic' ? 'Dinamik' : 'Sabit' ?></span>
</h1>
<p class="muted" style="margin-top:-2px;margin-bottom:18px"><a href="<?= site_url('admin/locations') ?>">&larr; Lokasyonlar</a></p>
<div class="card pad-lg center">
    <div id="qr" class="qr-box">Yükleniyor…</div>
    <p class="muted" id="hint" style="margin:14px 0 4px"></p>
    <p class="muted">Kod: <code><?= esc($location['code']) ?></code></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.js"></script>
<script>
(function () {
    var tokenUrl = <?= json_encode(site_url('admin/locations/' . $location['id'] . '/token')) ?>;
    var mode     = <?= json_encode($mode) ?>;
    var box  = document.getElementById('qr');
    var hint = document.getElementById('hint');
    function render(url){ var qr=qrcode(0,'M'); qr.addData(url); qr.make(); box.innerHTML=qr.createImgTag(6,12); }
    function refresh(){
        fetch(tokenUrl,{headers:{'X-Requested-With':'fetch'}})
            .then(function(r){return r.json();})
            .then(function(d){ render(d.url); hint.textContent=(mode==='dynamic')?'Bu kod otomatik yenilenir. Telefonunla okut.':'Telefonunla okutarak giriş/çıkış yap.'; })
            .catch(function(){ box.textContent='QR yüklenemedi.'; });
    }
    refresh();
    if (mode === 'dynamic') { setInterval(refresh, 25000); }
})();
</script>
<?= $this->endSection() ?>
