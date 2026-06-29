<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $isEdit = $shift !== null; ?>
<h1><?= $isEdit ? 'Vardiyayı düzenle' : 'Yeni vardiya' ?></h1>
<p class="back-link"><a href="<?= site_url('admin/shifts') ?>">&larr; Vardiyalar</a></p>
<div class="card pad-lg" style="max-width:560px">
    <?= view('admin/shifts/_form', ['shift' => $shift]) ?>
</div>
<?= $this->endSection() ?>
