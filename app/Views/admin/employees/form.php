<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $isEdit = $employee !== null; ?>
<h1><?= $isEdit ? 'Personeli düzenle' : 'Yeni personel' ?></h1>
<p class="back-link"><a href="<?= site_url('admin/employees') ?>">&larr; Personeller</a></p>
<div class="card pad-lg" style="max-width:720px">
    <?= view('admin/employees/_form', ['employee' => $employee, 'departments' => $departments, 'positions' => $positions, 'shifts' => $shifts]) ?>
</div>
<?= $this->endSection() ?>
