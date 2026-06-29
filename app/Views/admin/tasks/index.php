<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php
$today   = date('Y-m-d');
$prLabel = ['low' => 'Düşük', 'normal' => 'Normal', 'high' => 'Yüksek'];
$prBadge = ['low' => 'badge-grey', 'normal' => 'badge-blue', 'high' => 'badge-red'];
$stLabel = ['pending' => 'Bekliyor', 'in_progress' => 'Yapılıyor', 'done' => 'Tamamlandı', 'cancelled' => 'İptal'];
$stBadge = ['pending' => 'badge-amber', 'in_progress' => 'badge-blue', 'done' => 'badge-green', 'cancelled' => 'badge-grey'];
?>
<div class="card-head">
    <h1>Görevler <span class="count-pill"><?= count($tasks) ?></span></h1>
    <div class="btn-group">
        <a class="btn btn-primary" href="<?= site_url('admin/tasks/new') ?>" data-modal data-modal-title="Görev ata" data-modal-size="lg">+ Görev ata</a>
    </div>
</div>

<form method="get" action="<?= site_url('admin/tasks') ?>" class="filters">
    <select name="user_id" onchange="this.form.submit()">
        <option value="">Tüm personel</option>
        <?php foreach ($employees as $emp): ?>
            <option value="<?= (int) $emp['id'] ?>" <?= $fUser === (int) $emp['id'] ? 'selected' : '' ?>><?= esc($emp['full_name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status" onchange="this.form.submit()">
        <option value="">Tüm durumlar</option>
        <?php foreach ($stLabel as $k => $v): ?>
            <option value="<?= $k ?>" <?= $fStatus === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
    </select>
    <?php if ($fUser || $fStatus !== ''): ?><a class="btn btn-link btn-sm" href="<?= site_url('admin/tasks') ?>">Filtreyi temizle</a><?php endif; ?>
</form>

<div class="card">
    <?php if (empty($tasks)): ?>
        <?= view('partials/empty', ['title' => 'Görev yok', 'message' => 'İlk görevini atayarak başla.', 'actionUrl' => site_url('admin/tasks/new'), 'actionLabel' => '+ Görev ata', 'actionModal' => true, 'actionTitle' => 'Görev ata', 'actionSize' => 'lg']) ?>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Görev</th><th>Personel</th><th>Öncelik</th><th>Durum</th><th>Son tarih</th><th>Atayan</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php foreach ($tasks as $t): ?>
                <?php $overdue = $t['due_date'] && $t['due_date'] < $today && ! in_array($t['status'], ['done', 'cancelled'], true); ?>
                <tr>
                    <td><strong><?= esc($t['title']) ?></strong><?php if (! empty($t['description'])): ?><br><span class="muted-sm"><?= esc(mb_strimwidth((string) $t['description'], 0, 80, '…')) ?></span><?php endif; ?></td>
                    <td><?= esc($t['assignee_name'] ?: '—') ?></td>
                    <td><span class="badge <?= $prBadge[$t['priority']] ?? 'badge-grey' ?>"><?= $prLabel[$t['priority']] ?? esc($t['priority']) ?></span></td>
                    <td><span class="badge <?= $stBadge[$t['status']] ?? 'badge-grey' ?>"><?= $stLabel[$t['status']] ?? esc($t['status']) ?></span></td>
                    <td><?= $t['due_date'] ? esc(date('d.m.Y', strtotime($t['due_date']))) : '—' ?><?php if ($overdue): ?> <span class="badge badge-red">Gecikti</span><?php endif; ?></td>
                    <td class="muted"><?= esc($t['assigner_name'] ?: '—') ?></td>
                    <td class="row-actions">
                        <a class="btn btn-warning-soft btn-sm" href="<?= site_url('admin/tasks/' . $t['id'] . '/edit') ?>" data-modal data-modal-title="Görevi düzenle" data-modal-size="lg">Düzenle</a>
                        <form method="post" action="<?= site_url('admin/tasks/' . $t['id'] . '/delete') ?>" data-confirm="Bu görev silinsin mi?"><?= csrf_field() ?><button class="btn btn-danger-soft btn-sm">Sil</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
