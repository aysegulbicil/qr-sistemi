<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $mode = qr_effective_mode($location['qr_mode']); ?>
<h1><?= esc($location['name']) ?>
    <span class="badge <?= $mode === 'dynamic' ? 'badge-blue' : 'badge-grey' ?>" style="vertical-align:middle"><?= $mode === 'dynamic' ? 'Dinamik' : 'Sabit' ?></span>
</h1>
<p class="back-link"><a href="<?= site_url('admin/locations') ?>">&larr; Lokasyonlar</a></p>

<?php if ($mode === 'dynamic'): ?>
    <?php $kioskUrl = site_url('display/' . $location['id'] . '/' . $location['token_secret']); ?>
    <div class="card pad-lg" style="max-width:640px">
        <h2 class="m0">Kapı ekranı (kiosk)</h2>
        <p class="muted">Bu linki <strong>kapı cihazında</strong> aç. Panel girişi gerektirmez, yönetici oturumu taşımaz; panele dönmek için tekrar giriş gerekir. QR otomatik yenilenir, ekranı açık bırakman yeterli.</p>
        <div class="filters" style="margin:14px 0">
            <input type="text" id="kioskUrl" value="<?= esc($kioskUrl) ?>" readonly style="min-width:300px">
            <button class="btn btn-outline btn-sm" type="button" onclick="copyKiosk()">Kopyala</button>
            <a class="btn btn-primary btn-sm" href="<?= esc($kioskUrl) ?>" target="_blank" rel="noopener">Kiosk ekranını aç</a>
        </div>
        <p class="muted-sm">Kod: <code><?= esc($location['code']) ?></code></p>
    </div>
    <script>
    function copyKiosk(){var i=document.getElementById('kioskUrl');i.select();i.setSelectionRange(0,99999);try{navigator.clipboard.writeText(i.value);}catch(e){try{document.execCommand('copy');}catch(_){}}}
    </script>
<?php else: ?>
    <div class="card pad-lg center" style="max-width:640px">
        <div id="qr" class="qr-box">Yükleniyor…</div>
        <p class="muted" id="hint" style="margin:14px 0 4px"></p>
        <p class="muted">Kod: <code><?= esc($location['code']) ?></code></p>
        <p><button class="btn btn-outline btn-sm" type="button" onclick="window.print()">Yazdır</button></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.js"></script>
    <script>
    (function () {
        var tokenUrl = <?= json_encode(site_url('admin/locations/' . $location['id'] . '/token')) ?>;
        var box  = document.getElementById('qr'), hint = document.getElementById('hint');
        function render(url){ var qr=qrcode(0,'M'); qr.addData(url); qr.make(); box.innerHTML=qr.createImgTag(6,12); }
        fetch(tokenUrl,{headers:{'X-Requested-With':'fetch'}}).then(function(r){return r.json();})
            .then(function(d){ render(d.url); hint.textContent='Telefonla okutarak giriş/çıkış yap. Tek sefer bas, görünür yere as.'; })
            .catch(function(){ box.textContent='QR yüklenemedi.'; });
    })();
    </script>
<?php endif; ?>
<?= $this->endSection() ?>
