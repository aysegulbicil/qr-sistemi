<?php $isDynamic = $mode === 'dynamic'; ?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($location['name']) ?> — QR</title>
<style>
  *{box-sizing:border-box} html,body{margin:0;height:100%}
  body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:#0f1729;color:#fff;display:grid;place-items:center;min-height:100vh;padding:24px}
  .wrap{text-align:center;max-width:560px}
  .name{font-size:2rem;font-weight:800;letter-spacing:-.02em;margin:0 0 6px}
  .sub{color:#94a3b8;margin:0 0 26px;font-size:1.05rem}
  .qrbox{background:#fff;display:inline-block;padding:20px;border-radius:22px;box-shadow:0 28px 70px -22px rgba(0,0,0,.65)}
  .qrbox img{display:block;width:300px;height:300px;image-rendering:pixelated}
  .qrbox #qr{min-width:300px;min-height:300px;display:grid;place-items:center;color:#475569}
  .hint{margin:22px 0 0;color:#cbd5e1;font-size:1rem;min-height:1.2em}
  .pill{display:inline-block;margin-top:12px;font-size:.8rem;color:#93c5fd;border:1px solid #1e3a8a;border-radius:999px;padding:5px 13px}
</style>
</head>
<body>
<div class="wrap">
  <h1 class="name"><?= esc($location['name']) ?></h1>
  <p class="sub">Giriş / çıkış için telefonunla okut</p>
  <div class="qrbox"><div id="qr">Yükleniyor…</div></div>
  <p class="hint" id="hint"></p>
  <?php if ($isDynamic): ?><span class="pill">Kod otomatik yenilenir</span><?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.js"></script>
<script>
(function(){
  var tokenUrl = <?= json_encode($tokenUrl) ?>;
  var isDynamic = <?= json_encode($isDynamic) ?>;
  var box = document.getElementById('qr'), hint = document.getElementById('hint');
  function render(url){ var qr = qrcode(0,'M'); qr.addData(url); qr.make(); box.innerHTML = qr.createImgTag(7,0); }
  function refresh(){
    fetch(tokenUrl,{headers:{'X-Requested-With':'fetch'},cache:'no-store'})
      .then(function(r){ return r.json(); })
      .then(function(d){ if (d && d.url) { render(d.url); hint.textContent=''; } else { hint.textContent='QR alınamadı.'; } })
      .catch(function(){ hint.textContent='Bağlantı yok, yeniden denenecek…'; });
  }
  refresh();
  if (isDynamic) { setInterval(refresh, 20000); }
})();
</script>
</body>
</html>
