<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<h1>Talepler</h1>
<p class="muted" style="margin-top:-2px;margin-bottom:18px">Bekleyen izin ve avans talepleri</p>

<div class="card">
    <div class="card-head"><h2>İzin talepleri <span class="count-pill"><?= count($leaves) ?></span></h2></div>
    <?php if (empty($leaves)): ?>
        <p class="empty">Bekleyen izin talebi yok.</p>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Personel</th><th>Tür</th><th>Tarih</th><th>Gün</th><th>Açıklama</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($leaves as $l): ?>
                <tr>
                    <td><strong><?= esc($l['full_name']) ?></strong></td>
                    <td><?= esc($l['type_name'] ?: '—') ?></td>
                    <td><?= esc(date('d.m.Y', strtotime($l['start_date']))) ?> – <?= esc(date('d.m.Y', strtotime($l['end_date']))) ?></td>
                    <td><?= (float) $l['days'] ?></td>
                    <td class="muted"><?= esc($l['reason'] ?: '—') ?></td>
                    <td style="text-align:right;white-space:nowrap">
                        <form method="post" action="<?= site_url('admin/requests/leave/' . $l['id'] . '/approve') ?>" style="display:inline"><?= csrf_field() ?><button class="btn btn-primary btn-sm">Onayla</button></form>
                        <form method="post" action="<?= site_url('admin/requests/leave/' . $l['id'] . '/reject') ?>" style="display:inline;margin-left:6px"><?= csrf_field() ?><button class="btn btn-danger-soft btn-sm">Reddet</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-head"><h2>Avans talepleri <span class="count-pill"><?= count($advances) ?></span></h2></div>
    <?php if (empty($advances)): ?>
        <p class="empty">Bekleyen avans talebi yok.</p>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Personel</th><th>Tutar</th><th>Dönem</th><th>Açıklama</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($advances as $a): ?>
                <tr>
                    <td><strong><?= esc($a['full_name']) ?></strong></td>
                    <td><?= esc(money((float) $a['amount'])) ?></td>
                    <td><?= esc(sprintf('%02d/%04d', $a['period_month'], $a['period_year'])) ?></td>
                    <td class="muted"><?= esc($a['reason'] ?: '—') ?></td>
                    <td style="text-align:right;white-space:nowrap">
                        <form method="post" action="<?= site_url('admin/requests/advance/' . $a['id'] . '/approve') ?>" style="display:inline"><?= csrf_field() ?><button class="btn btn-primary btn-sm">Onayla</button></form>
                        <form method="post" action="<?= site_url('admin/requests/advance/' . $a['id'] . '/reject') ?>" style="display:inline;margin-left:6px"><?= csrf_field() ?><button class="btn btn-danger-soft btn-sm">Reddet</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
