<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $isEdit = $position !== null; ?>
<h1><?= $isEdit ? 'Pozisyonu düzenle' : 'Yeni pozisyon' ?></h1>
<p class="muted" style="margin-top:-2px;margin-bottom:18px"><a href="<?= site_url('admin/positions') ?>">&larr; Pozisyonlar</a></p>
<div class="card pad-lg" style="max-width:520px">
    <form method="post" action="<?= $isEdit ? site_url('admin/positions/' . $position['id']) : site_url('admin/positions') ?>">
        <?= csrf_field() ?>
        <div class="field"><label>Ad</label><input type="text" name="name" value="<?= esc($position['name'] ?? old('name')) ?>" required autofocus></div>
        <div class="field"><label>Departman</label>
            <select name="department_id">
                <option value="">— yok —</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= (string) ($position['department_id'] ?? old('department_id')) === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label>Açıklama</label><input type="text" name="description" value="<?= esc($position['description'] ?? old('description')) ?>"></div>
        <div class="form-actions">
            <button class="btn btn-primary">Kaydet</button>
            <a class="btn btn-link" href="<?= site_url('admin/positions') ?>">İptal</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
