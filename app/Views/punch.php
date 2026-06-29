<?= $this->extend('layout/bare') ?>
<?= $this->section('content') ?>
<div class="card pad-lg center">
    <div class="empty-state" style="padding:30px 16px">
        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M8 3v4"/><path d="M16 3v4"/></svg></div>
        <h3 class="m0">Önce QR okut</h3>
        <p class="muted m0" style="margin-top:6px">Panele girmek ve giriş/çıkış yapmak için kapıdaki <strong>QR kodunu</strong> telefonunla okutman gerekiyor.</p>
    </div>
</div>
<p style="text-align:center;margin-top:14px;font-size:.86rem"><a class="muted" href="<?= site_url('logout') ?>">Çıkış yap</a></p>
<?= $this->endSection() ?>
