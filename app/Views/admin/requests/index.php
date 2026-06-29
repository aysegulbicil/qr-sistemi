<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php
$reqBadge = static function (?string $s): string {
    return match ($s) {
        'approved' => '<span class="badge badge-green">Onaylandı</span>',
        'rejected' => '<span class="badge badge-red">Reddedildi</span>',
        default    => '<span class="badge badge-amber">Bekliyor</span>',
    };
};
$leavePending   = count(array_filter($leaves, static fn ($l) => $l['status'] === 'pending'));
$advancePending = count(array_filter($advances, static fn ($a) => $a['status'] === 'pending'));
?>
<h1>Talepler</h1>
<p class="page-sub">İzin ve avans talepleri — bekleyen, onaylanan ve reddedilenler</p>

<div class="card">
    <div class="card-head">
        <h2>İzin talepleri <span class="count-pill"><?= count($leaves) ?></span></h2>
        <?php if ($leavePending): ?><span class="badge badge-amber"><?= $leavePending ?> bekliyor</span><?php endif; ?>
    </div>
    <?php if (empty($leaves)): ?>
        <p class="empty">Henüz izin talebi yok.</p>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Personel</th><th>Tür</th><th>Tarih</th><th>Gün</th><th>Açıklama</th><th>Durum</th><th>İşlem</th></tr></thead>
            <tbody>
            <?php foreach ($leaves as $l): ?>
                <tr>
                    <td><strong><?= esc($l['full_name']) ?></strong></td>
                    <td><?= esc($l['type_name'] ?: '—') ?></td>
                    <td><?= esc(date('d.m.Y', strtotime($l['start_date']))) ?> – <?= esc(date('d.m.Y', strtotime($l['end_date']))) ?></td>
                    <td><?= (float) $l['days'] ?></td>
                    <td class="muted"><?= esc($l['reason'] ?: '—') ?></td>
                    <td><?= $reqBadge($l['status']) ?></td>
                    <td class="row-actions">
                        <?php if ($l['status'] === 'pending'): ?>
                            <form method="post" action="<?= site_url('admin/requests/leave/' . $l['id'] . '/approve') ?>"><?= csrf_field() ?><button class="btn btn-primary btn-sm">Onayla</button></form>
                            <form method="post" action="<?= site_url('admin/requests/leave/' . $l['id'] . '/reject') ?>"><?= csrf_field() ?><button class="btn btn-danger-soft btn-sm">Reddet</button></form>
                        <?php else: ?>
                            <span class="muted-sm"><?= $l['decided_at'] ? esc(date('d.m.Y', strtotime($l['decided_at']))) : '—' ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-head">
        <h2>Avans talepleri <span class="count-pill"><?= count($advances) ?></span></h2>
        <?php if ($advancePending): ?><span class="badge badge-amber"><?= $advancePending ?> bekliyor</span><?php endif; ?>
    </div>
    <?php if (empty($advances)): ?>
        <p class="empty">Henüz avans talebi yok.</p>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Personel</th><th>Tutar</th><th>Dönem</th><th>Açıklama</th><th>Durum</th><th>İşlem</th></tr></thead>
            <tbody>
            <?php foreach ($advances as $a): ?>
                <tr>
                    <td><strong><?= esc($a['full_name']) ?></strong></td>
                    <td><?= esc(money((float) $a['amount'])) ?></td>
                    <td><?= esc(sprintf('%02d/%04d', $a['period_month'], $a['period_year'])) ?></td>
                    <td class="muted"><?= esc($a['reason'] ?: '—') ?></td>
                    <td><?= $reqBadge($a['status']) ?></td>
                    <td class="row-actions">
                        <?php if ($a['status'] === 'pending'): ?>
                            <form method="post" action="<?= site_url('admin/requests/advance/' . $a['id'] . '/approve') ?>"><?= csrf_field() ?><button class="btn btn-primary btn-sm">Onayla</button></form>
                            <form method="post" action="<?= site_url('admin/requests/advance/' . $a['id'] . '/reject') ?>"><?= csrf_field() ?><button class="btn btn-danger-soft btn-sm">Reddet</button></form>
                        <?php else: ?>
                            <span class="muted-sm"><?= $a['decided_at'] ? esc(date('d.m.Y', strtotime($a['decided_at']))) : '—' ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
