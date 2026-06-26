<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<div class="card pad-lg center">
    <h1>Okutma sorunu</h1>
    <p class="muted"><?= esc($message) ?></p>
    <a class="btn btn-outline" href="<?= site_url('dashboard') ?>">Panele dön</a>
</div>
<?= $this->endSection() ?>
