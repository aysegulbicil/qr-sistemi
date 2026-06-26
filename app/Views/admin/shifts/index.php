<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $dayLabels = [1 => 'Pzt', 2 => 'Sal', 3 => 'Çar', 4 => 'Per', 5 => 'Cum', 6 => 'Cmt', 7 => 'Paz']; ?>
<div class="card-head">
    <h1>Vardiyalar <span class="count-pill"><?= count($shifts) ?></span></h1>
    <div style="display:flex;gap:8px">
        <a class="btn btn-outline" href="<?= site_url('admin/shift-schedule') ?>">Haftalık plan</a>
        <a class="btn btn-primary" href="<?= site_url('admin/shifts/new') ?>">+ Yeni vardiya</a>
    </div>
</div>
<div class="card">
    <?php if (empty($shifts)): ?>
        <?= view('partials/empty', ['title' => 'Henüz vardiya yok', 'message' => 'İlk vardiyanı oluşturarak başla.', 'actionUrl' => site_url('admin/shifts/new'), 'actionLabel' => '+ Yeni vardiya']) ?>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Vardiya</th><th>Saatler</th><th>Tolerans</th><th>Çalışma günleri</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($shifts as $s): ?>
                <tr>
                    <td><strong><?= esc($s['name']) ?></strong> <?php if ($s['crosses_midnight']): ?><span class="badge badge-blue">Gece</span><?php endif; ?></td>
                    <td><?= esc(substr($s['start_time'], 0, 5)) ?> – <?= esc(substr($s['end_time'], 0, 5)) ?></td>
                    <td class="muted">+<?= (int) $s['grace_in_minutes'] ?> / +<?= (int) $s['grace_out_minutes'] ?> dk</td>
                    <td>
                        <?php foreach (array_filter(explode(',', (string) ($s['workdays'] ?? ''))) as $d): ?><span class="badge badge-grey" style="margin-right:3px"><?= $dayLabels[(int) $d] ?? esc($d) ?></span><?php endforeach; ?>
                    </td>
                    <td style="text-align:right;white-space:nowrap">
                        <a href="<?= site_url('admin/shifts/' . $s['id'] . '/edit') ?>">Düzenle</a>
                        <form method="post" action="<?= site_url('admin/shifts/' . $s['id'] . '/delete') ?>" style="display:inline;margin-left:8px" onsubmit="return confirm('Bu vardiya silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger-soft btn-sm">Sil</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
