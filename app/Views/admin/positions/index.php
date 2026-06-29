<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<div class="card-head">
    <h1>Pozisyonlar <span class="count-pill"><?= count($positions) ?></span></h1>
    <a class="btn btn-primary" href="<?= site_url('admin/positions/new') ?>" data-modal data-modal-title="Yeni pozisyon">+ Yeni pozisyon</a>
</div>
<div class="card">
    <?php if (empty($positions)): ?>
        <?= view('partials/empty', ['title' => 'Henüz pozisyon yok', 'message' => 'İlk pozisyonunu ekleyerek başla.', 'actionUrl' => site_url('admin/positions/new'), 'actionLabel' => '+ Yeni pozisyon', 'actionModal' => true, 'actionTitle' => 'Yeni pozisyon']) ?>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Pozisyon</th><th>Departman</th><th>Açıklama</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($positions as $p): ?>
                <tr>
                    <td><strong><?= esc($p['name']) ?></strong></td>
                    <td><?= $p['department_name'] ? esc($p['department_name']) : '<span class="muted">—</span>' ?></td>
                    <td class="muted"><?= esc($p['description'] ?: '—') ?></td>
                    <td class="row-actions">
                        <a class="btn btn-warning-soft btn-sm" href="<?= site_url('admin/positions/' . $p['id'] . '/edit') ?>" data-modal data-modal-title="Pozisyonu düzenle">Düzenle</a>
                        <form method="post" action="<?= site_url('admin/positions/' . $p['id'] . '/delete') ?>" onsubmit="return confirm('Bu pozisyon silinsin mi?')">
                            <?= csrf_field() ?>
                            <button class="btn btn-danger-soft btn-sm" type="submit">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
