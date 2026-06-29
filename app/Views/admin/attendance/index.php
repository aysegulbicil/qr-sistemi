<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php
$dirBadge = static fn (string $t): string => $t === 'in'
    ? '<span class="badge badge-green">Giriş</span>'
    : ($t === 'out' ? '<span class="badge badge-grey">Çıkış</span>' : '—');
?>
<div class="card-head">
    <h1>Devam Kayıtları <span class="count-pill"><?= count($logs) ?></span></h1>
    <div class="btn-group">
        <a class="btn btn-primary" href="<?= site_url('admin/attendance/new') ?>" data-modal data-modal-title="Elle devam kaydı">+ Elle kayıt ekle</a>
    </div>
</div>

<?php if (! empty($openLogs)): ?>
<div class="card pad-lg" style="border-left:3px solid #e0a32e;margin-bottom:16px">
    <h3 class="m0">Açık kayıtlar — çıkış yapılmamış <span class="count-pill"><?= count($openLogs) ?></span></h3>
    <p class="muted" style="margin:4px 0 12px">Son hareketi "giriş" olan personel. Unutulan çıkışları buradan tamamlayabilirsin.</p>
    <div class="table-scroll">
        <table class="data no-dt">
            <thead><tr><th>Personel</th><th>Giriş zamanı</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($openLogs as $o): ?>
                <tr>
                    <td><strong><?= esc($o['full_name'] ?: '—') ?></strong></td>
                    <td class="muted"><?= esc(date('d.m.Y H:i', strtotime($o['event_at']))) ?></td>
                    <td class="row-actions">
                        <a class="btn btn-primary btn-sm" href="<?= site_url('admin/attendance/new') . '?user_id=' . (int) $o['user_id'] . '&type=out' ?>" data-modal data-modal-title="Çıkış ekle">Çıkış ekle</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <?php if (empty($logs)): ?>
        <?= view('partials/empty', ['title' => 'Kayıt yok', 'message' => 'Henüz giriş/çıkış kaydı yok.']) ?>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Tarih / Saat</th><th>Personel</th><th>Yön</th><th>Kaynak</th><th>Lokasyon</th><th>Not</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
                <tr>
                    <td><?= esc(date('d.m.Y H:i', strtotime($l['event_at']))) ?></td>
                    <td><?= esc($l['full_name'] ?: '—') ?></td>
                    <td><?= $dirBadge((string) $l['type']) ?></td>
                    <td><?= ($l['source'] ?? '') === 'manual' ? '<span class="badge badge-amber">Manuel</span>' : '<span class="badge badge-blue">QR</span>' ?></td>
                    <td class="muted"><?= esc($l['location_name'] ?: '—') ?></td>
                    <td class="muted"><?= esc($l['note'] ?: '') ?></td>
                    <td class="row-actions">
                        <a class="btn btn-warning-soft btn-sm" href="<?= site_url('admin/attendance/' . $l['id'] . '/edit') ?>" data-modal data-modal-title="Kaydı düzenle">Düzenle</a>
                        <form method="post" action="<?= site_url('admin/attendance/' . $l['id'] . '/delete') ?>" data-confirm="Bu kayıt silinsin mi? Puantajı etkileyebilir."><?= csrf_field() ?><button class="btn btn-danger-soft btn-sm">Sil</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
