<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $isEdit = $department !== null; ?>
<h1><?= $isEdit ? 'Departmanı düzenle' : 'Yeni departman' ?></h1>
<p class="back-link"><a href="<?= site_url('admin/departments') ?>">&larr; Departmanlar</a></p>
<div class="card pad-lg" style="max-width:520px">
    <?= view('admin/departments/_form', ['department' => $department]) ?>
</div>
<?= $this->endSection() ?>
