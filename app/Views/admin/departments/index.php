<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<div class="card-head">
    <h1>Departmanlar <span class="count-pill"><?= count($departments) ?></span></h1>
    <a class="btn btn-primary" href="<?= site_url('admin/departments/new') ?>">+ Yeni departman</a>
</div>
<div class="card">
    <?php if (empty($departments)): ?>
        <?= view('partials/empty', ['title' => 'Henüz departman yok', 'message' => 'İlk departmanını ekleyerek başla.', 'actionUrl' => site_url('admin/departments/new'), 'actionLabel' => '+ Yeni departman']) ?>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Departman</th><th>Açıklama</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($departments as $d): ?>
                <tr>
                    <td><strong><?= esc($d['name']) ?></strong></td>
                    <td class="muted"><?= esc($d['description'] ?: '—') ?></td>
                    <td class="row-actions">
                        <a href="<?= site_url('admin/departments/' . $d['id'] . '/edit') ?>">Düzenle</a>
                        <form method="post" action="<?= site_url('admin/departments/' . $d['id'] . '/delete') ?>" onsubmit="return confirm('Bu departman silinsin mi?')">
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
