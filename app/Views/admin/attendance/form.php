<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $isEdit = $log !== null; ?>
<h1><?= $isEdit ? 'Devam kaydını düzenle' : 'Elle devam kaydı' ?></h1>
<p class="back-link"><a href="<?= site_url('admin/attendance') ?>">&larr; Devam Kayıtları</a></p>
<div class="card pad-lg" style="max-width:560px">
    <?= view('admin/attendance/_form', [
        'log'         => $log,
        'employees'   => $employees,
        'locations'   => $locations,
        'prefillUser' => $prefillUser ?? 0,
        'prefillType' => $prefillType ?? 'in',
    ]) ?>
</div>
<?= $this->endSection() ?>
