<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $isEdit = $location !== null; ?>
<h1><?= $isEdit ? 'Lokasyonu düzenle' : 'Yeni lokasyon' ?></h1>
<p class="back-link"><a href="<?= site_url('admin/locations') ?>">&larr; Lokasyonlar</a></p>
<div class="card pad-lg" style="max-width:620px">
    <?= view('admin/locations/_form', ['location' => $location, 'regen' => $regen ?? ['limit' => 0, 'used' => 0]]) ?>
</div>
<?= $this->endSection() ?>
