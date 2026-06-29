<?php
/**
 * Menüsüz, sade layout — QR/işlem akışı için (panel dışı).
 * Sidebar/topbar yok; yalnızca ortalanmış içerik + flash bildirim.
 */
$title = $title ?? 'Giriş / Çıkış';
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
<div class="toasts" id="toasts">
    <?php if ($m = session()->getFlashdata('message')): ?>
        <div class="toast toast-success"><span class="tdot"></span><span><?= esc($m) ?></span><button class="x" type="button" onclick="this.parentElement.remove()">&times;</button></div>
    <?php endif; ?>
    <?php if ($e = session()->getFlashdata('error')): ?>
        <div class="toast toast-error"><span class="tdot"></span><span><?= esc($e) ?></span><button class="x" type="button" onclick="this.parentElement.remove()">&times;</button></div>
    <?php endif; ?>
</div>
<div class="auth-wrap"><div class="auth-card"><?= $this->renderSection('content') ?></div></div>
<script>
(function(){document.querySelectorAll('#toasts .toast').forEach(function(t){setTimeout(function(){t.style.transition='opacity .3s, transform .3s';t.style.opacity='0';t.style.transform='translateX(18px)';setTimeout(function(){t.remove();},320);},4200);});})();
</script>
</body>
</html>
