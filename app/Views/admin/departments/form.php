<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $isEdit = $department !== null; ?>
<h1><?= $isEdit ? 'Departmanı düzenle' : 'Yeni departman' ?></h1>
<p class="back-link"><a href="<?= site_url('admin/departments') ?>">&larr; Departmanlar</a></p>
<div class="card pad-lg" style="max-width:520px">
    <form method="post" action="<?= $isEdit ? site_url('admin/departments/' . $department['id']) : site_url('admin/departments') ?>">
        <?= csrf_field() ?>
        <div class="field"><label>Ad</label><input type="text" name="name" value="<?= esc($department['name'] ?? old('name')) ?>" required autofocus></div>
        <div class="field"><label>Açıklama</label><input type="text" name="description" value="<?= esc($department['description'] ?? old('description')) ?>"></div>
        <div class="form-actions">
            <button class="btn btn-primary">Kaydet</button>
            <a class="btn btn-link" href="<?= site_url('admin/departments') ?>">İptal</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
